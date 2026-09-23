/* Virtual consultation page (/virtual-consultation/) — pick a time, confirm it in a
   small dialog, then leave your details (app/views/virtual-consultation.php).
   Posts to /book-virtual, which books the time and returns where to go next.
   Without this file the page still works: every time is a radio button, the extra
   times are shown, and the form posts on its own. */
(function () {
  var root = document.querySelector('[data-vc-root]');
  if (!root) return;
  var form = root.querySelector('[data-vc-form]');
  if (!form) return;

  var panes    = Array.prototype.slice.call(root.querySelectorAll('[data-vc-step]'));
  var steps    = Array.prototype.slice.call(root.querySelectorAll('[data-vc-bar] li'));
  var nextBtn  = root.querySelector('[data-vc-next]');
  var errorEl  = root.querySelector('[data-vc-error]');
  var submit   = root.querySelector('[data-vc-submit]');
  var modal    = document.querySelector('[data-vc-modal]');
  var pickedTime = root.querySelector('[data-vc-picked-time]');
  var pickedDay  = root.querySelector('[data-vc-picked-day]');
  var FALLBACK = 'Something went wrong. Please try again in a moment.';
  var lastFocus = null;

  function track(name, extra) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(Object.assign({ event: name }, extra || {}));
  }
  function chosen() { return form.querySelector('input[name="slot"]:checked'); }

  /* --- "show all" times for a day --- */
  root.querySelectorAll('[data-vc-more]').forEach(function (button) {
    button.addEventListener('click', function () {
      button.parentNode.querySelectorAll('.vc-slot--more').forEach(function (slot) { slot.hidden = false; });
      button.remove();
    });
  });

  /* --- picking a time opens the confirmation --- */
  form.addEventListener('change', function (event) {
    if (event.target.name !== 'slot') return;
    nextBtn.disabled = false;
    openModal(event.target);
  });

  function openModal(slot) {
    if (!modal) return;
    lastFocus = slot;
    modal.querySelector('[data-vc-modal-day]').textContent  = slot.getAttribute('data-day');
    modal.querySelector('[data-vc-modal-time]').textContent = slot.getAttribute('data-time');
    modal.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    modal.querySelector('[data-vc-modal-book]').focus();
    track('virtual_consult_time_picked', { slot: slot.value });
  }
  function closeModal(restore) {
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    document.documentElement.style.overflow = '';
    if (restore && lastFocus) lastFocus.focus();
  }

  if (modal) {
    modal.querySelector('[data-vc-modal-book]').addEventListener('click', function () { closeModal(false); toDetails(); });
    modal.querySelector('[data-vc-modal-close]').addEventListener('click', function () { closeModal(true); });
    modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(true); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeModal(true); });
  }

  /* --- moving between the two steps --- */
  function toDetails() {
    var slot = chosen();
    if (!slot) return;
    if (pickedTime) pickedTime.textContent = slot.getAttribute('data-time');
    if (pickedDay)  pickedDay.textContent  = slot.getAttribute('data-day');
    panes[0].hidden = true;
    panes[1].hidden = false;
    mark(2);
    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    var first = panes[1].querySelector('input, select');
    if (first) first.focus({ preventScroll: true });
  }
  function toTimes() {
    panes[1].hidden = true;
    panes[0].hidden = false;
    mark(1);
    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  function mark(step) {
    steps.forEach(function (item, i) { item.classList.toggle('is-on', i < step); });
  }

  if (nextBtn) nextBtn.addEventListener('click', toDetails);
  root.querySelectorAll('[data-vc-back]').forEach(function (button) {
    button.addEventListener('click', toTimes);
  });

  /* --- booking --- */
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
        // the time went while they were typing: cross it off and send them back
        if (data && data.field === 'slot') {
          var taken = chosen();
          if (taken) {
            taken.closest('.vc-slot').classList.add('is-gone');
            taken.disabled = true;
            taken.checked = false;
          }
          nextBtn.disabled = true;
          toTimes();
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
