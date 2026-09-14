/* generated from location-preview.html */
(function () {
  var root = document.querySelector('.ig-locpage');
  if (!root || root.dataset.igLocReady) return;
  root.dataset.igLocReady = '1';

  /* ---------- hero: photo must end exactly at the trust card's midpoint ----------
     The overhang has to equal half the card's height, and CSS can't express
     "half my own height" (percentage margins resolve against width), so it's
     measured here. The 7.5vw in the stylesheet is the pre-JS fallback. */
  (function () {
    var hero = root.querySelector('.ig-lhero');
    var card = root.querySelector('.ig-trustcard');
    if (!hero || !card) return;
    var next = hero.nextElementSibling;

    function sync() {
      var half = Math.round(card.getBoundingClientRect().height / 2);
      hero.style.setProperty('--ig-tc-over', half + 'px');
      if (next) next.style.paddingTop = 'calc(var(--ig-pad) + ' + half + 'px)';
    }

    sync();
    window.addEventListener('resize', sync);
    if (window.ResizeObserver) new ResizeObserver(sync).observe(card);
    /* webfonts and the logo SVGs can change the card height after first paint */
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(sync);
    window.addEventListener('load', sync);
  })();

  /* ---------- FAQ accordion + category tabs ---------- */
  (function () {
    var list = root.querySelector('#igFaqList');
    var tabs = root.querySelectorAll('#igFaqTabs .ig-faq__tab');
    if (!list) return;

    var items = Array.prototype.slice.call(list.querySelectorAll('.ig-q'));

    function close(item) {
      item.classList.remove('is-open');
      item.querySelector('.ig-q__a').style.maxHeight = null;
      item.querySelector('.ig-q__btn').setAttribute('aria-expanded', 'false');
    }

    items.forEach(function (item) {
      var btn = item.querySelector('.ig-q__btn');
      var ans = item.querySelector('.ig-q__a');
      btn.addEventListener('click', function () {
        var willOpen = !item.classList.contains('is-open');
        items.forEach(function (other) { if (other !== item) close(other); });
        item.classList.toggle('is-open', willOpen);
        btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        ans.style.maxHeight = willOpen ? ans.scrollHeight + 'px' : null;
      });
    });

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var cat = tab.dataset.cat;
        tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
        items.forEach(function (item) {
          var show = cat === 'all' || item.dataset.cat === cat;
          item.style.display = show ? '' : 'none';
          if (!show) close(item);
        });
      });
    });

    /* an open answer must be re-measured if the viewport reflows it */
    window.addEventListener('resize', function () {
      var open = list.querySelector('.ig-q.is-open .ig-q__a');
      if (open) open.style.maxHeight = open.scrollHeight + 'px';
    });
  })();

  /* ---------- consultation form ---------- */
  (function () {
    var form = root.querySelector('#igLocForm');
    var note = root.querySelector('#igLocFormNote');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var missing = [];
      form.querySelectorAll('[required]').forEach(function (f) {
        var bad = f.type === 'checkbox' ? !f.checked : !f.value.trim();
        if (f.type === 'email' && f.value.trim() && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(f.value)) bad = true;
        if (bad) { missing.push(f); f.style.borderColor = '#D64545'; }
        else { f.style.borderColor = ''; }
      });

      if (missing.length) {
        note.textContent = 'Please complete the highlighted fields.';
        note.style.color = '#D64545';
        missing[0].focus();
        return;
      }

      /* NOTE: no backend is wired up. Give the <form> an action/method,
         or replace it with your form plugin's shortcode. */
      note.textContent = 'Thanks! This form is not connected to a mail handler yet.';
      note.style.color = '#F47421';
    });
  })();

  /* ---------- smooth scroll to the consultation form ---------- */
  root.querySelectorAll('a[href^="#ig-"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var t = document.querySelector(a.getAttribute('href'));
      if (!t) return;
      e.preventDefault();
      t.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
})();
