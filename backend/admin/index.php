<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$admin = require_login();

$stats = [
    ['New enquiries', (int) DB::value("SELECT COUNT(*) FROM enquiries WHERE status='new'"), 'inbox', 'enquiries.php?status=new', 'gold'],
    ['Total enquiries', (int) DB::value('SELECT COUNT(*) FROM enquiries'), 'messages-square', 'enquiries.php', 'green'],
    ['Confirmed events', (int) DB::value("SELECT COUNT(*) FROM enquiries WHERE status='confirmed'"), 'calendar-check', 'enquiries.php?status=confirmed', 'green'],
    ['This month', (int) DB::value('SELECT COUNT(*) FROM enquiries WHERE created_at >= ?', [date('Y-m-01 00:00:00')]), 'trending-up', 'enquiries.php', 'gold'],
];
$content = [
    ['Hero slides', 'hero_slides', 'gallery-horizontal-end', 'module.php?m=hero'],
    ['Services', 'services', 'chef-hat', 'module.php?m=services'],
    ['Gallery photos', 'gallery', 'images', 'module.php?m=gallery'],
    ['Events', 'events', 'party-popper', 'module.php?m=events'],
    ['Why choose us', 'features', 'award', 'module.php?m=features'],
    ['Testimonials', 'testimonials', 'message-square-quote', 'module.php?m=testimonials'],
];
$recent = DB::all('SELECT id, name, event_type, event_date, guests, status, created_at FROM enquiries ORDER BY created_at DESC LIMIT 6');
$upcoming = DB::all("SELECT id, name, event_type, event_date, guests, status FROM enquiries WHERE event_date >= ? AND status IN ('new','contacted','confirmed') ORDER BY event_date LIMIT 5", [date('Y-m-d')]);

// Enquiries per day, last 14 days (for the mini chart)
$daily = [];
foreach (DB::all('SELECT CAST(created_at AS DATE) d, COUNT(*) c FROM enquiries WHERE created_at >= ? GROUP BY CAST(created_at AS DATE)', [date('Y-m-d', strtotime('-13 day'))]) as $r) {
    $daily[substr((string) $r['d'], 0, 10)] = (int) $r['c'];
}
$chart = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $chart[] = ['d' => $d, 'c' => $daily[$d] ?? 0];
}
$max = max(1, ...array_column($chart, 'c'));

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

$pageTitle = 'Dashboard';
$pageIntro = $greeting . ', ' . $admin['name'] . '. Here is what is happening.';
require __DIR__ . '/partials/header.php';
?>
<div class="stats">
  <?php foreach ($stats as [$label, $value, $icon, $href, $tone]): ?>
    <a class="stat <?= $tone ?>" href="<?= e($href) ?>">
      <span class="stat-icon"><i data-lucide="<?= e($icon) ?>"></i></span>
      <span><strong data-count="<?= $value ?>"><?= $value ?></strong><small><?= e($label) ?></small></span>
    </a>
  <?php endforeach; ?>
</div>

<div class="grid-2 wide-left">
  <div class="card">
    <div class="card-head"><h2><i data-lucide="inbox"></i> Latest enquiries</h2><a class="btn small ghost" href="enquiries.php">View all</a></div>
    <?php if (!$recent): ?>
      <div class="empty"><i data-lucide="mail-open"></i><p>No enquiries yet. They will appear here when customers use the website form.</p></div>
    <?php else: ?>
      <div class="table-wrap"><table class="table">
        <thead><tr><th>Customer</th><th>Event</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $r): ?>
          <tr class="clickable" data-href="enquiry.php?id=<?= (int) $r['id'] ?>">
            <td data-label="Customer"><strong><?= e($r['name']) ?></strong><br><small class="muted"><?= e(date('d M, h:i A', strtotime($r['created_at']))) ?></small></td>
            <td data-label="Event"><?= e($r['event_type'] ?: '—') ?><?= $r['guests'] ? '<br><small class="muted">' . (int) $r['guests'] . ' guests</small>' : '' ?></td>
            <td data-label="Date"><?= $r['event_date'] ? e(date('d M Y', strtotime($r['event_date']))) : '—' ?></td>
            <td data-label="Status"><span class="status <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    <?php endif; ?>
  </div>

  <div>
    <div class="card">
      <div class="card-head"><h2><i data-lucide="bar-chart-3"></i> Enquiries · last 14 days</h2></div>
      <div class="mini-chart">
        <?php foreach ($chart as $c): ?>
          <div class="bar" style="--h:<?= round($c['c'] / $max * 100) ?>%" title="<?= e(date('d M', strtotime($c['d']))) ?>: <?= $c['c'] ?>"><span></span></div>
        <?php endforeach; ?>
      </div>
      <div class="chart-axis"><span><?= e(date('d M', strtotime($chart[0]['d']))) ?></span><span>Today</span></div>
    </div>
    <div class="card">
      <div class="card-head"><h2><i data-lucide="calendar-heart"></i> Upcoming events</h2></div>
      <?php if (!$upcoming): ?><p class="muted">No upcoming events yet.</p><?php else: ?>
        <ul class="upcoming">
          <?php foreach ($upcoming as $u): $t = strtotime($u['event_date']); ?>
            <li><a href="enquiry.php?id=<?= (int) $u['id'] ?>">
              <span class="date-badge"><b><?= date('d', $t) ?></b><?= date('M', $t) ?></span>
              <span><strong><?= e($u['name']) ?></strong><small><?= e($u['event_type'] ?: 'Event') ?><?= $u['guests'] ? ' · ' . (int) $u['guests'] . ' guests' : '' ?></small></span>
              <span class="status <?= e($u['status']) ?>"><?= e(ucfirst($u['status'])) ?></span>
            </a></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2><i data-lucide="layout-template"></i> Website content</h2><span class="muted small">Click a tile to edit</span></div>
  <div class="content-tiles">
    <?php foreach ($content as [$label, $table, $icon, $href]):
        $total = (int) DB::value("SELECT COUNT(*) FROM `$table`");
        $live = (int) DB::value("SELECT COUNT(*) FROM `$table` WHERE is_active = 1"); ?>
      <a class="tile" href="<?= e($href) ?>">
        <i data-lucide="<?= e($icon) ?>"></i>
        <strong><?= $live ?><small>/<?= $total ?></small></strong>
        <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
