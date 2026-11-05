<?php
declare(strict_types=1);

/* ---------------------------------------------------------------
 * Output
 * ------------------------------------------------------------- */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_response($data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/* ---------------------------------------------------------------
 * URLs
 * ------------------------------------------------------------- */
function app_url(): string
{
    $configured = rtrim((string) config('app_url', ''), '/');
    if ($configured !== '') {
        return $configured;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Backend may live in a sub folder (e.g. http://localhost/bb_caterers/backend)
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = preg_replace('#/(api|admin)(/.*)?$#', '', dirname($script));
    $base = rtrim((string) $base, '/.');
    return ($https ? 'https' : 'http') . '://' . $host . $base;
}

/** Turn a stored relative media path into an absolute URL (external URLs pass through). */
function media_url(?string $path): ?string
{
    if ($path === null || $path === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return app_url() . '/' . ltrim($path, '/');
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/* ---------------------------------------------------------------
 * Settings (key/value)
 * ------------------------------------------------------------- */
function settings_all(bool $fresh = false): array
{
    static $cache = null;
    if ($cache === null || $fresh) {
        $cache = [];
        foreach (DB::all('SELECT `key`, `value` FROM settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache;
}

function setting(string $key, $default = '')
{
    $all = settings_all();
    return array_key_exists($key, $all) && $all[$key] !== null ? $all[$key] : $default;
}

function settings_save(array $values): void
{
    $stmt = DB::pdo()->prepare(
        'INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
    );
    foreach ($values as $k => $v) {
        $stmt->execute([$k, $v]);
    }
    settings_all(true);
}

/* ---------------------------------------------------------------
 * Encryption for secrets stored in the DB (SMTP password)
 * ------------------------------------------------------------- */
function app_key(): string
{
    return hash('sha256', (string) config('app_key', 'bb-caterers'), true);
}

function encrypt_secret(string $plain): string
{
    if ($plain === '') {
        return '';
    }
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', app_key(), OPENSSL_RAW_DATA, $iv, $tag);
    return 'enc:' . base64_encode($iv . $tag . $cipher);
}

function decrypt_secret(?string $stored): string
{
    if (!$stored) {
        return '';
    }
    if (!str_starts_with($stored, 'enc:')) {
        return $stored; // plain value entered directly in DB
    }
    $raw = base64_decode(substr($stored, 4), true);
    if ($raw === false || strlen($raw) < 29) {
        return '';
    }
    $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', app_key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
    return $plain === false ? '' : $plain;
}

/* ---------------------------------------------------------------
 * Uploads
 * ------------------------------------------------------------- */
const UPLOAD_MAX_BYTES = 8 * 1024 * 1024;
const UPLOAD_MIME = [
    'image/jpeg'               => 'jpg',
    'image/png'                => 'png',
    'image/webp'               => 'webp',
    'image/gif'                => 'gif',
    'image/x-icon'             => 'ico',
    'image/vnd.microsoft.icon' => 'ico',
];

/**
 * Validate and store an uploaded image. Returns the relative path (media/<folder>/<file>)
 * or null when no file was sent. Throws RuntimeException with a friendly message on error.
 */
function handle_upload(string $field, string $folder): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (code ' . $file['error'] . '). The file may be too large.');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('Image is larger than 8 MB. Please upload a smaller file.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(UPLOAD_MIME[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, GIF or ICO images are allowed.');
    }
    if ($mime !== 'image/x-icon' && $mime !== 'image/vnd.microsoft.icon' && @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('That file does not look like a valid image.');
    }
    $folder = preg_replace('/[^a-z0-9_-]/', '', strtolower($folder)) ?: 'misc';
    $dir = BASE_PATH . '/' . MEDIA_DIR . '/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        throw new RuntimeException('Could not create the media folder.');
    }
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . UPLOAD_MIME[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save the uploaded image.');
    }
    return MEDIA_DIR . '/' . $folder . '/' . $name;
}

/** Delete a media file previously stored by handle_upload (only inside /media). */
function delete_media(?string $path): void
{
    if (!$path || !str_starts_with($path, MEDIA_DIR . '/')) {
        return;
    }
    $full = realpath(BASE_PATH . '/' . $path);
    $root = realpath(BASE_PATH . '/' . MEDIA_DIR);
    if ($full && $root && str_starts_with($full, $root) && is_file($full)) {
        // Keep the file if another record still references it (seed images are shared).
        if (media_reference_count($path) <= 1) {
            @unlink($full);
        }
    }
}

function media_reference_count(string $path): int
{
    $count = (int) DB::value('SELECT COUNT(*) FROM settings WHERE `value` = ?', [$path]);
    foreach (['hero_slides' => 'image', 'services' => 'image', 'gallery' => 'image', 'events' => 'image', 'testimonials' => 'photo'] as $t => $c) {
        $count += (int) DB::value("SELECT COUNT(*) FROM `$t` WHERE `$c` = ?", [$path]);
    }
    return $count;
}

/* ---------------------------------------------------------------
 * Small utilities
 * ------------------------------------------------------------- */
function str_limit(?string $s, int $len = 80): string
{
    $s = trim((string) $s);
    return mb_strlen($s) > $len ? mb_substr($s, 0, $len - 1) . '…' : $s;
}

/** Render a template string with {placeholders}. */
function fill_template(string $tpl, array $vars): string
{
    return preg_replace_callback('/\{(\w+)\}/', fn($m) => (string) ($vars[$m[1]] ?? $m[0]), $tpl);
}
