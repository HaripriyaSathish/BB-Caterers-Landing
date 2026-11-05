<?php
/**
 * CLI installer: creates the database, tables and seed content.
 *   php database/install.php
 * Safe to run again — existing rows are kept (INSERT IGNORE / IF NOT EXISTS).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/core/env.php';
load_env(dirname(__DIR__) . '/.env');
$config = require dirname(__DIR__) . '/config/config.php';
$db = $config['db'];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], (int) $db['port']),
        $db['user'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Could not connect to MySQL: {$e->getMessage()}\nCheck config/config.php\n");
    exit(1);
}

$sql = file_get_contents(__DIR__ . '/schema.sql');
// Use the database name from config instead of the default in schema.sql
$name = str_replace('`', '', $db['name']);
$sql = str_replace('`bb_caterers`', "`$name`", $sql);

// Split on semicolons at line ends (schema contains no procedures)
$statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql)));
$count = 0;
foreach ($statements as $stmt) {
    $lines = array_filter(explode("\n", $stmt), fn($l) => !str_starts_with(trim($l), '--'));
    $stmt = trim(implode("\n", $lines));
    if ($stmt === '') {
        continue;
    }
    $pdo->exec($stmt);
    $count++;
}

echo "✔ Database `$name` ready ($count statements).\n";
echo "  Admin login → username: admin   password: admin123\n";
