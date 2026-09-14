/* generated from home-preview.html */
(function () {
  var root = document.querySelector('.ig-home');
  if (!root || root.dataset.igHomeReady) return;
  root.dataset.igHomeReady = '1';

  /* ---------- 1. HERO SLIDER ---------- */
  (function () {
    var hero = root.querySelector('#igHero');
    if (!hero) return;
    var slides = hero.querySelectorAll('.ig-hero__slide');
    var dots   = hero.querySelectorAll('.ig-hero__dots button');
    var prev   = hero.querySelector('.ig-hero__nav--prev');
    var next   = hero.querySelector('.ig-hero__nav--next');
    if (slides.length < 2) return;

    var i = 0, timer;
    var DELAY = 6500;

    function show(n) {
      i = (n + slides.length) % slides.length;
      slides.forEach(function (s, k) { s.classList.toggle('is-active', k === i); });
      dots.forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
    }
    function start() { stop(); timer = setInterval(function () { show(i + 1); }, DELAY); }
    function stop()  { if (timer) clearInterval(timer); }

    next.addEventListener('click', function () { show(i + 1); start(); });
    prev.addEventListener('click', function () { show(i - 1); start(); });
    dots.forEach(function (d, k) {
      d.addEventListener('click', function () { show(k); start(); });
    });

    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);

    /* swipe on touch devices */
    var x0 = null;
    hero.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; stop(); }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 45) show(dx < 0 ? i + 1 : i - 1);
      x0 = null; start();
    });

    /* pause when the section is off-screen */
    if (window.IntersectionObserver) {
      new IntersectionObserver(function (entries) {
        entries[0].isIntersecting ? start() : stop();
      }, { threshold: 0.15 }).observe(hero);
    } else { start(); }
  })();

  /* ---------- 5. INSURANCE MARQUEE ----------
     Clone one full set so the -50% keyframe lands exactly on a repeat.
     Clones are aria-hidden so screen readers don't read every carrier twice. */
  (function () {
    var track = root.querySelector('#igMarquee');
    if (!track) return;
    Array.prototype.slice.call(track.children).forEach(function (node) {
      var clone = node.cloneNode(true);
      clone.setAttribute('aria-hidden', 'true');
      track.appendChild(clone);
    });
  })();

  /* ---------- 3. CONSULTATION FORM ---------- */
  (function () {
    var form = root.querySelector('#igConsultForm');
    var note = root.querySelector('#igFormNote');
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

      /* NOTE: no backend is wired up. Either give the <form> an action/method,
         or swap this whole block for your form plugin's shortcode. */
      window.igSubmit(form, note); return;
      note.style.color = '#F47421';
    });
  })();

  /* ---------- 8. "SEARCH NEAREST" ----------
     Filters the office list in place and re-centres the map embed.
     No backend and no API key needed — it matches against the text
     already in each card (practice name + street address). */
  (function () {
    var input  = root.querySelector('#igLocInput');
    var btn    = root.querySelector('#igLocBtn');
    var msg    = root.querySelector('#igLocMsg');
    var list   = root.querySelector('.ig-loc__list');
    var frame  = root.querySelector('.ig-loc__map iframe');
    if (!btn || !input || !list) return;

    var offices = Array.prototype.slice.call(list.querySelectorAll('.ig-office'));
    var haystack = offices.map(function (el) {
      return (el.textContent || '').toLowerCase().replace(/\s+/g, ' ');
    });
    var MAP = 'https://maps.google.com/maps?q=%Q%&z=%Z%&output=embed';

    function setMap(query, zoom) {
      if (!frame) return;
      var next = MAP.replace('%Q%', encodeURIComponent(query)).replace('%Z%', zoom);
      if (frame.getAttribute('src') !== next) frame.setAttribute('src', next);
    }

    function reset() {
      offices.forEach(function (el) { el.style.display = ''; });
      msg.textContent = '';
      /* EDIT: default map view */
      setMap('Michigan', 7);
    }

    function search() {
      var q = (input.value || '').trim();
      if (!q) { reset(); return; }

      var needle = q.toLowerCase().replace(/\s+/g, ' ');
      var hits = 0;

      offices.forEach(function (el, i) {
        var match = haystack[i].indexOf(needle) !== -1;
        el.style.display = match ? '' : 'none';
        if (match) hits++;
      });

      if (hits) {
        msg.textContent = hits + (hits === 1 ? ' office' : ' offices') + ' matching “' + q + '”.';
        msg.style.color = '';
        setMap(q + ', Michigan', 11);   /* EDIT: state used to disambiguate the map query */
      } else {
        offices.forEach(function (el) { el.style.display = ''; });
        msg.textContent = 'No offices matched “' + q + '” — showing all locations.';
        msg.style.color = '#D64545';
        setMap(q, 10);
      }
      list.scrollTop = 0;
    }

    btn.addEventListener('click', search);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); search(); }
    });
    /* live filter as you type, but leave the map to the button/Enter */
    input.addEventListener('input', function () {
      if (!input.value.trim()) { reset(); return; }
      var needle = input.value.trim().toLowerCase().replace(/\s+/g, ' ');
      var hits = 0;
      offices.forEach(function (el, i) {
        var match = haystack[i].indexOf(needle) !== -1;
        el.style.display = match ? '' : 'none';
        if (match) hits++;
      });
      msg.style.color = '';
      msg.textContent = hits ? '' : 'No offices matched — press Search Nearest to look it up on the map.';
    });
  })();

  /* ---------- smooth scroll for the in-page "Schedule Now" links ---------- */
  root.querySelectorAll('a[href^="#ig-"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var t = document.querySelector(a.getAttribute('href'));
      if (!t) return;
      e.preventDefault();
      t.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
})();
