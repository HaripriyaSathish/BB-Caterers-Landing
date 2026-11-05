<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$admin = require_login();

$keys = ['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_from_email', 'smtp_from_name', 'smtp_notify_email'];

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'save') {
        $data = [];
        foreach ($keys as $k) {
            $data[$k] = trim((string) ($_POST[$k] ?? ''));
        }
        $data['smtp_autoreply'] = !empty($_POST['smtp_autoreply']) ? '1' : '0';
        $errors = [];
        if ($data['smtp_port'] !== '' && (!ctype_digit($data['smtp_port']) || (int) $data['smtp_port'] > 65535)) {
            $errors[] = 'Port must be a number such as 587 or 465.';
        }
        if (!in_array($data['smtp_encryption'], ['tls', 'ssl', 'none'], true)) {
            $errors[] = 'Choose a valid encryption type.';
        }
        foreach (['smtp_from_email' => 'From email', 'smtp_notify_email' => 'Enquiry notification email'] as $k => $label) {
            if ($data[$k] !== '' && !filter_var($data[$k], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "$label is not a valid email address.";
            }
        }
        // Only overwrite the stored password when a new one is typed.
        $pw = (string) ($_POST['smtp_password'] ?? '');
        if ($pw !== '') {
            $data['smtp_password'] = encrypt_secret($pw);
        }
        if (!empty($_POST['smtp_password_clear'])) {
            $data['smtp_password'] = '';
        }
        if ($errors) {
            flash('error', implode(' ', $errors));
        } else {
            settings_save($data);
            flash('success', 'SMTP settings saved.');
        }
        redirect('smtp.php');
    }

    if ($action === 'test') {
        $to = trim((string) ($_POST['test_email'] ?? '')) ?: $admin['email'];
        try {
            (new Mailer())->send(
                $to,
                'SMTP test from ' . setting('site_name', 'BB Caterers'),
                email_template('Your email settings work!', '<p style="font-size:14px;line-height:1.6">This is a test email sent from the admin panel at ' . e(date('d M Y, h:i A')) . '. Enquiry notifications and password reset emails will be delivered like this one.</p>')
            );
            flash('success', "Test email sent to $to. Check your inbox (and spam folder).");
        } catch (Throwable $ex) {
            flash('error', 'Test failed: ' . $ex->getMessage());
        }
        redirect('smtp.php');
    }
}

$s = settings_all();
$hasPassword = !empty($s['smtp_password']);
$pageTitle = 'SMTP / Email Settings';
$pageIntro = 'Used for enquiry notifications, customer auto-replies and password reset emails.';
require __DIR__ . '/partials/header.php';
?>
<div class="grid-2 wide-left">
  <form class="card" method="post" autocomplete="off" data-dirty-check>
    <div class="card-head"><h2><i data-lucide="server"></i> Mail server</h2>
      <span class="status-pill <?= Mailer::isConfigured() ? 'ok' : 'off' ?>"><?= Mailer::isConfigured() ? 'Configured' : 'Not configured' ?></span></div>
    <?= csrf_field() ?><input type="hidden" name="action" value="save">
    <div class="form-grid">
      <div class="field half"><label for="smtp_host">SMTP host</label><input id="smtp_host" name="smtp_host" value="<?= e($s['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com"></div>
      <div class="field quarter"><label for="smtp_port">Port</label><input id="smtp_port" name="smtp_port" value="<?= e($s['smtp_port'] ?? '587') ?>" inputmode="numeric"></div>
      <div class="field quarter"><label for="smtp_encryption">Encryption</label>
        <select id="smtp_encryption" name="smtp_encryption" data-port-sync>
          <?php foreach (['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'None (25)'] as $k => $l): ?>
            <option value="<?= $k ?>" <?= ($s['smtp_encryption'] ?? 'tls') === $k ? 'selected' : '' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field half"><label for="smtp_username">Username</label><input id="smtp_username" name="smtp_username" value="<?= e($s['smtp_username'] ?? '') ?>" autocomplete="off" placeholder="you@gmail.com"></div>
      <div class="field half"><label for="smtp_password">Password / App password</label>
        <div class="input-icon"><i data-lucide="lock"></i><input id="smtp_password" type="password" name="smtp_password" autocomplete="new-password" placeholder="<?= $hasPassword ? '•••••••• (saved — leave blank to keep)' : '' ?>">
        <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i data-lucide="eye"></i></button></div>
        <?php if ($hasPassword): ?><label class="remove-image"><input type="checkbox" name="smtp_password_clear" value="1"> Remove saved password</label><?php endif; ?>
        <small class="help">Stored encrypted. For Gmail, create an App Password in your Google account.</small></div>
      <div class="field half"><label for="smtp_from_email">From email</label><input id="smtp_from_email" type="email" name="smtp_from_email" value="<?= e($s['smtp_from_email'] ?? '') ?>"></div>
      <div class="field half"><label for="smtp_from_name">From name</label><input id="smtp_from_name" name="smtp_from_name" value="<?= e($s['smtp_from_name'] ?? '') ?>"></div>
      <div class="field full"><label for="smtp_notify_email">Send new enquiries to</label><input id="smtp_notify_email" type="email" name="smtp_notify_email" value="<?= e($s['smtp_notify_email'] ?? '') ?>">
        <small class="help">You will get an email every time someone submits the website enquiry form.</small></div>
      <div class="field full field-toggle"><label class="switch"><input type="checkbox" name="smtp_autoreply" value="1" <?= ($s['smtp_autoreply'] ?? '0') === '1' ? 'checked' : '' ?>><span class="slider"></span><span class="switch-label">Send an automatic "thank you" email to customers</span></label></div>
    </div>
    <div class="form-actions"><button class="btn primary" type="submit"><i data-lucide="save"></i> Save settings</button></div>
  </form>

  <div>
    <form class="card" method="post">
      <div class="card-head"><h2><i data-lucide="send"></i> Send a test email</h2></div>
      <?= csrf_field() ?><input type="hidden" name="action" value="test">
      <div class="field"><label for="test_email">Send test to</label><input id="test_email" type="email" name="test_email" value="<?= e($admin['email']) ?>"></div>
      <div class="form-actions"><button class="btn outline" type="submit" data-loading="Sending…"><i data-lucide="send"></i> Send test email</button></div>
      <p class="muted small">Save your settings first, then send a test.</p>
    </form>
    <div class="card tips">
      <div class="card-head"><h2><i data-lucide="lightbulb"></i> Common settings</h2></div>
      <ul>
        <li><b>Gmail:</b> smtp.gmail.com · 587 · TLS · App Password</li>
        <li><b>Outlook / Office 365:</b> smtp.office365.com · 587 · TLS</li>
        <li><b>Zoho:</b> smtp.zoho.in · 465 · SSL</li>
        <li><b>Hostinger:</b> smtp.hostinger.com · 465 · SSL</li>
        <li><b>cPanel hosting:</b> mail.yourdomain.com · 465 · SSL</li>
      </ul>
    </div>
  </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
