/* Shared form submit: page scripts validate their form, then hand it here. */
window.igSubmit = function (form, note) {
  var btn = form.querySelector('[type="submit"]');
  var data = new FormData(form);
  if (!data.has('source')) data.append('source', window.location.pathname);

  var fallback = 'Something went wrong. Please call us at (586) 393-7972.';
  function show(text, color) { note.textContent = text; note.style.color = color; }

  if (btn) btn.disabled = true;
  show('Sending…', '');

  fetch(form.getAttribute('action') || '/consult', {
    method: 'POST',
    body: data,
    headers: { 'Accept': 'application/json' }
  })
    .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
    .then(function (res) {
      if (res.ok) {
        form.reset();
        show('Thank you! Our team will contact you shortly to schedule your consultation.', '#F47421');
      } else {
        show(res.error || fallback, '#D64545');
      }
    })
    .catch(function () { show(fallback, '#D64545'); })
    .then(function () { if (btn) btn.disabled = false; });
};
