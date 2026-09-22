/* Booking page (/booking/) — the consultation request, one question at a time (app/views/booking.php).
   Posts to /book; on success keeps a short summary for the thank-you page and goes there.
   On an office's booking link the office is a hidden field and its step is not shown. */
(function () {
  var root = document.querySelector('[data-bk-root]');
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
  var lastPointer = 0;
  var FALLBACK = 'Something went wrong. Please try again in a moment.';

  function track(name) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: name, booking_step: step });
  }

  // a chosen radio, or the office fixed by the page
  function chosen(name) {
    return form.querySelector('input[name="' + name + '"]:checked') ||
      form.querySelector('input[type="hidden"][name="' + name + '"]');
  }
  function labelOf(name) { var r = chosen(name); return r ? r.getAttribute('data-label') : ''; }

  function whenLabel() {
    var time = chosen('time');
    return labelOf('date') + (time && time.value !== 'any' ? ', ' + labelOf('time') : '');
  }

  function stepValid(n) {
    var ok = true;
    panes[n - 1].querySelectorAll('input[type="radio"][required]').forEach(function (r) {
      if (!chosen(r.name)) ok = false;
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
    // keep the question in view when the page has scrolled past the top of the form
    var top = root.getBoundingClientRect().top;
    if (top < 0 || top > window.innerHeight * 0.6) root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    var q = panes[step - 1].querySelector('.ig-bk__q');
    if (q) {
      q.setAttribute('tabindex', '-1');
      q.focus({ preventScroll: true });
    }
    track('booking_step');
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

  // the step holding a field the server rejected
  function stepOf(field) {
    for (var i = 0; i < panes.length; i++) {
      if (panes[i].querySelector('[name="' + field + '"]')) return i + 1;
    }
    return 0;
  }

  backBtn.addEventListener('click', function () { go(step - 1); });

  panes.forEach(function (p) {
    var next = p.querySelector('[data-bk-next]');
    if (next && next.type === 'button') next.addEventListener('click', function () { if (stepValid(step)) go(step + 1); });
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
    data.append('source', document.referrer ? new URL(document.referrer).pathname : window.location.pathname);

    fetch(form.getAttribute('action'), {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (res) {
        if (res && res.ok) {
          var office = chosen('office');
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
        var back = res && res.field ? stepOf(res.field) : 0;
        if (back && back < total) go(back);
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
  track('booking_view');
})();
