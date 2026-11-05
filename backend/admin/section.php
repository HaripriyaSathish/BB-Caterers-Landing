<?php
/** Settings-only sections: Branding, About, Contact, CTA & Footer. */
declare(strict_types=1);

require __DIR__ . '/_init.php';
require __DIR__ . '/partials/fields.php';
require_login();

$key = (string) ($_GET['g'] ?? '');
$groups = settings_groups();
if (!isset($groups[$key])) {
    flash('error', 'That section does not exist.');
    redirect('index.php');
}
$group = $groups[$key];
$current = array_intersect_key(settings_all(), $group['fields']);
$values = $current;
$errors = [];

if (is_post()) {
    verify_csrf();
    [$data, $errors] = collect_fields($group['fields'], $group['folder'], $current);
    if (isset($data['enquiry_min_guests'], $data['enquiry_max_guests']) && !$errors) {
        if ((int) $data['enquiry_min_guests'] < 1) {
            $errors['enquiry_min_guests'] = 'Minimum guests must be at least 1.';
        } elseif ((int) $data['enquiry_min_guests'] > (int) $data['enquiry_max_guests']) {
            $errors['enquiry_max_guests'] = 'Maximum guests must be greater than the minimum.';
        }
        $data['enquiry_min_guests'] = (string) (int) $data['enquiry_min_guests'];
        $data['enquiry_max_guests'] = (string) (int) $data['enquiry_max_guests'];
    }
    if (!$errors) {
        settings_save($data);
        cleanup_replaced_images($group['fields'], $current, $data);
        flash('success', $group['title'] . ' saved successfully.');
        redirect('section.php?g=' . urlencode($key));
    }
    $values = array_merge($current, array_diff_key($data, $errors));
    flash('error', 'Please fix the highlighted fields.');
}

$pageTitle = $group['title'];
$pageIntro = $group['intro'];
require __DIR__ . '/partials/header.php';
?>
<form class="card" method="post" enctype="multipart/form-data" data-dirty-check>
  <?= csrf_field() ?>
  <div class="form-grid"><?= render_fields($group['fields'], $values, $errors) ?></div>
  <div class="form-actions sticky">
    <button class="btn primary" type="submit"><i data-lucide="save"></i> Save changes</button>
  </div>
</form>
<?php require __DIR__ . '/partials/footer.php'; ?>
