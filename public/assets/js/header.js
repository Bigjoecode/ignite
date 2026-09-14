/* generated from menu.html */
(function () {
  var root   = document.querySelector('.ig-nav-root');
  if (!root || root.dataset.igReady) return;
  root.dataset.igReady = '1';

  var header = root.querySelector('#igHeader');
  var spacer = root.querySelector('#igSpacer');
  var burger = root.querySelector('#igBurger');
  var panel  = root.querySelector('#igPanel');
  var scrim  = root.querySelector('#igScrim');
  var close  = root.querySelector('#igClose');

  /* ---------- keep the spacer exactly as tall as the header ---------- */
  function syncSpacer() {
    if (getComputedStyle(header).position !== 'fixed') { spacer.style.height = '0px'; return; }
    spacer.style.height = header.offsetHeight + 'px';
  }
  syncSpacer();
  window.addEventListener('resize', syncSpacer);
  if (window.ResizeObserver) new ResizeObserver(syncSpacer).observe(header);

  /* ---------- shrink-on-scroll ---------- */
  var ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () {
      header.classList.toggle('is-stuck', window.pageYOffset > 24);
      ticking = false;
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- desktop locations dropdown (hover + click + keyboard) ---------- */
  root.querySelectorAll('[data-ig-dropdown]').forEach(function (item) {
    var btn = item.querySelector('button');
    var timer;
    function open(state) {
      item.classList.toggle('is-open', state);
      btn.setAttribute('aria-expanded', state ? 'true' : 'false');
    }
    item.addEventListener('mouseenter', function () { clearTimeout(timer); open(true); });
    item.addEventListener('mouseleave', function () { timer = setTimeout(function () { open(false); }, 160); });
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      open(!item.classList.contains('is-open'));
    });
    document.addEventListener('click', function (e) { if (!item.contains(e.target)) open(false); });
    item.addEventListener('keydown', function (e) { if (e.key === 'Escape') { open(false); btn.focus(); } });
  });

  /* ---------- off-canvas panel ---------- */
  var lastFocus = null;

  function setPanel(open) {
    panel.classList.toggle('is-open', open);
    scrim.classList.toggle('is-open', open);
    burger.classList.toggle('is-active', open);
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) { scrim.hidden = false; } else { setTimeout(function(){ if(!panel.classList.contains('is-open')) scrim.hidden = true; }, 380); }

    if (open) {
      lastFocus = document.activeElement;
      document.body.style.overflow = 'hidden';
      setTimeout(function () { close.focus(); }, 60);
    } else {
      document.body.style.overflow = '';
      if (lastFocus) lastFocus.focus();
    }
  }

  burger.addEventListener('click', function () { setPanel(!panel.classList.contains('is-open')); });
  close.addEventListener('click', function () { setPanel(false); });
  scrim.addEventListener('click', function () { setPanel(false); });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape' || !panel.classList.contains('is-open')) return;
    setPanel(false);
  });

  /* focus trap while the panel is open */
  panel.addEventListener('keydown', function (e) {
    if (e.key !== 'Tab') return;
    var f = panel.querySelectorAll('a[href], button:not([disabled])');
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  });

  /* ---------- accordion submenus ---------- */
  root.querySelectorAll('[data-ig-parent]').forEach(function (li) {
    var toggle = li.querySelector('.ig-menu__toggle');
    var sub    = li.querySelector('.ig-sub');
    if (!toggle || !sub) return;

    toggle.addEventListener('click', function () {
      var willOpen = !li.classList.contains('is-open');

      /* accordion behaviour: close siblings */
      root.querySelectorAll('[data-ig-parent].is-open').forEach(function (other) {
        if (other === li) return;
        other.classList.remove('is-open');
        other.querySelector('.ig-sub').style.maxHeight = null;
        other.querySelector('.ig-menu__toggle').setAttribute('aria-expanded', 'false');
      });

      li.classList.toggle('is-open', willOpen);
      toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
      sub.style.maxHeight = willOpen ? sub.scrollHeight + 'px' : null;
    });
  });

  /* ---------- close panel after clicking a link (same-page anchors) ---------- */
  panel.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function () { setPanel(false); });
  });
})();
