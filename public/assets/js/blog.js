/* Blog: index search + topic filter, article reading progress,
   "On this page" highlight, and copy-link buttons. */
(function () {
  var toArray = function (list) { return Array.prototype.slice.call(list); };

  /* ---------- index: search + topic chips ---------- */
  var search = document.querySelector('[data-bl-search]');
  var chips  = toArray(document.querySelectorAll('[data-bl-cat]'));
  var items  = toArray(document.querySelectorAll('[data-bl-item]'));
  var empty  = document.querySelector('[data-bl-empty]');

  if (items.length && (search || chips.length)) {
    var active = '';

    var apply = function () {
      var q = search ? search.value.trim().toLowerCase() : '';
      var shown = 0;
      items.forEach(function (el) {
        var ok = (!active || el.getAttribute('data-cat') === active) &&
                 (!q || el.getAttribute('data-text').indexOf(q) !== -1);
        el.hidden = !ok;
        if (ok) shown++;
      });
      if (empty) empty.hidden = shown > 0;
      chips.forEach(function (chip) {
        var on = chip.getAttribute('data-bl-cat') === active;
        chip.classList.toggle('is-active', on);
        chip.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
    };

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        active = chip.getAttribute('data-bl-cat');
        apply();
        if (window.history && history.replaceState) {
          history.replaceState(null, '', active ? '?topic=' + encodeURIComponent(active) : window.location.pathname);
        }
      });
    });
    if (search) search.addEventListener('input', apply);

    // /blog/?topic=kids-teens (linked from an article's topic tag)
    var topic = new URLSearchParams(window.location.search).get('topic');
    if (topic && chips.some(function (c) { return c.getAttribute('data-bl-cat') === topic; })) active = topic;
    apply();
  }

  /* ---------- article: reading progress under the fixed header ---------- */
  var article = document.querySelector('[data-bl-article]');
  var bar = document.querySelector('[data-bl-progress]');
  if (article && bar) {
    var header = document.getElementById('igHeader');
    var ticking = false;
    var update = function () {
      ticking = false;
      if (header) bar.parentNode.style.top = Math.max(0, header.getBoundingClientRect().bottom) + 'px';
      var rect = article.getBoundingClientRect();
      var total = article.offsetHeight - window.innerHeight;
      var p = total > 0 ? Math.min(1, Math.max(0, -rect.top / total)) : 1;
      bar.style.width = (p * 100).toFixed(2) + '%';
    };
    var onScroll = function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
  }

  /* ---------- article: highlight the section being read ---------- */
  var tocLinks = toArray(document.querySelectorAll('[data-bl-toc] a'));
  if (tocLinks.length && 'IntersectionObserver' in window) {
    var ids = [];
    tocLinks.forEach(function (a) {
      var id = a.getAttribute('href').slice(1);
      if (ids.indexOf(id) === -1 && document.getElementById(id)) ids.push(id);
    });
    var setActive = function (id) {
      tocLinks.forEach(function (a) { a.classList.toggle('is-active', a.getAttribute('href') === '#' + id); });
    };
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) { if (entry.isIntersecting) setActive(entry.target.id); });
    }, { rootMargin: '-120px 0px -65% 0px' });
    ids.forEach(function (id) { observer.observe(document.getElementById(id)); });
  }

  // mobile "On this page": close the panel after jumping to a section
  toArray(document.querySelectorAll('.bl-toc-mobile a')).forEach(function (a) {
    a.addEventListener('click', function () { a.closest('details').open = false; });
  });

  /* ---------- copy link ---------- */
  toArray(document.querySelectorAll('[data-bl-copy]')).forEach(function (btn) {
    var label = btn.querySelector('span');
    var idle = label ? label.textContent : '';
    var copied = function () {
      btn.classList.add('is-copied');
      if (label) label.textContent = 'Link copied';
      setTimeout(function () { btn.classList.remove('is-copied'); if (label) label.textContent = idle; }, 2000);
    };
    var fallback = function (text) {
      var t = document.createElement('textarea');
      t.value = text;
      t.setAttribute('readonly', '');
      t.style.position = 'fixed';
      t.style.opacity = '0';
      document.body.appendChild(t);
      t.select();
      try { if (document.execCommand('copy')) copied(); } catch (e) { /* nothing else to try */ }
      document.body.removeChild(t);
    };
    btn.addEventListener('click', function () {
      var url = btn.getAttribute('data-url') || window.location.href;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(copied, function () { fallback(url); });
      } else {
        fallback(url);
      }
    });
  });
})();
