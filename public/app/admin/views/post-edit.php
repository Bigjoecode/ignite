<?php
// Vars: $post (posts row, or rejected form input merged in), $categories
$id        = (int) $post['id'];
$faq       = json_decode((string) $post['faq'], true) ?: [];
$state     = $id ? admin_post_state($post) : 'Draft';
$isLive    = $id && $post['status'] === 'published';
$whenValue = $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '';
$faqRow = static function (string $q = '', string $a = ''): string {
    return '<div class="adm-faq-row">'
        . '<div class="adm-faq-row__fields">'
        . '<input type="text" name="faq_q[]" value="' . e($q) . '" placeholder="Question" aria-label="Question" maxlength="300">'
        . '<textarea name="faq_a[]" rows="2" placeholder="Answer" aria-label="Answer" maxlength="2000">' . e($a) . '</textarea>'
        . '</div>'
        . '<button type="button" class="adm-icon-btn" data-adm-faq-remove aria-label="Remove this question">'
        . '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>'
        . '</div>';
};
?>
<form class="adm-editor" method="post" action="/admin/posts/save/" data-adm-editor novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="adm-head">
    <h1><?= $id ? 'Edit Post' : 'Add New Post' ?></h1>
<?php if ($id): ?>
    <a class="adm-btn" href="/admin/posts/new/"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add New</a>
<?php endif; ?>
  </div>

  <div class="adm-editor__grid">
    <div class="adm-editor__main">

      <input class="adm-title-input" type="text" name="title" value="<?= e($post['title']) ?>" placeholder="Add title" aria-label="Post title" maxlength="200" data-adm-title>

      <div class="adm-permalink">
        <span class="adm-permalink__label">Permalink:</span>
        <span class="adm-permalink__base">igniteorthodontics.com/blog/</span>
        <input type="text" name="slug" value="<?= e($post['slug']) ?>" placeholder="created-from-the-title" aria-label="Post web address" maxlength="80" data-adm-slug data-locked="<?= $post['slug'] !== '' ? '1' : '0' ?>">
        <span>/</span>
<?php if ($state === 'Published'): ?>
        <a href="/blog/<?= e($post['slug']) ?>/" target="_blank" rel="noopener">View post &nearr;</a>
<?php endif; ?>
      </div>
<?php if ($state === 'Published'): ?>
      <p class="adm-help adm-help--warn" data-adm-slug-warning hidden>Changing the address of a published post breaks links people may have saved or shared.</p>
<?php endif; ?>

      <div class="adm-editor__bar">
        <button type="button" class="adm-btn" data-adm-add-media>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z M4 15l4.5-4.5 4 4 3-3L20 16 M15.5 9.5h.01"/></svg>Add Media
        </button>
        <span class="adm-help">Tip: use Heading 2 for main sections &mdash; they become the article&rsquo;s &ldquo;On this page&rdquo; menu.</span>
      </div>
      <textarea name="body" id="adm-body" rows="24" data-adm-body><?= e($post['body']) ?></textarea>

      <section class="adm-card adm-box">
        <h2>Summary</h2>
        <p class="adm-help">Shown under the title, on the blog page cards and in Google search results. About 120&ndash;160 characters works best.</p>
        <textarea name="description" rows="3" maxlength="320" data-adm-count="160"><?= e($post['description']) ?></textarea>
        <p class="adm-counter" data-adm-counter aria-live="polite"></p>
      </section>

      <section class="adm-card adm-box">
        <h2>Frequently asked questions</h2>
        <p class="adm-help">Optional. Shown at the end of the article, and shared with Google as FAQ results.</p>
        <div class="adm-faq" data-adm-faq-list>
<?php foreach ($faq as $item) echo $faqRow((string) ($item[0] ?? ''), (string) ($item[1] ?? '')); ?>
        </div>
        <template data-adm-faq-template><?= $faqRow() ?></template>
        <button type="button" class="adm-btn" data-adm-faq-add>
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add question
        </button>
      </section>
    </div>

    <aside class="adm-editor__side">

      <section class="adm-card adm-box adm-publish">
        <h2>Publish</h2>
        <dl class="adm-publish__meta">
          <div><dt>Status</dt><dd><span class="adm-status adm-status--<?= e(strtolower($state)) ?>"><?= e($state) ?></span></dd></div>
          <div>
            <dt><label for="adm-when">Publish on</label></dt>
            <dd>
              <input type="datetime-local" id="adm-when" name="published_at" value="<?= e($whenValue) ?>" data-adm-when>
              <small class="adm-help">Leave empty to publish right away. A future date schedules the post.</small>
            </dd>
          </div>
<?php if ($id && $post['updated_at']): ?>
          <div><dt>Last saved</dt><dd><?= e(admin_datetime($post['updated_at'])) ?></dd></div>
<?php endif; ?>
        </dl>

        <div class="adm-publish__row">
<?php if (!$isLive): ?>
          <button type="submit" name="action" value="save_draft" class="adm-btn">Save Draft</button>
<?php else: ?>
          <button type="submit" name="action" value="unpublish" class="adm-btn" data-adm-confirm="Switch this post to a draft? It will be removed from the blog until you publish it again.">Switch to Draft</button>
<?php endif; ?>
<?php if ($id): ?>
          <a class="adm-btn" href="/admin/posts/<?= $id ?>/preview/" target="_blank" rel="noopener" data-adm-preview>Preview</a>
<?php endif; ?>
        </div>

        <div class="adm-publish__foot">
<?php if ($id): ?>
          <button type="submit" formaction="/admin/posts/<?= $id ?>/trash/" class="adm-link adm-link--danger" data-adm-confirm="Move this post to the trash? It will be removed from the blog.">Move to Trash</button>
<?php else: ?>
          <span></span>
<?php endif; ?>
<?php if ($isLive): ?>
          <button type="submit" name="action" value="update" class="adm-btn adm-btn--primary">Update</button>
<?php else: ?>
          <button type="submit" name="action" value="publish" class="adm-btn adm-btn--primary" data-adm-publish>Publish</button>
<?php endif; ?>
        </div>
      </section>

      <section class="adm-card adm-box">
        <h2><label for="adm-topic">Topic</label></h2>
        <input type="text" id="adm-topic" name="category" value="<?= e($post['category']) ?>" list="adm-topics" maxlength="40" placeholder="e.g. Treatment Options">
        <datalist id="adm-topics">
<?php foreach ($categories as $category): ?>
          <option value="<?= e($category) ?>"></option>
<?php endforeach; ?>
        </datalist>
        <p class="adm-help">Choose an existing topic or type a new one. Topics become the filters on the blog page.</p>
      </section>

      <section class="adm-card adm-box">
        <h2>Featured image</h2>
        <div class="adm-featured" data-adm-featured>
          <input type="hidden" name="image" value="<?= e($post['image']) ?>" data-adm-featured-input>
          <button type="button" class="adm-featured__pick" data-adm-featured-pick aria-label="Choose featured image">
            <img src="<?= e($post['image']) ?>" alt="" data-adm-featured-img<?= $post['image'] === '' ? ' hidden' : '' ?>>
            <span data-adm-featured-empty<?= $post['image'] !== '' ? ' hidden' : '' ?>>
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z M4 15l4.5-4.5 4 4 3-3L20 16 M15.5 9.5h.01"/></svg>
              Set featured image
            </span>
          </button>
          <label class="adm-field">
            <span>Alt text</span>
            <input type="text" name="image_alt" value="<?= e($post['image_alt']) ?>" maxlength="200" placeholder="Describe the image" data-adm-featured-alt>
          </label>
          <div class="adm-featured__actions"<?= $post['image'] === '' ? ' hidden' : '' ?> data-adm-featured-actions>
            <button type="button" class="adm-link" data-adm-featured-pick>Replace</button>
            <button type="button" class="adm-link adm-link--danger" data-adm-featured-remove>Remove</button>
          </div>
        </div>
      </section>

      <section class="adm-card adm-box">
        <h2>Options</h2>
        <label class="adm-check">
          <input type="checkbox" name="featured" value="1"<?= $post['featured'] ? ' checked' : '' ?>>
          <span>Feature this post at the top of the blog page</span>
        </label>
      </section>
    </aside>
  </div>
</form>

<script src="<?= admin_asset('tinymce/tinymce.min.js') ?>"></script>
