/* Virtual consultation page (/virtual-consultation/) — pick a free time, then leave
   your details (app/views/virtual-consultation.php). Posts to /book-virtual, which
   books the Google Calendar slot and returns where to go next. Works without this
   file too: every slot is a radio button and the form posts on its own. */
(function () {
  var root = document.querySelector('[data-vc-root]');
  if (!root) return;

  var form    = root.querySelector('[data-vc-form]');
  if (!form) return;

  var panes   = Array.prototype.slice.call(root.querySelectorAll('[data-vc-step]'));
  var nextBtn = root.querySelector('[data-vc-next]');
  var summary = root.querySelector('[data-vc-summary]');
  var errorEl = root.querySelector('[data-vc-error]');
  var submit  = root.querySelector('[data-vc-submit]');
  var FALLBACK = 'Something went wrong. Please try again in a moment.';

  function track(name, extra) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(Object.assign({ event: name }, extra || {}));
  }

  function chosenSlot() { return form.querySelector('input[name="slot"]:checked'); }

  /* --- day tabs --- */
  root.querySelectorAll('[data-vc-day]').forEach(function (tab) {
    tab.addEventListener('click', function () {
      var day = tab.getAttribute('data-vc-day');
      root.querySelectorAll('[data-vc-day]').forEach(function (other) {
        var on = other === tab;
        other.classList.toggle('is-on', on);
        other.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      root.querySelectorAll('[data-vc-times]').forEach(function (group) {
        group.hidden = group.getAttribute('data-vc-times') !== day;
      });
    });
  });

  form.addEventListener('change', function (event) {
    if (event.target.name === 'slot') {
      nextBtn.disabled = false;
    }
  });

  /* --- step 1 to step 2 --- */
  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      var slot = chosenSlot();
      if (!slot) return;
      if (summary) summary.textContent = slot.getAttribute('data-label') + ' (Eastern time)';
      panes[0].hidden = true;
      panes[1].hidden = false;
      panes[1].scrollIntoView({ behavior: 'smooth', block: 'start' });
      var first = panes[1].querySelector('input, select');
      if (first) first.focus({ preventScroll: true });
      track('virtual_consult_time_picked', { slot: slot.value });
    });
  }

  /* --- submit --- */
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (errorEl) errorEl.hidden = true;

    var missing = form.querySelector('[required]:invalid');
    if (missing) {
      missing.focus();
      return fail('Please fill in the highlighted fields.');
    }

    submit.disabled = true;
    submit.textContent = 'Booking…';

    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } })
      .then(function (response) { return response.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && data.ok && data.redirect) {
          track('virtual_consult_booked', {});
          window.location.href = data.redirect;
          return;
        }
        // the slot went while they were typing: send them back to pick another
        if (data && data.field === 'slot') {
          panes[1].hidden = true;
          panes[0].hidden = false;
          var taken = chosenSlot();
          if (taken) {
            taken.closest('.vc-slot').classList.add('is-gone');
            taken.disabled = true;
            taken.checked = false;
          }
          nextBtn.disabled = true;
          panes[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        fail((data && data.error) || FALLBACK);
      })
      .catch(function () { fail(FALLBACK); });
  });

  function fail(message) {
    submit.disabled = false;
    submit.textContent = 'Confirm My Video Visit';
    if (!errorEl) { window.alert(message); return; }
    errorEl.textContent = message;
    errorEl.hidden = false;
    errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  track('virtual_consult_view', {});
}());
