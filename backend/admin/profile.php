<?php
declare(strict_types=1);

require __DIR__ . '/_init.php';
$admin = require_login();

if (is_post()) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        if ($name === '' || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            flash('error', 'Enter a name and a username of 3–50 letters, numbers, dots, dashes or underscores.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid email address.');
        } elseif (DB::value('SELECT id FROM admins WHERE (username = ? OR email = ?) AND id <> ?', [$username, $email, $admin['id']])) {
            flash('error', 'That username or email is already used by another admin.');
        } else {
            DB::update('admins', ['name' => $name, 'username' => $username, 'email' => $email], (int) $admin['id']);
            flash('success', 'Profile updated successfully.');
        }
        redirect('profile.php');
    }

    if ($action === 'password') {
        $row = DB::one('SELECT password_hash FROM admins WHERE id = ?', [$admin['id']]);
        $currentPw = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        if (!password_verify($currentPw, $row['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
        } elseif ($problem = password_problem($new, $confirm)) {
            flash('error', $problem);
        } elseif (password_verify($new, $row['password_hash'])) {
            flash('error', 'The new password must be different from the current one.');
        } else {
            DB::update('admins', ['password_hash' => password_hash($new, PASSWORD_DEFAULT), 'must_change_password' => 0], (int) $admin['id']);
            session_regenerate_id(true);
            flash('success', 'Password changed successfully.');
        }
        redirect('profile.php');
    }
}

$pageTitle = 'Profile & Password';
$pageIntro = 'Update your login details.';
require __DIR__ . '/partials/header.php';
?>
<div class="grid-2">
  <form class="card" method="post">
    <div class="card-head"><h2><i data-lucide="user-cog"></i> Profile</h2></div>
    <?= csrf_field() ?><input type="hidden" name="action" value="profile">
    <div class="form-grid">
      <div class="field full"><label for="name">Full name</label><input id="name" name="name" value="<?= e($admin['name']) ?>" required></div>
      <div class="field full"><label for="username">Username</label><input id="username" name="username" value="<?= e($admin['username']) ?>" required autocomplete="username"></div>
      <div class="field full"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($admin['email']) ?>" required>
        <small class="help">Password reset links are sent here.</small></div>
    </div>
    <?php if ($admin['last_login_at']): ?><p class="muted small">Last login: <?= e(date('d M Y, h:i A', strtotime($admin['last_login_at']))) ?></p><?php endif; ?>
    <div class="form-actions"><button class="btn primary" type="submit"><i data-lucide="save"></i> Save profile</button></div>
  </form>

  <form class="card" method="post">
    <div class="card-head"><h2><i data-lucide="key-round"></i> Change password</h2></div>
    <?= csrf_field() ?><input type="hidden" name="action" value="password">
    <div class="form-grid">
      <div class="field full"><label for="current_password">Current password</label>
        <div class="input-icon"><i data-lucide="lock"></i><input id="current_password" type="password" name="current_password" required autocomplete="current-password">
        <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i data-lucide="eye"></i></button></div></div>
      <div class="field full"><label for="new_password">New password</label>
        <div class="input-icon"><i data-lucide="lock-keyhole"></i><input id="new_password" type="password" name="new_password" required minlength="8" autocomplete="new-password" data-strength>
        <button type="button" class="pw-toggle" data-pw-toggle aria-label="Show password"><i data-lucide="eye"></i></button></div>
        <div class="strength"><span></span></div>
        <small class="help">At least 8 characters, with a letter and a number.</small></div>
      <div class="field full"><label for="confirm_password">Confirm new password</label>
        <div class="input-icon"><i data-lucide="lock-keyhole"></i><input id="confirm_password" type="password" name="confirm_password" required autocomplete="new-password"></div></div>
    </div>
    <div class="form-actions"><button class="btn primary" type="submit"><i data-lucide="shield-check"></i> Update password</button></div>
  </form>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
