<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/_modules.php';

// --- Session -------------------------------------------------------
session_name('bbc_admin');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

// --- Auth ----------------------------------------------------------
function current_admin(): ?array
{
    static $admin = false;
    if ($admin === false) {
        $admin = !empty($_SESSION['admin_id'])
            ? DB::one('SELECT id, name, username, email, must_change_password, last_login_at FROM admins WHERE id = ?', [$_SESSION['admin_id']])
            : null;
    }
    return $admin;
}

function require_login(): array
{
    $admin = current_admin();
    if (!$admin) {
        redirect('login.php');
    }
    // Idle timeout: 2 hours
    if (isset($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > 7200) {
        session_unset();
        flash('warning', 'Your session expired. Please log in again.');
        redirect('login.php');
    }
    $_SESSION['last_seen'] = time();
    return $admin;
}

// --- CSRF ----------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        if (is_ajax()) {
            json_response(['success' => false, 'message' => 'Security token expired. Please reload the page.'], 419);
        }
        flash('error', 'Security token expired. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function is_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
}

// --- Flash messages (rendered as toast popups) --------------------
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Returns a human message if the new password is not acceptable, else null. */
function password_problem(string $pw, string $confirm): ?string
{
    if (strlen($pw) < 8) {
        return 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) {
        return 'Password must contain at least one letter and one number.';
    }
    if ($pw !== $confirm) {
        return 'The two passwords do not match.';
    }
    return null;
}

// --- Misc ----------------------------------------------------------
function new_enquiries_count(): int
{
    return (int) DB::value("SELECT COUNT(*) FROM enquiries WHERE status = 'new'");
}

function active_page(string ...$pages): string
{
    $current = basename($_SERVER['SCRIPT_NAME']) . (isset($_GET['m']) ? '?m=' . $_GET['m'] : '') . (isset($_GET['g']) ? '?g=' . $_GET['g'] : '');
    foreach ($pages as $p) {
        if ($current === $p || basename($_SERVER['SCRIPT_NAME']) === $p) {
            return 'active';
        }
    }
    return '';
}

set_exception_handler(function (Throwable $e) {
    error_log('[admin] ' . $e);
    http_response_code(500);
    $msg = config('debug') ? $e->getMessage() : 'Unexpected error. Please try again.';
    if (is_ajax()) {
        json_response(['success' => false, 'message' => $msg], 500);
    }
    echo '<div style="font-family:sans-serif;padding:40px;color:#7a1f1f"><h2>Something went wrong</h2><p>' . e($msg) . '</p><p><a href="index.php">Back to dashboard</a></p></div>';
});
