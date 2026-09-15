<?php
// Media library panel, shared by the Media Library page ($mode = 'manage') and the Add Media modal ('select').
$mode = $mode ?? 'manage';
?>
<div class="adm-lib" data-adm-library data-mode="<?= e($mode) ?>">
  <div class="adm-lib__tabs" role="tablist">
    <button type="button" role="tab" class="is-active" aria-selected="true" data-adm-lib-tab="library">Media Library</button>
    <button type="button" role="tab" aria-selected="false" data-adm-lib-tab="upload">Upload Files</button>
    <button type="button" role="tab" aria-selected="false" data-adm-lib-tab="url">Insert from URL</button>
  </div>

  <div class="adm-lib__pane" data-adm-lib-pane="upload" hidden>
    <label class="adm-drop" data-adm-drop>
      <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-adm-file>
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4 M7 9l5-5 5 5 M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
      <strong>Drop images here to upload</strong>
      <span>or</span>
      <span class="adm-btn">Select Files</span>
      <small>JPG, PNG, WebP or GIF &middot; up to 15 MB each &middot; large photos are resized automatically</small>
    </label>
    <ul class="adm-uploads" data-adm-upload-list></ul>
  </div>

  <div class="adm-lib__pane" data-adm-lib-pane="url" hidden>
    <form class="adm-url-form" data-adm-url-form>
      <label class="adm-field">
        <span>Image address</span>
        <input type="url" placeholder="https://example.com/photo.jpg" required data-adm-url>
      </label>
      <p class="adm-help">The image is copied into your media library, so the site never loads it from the other website.</p>
      <button class="adm-btn adm-btn--primary" type="submit">Import image</button>
      <p class="adm-notice" data-adm-url-status hidden></p>
    </form>
  </div>

  <div class="adm-lib__pane" data-adm-lib-pane="library">
    <div class="adm-lib__bar">
      <input type="search" placeholder="Search media" aria-label="Search media" data-adm-lib-search>
    </div>
    <div class="adm-lib__body">
      <div class="adm-lib__scroll">
        <div class="adm-lib__grid" data-adm-lib-grid aria-live="polite"></div>
        <p class="adm-lib__empty" data-adm-lib-empty hidden>No images found.</p>
        <button type="button" class="adm-btn adm-lib__more" data-adm-lib-more hidden>Load more</button>
      </div>
      <aside class="adm-lib__details" data-adm-lib-details hidden>
        <h3>Attachment details</h3>
        <img alt="" data-adm-d-img>
        <p class="adm-lib__name" data-adm-d-name></p>
        <p class="adm-help" data-adm-d-meta></p>
        <label class="adm-field">
          <span>Alt text</span>
          <input type="text" maxlength="200" data-adm-d-alt>
        </label>
        <p class="adm-help" data-adm-d-alt-status>Describe the image for people who can&rsquo;t see it.</p>
        <label class="adm-field">
          <span>File URL</span>
          <input type="text" readonly data-adm-d-url>
        </label>
        <button type="button" class="adm-link adm-link--danger" data-adm-d-delete>Delete permanently</button>
      </aside>
    </div>
  </div>

<?php if ($mode === 'select'): ?>
  <footer class="adm-lib__foot">
    <span class="adm-help" data-adm-lib-selected>No image selected</span>
    <button type="button" class="adm-btn adm-btn--primary" data-adm-lib-choose disabled>Insert into post</button>
  </footer>
<?php endif; ?>
</div>
