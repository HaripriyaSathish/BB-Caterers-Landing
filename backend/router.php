<?php
/**
 * Router for PHP's built-in dev server:
 *   php -S 127.0.0.1:8090 router.php
 * (On Apache/XAMPP the .htaccess files do the same job.)
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

// Never serve private folders or partial/include files.
if (preg_match('#^/(config|core|database)(/|$)#i', $path)
    || preg_match('#/(_[^/]*\.php|partials/.*)$#i', $path)
    || preg_match('#^/router\.php$#i', $path)
    || preg_match('#/\.[^/]*$#', $path)           // .env, .htaccess, .gitignore …
    || preg_match('#^/media/.*\.(php|phtml|phar)#i', $path)
    || str_contains($path, '..')) {
    http_response_code(404);
    exit('Not found');
}

if ($path === '/' || $path === '') {
    header('Location: /admin/');
    exit;
}
if ($path === '/admin') {
    header('Location: /admin/');
    exit;
}

$file = __DIR__ . $path;

// Uploads stored in the database (MEDIA_STORAGE=database) have no file on disk
if (str_starts_with($path, '/media/uploads/') && !is_file($file)) {
    $_GET['p'] = substr($path, strlen('/media/'));
    require __DIR__ . '/media.php';
    return true;
}
if (is_file($file) || is_file(rtrim($file, '/') . '/index.php')) {
    if (str_starts_with($path, '/media/')) {
        header('Cache-Control: public, max-age=604800');
    }
    return false; // built-in server runs .php files / sends static files itself
}

http_response_code(404);
echo 'Not found';
