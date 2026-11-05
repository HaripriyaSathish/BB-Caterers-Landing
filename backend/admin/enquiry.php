<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require_login();

$statuses = ['new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed', 'closed' => 'Closed'];
$id = (int) ($_GET['id'] ?? 0);
$enq = DB::one('SELECT * FROM enquiries WHERE id = ?', [$id]);
if (!$enq) {
    flash('error', 'Enquiry not found.');
    redirect('enquiries.php');
}

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $st = (string) ($_POST['status'] ?? '');
        if (isset($statuses[$st])) {
            DB::update('enquiries', ['status' => $st, 'admin_note' => trim((string) ($_POST['admin_note'] ?? '')) ?: null], $id);
            flash('success', 'Enquiry updated.');
        }
    } elseif ($action === 'reply') {
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        if ($subject === '' || $body === '') {
            flash('error', 'Please enter a subject and a message.');
        } else {
            try {
                (new Mailer())->send(
                    $enq['email'],
                    $subject,
                    email_template('Hello ' . e($enq['name']) . ',', '<div style="font-size:14px;line-height:1.7">' . nl2br(e($body)) . '</div>'),
                    setting('contact_email') ?: null
                );
                $note = trim(($enq['admin_note'] ?? '') . "\n[" . date('d M Y H:i') . "] Replied by email: $subject");
                DB::update('enquiries', ['admin_note' => $note, 'status' => $enq['status'] === 'new' ? 'contacted' : $enq['status']], $id);
                flash('success', 'Reply sent to ' . $enq['email'] . '.');
            } catch (Throwable $ex) {
                flash('error', 'Could not send: ' . $ex->getMessage());
            }
        }
    } elseif ($action === 'delete') {
        DB::query('DELETE FROM enquiries WHERE id = ?', [$id]);
        flash('success', 'Enquiry deleted.');
        redirect('enquiries.php');
    }
    redirect('enquiry.php?id=' . $id);
}

// Opening a new enquiry doesn't change its status — admin decides. (Shown with a hint.)
$phoneDigits = preg_replace('/\D/', '', (string) $enq['phone']);
if (strlen($phoneDigits) === 10) {
    $phoneDigits = '91' . $phoneDigits;
}

$pageTitle = 'Enquiry #' . $enq['id'];
$pageIntro = 'Received ' . date('d M Y, h:i A', strtotime($enq['created_at']));
require __DIR__ . '/partials/header.php';
?>
<a class="back-link" href="enquiries.php"><i data-lucide="arrow-left"></i> Back to enquiries</a>

<div class="grid-2 wide-left">
  <div>
    <div class="card">
      <div class="card-head">
        <h2><i data-lucide="user-round"></i> <?= e($enq['name']) ?></h2>
        <span class="status <?= e($enq['status']) ?>"><?= e($statuses[$enq['status']]) ?></span>
      </div>
      <dl class="details">
        <div><dt>Email</dt><dd><a href="mailto:<?= e($enq['email']) ?>"><?= e($enq['email']) ?></a></dd></div>
        <div><dt>Phone</dt><dd><?= $enq['phone'] ? '<a href="tel:' . e($enq['phone']) . '">' . e($enq['phone']) . '</a>' : '—' ?></dd></div>
        <div><dt>Event type</dt><dd><?= e($enq['event_type'] ?: '—') ?></dd></div>
        <div><dt>Event date</dt><dd><?= $enq['event_date'] ? e(date('l, d M Y', strtotime($enq['event_date']))) : '—' ?></dd></div>
        <div><dt>Guests</dt><dd><?= $enq['guests'] ? (int) $enq['guests'] : '—' ?></dd></div>
        <div class="full"><dt>Message</dt><dd class="message"><?= $enq['message'] ? nl2br(e($enq['message'])) : '<span class="muted">No message</span>' ?></dd></div>
      </dl>
      <div class="quick-actions">
        <?php if ($enq['phone']): ?>
          <a class="btn outline small" href="tel:<?= e($enq['phone']) ?>"><i data-lucide="phone"></i> Call</a>
          <a class="btn outline small" target="_blank" rel="noopener" href="https://wa.me/<?= e($phoneDigits) ?>?text=<?= rawurlencode('Hello ' . $enq['name'] . ', thank you for your enquiry with ' . setting('site_name', 'BB Caterers') . '.') ?>"><i data-lucide="message-circle"></i> WhatsApp</a>
        <?php endif; ?>
        <a class="btn outline small" href="mailto:<?= e($enq['email']) ?>"><i data-lucide="mail"></i> Open in mail app</a>
      </div>
    </div>

    <form class="card" method="post">
      <div class="card-head"><h2><i data-lucide="reply"></i> Reply by email</h2>
        <?php if (!Mailer::isConfigured()): ?><a class="status-pill off" href="smtp.php">Set up SMTP first</a><?php endif; ?></div>
      <?= csrf_field() ?><input type="hidden" name="action" value="reply">
      <div class="form-grid">
        <div class="field full"><label for="subject">Subject</label><input id="subject" name="subject" value="Re: Your catering enquiry — <?= e(setting('site_name', 'BB Caterers')) ?>"></div>
        <div class="field full"><label for="body">Message</label><textarea id="body" name="body" rows="6" placeholder="Thank you for reaching out! For your <?= e($enq['event_type'] ?: 'event') ?> …"></textarea></div>
      </div>
      <div class="form-actions"><button class="btn primary" type="submit" data-loading="Sending…" <?= Mailer::isConfigured() ? '' : 'disabled' ?>><i data-lucide="send"></i> Send reply</button></div>
    </form>
  </div>

  <div>
    <form class="card" method="post">
      <div class="card-head"><h2><i data-lucide="clipboard-check"></i> Status & notes</h2></div>
      <?= csrf_field() ?><input type="hidden" name="action" value="update">
      <div class="status-picker">
        <?php foreach ($statuses as $k => $label): ?>
          <label><input type="radio" name="status" value="<?= $k ?>" <?= $enq['status'] === $k ? 'checked' : '' ?>><span class="status <?= $k ?>"><?= $label ?></span></label>
        <?php endforeach; ?>
      </div>
      <div class="field"><label for="admin_note">Private notes</label><textarea id="admin_note" name="admin_note" rows="6" placeholder="Menu discussed, quote sent…"><?= e($enq['admin_note']) ?></textarea></div>
      <div class="form-actions"><button class="btn primary" type="submit"><i data-lucide="save"></i> Save</button></div>
    </form>
    <form class="card danger-zone" method="post" data-confirm="Delete this enquiry permanently?">
      <?= csrf_field() ?><input type="hidden" name="action" value="delete">
      <p>Remove this enquiry permanently.</p>
      <button class="btn danger small" type="submit"><i data-lucide="trash-2"></i> Delete enquiry</button>
    </form>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
