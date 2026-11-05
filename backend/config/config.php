<?php
/**
 * App configuration. All values come from backend/.env (see .env.example).
 * This file is never served to the browser (see router.php / .htaccess).
 */
$list = fn(string $v) => array_values(array_filter(array_map('trim', explode(',', $v))));

return [
    'db' => [
        // On Render: DATABASE_URL (Neon PostgreSQL) — when set it wins over the DB_* values below.
        // Locally: leave DATABASE_URL empty and use MySQL through DB_*.
        'url'      => (string) env('DATABASE_URL', ''),
        'driver'   => env('DB_DRIVER', 'mysql'),
        'host'     => env('DB_HOST', '127.0.0.1'),
        'port'     => (int) env('DB_PORT', 3306),
        'name'     => env('DB_DATABASE', 'bb_caterers'),
        'user'     => env('DB_USERNAME', 'root'),
        'password' => (string) env('DB_PASSWORD', ''),
    ],

    // Public URL of this backend (absolute image URLs). Empty = auto-detect from the request.
    'app_url' => (string) env('APP_URL', ''),

    // Where the React website runs ("View Website" link in the admin).
    'frontend_url' => (string) env('FRONTEND_URL', 'http://localhost:5173'),

    // Origins allowed to call the public API (comma separated).
    'cors_origins' => $list((string) env('CORS_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173')),

    // Secret used to encrypt the SMTP password stored in the DB. Changing it means re-entering the SMTP password.
    'app_key' => (string) env('APP_KEY', ''),

    // 'files' = uploads saved in backend/media (local). 'database' = uploads stored in the DB
    // (needed on Render, whose disk is wiped on every deploy).
    'media_storage' => (string) env('MEDIA_STORAGE', 'files'),

    'timezone' => (string) env('APP_TIMEZONE', 'Asia/Kolkata'),
    'debug'    => (bool) env('APP_DEBUG', false),
];
