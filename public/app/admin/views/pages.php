<?php
// Vars: $rows, $type, $tab, $q, $counts
$tabs = [
    'all'       => ['All', 'all_pages'],
    'published' => ['Published', 'published'],
    'draft'     => ['Drafts', 'draft'],
    'trash'     => ['Trash', 'trash'],
];
[$heading, $intro] = PAGE_TYPES[$type];
$newLabel = ['location' => 'Add New Office', 'lp' => 'Add New Landing Page'][$type] ?? 'Add New Page';
?>
<div class="adm-head">
  <h1><?= e($heading) ?></h1>
  <a class="adm-btn adm-btn--primary" href="/admin/pages/new/?type=<?= e($type) ?>">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><?= e($newLabel) ?>
  </a>
  <p class="adm-head__sub"><?= e($intro) ?></p>
</div>

<div class="adm-toolbar">
  <nav class="adm-tabs" aria-label="Filter pages">
<?php foreach ($tabs as $key => [$label, $countKey]): ?>
<?php if ($key !== 'all' && !$counts[$countKey] && $tab !== $key) continue; ?>
    <a href="/admin/pages/?type=<?= e($type) ?>&status=<?= e($key) ?>"<?= $tab === $key ? ' class="is-active" aria-current="page"' : '' ?>><?= e($label) ?> <span>(<?= (int) $counts[$countKey] ?>)</span></a>
<?php endforeach; ?>
  </nav>
  <form class="adm-search" method="get" action="/admin/pages/" role="search">
    <input type="hidden" name="type" value="<?= e($type) ?>">
    <input type="hidden" name="status" value="<?= e($tab) ?>">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search pages" aria-label="Search pages">
    <button class="adm-btn" type="submit">Search</button>
  </form>
</div>

<?php if (!$rows): ?>
<div class="adm-card adm-empty">
<?php if ($q !== ''): ?>
  <h2>No pages match &ldquo;<?= e($q) ?>&rdquo;</h2>
  <p><a href="/admin/pages/?type=<?= e($type) ?>&status=<?= e($tab) ?>">Clear the search</a></p>
<?php elseif ($tab === 'trash'): ?>
  <h2>The trash is empty</h2>
<?php else: ?>
  <h2>Nothing here yet</h2>
  <p>Create your first page and choose the layout it should use.</p>
  <a class="adm-btn adm-btn--primary" href="/admin/pages/new/?type=<?= e($type) ?>"><?= e($newLabel) ?></a>
<?php endif; ?>
</div>
<?php else: ?>
<div class="adm-card adm-table-wrap">
  <table class="adm-table">
    <thead>
      <tr>
        <th scope="col">Page</th>
        <th scope="col">Layout</th>
<?php if ($type === 'service'): ?>
        <th scope="col">Menu</th>
<?php endif; ?>
        <th scope="col">Status</th>
        <th scope="col">Last saved</th>
      </tr>
    </thead>
    <tbody>
<?php foreach ($rows as $row):
    $id    = (int) $row['id'];
    $state = admin_page_state($row);
    $tpl   = template($row['template']);
    $url   = admin_page_url($row);
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
              <a class="adm-post-title" href="/admin/pages/<?= $id ?>/"><?= e($title) ?></a>
<?php endif; ?>
              <div class="adm-muted adm-path"><?= e($url) ?></div>
              <div class="adm-row-actions">
<?php if ($row['status'] === 'trash'): ?>
                <form method="post" action="/admin/pages/<?= $id ?>/restore/"><?= csrf_field() ?><button type="submit" class="adm-link">Restore</button></form>
                <form method="post" action="/admin/pages/<?= $id ?>/delete/" data-adm-confirm="Permanently delete this page? This cannot be undone."><?= csrf_field() ?><button type="submit" class="adm-link adm-link--danger">Delete permanently</button></form>
<?php else: ?>
                <a href="/admin/pages/<?= $id ?>/">Edit</a>
                <a href="/admin/pages/<?= $id ?>/preview/" target="_blank" rel="noopener">Preview</a>
<?php if ($state === 'Published'): ?>
                <a href="<?= e($url) ?>" target="_blank" rel="noopener">View</a>
<?php endif; ?>
                <form method="post" action="/admin/pages/<?= $id ?>/duplicate/"><?= csrf_field() ?><button type="submit" class="adm-link">Duplicate</button></form>
                <form method="post" action="/admin/pages/<?= $id ?>/trash/" data-adm-confirm="Move this page to the trash? It will be removed from the site."><?= csrf_field() ?><button type="submit" class="adm-link adm-link--danger">Trash</button></form>
<?php endif; ?>
              </div>
            </div>
          </div>
        </td>
        <td><?= $tpl ? e($tpl['name']) : '<span class="adm-muted">&mdash;</span>' ?></td>
<?php if ($type === 'service'): ?>
        <td><?= $row['menu'] ? '<span class="adm-badge">In menu</span>' : '<span class="adm-muted">&mdash;</span>' ?></td>
<?php endif; ?>
        <td><span class="adm-status adm-status--<?= e(strtolower($state)) ?>"><?= e($state) ?></span></td>
        <td class="adm-date"><?= e(admin_datetime($row['updated_at'])) ?></td>
      </tr>
<?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
