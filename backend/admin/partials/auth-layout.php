<?php
/** Shared shell for login / forgot / reset pages. Call auth_open($title) … auth_close(). */
function auth_open(string $title, string $subtitle = ''): void
{
    $favicon = media_url(setting('favicon'));
    $logo = media_url(setting('logo'));
    ?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · <?= e(setting('site_name', 'BB Caterers')) ?></title>
<?php if ($favicon): ?><link rel="icon" href="<?= e($favicon) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css?v=3">
<script src="https://unpkg.com/lucide@0.460.0/dist/umd/lucide.min.js"></script>
</head>
<body class="auth-body">
<div class="auth-wrap">
  <div class="auth-art" aria-hidden="true">
    <div class="auth-art-inner">
      <?php if ($logo): ?><img src="<?= e($logo) ?>" alt=""><?php endif; ?>
      <h2><?= e(setting('site_name', 'BB Caterers')) ?></h2>
      <p class="tagline"><?= e(setting('site_tagline')) ?></p>
      <p class="quote">Good Food. Beautiful Moments.<br><em>Timeless Traditions.</em></p>
    </div>
  </div>
  <div class="auth-panel">
    <div class="auth-card">
      <h1><?= e($title) ?></h1>
      <?php if ($subtitle): ?><p class="auth-sub"><?= e($subtitle) ?></p><?php endif; ?>
<?php
}

function auth_close(): void
{
    ?>
    </div>
  </div>
</div>
<div class="toasts" id="toasts" aria-live="polite"></div>
<script>window.__FLASH__ = <?= json_encode(take_flashes(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="assets/admin.js?v=3"></script>
</body></html>
<?php
}
