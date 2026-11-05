<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
require __DIR__ . '/partials/auth-layout.php';

if (current_admin()) {
    redirect('index.php');
}

const MAX_ATTEMPTS = 5;
const LOCK_MINUTES = 15;

$login = '';
if (is_post()) {
    verify_csrf();
    $ip = client_ip();
    $login = trim((string) ($_POST['login'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $attempts = (int) DB::value(
        'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?',
        [$ip, date('Y-m-d H:i:s', time() - LOCK_MINUTES * 60)]
    );
    if ($attempts >= MAX_ATTEMPTS) {
        flash('error', 'Too many failed attempts. Please wait ' . LOCK_MINUTES . ' minutes and try again.');
    } else {
        $admin = DB::one('SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1', [$login, $login]);
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['last_seen'] = time();
            DB::query('DELETE FROM login_attempts WHERE ip = ?', [$ip]);
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                DB::update('admins', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], (int) $admin['id']);
            }
            DB::update('admins', ['last_login_at' => date('Y-m-d H:i:s')], (int) $admin['id']);
            flash('success', 'Welcome back, ' . $admin['name'] . '!');
            redirect('index.php');
        }
        DB::insert('login_attempts', ['ip' => $ip, 'attempted_at' => date('Y-m-d H:i:s')]);
        $left = MAX_ATTEMPTS - $attempts - 1;
        flash('error', 'Incorrect username or password.' . ($left > 0 && $left < 3 ? " $left attempt(s) left." : ''));
    }
}

auth_open('Welcome back', 'Sign in to manage your website');
?>
<form method="post" class="auth-form" autocomplete="on">
  <?= csrf_field() ?>
  <div class="field">
    <label for="login">Username or email</label>
    <div class="input-icon"><i data-lucide="user-round"></i><input id="login" name="login" value="<?= e($login) ?>" required autofocus autocomplete="username"></div>
  </div>
  <div class="field">
    <label for="password">Password</label>
    <div class="input-icon"><i data-lucide="lock"></i><input id="password" type="password" name="password" required autocomplete="current-password">
      <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i data-lucide="eye"></i></button></div>
  </div>
  <div class="auth-row"><a href="forgot-password.php">Forgot password?</a></div>
  <button class="btn primary block" type="submit"><i data-lucide="log-in"></i> Sign in</button>
</form>
<?php auth_close();
