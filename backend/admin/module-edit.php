<?php
/** Add / edit one row of a repeatable section. */
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
$id = (int) ($_GET['id'] ?? 0);
$row = $id ? DB::one("SELECT * FROM `$table` WHERE id = ?", [$id]) : null;
if ($id && !$row) {
    flash('error', $mod['singular'] . ' not found.');
    redirect('module.php?m=' . urlencode($key));
}

$values = $row ?? ['is_active' => 1];
$errors = [];

if (is_post()) {
    verify_csrf();
    [$data, $errors] = collect_fields($mod['fields'], $mod['folder'], $row ?? []);
    $data['is_active'] = !empty($_POST['is_active']) ? 1 : 0;

    if (!$errors) {
        if ($row) {
            DB::update($table, $data, $id);
            cleanup_replaced_images($mod['fields'], $row, $data);
            flash('success', $mod['singular'] . ' updated successfully.');
        } else {
            $data['sort_order'] = (int) DB::value("SELECT COALESCE(MAX(sort_order), 0) + 1 FROM `$table`");
            DB::insert($table, $data);
            flash('success', $mod['singular'] . ' added successfully.');
        }
        redirect(!empty($_POST['add_another']) ? 'module-edit.php?m=' . urlencode($key) : 'module.php?m=' . urlencode($key));
    }
    // Keep what the user typed (but not failed uploads)
    foreach ($mod['fields'] as $n => $f) {
        if ($f['type'] === 'image' && isset($errors[$n])) {
            $data[$n] = $row[$n] ?? null;
        }
    }
    $values = array_merge($values, $data);
    flash('error', 'Please fix the highlighted fields.');
}

$pageTitle = ($row ? 'Edit ' : 'Add ') . $mod['singular'];
$pageIntro = $mod['title'];
require __DIR__ . '/partials/header.php';
?>
<a class="back-link" href="module.php?m=<?= e($key) ?>"><i data-lucide="arrow-left"></i> Back to <?= e($mod['title']) ?></a>

<form class="card" method="post" enctype="multipart/form-data" data-dirty-check>
  <?= csrf_field() ?>
  <div class="form-grid">
    <?= render_fields($mod['fields'], $values, $errors) ?>
    <div class="field full field-toggle">
      <label class="switch"><input type="checkbox" name="is_active" value="1" <?= !empty($values['is_active']) ? 'checked' : '' ?>><span class="slider"></span><span class="switch-label">Visible on the website</span></label>
    </div>
  </div>
  <div class="form-actions">
    <a class="btn ghost" href="module.php?m=<?= e($key) ?>">Cancel</a>
    <?php if (!$row): ?><button class="btn outline" type="submit" name="add_another" value="1"><i data-lucide="copy-plus"></i> Save & add another</button><?php endif; ?>
    <button class="btn primary" type="submit"><i data-lucide="save"></i> <?= $row ? 'Save changes' : 'Save' ?></button>
  </div>
</form>

<?php require __DIR__ . '/partials/footer.php'; ?>
