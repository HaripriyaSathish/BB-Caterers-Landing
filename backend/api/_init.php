<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/bootstrap.php';

// --- CORS -----------------------------------------------------------
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && in_array($origin, (array) config('cors_origins', []), true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 86400');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('X-Content-Type-Options: nosniff');

set_exception_handler(function (Throwable $e) {
    error_log('[api] ' . $e->getMessage());
    json_response([
        'success' => false,
        'message' => config('debug') ? $e->getMessage() : 'Something went wrong. Please try again later.',
    ], 500);
});
