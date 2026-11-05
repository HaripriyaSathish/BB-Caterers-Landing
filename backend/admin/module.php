<?php
/** List screen for every repeatable section (services, gallery, events ...). */
declare(strict_types=1);

require __DIR__ . '/_init.php';
require __DIR__ . '/partials/fields.php';
require_login();

$key = (string) ($_GET['m'] ?? '');
$modules = list_modules();
if (!isset($modules[$key])) {
    flash('error', 'That section does not exist.');
    redirect('index.php');
}
$mod = $modules[$key];
$table = $mod['table'];
$self = 'module.php?m=' . urlencode($key);

// ---------------- Actions ----------------
if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'reorder') {
        $ids = array_map('intval', (array) ($_POST['ids'] ?? []));
        $stmt = DB::prepare("UPDATE `$table` SET sort_order = ? WHERE id = ?");
        foreach ($ids as $i => $id) {
            $stmt->execute([$i + 1, $id]);
        }
        json_response(['success' => true, 'message' => 'Order saved.']);
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        DB::query("UPDATE `$table` SET is_active = 1 - is_active WHERE id = ?", [$id]);
        $on = (int) DB::value("SELECT is_active FROM `$table` WHERE id = ?", [$id]);
        json_response(['success' => true, 'active' => (bool) $on, 'message' => $on ? 'Now visible on the website.' : 'Hidden from the website.']);
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $row = DB::one("SELECT * FROM `$table` WHERE id = ?", [$id]);
        if ($row) {
            DB::query("DELETE FROM `$table` WHERE id = ?", [$id]);
            foreach ($mod['fields'] as $name => $f) {
                if ($f['type'] === 'image') {
                    delete_media($row[$name] ?? null);
                }
            }
            flash('success', $mod['singular'] . ' deleted.');
        }
        redirect($self);
    }

    if ($action === 'settings' && !empty($mod['settings'])) {
        $current = array_intersect_key(settings_all(), $mod['settings']);
        [$values, $errors] = collect_fields($mod['settings'], $mod['folder'], $current);
        if ($errors) {
            $_SESSION['old_errors'] = $errors;
            flash('error', reset($errors));
        } else {
            settings_save($values);
            cleanup_replaced_images($mod['settings'], $current, $values);
            flash('success', 'Section settings saved.');
        }
        redirect($self);
    }
}

// ---------------- View ----------------
$rows = DB::all("SELECT * FROM `$table` ORDER BY sort_order, id");
$settingErrors = $_SESSION['old_errors'] ?? [];
unset($_SESSION['old_errors']);

$pageTitle = $mod['title'];
$pageIntro = $mod['intro'];
require __DIR__ . '/partials/header.php';
?>

<?php if (!empty($mod['settings'])): ?>
<form class="card" method="post" enctype="multipart/form-data" data-dirty-check>
  <div class="card-head"><h2><i data-lucide="sliders-horizontal"></i> Section settings</h2></div>
  <?= csrf_field() ?><input type="hidden" name="action" value="settings">
  <div class="form-grid"><?= render_fields($mod['settings'], settings_all(), $settingErrors) ?></div>
  <div class="form-actions"><button class="btn primary" type="submit"><i data-lucide="save"></i> Save settings</button></div>
</form>
<?php endif; ?>

<div class="card">
  <div class="card-head">
    <h2><i data-lucide="<?= e($mod['icon']) ?>"></i> <?= e($mod['title']) ?> <span class="count"><?= count($rows) ?></span></h2>
    <a class="btn primary" href="module-edit.php?m=<?= e($key) ?>"><i data-lucide="plus"></i> Add <?= e(strtolower($mod['singular'])) ?></a>
  </div>

  <?php if (!$rows): ?>
    <div class="empty"><i data-lucide="inbox"></i><p>Nothing here yet.</p><a class="btn primary" href="module-edit.php?m=<?= e($key) ?>">Add the first <?= e(strtolower($mod['singular'])) ?></a></div>
  <?php else: ?>
    <p class="hint"><i data-lucide="grip-vertical"></i> Drag rows to change the order on the website.</p>
    <div class="table-wrap">
    <table class="table" data-sortable data-endpoint="<?= e($self) ?>">
      <thead><tr>
        <th class="w-drag"></th>
        <?php foreach ($mod['columns'] as $col => $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
        <th class="w-status">Visible</th><th class="w-actions">Actions</th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr data-id="<?= (int) $r['id'] ?>">
          <td class="drag-handle" title="Drag to reorder"><i data-lucide="grip-vertical"></i></td>
          <?php foreach ($mod['columns'] as $col => $label):
              $type = $mod['fields'][$col]['type'] ?? 'text'; $v = $r[$col] ?? ''; ?>
            <td data-label="<?= e($label) ?>">
              <?php if ($type === 'image'): ?>
                <?= $v ? '<img class="thumb" src="' . e(media_url($v)) . '" alt="" loading="lazy">' : '<span class="muted">—</span>' ?>
              <?php elseif ($type === 'icon'): ?>
                <?= $v ? '<span class="icon-pill"><i data-lucide="' . e($v) . '"></i></span>' : '<span class="muted">—</span>' ?>
              <?php elseif ($type === 'rating'): ?>
                <span class="stars"><?= str_repeat('★', (int) $v) ?><span class="muted"><?= str_repeat('★', 5 - (int) $v) ?></span></span>
              <?php elseif ($type === 'select'): ?>
                <span class="pill"><?= e($mod['fields'][$col]['options'][$v] ?? $v) ?></span>
              <?php else: ?>
                <?= e(str_limit((string) $v, 70)) ?>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td data-label="Visible">
            <label class="switch small"><input type="checkbox" data-toggle-id="<?= (int) $r['id'] ?>" <?= $r['is_active'] ? 'checked' : '' ?>><span class="slider"></span></label>
          </td>
          <td class="actions">
            <a class="icon-btn" href="module-edit.php?m=<?= e($key) ?>&id=<?= (int) $r['id'] ?>" title="Edit"><i data-lucide="pencil"></i></a>
            <form method="post" data-confirm="Delete this <?= e(strtolower($mod['singular'])) ?>? This cannot be undone.">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="icon-btn danger" type="submit" title="Delete"><i data-lucide="trash-2"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
