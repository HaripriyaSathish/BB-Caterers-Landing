<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('MEDIA_DIR', 'media');

require_once __DIR__ . '/env.php';
if (!is_file(BASE_PATH . '/.env')) {
    http_response_code(500);
    exit('Missing backend/.env — copy .env.example to .env and set your database details.');
}
load_env(BASE_PATH . '/.env');
$GLOBALS['config'] = require BASE_PATH . '/config/config.php';

date_default_timezone_set(config('timezone', 'Asia/Kolkata'));

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Mailer.php';

function config(string $key, $default = null)
{
    $value = $GLOBALS['config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}
