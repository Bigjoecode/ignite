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

  /* --- keeping the details of someone who starts and leaves ---
     Saved as "Started, never finished" so the practice can follow up. They have not
     agreed to anything yet, which is why the dashboard marks these apart. */
  var draftField = root.querySelector('[data-sm-draft]');
  var draftId = null;
  var draftSent = '';

  function draft() {
    if (draftId) return draftId;
    try {
      draftId = window.sessionStorage.getItem('ig_smile_draft');
    } catch (e) { /* private browsing: just keep it in memory */ }
    if (!draftId) {
      var bytes = new Uint8Array(8);
      (window.crypto || {}).getRandomValues ? window.crypto.getRandomValues(bytes) : bytes.fill(0);
      draftId = Array.prototype.map.call(bytes, function (b) {
        return ('0' + b.toString(16)).slice(-2);
      }).join('') + Date.now().toString(16).slice(-8);
      try { window.sessionStorage.setItem('ig_smile_draft', draftId); } catch (e) {}
    }
    if (draftField) draftField.value = draftId;
    return draftId;
  }

  function value(name) {
    var el = form.querySelector('[name="' + name + '"]');
    return el ? el.value.trim() : '';
  }

  function keepDetails() {
    var email = value('email');
    var phone = value('phone').replace(/\D/g, '');
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email) && phone.length < 10) return;

    var body = new FormData();
    body.append('draft_id', draft());
    ['first_name', 'last_name', 'email', 'phone', 'office', 'concerns', 'notes'].forEach(function (name) {
      body.append(name, value(name));
    });
    body.append('source', '/see-your-smile/');
    body.append('kind', 'smile');

    var fingerprint = Array.prototype.join.call(body.values ? Array.from(body.values()) : [], '|');
    if (fingerprint === draftSent) return;       // nothing new since last time
    draftSent = fingerprint;

    fetch('/lead-partial', { method: 'POST', body: body, keepalive: true }).catch(function () {});
  }

  form.addEventListener('change', keepDetails);
  ['email', 'phone'].forEach(function (name) {
    var el = form.querySelector('[name="' + name + '"]');
    if (el) el.addEventListener('blur', keepDetails);
  });
  window.addEventListener('pagehide', keepDetails);

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
