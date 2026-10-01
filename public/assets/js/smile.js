/* See Your Smile (/see-your-smile/) — shows the chosen photo before it is sent and
   posts the form without reloading (app/views/see-smile.php). Without this file the
   form still works: it is a plain upload form that posts to /see-smile. */
(function () {
  var root = document.querySelector('[data-sm-root]');
  if (!root) return;

  var form    = root.querySelector('[data-sm-form]');
  var file    = root.querySelector('[data-sm-file]');
  var drop    = root.querySelector('[data-sm-drop]');
  var empty   = root.querySelector('[data-sm-empty]');
  var preview = root.querySelector('[data-sm-preview]');
  var chosen  = root.querySelector('[data-sm-chosen]');
  var clear   = root.querySelector('[data-sm-clear]');
  var errorEl = root.querySelector('[data-sm-error]');
  var submit  = root.querySelector('[data-sm-submit]');
  var FALLBACK = 'Something went wrong. Please try again in a moment.';

  function track(name) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event: name });
    if (window.fbq) window.fbq('trackCustom', name);
  }

  /* --- showing the photo they picked --- */
  file.addEventListener('change', function () {
    var picked = file.files && file.files[0];
    if (!picked) return reset();
    if (picked.size > 12 * 1024 * 1024) {
      reset();
      return fail('That photo is too large. Please use one under 12MB.');
    }
    preview.src = URL.createObjectURL(picked);
    preview.hidden = false;
    empty.hidden = true;
    chosen.hidden = false;
    if (errorEl) errorEl.hidden = true;
    track('smile_photo_chosen');
  });

  function reset() {
    file.value = '';
    preview.hidden = true;
    preview.removeAttribute('src');
    empty.hidden = false;
    chosen.hidden = true;
  }
  if (clear) clear.addEventListener('click', function (event) { event.preventDefault(); reset(); });

  /* --- dragging a photo onto the box --- */
  ['dragenter', 'dragover'].forEach(function (name) {
    drop.addEventListener(name, function (event) { event.preventDefault(); drop.classList.add('is-over'); });
  });
  ['dragleave', 'drop'].forEach(function (name) {
    drop.addEventListener(name, function () { drop.classList.remove('is-over'); });
  });
  drop.addEventListener('drop', function (event) {
    event.preventDefault();
    if (event.dataTransfer.files && event.dataTransfer.files[0]) {
      file.files = event.dataTransfer.files;
      file.dispatchEvent(new Event('change'));
    }
  });

  /* --- sending it --- */
  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (errorEl) errorEl.hidden = true;

    if (!file.files || !file.files[0]) {
      return fail('Please choose a photo of your smile.');
    }
    var missing = form.querySelector('[required]:invalid');
    if (missing) {
      missing.focus();
      return fail('Please fill in the highlighted fields.');
    }

    submit.disabled = true;
    submit.textContent = 'Sending…';

    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } })
      .then(function (response) { return response.json().catch(function () { return {}; }); })
      .then(function (data) {
        if (data && data.ok && data.redirect) {
          track('smile_photo_sent');
          window.location.href = data.redirect;
          return;
        }
        fail((data && data.error) || FALLBACK);
      })
      .catch(function () { fail(FALLBACK); });
  });

  function fail(message) {
    submit.disabled = false;
    submit.textContent = 'See My Smile';
    if (!errorEl) { window.alert(message); return; }
    errorEl.textContent = message;
    errorEl.hidden = false;
    errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  track('smile_page_view');
}());
