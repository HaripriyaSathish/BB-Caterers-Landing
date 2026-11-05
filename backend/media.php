<?php
/**
 * Serves images uploaded from the admin when MEDIA_STORAGE=database (Render).
 * Apache / router.php send /media/uploads/* here when no file exists on disk.
 */
declare(strict_types=1);

require __DIR__ . '/core/bootstrap.php';

$path = 'media/' . ltrim((string) ($_GET['p'] ?? ''), '/');
if (!preg_match('#^media/uploads/[a-z0-9_-]+/[A-Za-z0-9_.-]+$#', $path) || str_contains($path, '..')) {
    http_response_code(404);
    exit;
}

$row = DB::one('SELECT mime, size, data, created_at FROM media_files WHERE path = ?', [$path]);
if (!$row) {
    http_response_code(404);
    exit;
}

$etag = '"' . md5($path . $row['size'] . $row['created_at']) . '"';
header('Content-Type: ' . $row['mime']);
header('Cache-Control: public, max-age=2592000, immutable');
header('ETag: ' . $etag);
header('X-Content-Type-Options: nosniff');
if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}
$bin = base64_decode((string) $row['data'], true);
header('Content-Length: ' . strlen((string) $bin));
echo $bin;
