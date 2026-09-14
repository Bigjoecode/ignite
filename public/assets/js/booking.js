/* Booking popup — multi-step consultation request (app/views/partials/booking.php).
   Opens from [data-book] elements and links to #ig-consult, or /?book=1[&office=slug].
   Posts to /book; on success keeps a short summary for the thank-you page and redirects. */
(function () {
  var root = document.getElementById('igBook');
  if (!root) return;

  var form     = root.querySelector('[data-bk-form]');
  var panes    = Array.prototype.slice.call(root.querySelectorAll('[data-step]'));
  var bar      = root.querySelector('[data-bk-bar]');
  var counter  = root.querySelector('[data-bk-count]');
  var backBtn  = root.querySelector('[data-bk-back]');
  var errorEl  = root.querySelector('[data-bk-error]');
  var summary  = root.querySelector('[data-bk-summary]');
  var submit   = root.querySelector('[data-bk-submit]');
  var total    = panes.length;
  var step     = 1;
  var trigger  = null;
  var lastPointer = 0;
  var FALLBACK = 'Something went wrong. Please try again in a moment.';

  function track(name) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: name, booking_step: step });
  }

  function checked(name) { return form.querySelector('input[name="' + name + '"]:checked'); }
  function labelOf(name) { var r = checked(name); return r ? r.getAttribute('data-label') : ''; }

  function whenLabel() {
    var time = checked('time');
    return labelOf('date') + (time && time.value !== 'any' ? ', ' + labelOf('time') : '');
  }

  function stepValid(n) {
    var ok = true;
    panes[n - 1].querySelectorAll('input[type="radio"][required]').forEach(function (r) {
      if (!checked(r.name)) ok = false;
    });
    return ok;
  }

  function syncChecked() {
    form.querySelectorAll('.ig-bk__opt').forEach(function (opt) {
      var input = opt.querySelector('input');
      opt.classList.toggle('is-checked', !!(input && input.checked));
    });
  }

  function summarize() {
    summary.innerHTML = '';
    [labelOf('treatment'), labelOf('office'), whenLabel()].forEach(function (text) {
      if (!text) return;
      var chip = document.createElement('span');
      chip.textContent = text;
      summary.appendChild(chip);
    });
  }

  function render() {
    panes.forEach(function (p, i) { p.hidden = i + 1 !== step; });
    bar.style.width = (step / total * 100) + '%';
    counter.textContent = 'Step ' + step + ' of ' + total;
    backBtn.disabled = step === 1;
    var next = panes[step - 1].querySelector('[data-bk-next]');
    if (next) next.disabled = !stepValid(step);
    if (step === total) summarize();
  }

  function showError(msg) { errorEl.textContent = msg; errorEl.hidden = false; }
  function hideError() { errorEl.hidden = true; errorEl.textContent = ''; }

  function go(n) {
    step = Math.max(1, Math.min(total, n));
    hideError();
    render();
    form.scrollTop = 0;
    var q = panes[step - 1].querySelector('.ig-bk__q');
    if (q) {
      q.setAttribute('tabindex', '-1');
      q.focus({ preventScroll: true });
    }
  }

  function open(office) {
    if (!root.hidden) return;
    // close the mobile menu first if the button was inside it
    var panel = document.getElementById('igPanel');
    if (panel && panel.classList.contains('is-open')) {
      var closeMenu = document.getElementById('igClose');
      if (closeMenu) closeMenu.click();
    }
    trigger = document.activeElement;
    if (office && !checked('office')) {
      var r = form.querySelector('input[name="office"][value="' + String(office).replace(/[^a-z0-9-]/gi, '') + '"]');
      if (r) { r.checked = true; syncChecked(); }
    }
    root.hidden = false;
    document.documentElement.classList.add('ig-bk-lock');
    void root.offsetWidth; // start the fade from the hidden state
    root.classList.add('is-open');
    go(step); // reopening resumes where the visitor left off
    track('booking_open');
  }

  function close() {
    root.classList.remove('is-open');
    document.documentElement.classList.remove('ig-bk-lock');
    setTimeout(function () { if (!root.classList.contains('is-open')) root.hidden = true; }, 260);
    if (trigger && trigger.focus) trigger.focus();
  }

  function invalidFields() {
    var bad = [];
    function flag(name, ok, label) {
      var el = form.elements[name];
      if (ok) {
        el.removeAttribute('aria-invalid');
      } else {
        el.setAttribute('aria-invalid', 'true');
        bad.push({ el: el, label: label });
      }
    }
    var digits = form.elements.phone.value.replace(/\D/g, '');
    flag('first_name', form.elements.first_name.value.trim() !== '', 'first name');
    flag('last_name', form.elements.last_name.value.trim() !== '', 'last name');
    flag('phone', digits.length >= 10 && digits.length <= 15, 'phone number');
    flag('email', /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(form.elements.email.value.trim()), 'email');
    var consent = form.elements.consent;
    consent.closest('.ig-bk__consent').classList.toggle('is-invalid', !consent.checked);
    if (!consent.checked) bad.push({ el: consent, label: 'consent checkbox' });
    return bad;
  }

  /* ---------- open triggers (capture phase, so page scripts don't also scroll) ---------- */
  document.addEventListener('click', function (e) {
    var el = e.target.closest ? e.target.closest('[data-book], a[href="#ig-consult"]') : null;
    if (!el || root.contains(el)) return;
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    e.preventDefault();
    e.stopPropagation();
    open(el.getAttribute('data-book-office') || document.body.getAttribute('data-book-office'));
  }, true);

  /* ---------- inside the popup ---------- */
  root.addEventListener('click', function (e) { if (e.target === root) close(); });
  root.querySelector('[data-bk-close]').addEventListener('click', close);
  backBtn.addEventListener('click', function () { go(step - 1); });

  panes.forEach(function (p) {
    var next = p.querySelector('[data-bk-next]');
    if (next) next.addEventListener('click', function () { if (stepValid(step)) go(step + 1); });
  });

  // single-choice steps advance on a tap/click, but not while choosing with arrow keys
  form.addEventListener('pointerdown', function () { lastPointer = Date.now(); });

  form.addEventListener('change', function (e) {
    syncChecked();
    render();
    var from = step;
    if (e.target.type === 'radio' && panes[from - 1].hasAttribute('data-auto') &&
        Date.now() - lastPointer < 1000 && stepValid(from)) {
      setTimeout(function () { if (step === from && stepValid(from)) go(from + 1); }, 260);
    }
  });

  form.addEventListener('input', function (e) {
    if (e.target.hasAttribute('aria-invalid')) e.target.removeAttribute('aria-invalid');
    if (e.target.name === 'consent') e.target.closest('.ig-bk__consent').classList.remove('is-invalid');
  });

  document.addEventListener('keydown', function (e) {
    if (root.hidden) return;
    if (e.key === 'Escape') { e.preventDefault(); close(); return; }
    if (e.key !== 'Tab') return;
    var focusable = Array.prototype.filter.call(
      root.querySelectorAll('button, input, textarea, a[href]'),
      function (el) { return !el.disabled && el.tabIndex !== -1 && el.offsetParent !== null && !el.closest('[hidden]'); }
    );
    if (!focusable.length) return;
    var first = focusable[0], last = focusable[focusable.length - 1];
    if (e.shiftKey && (document.activeElement === first || !root.contains(document.activeElement))) {
      e.preventDefault(); last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault(); first.focus();
    }
  });

  /* ---------- submit ---------- */
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (step < total) {
      if (stepValid(step)) go(step + 1);
      return;
    }
    for (var n = 1; n < total; n++) {
      if (!stepValid(n)) { go(n); showError('Please finish this step first.'); return; }
    }
    var bad = invalidFields();
    if (bad.length) {
      showError('Please check your ' + bad.map(function (b) { return b.label; }).join(', ') + '.');
      bad[0].el.focus();
      return;
    }

    hideError();
    var idleLabel = submit.textContent;
    submit.disabled = true;
    submit.textContent = 'Sending…';

    var data = new FormData(form);
    data.append('source', window.location.pathname);

    fetch(form.getAttribute('action'), {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (res) {
        if (res && res.ok) {
          var office = checked('office');
          try {
            sessionStorage.setItem('igBooking', JSON.stringify({
              first: form.elements.first_name.value.trim(),
              treatment: labelOf('treatment'),
              office: labelOf('office'),
              when: whenLabel(),
              phone: office ? office.getAttribute('data-phone') : '',
              tel: office ? office.getAttribute('data-tel') : ''
            }));
          } catch (err) { /* private mode: the thank-you page falls back to generic copy */ }
          track('booking_submitted');
          window.location.href = res.redirect || '/thank-you/';
          return;
        }
        submit.disabled = false;
        submit.textContent = idleLabel;
        if (res && res.step && res.step < total) go(res.step);
        showError((res && res.error) || FALLBACK);
      })
      .catch(function () {
        submit.disabled = false;
        submit.textContent = idleLabel;
        showError(FALLBACK);
      });
  });

  syncChecked();
  render();

  var params = new URLSearchParams(window.location.search);
  if (params.has('book')) open(params.get('office') || document.body.getAttribute('data-book-office'));

  window.IgniteBooking = { open: open, close: close };
})();
