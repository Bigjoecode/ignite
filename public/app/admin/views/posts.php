<?php
// Vars: $posts, $tab, $q, $counts
$tabs = [
    'all'       => ['All', 'all_posts'],
    'published' => ['Published', 'published'],
    'scheduled' => ['Scheduled', 'scheduled'],
    'draft'     => ['Drafts', 'draft'],
    'trash'     => ['Trash', 'trash'],
];
$now = date('Y-m-d H:i:s');
?>
<div class="adm-head">
  <h1>Posts</h1>
  <a class="adm-btn adm-btn--primary" href="/admin/posts/new/">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Add New Post
  </a>
</div>

<div class="adm-toolbar">
  <nav class="adm-tabs" aria-label="Filter posts">
<?php foreach ($tabs as $key => [$label, $countKey]): ?>
<?php if ($key !== 'all' && !$counts[$countKey] && $tab !== $key) continue; ?>
    <a href="/admin/posts/?status=<?= e($key) ?>"<?= $tab === $key ? ' class="is-active" aria-current="page"' : '' ?>><?= e($label) ?> <span>(<?= (int) $counts[$countKey] ?>)</span></a>
<?php endforeach; ?>
  </nav>
  <form class="adm-search" method="get" action="/admin/posts/" role="search">
    <input type="hidden" name="status" value="<?= e($tab) ?>">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search posts" aria-label="Search posts">
    <button class="adm-btn" type="submit">Search</button>
  </form>
</div>

<?php if (!$posts): ?>
<div class="adm-card adm-empty">
<?php if ($q !== ''): ?>
  <h2>No posts match &ldquo;<?= e($q) ?>&rdquo;</h2>
  <p><a href="/admin/posts/?status=<?= e($tab) ?>">Clear the search</a></p>
<?php elseif ($tab === 'trash'): ?>
  <h2>The trash is empty</h2>
<?php else: ?>
  <h2>No posts here yet</h2>
  <p>Write your first article and publish it to the blog.</p>
  <a class="adm-btn adm-btn--primary" href="/admin/posts/new/">Add New Post</a>
<?php endif; ?>
</div>
<?php else: ?>
<div class="adm-card adm-table-wrap">
  <table class="adm-table">
    <thead>
      <tr><th scope="col">Title</th><th scope="col">Topic</th><th scope="col">Status</th><th scope="col">Date</th></tr>
    </thead>
    <tbody>
<?php foreach ($posts as $row):
    $state = admin_post_state($row);
    $title = $row['title'] !== '' ? $row['title'] : '(no title)';
?>
      <tr>
        <td>
          <div class="adm-post-cell">
            <span class="adm-thumb"><?php if ($row['image'] !== ''): ?><img src="<?= e($row['image']) ?>" alt="" loading="lazy"><?php endif; ?></span>
            <div>
<?php if ($row['status'] === 'trash'): ?>
              <span class="adm-post-title"><?= e($title) ?></span>
<?php else: ?>
              <a class="adm-post-title" href="/admin/posts/<?= (int) $row['id'] ?>/"><?= e($title) ?></a>
<?php endif; ?>
<?php if ($row['featured']): ?><span class="adm-badge">Featured</span><?php endif; ?>
              <div class="adm-row-actions">
<?php if ($row['status'] === 'trash'): ?>
                <form method="post" action="/admin/posts/<?= (int) $row['id'] ?>/restore/"><?= csrf_field() ?><button type="submit" class="adm-link">Restore</button></form>
                <form method="post" action="/admin/posts/<?= (int) $row['id'] ?>/delete/" data-adm-confirm="Permanently delete this post? This cannot be undone."><?= csrf_field() ?><button type="submit" class="adm-link adm-link--danger">Delete permanently</button></form>
<?php else: ?>
                <a href="/admin/posts/<?= (int) $row['id'] ?>/">Edit</a>
                <a href="/admin/posts/<?= (int) $row['id'] ?>/preview/" target="_blank" rel="noopener">Preview</a>
<?php if ($state === 'Published'): ?>
                <a href="/blog/<?= e($row['slug']) ?>/" target="_blank" rel="noopener">View</a>
<?php endif; ?>
                <form method="post" action="/admin/posts/<?= (int) $row['id'] ?>/trash/" data-adm-confirm="Move this post to the trash? It will be removed from the blog."><?= csrf_field() ?><button type="submit" class="adm-link adm-link--danger">Trash</button></form>
<?php endif; ?>
              </div>
            </div>
          </div>
        </td>
        <td><?= $row['category'] !== '' ? e($row['category']) : '<span class="adm-muted">&mdash;</span>' ?></td>
        <td><span class="adm-status adm-status--<?= e(strtolower($state)) ?>"><?= e($state) ?></span></td>
        <td class="adm-date">
<?php if (in_array($state, ['Published', 'Scheduled'], true)): ?>
          <span><?= $state === 'Scheduled' ? 'Scheduled for' : 'Published' ?></span><?= e(admin_datetime($row['published_at'])) ?>
<?php else: ?>
          <span>Last modified</span><?= e(admin_datetime($row['updated_at'])) ?>
<?php endif; ?>
        </td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
