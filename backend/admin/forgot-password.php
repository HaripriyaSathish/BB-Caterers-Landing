<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require __DIR__ . '/partials/auth-layout.php';

if (current_admin()) {
    redirect('index.php');
}

$sent = false;
if (is_post()) {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $admin = filter_var($email, FILTER_VALIDATE_EMAIL) ? DB::one('SELECT * FROM admins WHERE email = ?', [$email]) : null;

    if (!Mailer::isConfigured()) {
        flash('error', 'Email (SMTP) is not set up yet, so a reset link cannot be sent. Ask your developer to reset the password.');
    } else {
        if ($admin) {
            $token = bin2hex(random_bytes(32));
            DB::update('admins', [
                'reset_token_hash' => hash('sha256', $token),
                'reset_expires_at' => date('Y-m-d H:i:s', time() + 3600),
            ], (int) $admin['id']);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            $link = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir . '/reset-password.php?token=' . $token;

            try {
                (new Mailer())->send(
                    $admin['email'],
                    'Reset your admin password',
                    email_template('Password reset',
                        '<p style="font-size:14px;line-height:1.6">Hi ' . e($admin['name']) . ', we received a request to reset your admin password. This link is valid for 1 hour.</p>'
                        . '<p style="text-align:center;margin:26px 0"><a href="' . e($link) . '" style="background:#214A2B;color:#fff;padding:12px 26px;border-radius:30px;text-decoration:none;font-weight:bold">Reset password</a></p>'
                        . '<p style="font-size:12px;color:#8a7a5a">If you did not request this, you can ignore this email.</p>')
                );
            } catch (Throwable $ex) {
                error_log('[reset mail] ' . $ex->getMessage());
            }
        }
        // Same message either way so the form can't be used to discover admin emails.
        $sent = true;
    }
}

auth_open('Forgot password', 'Enter your admin email and we will send you a reset link.');
if ($sent): ?>
  <div class="alert success"><i data-lucide="mail-check"></i> If that email belongs to an admin account, a reset link is on its way. Check your inbox.</div>
  <a class="btn ghost block" href="login.php"><i data-lucide="arrow-left"></i> Back to sign in</a>
<?php else: ?>
<form method="post" class="auth-form">
  <?= csrf_field() ?>
  <div class="field">
    <label for="email">Admin email</label>
    <div class="input-icon"><i data-lucide="mail"></i><input id="email" type="email" name="email" required autofocus></div>
  </div>
  <button class="btn primary block" type="submit"><i data-lucide="send"></i> Send reset link</button>
  <div class="auth-row center"><a href="login.php">Back to sign in</a></div>
</form>
<?php endif;
auth_close();
