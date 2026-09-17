/* Ignite Orthodontics admin: media library, Add Media modal and the post editor. */
(() => {
  'use strict';

  const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  const $  = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const GENERIC_ERROR = 'Something went wrong. Please try again.';

  async function api(url, { method = 'GET', fields = null } = {}) {
    let body = null;
    if (fields) {
      body = new FormData();
      body.append('_csrf', csrf);
      Object.entries(fields).forEach(([k, v]) => body.append(k, v));
    }
    try {
      const res = await fetch(url, {
        method, body, credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-CSRF-Token': csrf },
      });
      const data = await res.json().catch(() => null);
      return data || { ok: false, error: GENERIC_ERROR };
    } catch (e) {
      return { ok: false, error: 'Could not reach the server. Check your connection and try again.' };
    }
  }

  function upload(file, onProgress) {
    return new Promise((resolve) => {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', '/admin/media/upload/');
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('X-CSRF-Token', csrf);
      xhr.upload.onprogress = (e) => { if (e.lengthComputable && onProgress) onProgress(e.loaded / e.total); };
      xhr.onload = () => {
        let data = null;
        try { data = JSON.parse(xhr.responseText); } catch (e) { /* not JSON */ }
        resolve(data || { ok: false, error: GENERIC_ERROR });
      };
      xhr.onerror = () => resolve({ ok: false, error: 'The upload failed. Check your connection.' });
      const fd = new FormData();
      fd.append('_csrf', csrf);
      fd.append('files[]', file, file.name || 'image');
      xhr.send(fd);
    });
  }

  const formatBytes = (n) => (n >= 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB');
  const escapeAttr = (s) => String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  const IMAGE_TYPES = /^image\/(jpeg|png|webp|gif)$/;

  /* ---------- sidebar on small screens ---------- */
  const menuBtn = $('[data-adm-menu]');
  const side = $('[data-adm-side]');
  if (menuBtn && side) {
    menuBtn.addEventListener('click', () => {
      const open = side.classList.toggle('is-open');
      menuBtn.setAttribute('aria-expanded', String(open));
    });
  }

  /* ---------- confirmations (forms and individual submit buttons) ---------- */
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-adm-confirm]');
    if (btn && !window.confirm(btn.getAttribute('data-adm-confirm'))) e.preventDefault();
  }, true);
  document.addEventListener('submit', (e) => {
    const msg = e.target.getAttribute('data-adm-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  }, true);

  /* ============================================================
     MEDIA LIBRARY (page and modal share this)
     ============================================================ */
  class Library {
    constructor(root) {
      this.root = root;
      this.mode = root.dataset.mode;
      this.items = [];
      this.page = 1;
      this.q = '';
      this.loaded = false;
      this.selected = null;
      this.onChoose = null;

      this.grid     = $('[data-adm-lib-grid]', root);
      this.empty    = $('[data-adm-lib-empty]', root);
      this.more     = $('[data-adm-lib-more]', root);
      this.details  = $('[data-adm-lib-details]', root);
      this.chooseBtn = $('[data-adm-lib-choose]', root);
      this.selectedLabel = $('[data-adm-lib-selected]', root);

      $$('[data-adm-lib-tab]', root).forEach((tab) => tab.addEventListener('click', () => this.tab(tab.dataset.admLibTab)));

      let timer;
      $('[data-adm-lib-search]', root).addEventListener('input', (e) => {
        clearTimeout(timer);
        timer = setTimeout(() => { this.q = e.target.value.trim(); this.load(true); }, 250);
      });
      this.more.addEventListener('click', () => { this.page += 1; this.load(false); });

      this.bindUpload();
      this.bindUrl();
      this.bindDetails();
      if (this.chooseBtn) this.chooseBtn.addEventListener('click', () => this.choose());
    }

    tab(name) {
      $$('[data-adm-lib-tab]', this.root).forEach((t) => {
        const on = t.dataset.admLibTab === name;
        t.classList.toggle('is-active', on);
        t.setAttribute('aria-selected', String(on));
      });
      $$('[data-adm-lib-pane]', this.root).forEach((p) => { p.hidden = p.dataset.admLibPane !== name; });
      if (name === 'library' && !this.loaded) this.load(true);
      if (name === 'url') setTimeout(() => $('[data-adm-url]', this.root).focus(), 20);
    }

    async load(reset) {
      if (reset) { this.page = 1; this.items = []; this.grid.innerHTML = ''; }
      this.grid.setAttribute('aria-busy', 'true');
      const data = await api(`/admin/media/list/?q=${encodeURIComponent(this.q)}&page=${this.page}`);
      this.grid.removeAttribute('aria-busy');
      this.loaded = true;
      if (!data.ok) { this.empty.textContent = data.error || GENERIC_ERROR; this.empty.hidden = false; return; }
      data.items.forEach((item) => { this.items.push(item); this.grid.appendChild(this.tile(item)); });
      this.empty.textContent = this.q ? 'No images match your search.' : 'No images yet. Upload your first one.';
      this.empty.hidden = this.items.length > 0;
      this.more.hidden = !data.has_more;
      if (this.selected) this.markSelected();
    }

    tile(item) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'adm-lib__item';
      btn.dataset.id = item.id;
      btn.title = item.name;
      btn.setAttribute('aria-label', item.alt || item.name);
      const img = document.createElement('img');
      img.src = item.url;
      img.alt = '';
      img.loading = 'lazy';
      btn.appendChild(img);
      btn.addEventListener('click', () => this.select(item.id));
      btn.addEventListener('dblclick', () => { if (this.mode === 'select') { this.select(item.id); this.choose(); } });
      return btn;
    }

    select(id) {
      this.selected = this.items.find((i) => i.id === id) || null;
      this.markSelected();
      this.showDetails();
    }

    markSelected() {
      $$('.adm-lib__item', this.grid).forEach((el) => el.classList.toggle('is-selected', this.selected && Number(el.dataset.id) === this.selected.id));
      if (this.chooseBtn) this.chooseBtn.disabled = !this.selected;
      if (this.selectedLabel) this.selectedLabel.textContent = this.selected ? `Selected: ${this.selected.name}` : 'No image selected';
    }

    showDetails() {
      const item = this.selected;
      this.details.hidden = !item;
      if (!item) return;
      $('[data-adm-d-img]', this.details).src = item.url;
      $('[data-adm-d-name]', this.details).textContent = item.name;
      $('[data-adm-d-meta]', this.details).textContent = `${item.width} × ${item.height} px · ${formatBytes(item.bytes)} · ${item.date}`;
      $('[data-adm-d-alt]', this.details).value = item.alt || '';
      $('[data-adm-d-alt-status]', this.details).textContent = 'Describe the image for people who can’t see it.';
      $('[data-adm-d-url]', this.details).value = item.url;
      $('[data-adm-d-delete]', this.details).hidden = !item.deletable;
    }

    bindDetails() {
      const alt = $('[data-adm-d-alt]', this.details);
      const status = $('[data-adm-d-alt-status]', this.details);
      alt.addEventListener('change', async () => {
        const item = this.selected;
        if (!item) return;
        status.textContent = 'Saving…';
        const data = await api(`/admin/media/${item.id}/`, { method: 'POST', fields: { alt: alt.value } });
        if (data.ok) {
          item.alt = data.item.alt;
          status.textContent = 'Alt text saved.';
        } else {
          status.textContent = data.error || GENERIC_ERROR;
        }
      });
      $('[data-adm-d-url]', this.details).addEventListener('focus', (e) => e.target.select());
      $('[data-adm-d-delete]', this.details).addEventListener('click', async () => {
        const item = this.selected;
        if (!item || !window.confirm(`Delete "${item.name}" permanently? Posts that use it will show a missing image.`)) return;
        const data = await api(`/admin/media/${item.id}/delete/`, { method: 'POST', fields: {} });
        if (!data.ok) { window.alert(data.error || GENERIC_ERROR); return; }
        this.items = this.items.filter((i) => i.id !== item.id);
        const tile = this.grid.querySelector(`[data-id="${item.id}"]`);
        if (tile) tile.remove();
        this.selected = null;
        this.markSelected();
        this.showDetails();
        this.empty.hidden = this.items.length > 0;
      });
    }

    async added(items) {
      if (!items.length) return;
      if (this.loaded && !this.q) {
        items.slice().reverse().forEach((item) => { this.items.unshift(item); this.grid.prepend(this.tile(item)); });
        this.empty.hidden = true;
        this.tab('library');
      } else {
        this.q = '';
        $('[data-adm-lib-search]', this.root).value = '';
        this.tab('library');
        await this.load(true);
      }
      this.select(items[items.length - 1].id);
    }

    bindUpload() {
      const drop = $('[data-adm-drop]', this.root);
      const input = $('[data-adm-file]', this.root);
      const list = $('[data-adm-upload-list]', this.root);

      const run = async (files) => {
        const done = [];
        for (const file of files) {
          const li = document.createElement('li');
          const name = document.createElement('span');
          name.textContent = file.name;
          const state = document.createElement('span');
          li.append(name, state);
          list.prepend(li);
          if (!IMAGE_TYPES.test(file.type)) {
            state.textContent = 'Only JPG, PNG, WebP or GIF';
            state.className = 'is-error';
            continue;
          }
          const bar = document.createElement('progress');
          bar.max = 100;
          bar.value = 0;
          li.appendChild(bar);
          state.textContent = 'Uploading…';
          // eslint-disable-next-line no-await-in-loop
          const data = await upload(file, (p) => { bar.value = Math.round(p * 100); });
          bar.remove();
          if (data.ok && data.items && data.items.length) {
            state.textContent = 'Uploaded';
            state.className = 'is-done';
            done.push(data.items[0]);
          } else {
            state.textContent = (data.errors && data.errors[0]) || data.error || 'Upload failed';
            state.className = 'is-error';
          }
        }
        if (done.length) this.added(done);
      };

      input.addEventListener('change', () => { run(Array.from(input.files)); input.value = ''; });
      ['dragenter', 'dragover'].forEach((type) => drop.addEventListener(type, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
      ['dragleave', 'drop'].forEach((type) => drop.addEventListener(type, (e) => { e.preventDefault(); drop.classList.remove('is-over'); }));
      drop.addEventListener('drop', (e) => run(Array.from(e.dataTransfer.files)));
      // dropping anywhere on the library opens the upload tab
      this.root.addEventListener('dragenter', (e) => {
        if (e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files')) this.tab('upload');
      });
    }

    bindUrl() {
      const form = $('[data-adm-url-form]', this.root);
      const input = $('[data-adm-url]', this.root);
      const status = $('[data-adm-url-status]', this.root);
      const button = $('button[type="submit"]', form);
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!input.value.trim()) { input.focus(); return; }
        button.disabled = true;
        status.hidden = false;
        status.className = 'adm-notice';
        status.textContent = 'Importing image…';
        const data = await api('/admin/media/url/', { method: 'POST', fields: { url: input.value.trim() } });
        button.disabled = false;
        if (data.ok) {
          status.className = 'adm-notice adm-notice--success';
          status.textContent = 'Image imported into your media library.';
          input.value = '';
          this.added([data.item]);
        } else {
          status.className = 'adm-notice adm-notice--error';
          status.textContent = data.error || GENERIC_ERROR;
        }
      });
    }

    choose() {
      if (this.selected && this.onChoose) this.onChoose(this.selected);
    }
  }

  // Media Library page
  $$('[data-adm-library]').forEach((root) => {
    if (!root.closest('[data-adm-modal]')) new Library(root).tab('library');
  });

  /* ---------- Add Media modal ---------- */
  const modal = $('[data-adm-modal]');
  const modalLib = modal ? new Library($('[data-adm-library]', modal)) : null;
  let returnFocus = null;

  function closeMedia() {
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    document.documentElement.classList.remove('adm-lock');
    if (returnFocus && returnFocus.focus) returnFocus.focus();
  }

  function openMedia({ title = 'Add Media', button = 'Insert into post', onChoose }) {
    if (!modal) return;
    $('[data-adm-modal-title]', modal).textContent = title;
    $('[data-adm-lib-choose]', modal).textContent = button;
    modalLib.onChoose = (item) => { closeMedia(); onChoose(item); };
    returnFocus = document.activeElement;
    modal.hidden = false;
    document.documentElement.classList.add('adm-lock');
    modalLib.tab('library');
    setTimeout(() => $('[data-adm-lib-search]', modal).focus(), 30);
  }

  if (modal) {
    $('[data-adm-modal-close]', modal).addEventListener('click', closeMedia);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeMedia(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMedia(); });
  }

  /* ============================================================
     POST EDITOR
     ============================================================ */
  const form = $('[data-adm-editor]');
  if (!form) return;

  let dirty = false;
  let keepDirty = false; // the preview opens in a new tab, so the page itself keeps its changes
  const markDirty = () => { dirty = true; };
  form.addEventListener('input', markDirty);
  form.addEventListener('change', markDirty);
  form.addEventListener('submit', () => {
    if (keepDirty) { keepDirty = false; return; }
    dirty = false;
  });
  window.addEventListener('beforeunload', (e) => {
    if (!dirty) return;
    e.preventDefault();
    e.returnValue = '';
  });

  /* title -> web address */
  const title = $('[data-adm-title]', form);
  const slug = $('[data-adm-slug]', form);
  const slugWarning = $('[data-adm-slug-warning]');
  const slugify = (s) => s.toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80);
  let slugLocked = slug.dataset.locked === '1';
  const originalSlug = slug.value;
  title.addEventListener('input', () => { if (!slugLocked) slug.value = slugify(title.value); });
  slug.addEventListener('input', () => {
    slugLocked = slug.value.trim() !== '';
    if (slugWarning) slugWarning.hidden = slug.value === originalSlug;
  });
  slug.addEventListener('blur', () => {
    slug.value = slugify(slug.value);
    if (!slug.value) { slugLocked = false; slug.value = slugify(title.value); }
  });
  if (!title.value) title.focus();

  /* summary character count */
  $$('[data-adm-count]', form).forEach((field) => {
    const out = $('[data-adm-counter]', field.parentElement);
    const target = Number(field.dataset.admCount);
    const update = () => {
      const n = field.value.trim().length;
      let note = '';
      if (n > target) note = ' — may be cut off in Google results';
      else if (n > 0 && n < 70) note = ' — a little short';
      out.textContent = `${n} characters${note}`;
      out.classList.toggle('is-warn', n > target);
    };
    field.addEventListener('input', update);
    update();
  });

  /* FAQ rows (posts) */
  const faqList = $('[data-adm-faq-list]', form);
  const faqTemplate = $('[data-adm-faq-template]', form);
  if (faqList && faqTemplate) {
    $('[data-adm-faq-add]', form).addEventListener('click', () => {
      faqList.insertAdjacentHTML('beforeend', faqTemplate.innerHTML);
      $('input', faqList.lastElementChild).focus();
      markDirty();
    });
    faqList.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-adm-faq-remove]');
      if (!btn) return;
      btn.closest('.adm-faq-row').remove();
      markDirty();
    });
  }

  /* Publish becomes Schedule for a future date */
  const when = $('[data-adm-when]', form);
  const publishBtn = $('[data-adm-publish]', form);
  const updatePublish = () => {
    if (!publishBtn) return;
    publishBtn.textContent = when.value && new Date(when.value) > new Date() ? 'Schedule' : 'Publish';
  };
  if (when) when.addEventListener('input', updatePublish);
  updatePublish();

  const previewLink = $('[data-adm-preview]', form);
  if (previewLink) {
    previewLink.addEventListener('click', (e) => {
      if (dirty && !window.confirm('The preview shows the last saved version. Save first to see your latest changes.\n\nOpen the preview anyway?')) {
        e.preventDefault();
      }
    });
  }

  /* featured image (posts) */
  const featured = $('[data-adm-featured]', form);
  if (featured) {
  const setFeatured = (item) => {
    $('[data-adm-featured-input]', featured).value = item ? item.url : '';
    const img = $('[data-adm-featured-img]', featured);
    img.src = item ? item.url : '';
    img.hidden = !item;
    $('[data-adm-featured-empty]', featured).hidden = !!item;
    $('[data-adm-featured-actions]', featured).hidden = !item;
    const alt = $('[data-adm-featured-alt]', featured);
    if (item && !alt.value) alt.value = item.alt || '';
    markDirty();
  };
  $$('[data-adm-featured-pick]', featured).forEach((btn) => btn.addEventListener('click', () => {
    openMedia({ title: 'Featured image', button: 'Set featured image', onChoose: setFeatured });
  }));
  $('[data-adm-featured-remove]', featured).addEventListener('click', () => setFeatured(null));
  }

  /* photo fields (pages): picker, preview and alt text */
  function bindImages(root) {
    $$('[data-adm-img]', root).forEach((box) => {
      if (box.dataset.bound) return;
      box.dataset.bound = '1';
      const input = $('[data-adm-img-input]', box);
      const preview = $('[data-adm-img-preview]', box);
      const empty = $('[data-adm-img-empty]', box);
      const actions = $('[data-adm-img-actions]', box);
      const alt = $('[data-adm-img-alt]', box);
      const set = (item) => {
        input.value = item ? item.url : '';
        preview.src = item ? item.url : '';
        preview.hidden = !item;
        empty.hidden = !!item;
        if (actions) actions.hidden = !item;
        if (item && alt && !alt.value) alt.value = item.alt || '';
        markDirty();
      };
      $$('[data-adm-img-pick]', box).forEach((btn) => btn.addEventListener('click', () => {
        openMedia({ title: 'Choose a photo', button: 'Use this photo', onChoose: set });
      }));
      const remove = $('[data-adm-img-remove]', box);
      if (remove) remove.addEventListener('click', () => set(null));
    });
  }
  bindImages(form);

  /* ---------- page editor: sections and repeating rows ---------- */
  if (form.hasAttribute('data-adm-page')) {
    $$('[data-adm-sec]', form).forEach((sec) => {
      const toggle = $('[data-adm-sec-toggle]', sec);
      const body = $('.adm-sec__body', sec);
      toggle.addEventListener('click', () => {
        const open = body.hidden;
        body.hidden = !open;
        sec.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
      });
      const show = $('[data-adm-sec-show]', sec);
      if (show) show.addEventListener('change', () => sec.classList.toggle('is-off', !show.checked));
    });
    // the first section starts open, so the editor never looks empty
    const firstSection = $('[data-adm-sec]', form);
    if (firstSection) $('[data-adm-sec-toggle]', firstSection).click();

    const renumber = (list) => {
      const rows = $$('[data-adm-item]', list);
      rows.forEach((row, i) => { $('[data-adm-item-title]', row).textContent = `${list.dataset.label} ${i + 1}`; });
      const count = $('[data-adm-list-count]', list);
      const max = Number(list.dataset.max);
      if (count) count.textContent = rows.length ? `${rows.length} of ${max}` : 'None yet';
      $('[data-adm-list-add]', list).disabled = rows.length >= max;
    };

    // names only have to be unique: the order comes from the order on the page
    let nextRow = 1000;
    const bindList = (list) => {
      if (list.dataset.bound) return;
      list.dataset.bound = '1';
      const rows = $('[data-adm-list-rows]', list);
      const template = $('[data-adm-list-template]', list);
      $('[data-adm-list-add]', list).addEventListener('click', () => {
        rows.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__i__/g, String(nextRow++)));
        const row = rows.lastElementChild;
        bindImages(row);
        renumber(list);
        const first = $('input[type="text"], textarea', row);
        if (first) first.focus();
        markDirty();
      });
      list.addEventListener('click', (e) => {
        const row = e.target.closest('[data-adm-item]');
        if (!row) return;
        if (e.target.closest('[data-adm-item-remove]')) {
          row.remove();
        } else if (e.target.closest('[data-adm-item-up]') && row.previousElementSibling) {
          rows.insertBefore(row, row.previousElementSibling);
        } else if (e.target.closest('[data-adm-item-down]') && row.nextElementSibling) {
          rows.insertBefore(row.nextElementSibling, row);
        } else {
          return;
        }
        renumber(list);
        markDirty();
      });
      renumber(list);
    };
    $$('[data-adm-list]', form).forEach(bindList);

    /* page blocks (Treatment Guide): add by type, open, reorder, remove */
    const setBlockOpen = (block, open) => {
      $('.adm-block__body', block).hidden = !open;
      block.classList.toggle('is-open', open);
      $('[data-adm-block-toggle]', block).setAttribute('aria-expanded', String(open));
    };
    $$('[data-adm-blocks]', form).forEach((box) => {
      const rows = $('[data-adm-blocks-rows]', box);
      const picker = $('[data-adm-block-type]', box);
      let nextBlock = 1000;

      $('[data-adm-block-add]', box).addEventListener('click', () => {
        const template = $(`template[data-adm-block-template="${picker.value}"]`, box);
        if (!template) return;
        rows.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__b__/g, String(nextBlock++)));
        const block = rows.lastElementChild;
        bindImages(block);
        $$('[data-adm-list]', block).forEach(bindList);
        setBlockOpen(block, true);
        block.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const first = $('input[type="text"]', block);
        if (first) setTimeout(() => first.focus({ preventScroll: true }), 300);
        markDirty();
      });

      rows.addEventListener('click', (e) => {
        const block = e.target.closest('[data-adm-block]');
        if (!block || block.parentElement !== rows) return;
        if (e.target.closest('[data-adm-block-toggle]')) {
          setBlockOpen(block, block.querySelector('.adm-block__body').hidden);
          return;
        }
        if (e.target.closest('[data-adm-block-remove]')) {
          if (!window.confirm('Remove this block from the page?')) return;
          block.remove();
        } else if (e.target.closest('[data-adm-block-up]') && block.previousElementSibling) {
          rows.insertBefore(block, block.previousElementSibling);
        } else if (e.target.closest('[data-adm-block-down]') && block.nextElementSibling) {
          rows.insertBefore(block.nextElementSibling, block);
        } else {
          return;
        }
        markDirty();
      });

      // the collapsed header shows the block's heading
      rows.addEventListener('input', (e) => {
        if (!e.target.matches('input[name$="[heading]"]')) return;
        const block = e.target.closest('[data-adm-block]');
        if (block && e.target.closest('[data-adm-block]') === block && e.target.name.split('[').length === 5) {
          $('[data-adm-block-title]', block).textContent = e.target.value.replace(/\*/g, '');
        }
      });
    });

    /* parent page: the address prefix follows the choice */
    const parentSelect = $('[data-adm-parent]', form);
    if (parentSelect) {
      parentSelect.addEventListener('change', () => {
        $$('[data-adm-parent-prefix]', form).forEach((el) => { el.textContent = parentSelect.value ? `${parentSelect.value}/` : ''; });
      });
    }

    /* switching layout is a save-less round trip, so it needs its own button */
    const templateSelect = $('[data-adm-template]', form);
    if (templateSelect) {
      const chosen = templateSelect.value;
      const apply = $('[data-adm-template-apply]', form);
      const note = $('[data-adm-template-note]', form);
      templateSelect.addEventListener('change', () => {
        const changed = templateSelect.value !== chosen;
        if (apply) apply.hidden = !changed;
        if (note) note.hidden = !changed;
      });
    }

    /* preview what is on screen, including unsaved changes */
    const previewBtn = $('[data-adm-preview-post]', form);
    if (previewBtn) {
      previewBtn.addEventListener('click', () => {
        keepDirty = true;
        if (window.tinymce) window.tinymce.triggerSave();
      });
    }

    /* the address shown in the sidebar follows what is typed */
    const urlSlugs = $$('[data-adm-url-slug]', form);
    if (urlSlugs.length) {
      const updateUrl = () => {
        const value = slug.value.trim() || slugify(title.value) || '…';
        urlSlugs.forEach((el) => { el.textContent = value; });
      };
      [title, slug].forEach((el) => el.addEventListener('input', updateUrl));
      updateUrl();
    }

    /* live Google-result preview */
    const seoTitle = $('[data-adm-seo-title]', form);
    const seoDesc = $('[data-adm-seo-desc]', form);
    if (seoTitle && seoDesc) {
      const seoTitleInput = $('[data-adm-seo-title-input]', form);
      const seoDescInput = $('[data-adm-seo-desc-input]', form);
      const clip = (text, max) => (text.length > max ? `${text.slice(0, max - 1).trimEnd()}…` : text);
      const updateSeo = () => {
        const name = title.value.trim();
        seoTitle.textContent = clip(seoTitleInput.value.trim() || (name ? `${name} | Ignite Orthodontics` : 'Page name | Ignite Orthodontics'), 60);
        seoDesc.textContent = clip(seoDescInput.value.trim() || 'Add a summary so Google shows the right words here.', 160);
      };
      [title, seoTitleInput, seoDescInput].forEach((el) => el.addEventListener('input', updateSeo));
      updateSeo();
    }
  }

  /* rich text editor */
  const insertImage = (editor, item) => {
    if (!editor) return;
    editor.insertContent(`<img src="${escapeAttr(item.url)}" alt="${escapeAttr(item.alt || '')}" width="${item.width}" height="${item.height}">`);
  };
  const openAddMedia = () => openMedia({ onChoose: (item) => insertImage(window.tinymce && window.tinymce.activeEditor, item) });
  const addMediaBtn = $('[data-adm-add-media]', form);
  if (addMediaBtn) addMediaBtn.addEventListener('click', openAddMedia);

  if (!window.tinymce) return; // plain textarea still works if the editor failed to load

  const editorConfig = (selector, height) => ({
    selector,
    license_key: 'gpl',
    base_url: '/admin-assets/tinymce',
    suffix: '.min',
    plugins: 'lists link image table code autolink wordcount fullscreen searchreplace charmap visualblocks quickbars',
    menubar: false,
    toolbar: 'undo redo | blocks | bold italic underline | bullist numlist blockquote callout | link addmedia table | removeformat | searchreplace visualblocks code fullscreen',
    toolbar_mode: 'wrap',
    block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
    quickbars_insert_toolbar: false,
    quickbars_selection_toolbar: 'bold italic | h2 h3 | link blockquote',
    quickbars_image_toolbar: 'image',
    // fixed-height editor that scrolls (drag the corner to make it taller); an auto-growing frame left
    // empty space below the text where clicks did not place a visible cursor
    height,
    min_height: 300,
    resize: true,
    relative_urls: false,
    remove_script_host: true,
    convert_urls: true,
    document_base_url: window.location.origin + '/',
    link_title: false,
    target_list: false,
    link_default_protocol: 'https',
    image_dimensions: false,
    image_description: true,
    object_resizing: false,
    table_style_by_css: false,
    table_default_styles: {},
    table_default_attributes: {},
    paste_data_images: true,
    automatic_uploads: true,
    images_file_types: 'jpeg,jpg,png,webp,gif',
    images_upload_handler: (blobInfo, progress) => upload(blobInfo.blob(), progress && ((p) => progress(p * 100)))
      .then((data) => {
        if (data.ok && data.location) return data.location;
        throw { message: (data.errors && data.errors[0]) || data.error || 'Image upload failed.', remove: true };
      }),
    content_css: ['https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap'],
    content_style: [
      // the text area fills the whole editing frame, so a click anywhere lands in the text and shows the cursor
      'html{height:100%}',
      'body{box-sizing:border-box;min-height:100%;margin:0;padding:24px 32px 48px;font-family:Poppins,system-ui,sans-serif;font-size:17px;line-height:1.8;color:#33435A;caret-color:#F47421}',
      'h2{font-size:28px;line-height:1.25;color:#0B2A55;margin:1.6em 0 .6em}',
      'h3{font-size:21px;line-height:1.35;color:#0B2A55;margin:1.4em 0 .45em}',
      'h4{font-size:18px;color:#0B2A55;margin:1.2em 0 .4em}',
      'a{color:#DA6210}strong{color:#0B2A55}',
      'img{max-width:100%;height:auto;border-radius:12px}',
      'blockquote{margin:1.5em 0;padding:.6em 1.2em;border-left:4px solid #F47421;background:#FFF6EF;color:#0B2A55}',
      '.post-callout{margin:1.6em 0;padding:18px 22px;border-left:5px solid #F47421;border-radius:0 14px 14px 0;background:#FDEEE3}',
      '.post-callout strong{display:block;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#DA6210}',
      '.post-callout p{margin:.3em 0 0;color:#0B2A55}',
      'table{width:100%;border-collapse:collapse;margin:1.4em 0}',
      'th,td{padding:10px 12px;border:1px solid #E3E9F1;text-align:left;vertical-align:top}',
      'thead th{background:#0B2A55;color:#fff}',
    ].join(''),
    setup: (editor) => {
      editor.ui.registry.addButton('addmedia', {
        icon: 'image',
        text: 'Add Media',
        tooltip: 'Insert an image from the media library',
        onAction: openAddMedia,
      });
      editor.ui.registry.addButton('callout', {
        icon: 'info',
        tooltip: 'Key takeaway box',
        onAction: () => editor.insertContent('<div class="post-callout"><strong>Key takeaway</strong><p>Write the key point here.</p></div><p></p>'),
      });
      editor.on('input change undo redo SetContent', () => { if (editor.initialized) markDirty(); });
      editor.on('init', () => { dirty = false; });
    },
  });

  if ($('#adm-body')) window.tinymce.init(editorConfig('#adm-body', 700));
  if ($('[data-adm-rte]')) window.tinymce.init(editorConfig('[data-adm-rte]', 460));
})();
