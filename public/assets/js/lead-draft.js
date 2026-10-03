/* Keeps the details of someone who starts one of the forms and leaves.
   Used by the booking page, the virtual consultation page and See Your Smile.

   As soon as there is an email or a phone number, those details are sent to
   /lead-partial and saved as "Started, never finished". One record per visitor:
   the id is kept for the tab and sent with the finished form, so completing it
   turns that same record into the real request instead of leaving a duplicate.

   These people have NOT agreed to be contacted — the consent box is on the
   submit they have not made — which is why the dashboard marks them apart. */
(function () {
  window.igLeadDraft = function (form, kind, fields) {
    if (!form) return;

    var hidden = form.querySelector('[name="draft_id"]');
    var key    = 'ig_draft_' + kind;
    var id     = null;
    var sent   = '';

    function draftId() {
      if (id) return id;
      try { id = window.sessionStorage.getItem(key); } catch (e) { /* private browsing */ }
      if (!id) {
        var bytes = new Uint8Array(8);
        if (window.crypto && window.crypto.getRandomValues) {
          window.crypto.getRandomValues(bytes);
        } else {
          for (var i = 0; i < 8; i++) bytes[i] = Math.floor(Math.random() * 256);
        }
        id = Array.prototype.map.call(bytes, function (b) {
          return ('0' + b.toString(16)).slice(-2);
        }).join('') + Date.now().toString(16).slice(-8);
        try { window.sessionStorage.setItem(key, id); } catch (e) {}
      }
      if (hidden) hidden.value = id;
      return id;
    }

    // a typed field, or the chosen radio, or the office fixed by the page
    function value(name) {
      var checked = form.querySelector('[name="' + name + '"]:checked');
      if (checked) return checked.value;
      var el = form.querySelector('[name="' + name + '"]');
      return el ? String(el.value || '').trim() : '';
    }

    function keep() {
      var email = value('email');
      var phone = value('phone').replace(/\D/g, '');
      if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email) && phone.length < 10) return;

      var body = new FormData();
      body.append('draft_id', draftId());
      body.append('kind', kind);
      body.append('source', window.location.pathname);
      var parts = [];
      fields.forEach(function (name) {
        var v = value(name);
        body.append(name, v);
        parts.push(v);
      });

      var now = parts.join('|');
      if (now === sent) return;        // nothing new since the last one
      sent = now;

      fetch('/lead-partial', { method: 'POST', body: body, keepalive: true }).catch(function () {});
    }

    form.addEventListener('change', keep);
    ['email', 'phone'].forEach(function (name) {
      var el = form.querySelector('[name="' + name + '"]');
      if (el) el.addEventListener('blur', keep);
    });
    window.addEventListener('pagehide', keep);

    draftId();
  };
}());
