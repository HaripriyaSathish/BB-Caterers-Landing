<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require __DIR__ . '/partials/auth-layout.php';

$token = (string) ($_GET['token'] ?? $_POST['token'] ?? '');
$admin = preg_match('/^[a-f0-9]{64}$/', $token)
    ? DB::one('SELECT * FROM admins WHERE reset_token_hash = ? AND reset_expires_at > ?', [hash('sha256', $token), date('Y-m-d H:i:s')])
    : null;

if (!$admin) {
    flash('error', 'This reset link is invalid or has expired. Please request a new one.');
    redirect('forgot-password.php');
}

if (is_post()) {
    verify_csrf();
    $pw = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');
    $problem = password_problem($pw, $confirm);
    if ($problem) {
        flash('error', $problem);
    } else {
        DB::update('admins', [
            'password_hash'        => password_hash($pw, PASSWORD_DEFAULT),
            'must_change_password' => 0,
            'reset_token_hash'     => null,
            'reset_expires_at'     => null,
        ], (int) $admin['id']);
        flash('success', 'Password updated. You can now sign in.');
        redirect('login.php');
    }
}

auth_open('Choose a new password', 'For ' . $admin['email']);
?>
<form method="post" class="auth-form">
  <?= csrf_field() ?><input type="hidden" name="token" value="<?= e($token) ?>">
  <div class="field">
    <label for="password">New password</label>
    <div class="input-icon"><i data-lucide="lock"></i><input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" data-strength>
      <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i data-lucide="eye"></i></button></div>
    <div class="strength"><span></span></div>
    <small class="help">At least 8 characters, with a letter and a number.</small>
  </div>
  <div class="field">
    <label for="password_confirm">Confirm new password</label>
    <div class="input-icon"><i data-lucide="lock-keyhole"></i><input id="password_confirm" type="password" name="password_confirm" required autocomplete="new-password"></div>
  </div>
  <button class="btn primary block" type="submit"><i data-lucide="check"></i> Update password</button>
</form>
<?php auth_close();
