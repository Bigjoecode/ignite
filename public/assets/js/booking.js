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
    // the office search fills a hidden field
    panes[n - 1].querySelectorAll('input[type="hidden"][data-required]').forEach(function (h) {
      if (!h.value) ok = false;
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
    // the office step is a search box: put the cursor straight in it
    var box = panes[step - 1].querySelector('[data-bk-find-input]');
    if (box && !box.closest('[hidden]')) box.focus({ preventScroll: true });
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

  /* ---------- office search: type a city, zip code or office name, then pick a match ---------- */
  var find = root.querySelector('[data-bk-find]');
  if (find) {
    var offices   = JSON.parse(find.getAttribute('data-offices') || '[]');
    var findInput = find.querySelector('[data-bk-find-input]');
    var findList  = find.querySelector('[data-bk-find-list]');
    var findMsg   = find.querySelector('[data-bk-find-msg]');
    var picked    = root.querySelector('[data-bk-picked]');
    var officeIn  = form.querySelector('input[type="hidden"][name="office"]');
    var paneOf    = panes.indexOf(find.closest('[data-step]')) + 1;
    var results   = [];
    var active    = -1;
    var PIN = '<span class="ig-bk__ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7z M12 11.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z"/></svg></span>';

    var norm = function (s) {
      return String(s).toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, ' ').trim();
    };
    // edit distance between two short words, so "sterlng" still finds Sterling
    var distance = function (a, b) {
      if (Math.abs(a.length - b.length) > 2) return 9;
      var prev = [], cur, i, j;
      for (j = 0; j <= b.length; j++) prev[j] = j;
      for (i = 1; i <= a.length; i++) {
        cur = [i];
        for (j = 1; j <= b.length; j++) {
          cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
        }
        prev = cur;
      }
      return prev[b.length];
    };

    offices.forEach(function (o) {
      // words people might type that every office shares still count as a match
      o.words = norm([o.name, o.city, o.street, o.state, o.zip, 'ignite orthodontics office michigan'].join(' ')).split(' ');
      o.lead  = norm(o.name + ' ' + o.city);
    });

    var wordScore = function (w, h) {
      if (h === w) return 30;
      if (h.indexOf(w) === 0) return 24;
      if (w.length >= 3 && h.indexOf(w) > 0) return 12;
      if (w.length >= 4) {
        var allowed = w.length >= 7 ? 2 : 1;
        if (distance(w, h) <= allowed) return 14;                        // typo in a whole word
        if (distance(w, h.slice(0, w.length)) <= allowed) return 10;     // typo while still typing
      }
      return 0;
    };

    var score = function (o, q) {
      if (/^\d+$/.test(q)) {
        if (o.zip.indexOf(q) === 0) return 100;
        // the first three digits of a zip code cover one area, so these offices are the nearby ones
        if (q.length >= 3 && o.zip.slice(0, 3) === q.slice(0, 3)) return 50;
        return 0;
      }
      var total = o.lead.indexOf(q) === 0 ? 40 : 0;
      var words = q.split(' ');
      for (var i = 0; i < words.length; i++) {
        var best = 0;
        for (var k = 0; k < o.words.length; k++) best = Math.max(best, wordScore(words[i], o.words[k]));
        if (!best) return 0; // every word typed has to match something
        total += best;
      }
      return total;
    };

    var setActive = function (i) {
      var items = findList.children;
      active = results.length ? (i + results.length) % results.length : -1;
      Array.prototype.forEach.call(items, function (li, n) {
        li.classList.toggle('is-active', n === active);
        li.setAttribute('aria-selected', String(n === active));
      });
      if (active >= 0) {
        findInput.setAttribute('aria-activedescendant', items[active].id);
        items[active].scrollIntoView({ block: 'nearest' });
      } else {
        findInput.removeAttribute('aria-activedescendant');
      }
    };

    var closeList = function () {
      findList.hidden = true;
      findInput.setAttribute('aria-expanded', 'false');
      findInput.removeAttribute('aria-activedescendant');
      active = -1;
    };

    var draw = function (list, message) {
      results = list;
      findList.innerHTML = '';
      list.forEach(function (o, i) {
        var li = document.createElement('li');
        li.id = 'bkFindOpt' + i;
        li.className = 'bk-find__opt';
        li.setAttribute('role', 'option');
        li.setAttribute('aria-selected', 'false');
        li.innerHTML = PIN + '<span><b></b><small></small></span>';
        li.querySelector('b').textContent = o.name;
        li.querySelector('small').textContent = o.address;
        li.addEventListener('mousedown', function (e) { e.preventDefault(); }); // keep focus in the box
        li.addEventListener('click', function () { choose(o); });
        findList.appendChild(li);
      });
      findList.hidden = !list.length;
      findInput.setAttribute('aria-expanded', String(list.length > 0));
      findMsg.textContent = message || '';
      active = -1;
      if (list.length) setActive(0);
    };

    var showAll = function () {
      draw(offices.slice(), 'All ' + offices.length + ' Ignite Orthodontics offices');
      findInput.focus();
    };

    var search = function () {
      var raw = findInput.value.trim();
      var q = norm(raw);
      if (!q) { closeList(); findMsg.textContent = ''; return; }
      var found = offices
        .map(function (o) { return { o: o, s: score(o, q) }; })
        .filter(function (r) { return r.s > 0; })
        .sort(function (a, b) { return b.s - a.s; })
        .map(function (r) { return r.o; });

      if (!found.length) {
        draw([], '');
        findMsg.textContent = /^\d{5}$/.test(q)
          ? 'We don’t have an office in the ' + raw + ' area yet. '
          : 'No office matches “' + raw + '”. Try a city, zip code or office name. ';
        var all = document.createElement('button');
        all.type = 'button';
        all.className = 'bk-find__all';
        all.textContent = 'Show all ' + offices.length + ' offices';
        all.addEventListener('click', showAll);
        findMsg.appendChild(all);
        return;
      }
      var message = found.length + (found.length === 1 ? ' office matches' : ' offices match');
      if (/^\d{3,}$/.test(q)) {
        var exact = found.filter(function (o) { return o.zip.indexOf(q) === 0; }).length;
        var near  = found.length - exact;
        if (!exact) message = 'No office in ' + raw + ' itself — these are the closest.';
        else if (near) message = exact + ' in ' + raw + (near === 1 ? ', plus 1 nearby office' : ', plus ' + near + ' nearby offices');
      }
      draw(found, message);
    };

    var choose = function (o) {
      officeIn.value = o.slug;
      officeIn.setAttribute('data-label', o.name + ' office');
      officeIn.setAttribute('data-phone', o.phone);
      officeIn.setAttribute('data-tel', o.tel);
      picked.querySelector('[data-bk-picked-name]').textContent = 'Ignite Orthodontics ' + o.name;
      picked.querySelector('[data-bk-picked-addr]').textContent = o.address;
      picked.hidden = false;
      find.hidden = true;
      closeList();
      findMsg.textContent = '';
      render();
      track('booking_office_chosen');
      setTimeout(function () { if (step === paneOf && stepValid(step)) go(step + 1); }, 450);
    };

    picked.querySelector('[data-bk-picked-change]').addEventListener('click', function () {
      officeIn.value = '';
      ['data-label', 'data-phone', 'data-tel'].forEach(function (a) { officeIn.setAttribute(a, ''); });
      picked.hidden = true;
      find.hidden = false;
      findInput.value = '';
      render();
      findInput.focus();
    });

    findInput.addEventListener('input', search);
    findInput.addEventListener('focus', function () { if (findInput.value.trim()) search(); });
    findInput.addEventListener('blur', function () { setTimeout(closeList, 150); });
    findInput.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { e.preventDefault(); if (findList.hidden) search(); else setActive(active + 1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
      else if (e.key === 'Enter') {
        e.preventDefault(); // never submits the form from the search box
        if (!findList.hidden && results[active]) choose(results[active]);
      } else if (e.key === 'Escape') { closeList(); }
    });
  }

  syncChecked();
  render();
  track('booking_view');
})();
