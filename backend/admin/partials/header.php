<?php
/** @var string $pageTitle */
$admin = current_admin();
$newCount = new_enquiries_count();
$favicon = media_url(setting('favicon'));
$logo = media_url(setting('logo'));
$mods = list_modules();
$groups = settings_groups();
$frontend = rtrim((string) config('frontend_url', '/'), '/') . '/';

$nav = [
    'Overview' => [
        ['index.php', 'Dashboard', 'layout-dashboard', null],
        ['enquiries.php', 'Enquiries', 'inbox', $newCount ?: null],
    ],
    'Website Content' => [
        ['section.php?g=branding', $groups['branding']['title'], $groups['branding']['icon'], null],
        ['module.php?m=menu', $mods['menu']['title'], $mods['menu']['icon'], null],
        ['module.php?m=hero', $mods['hero']['title'], $mods['hero']['icon'], null],
        ['section.php?g=about', $groups['about']['title'], $groups['about']['icon'], null],
        ['module.php?m=services', 'Services', $mods['services']['icon'], null],
        ['module.php?m=gallery', 'Gallery', $mods['gallery']['icon'], null],
        ['module.php?m=highlights', $mods['highlights']['title'], $mods['highlights']['icon'], null],
        ['module.php?m=events', $mods['events']['title'], $mods['events']['icon'], null],
        ['module.php?m=features', $mods['features']['title'], $mods['features']['icon'], null],
        ['module.php?m=testimonials', $mods['testimonials']['title'], $mods['testimonials']['icon'], null],
        ['section.php?g=contact', $groups['contact']['title'], $groups['contact']['icon'], null],
        ['section.php?g=footer', 'CTA & Footer', $groups['footer']['icon'], null],
        ['section.php?g=floating', $groups['floating']['title'], $groups['floating']['icon'], null],
    ],
    'Settings' => [
        ['smtp.php', 'SMTP / Email', 'mail', null],
        ['profile.php', 'Profile & Password', 'key-round', null],
    ],
];

$currentUri = basename($_SERVER['SCRIPT_NAME']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$isActive = function (string $href) use ($currentUri): bool {
    if ($href === $currentUri) {
        return true;
    }
    // module-edit.php?m=services&id=3 → highlight module.php?m=services ; enquiry.php → enquiries.php
    $script = basename($_SERVER['SCRIPT_NAME']);
    if ($script === 'module-edit.php' && isset($_GET['m'])) {
        return $href === 'module.php?m=' . $_GET['m'];
    }
    if ($script === 'enquiry.php') {
        return $href === 'enquiries.php';
    }
    return false;
};
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($pageTitle ?? 'Admin') ?> · <?= e(setting('site_name', 'BB Caterers')) ?> Admin</title>
<?php if ($favicon): ?><link rel="icon" href="<?= e($favicon) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css?v=3">
<script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <a class="brand" href="index.php">
      <?php if ($logo): ?><img src="<?= e($logo) ?>" alt=""><?php endif; ?>
      <span>
        <strong><?= e(setting('site_name', 'BB Caterers')) ?></strong>
        <small>Admin Panel</small>
      </span>
    </a>
    <nav>
      <?php foreach ($nav as $heading => $items): ?>
        <div class="nav-heading"><?= e($heading) ?></div>
        <?php foreach ($items as [$href, $label, $icon, $badge]): ?>
          <a href="<?= e($href) ?>" class="nav-link <?= $isActive($href) ? 'active' : '' ?>">
            <i data-lucide="<?= e($icon) ?>"></i><span><?= e($label) ?></span>
            <?php if ($badge): ?><b class="badge"><?= (int) $badge ?></b><?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
      <div class="nav-heading">Quick links</div>
      <a href="<?= e($frontend) ?>" target="_blank" rel="noopener" class="nav-link"><i data-lucide="external-link"></i><span>View Website</span></a>
      <form method="post" action="logout.php"><?= csrf_field() ?>
        <button type="submit" class="nav-link as-button"><i data-lucide="log-out"></i><span>Logout</span></button>
      </form>
    </nav>
  </aside>
  <div class="sidebar-backdrop" data-sidebar-close></div>

  <div class="main">
    <header class="topbar">
      <button class="icon-btn menu-toggle" type="button" data-sidebar-open aria-label="Open menu"><i data-lucide="menu"></i></button>
      <div class="topbar-title">
        <h1><?= e($pageTitle ?? '') ?></h1>
        <?php if (!empty($pageIntro)): ?><p><?= e($pageIntro) ?></p><?php endif; ?>
      </div>
      <div class="topbar-actions">
        <a href="enquiries.php?status=new" class="icon-btn" title="New enquiries">
          <i data-lucide="bell"></i><?php if ($newCount): ?><span class="dot"><?= $newCount ?></span><?php endif; ?>
        </a>
        <a href="profile.php" class="user-chip">
          <span class="avatar"><?= e(strtoupper(mb_substr($admin['name'] ?: $admin['username'], 0, 1))) ?></span>
          <span class="user-name"><?= e($admin['name']) ?></span>
        </a>
      </div>
    </header>
    <main class="content">
      <?php if (!empty($admin['must_change_password']) && basename($_SERVER['SCRIPT_NAME']) !== 'profile.php'): ?>
        <div class="alert warning"><i data-lucide="shield-alert"></i> You are using a temporary password. <a href="profile.php">Change it now</a>.</div>
      <?php endif; ?>
