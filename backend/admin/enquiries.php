<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require_login();

const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed', 'closed' => 'Closed'];

$status = (string) ($_GET['status'] ?? '');
$q = trim((string) ($_GET['q'] ?? ''));
$where = [];
$params = [];
if (isset(STATUSES[$status])) {
    $where[] = 'status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $like = DB::ilike();
    $where[] = "(name $like ? OR email $like ? OR phone $like ? OR event_type $like ?)";
    array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Bulk actions / delete
if (is_post()) {
    verify_csrf();
    $ids = array_filter(array_map('intval', (array) ($_POST['ids'] ?? [])));
    $action = $_POST['action'] ?? '';
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'delete') {
            DB::query("DELETE FROM enquiries WHERE id IN ($in)", $ids);
            flash('success', count($ids) . ' enquir' . (count($ids) === 1 ? 'y' : 'ies') . ' deleted.');
        } elseif (isset(STATUSES[$action])) {
            DB::query("UPDATE enquiries SET status = ? WHERE id IN ($in)", array_merge([$action], $ids));
            flash('success', 'Marked ' . count($ids) . ' as ' . STATUSES[$action] . '.');
        }
    } else {
        flash('warning', 'Select at least one enquiry first.');
    }
    redirect('enquiries.php?' . http_build_query(array_filter(['status' => $status, 'q' => $q])));
}

// CSV export (current filter)
if (($_GET['export'] ?? '') === 'csv') {
    $rows = DB::all("SELECT id, created_at, name, email, phone, event_type, event_date, guests, status, message, admin_note FROM enquiries $whereSql ORDER BY created_at DESC", $params);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="enquiries-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // Excel UTF-8 BOM
    fputcsv($out, ['ID', 'Received', 'Name', 'Email', 'Phone', 'Event type', 'Event date', 'Guests', 'Status', 'Message', 'Notes']);
    foreach ($rows as $r) {
        // Neutralise spreadsheet formulas
        fputcsv($out, array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $r));
    }
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 15;
$total = (int) DB::value("SELECT COUNT(*) FROM enquiries $whereSql", $params);
$pages = max(1, (int) ceil($total / $per));
$page = min($page, $pages);
$rows = DB::all("SELECT * FROM enquiries $whereSql ORDER BY created_at DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$counts = ['' => (int) DB::value('SELECT COUNT(*) FROM enquiries')];
foreach (DB::all('SELECT status, COUNT(*) c FROM enquiries GROUP BY status') as $c) {
    $counts[$c['status']] = (int) $c['c'];
}
$qs = fn(array $extra) => 'enquiries.php?' . http_build_query(array_filter(array_merge(['status' => $status, 'q' => $q], $extra), fn($v) => $v !== '' && $v !== null));

$pageTitle = 'Enquiries';
$pageIntro = 'Catering requests submitted from the website contact form.';
require __DIR__ . '/partials/header.php';
?>
<div class="card">
  <div class="toolbar">
    <div class="tabs">
      <a class="<?= $status === '' ? 'active' : '' ?>" href="<?= e($qs(['status' => ''])) ?>">All <span><?= $counts[''] ?></span></a>
      <?php foreach (STATUSES as $k => $label): ?>
        <a class="<?= $status === $k ? 'active' : '' ?>" href="<?= e($qs(['status' => $k])) ?>"><?= $label ?> <span><?= $counts[$k] ?? 0 ?></span></a>
      <?php endforeach; ?>
    </div>
    <form class="search" method="get">
      <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
      <i data-lucide="search"></i><input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, phone…">
    </form>
    <a class="btn outline small" href="<?= e($qs(['export' => 'csv'])) ?>"><i data-lucide="download"></i> Export CSV</a>
  </div>

  <?php if (!$rows): ?>
    <div class="empty"><i data-lucide="inbox"></i><p><?= $q || $status ? 'No enquiries match this filter.' : 'No enquiries yet.' ?></p></div>
  <?php else: ?>
  <form method="post" id="bulkForm">
    <?= csrf_field() ?>
    <div class="bulk-bar">
      <label class="check"><input type="checkbox" data-check-all> Select all</label>
      <select name="action" aria-label="Bulk action">
        <option value="">Bulk action…</option>
        <?php foreach (STATUSES as $k => $label): ?><option value="<?= $k ?>">Mark as <?= $label ?></option><?php endforeach; ?>
        <option value="delete">Delete</option>
      </select>
      <button class="btn small ghost" type="submit" data-bulk-submit>Apply</button>
    </div>
    <div class="table-wrap">
    <table class="table">
      <thead><tr><th class="w-check"></th><th>Customer</th><th>Event</th><th>Event date</th><th>Guests</th><th>Received</th><th>Status</th><th class="w-actions"></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="<?= $r['status'] === 'new' ? 'unread' : '' ?>">
          <td><input type="checkbox" name="ids[]" value="<?= (int) $r['id'] ?>" aria-label="Select"></td>
          <td data-label="Customer"><a href="enquiry.php?id=<?= (int) $r['id'] ?>"><strong><?= e($r['name']) ?></strong></a><br><small class="muted"><?= e($r['email']) ?><?= $r['phone'] ? ' · ' . e($r['phone']) : '' ?></small></td>
          <td data-label="Event"><?= e($r['event_type'] ?: '—') ?></td>
          <td data-label="Event date"><?= $r['event_date'] ? e(date('d M Y', strtotime($r['event_date']))) : '—' ?></td>
          <td data-label="Guests"><?= $r['guests'] ? (int) $r['guests'] : '—' ?></td>
          <td data-label="Received"><?= e(date('d M Y, h:i A', strtotime($r['created_at']))) ?></td>
          <td data-label="Status"><span class="status <?= e($r['status']) ?>"><?= e(STATUSES[$r['status']]) ?></span></td>
          <td class="actions"><a class="icon-btn" href="enquiry.php?id=<?= (int) $r['id'] ?>" title="Open"><i data-lucide="eye"></i></a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </form>
  <?php if ($pages > 1): ?>
    <nav class="pagination">
      <?php for ($p = 1; $p <= $pages; $p++): ?>
        <a class="<?= $p === $page ? 'active' : '' ?>" href="<?= e($qs(['page' => $p])) ?>"><?= $p ?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
