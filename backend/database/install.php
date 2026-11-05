<?php
/**
 * CLI installer: creates the tables and seed content.
 *   php database/install.php
 * Works with MySQL (local, DB_* in .env) and PostgreSQL (Neon, DATABASE_URL).
 * Safe to run again — existing rows are never changed (INSERT IGNORE / ON CONFLICT DO NOTHING).
 * Render runs it automatically on every start (see deploy/start.sh).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/core/bootstrap.php';

/** Split a .sql file into statements (semicolon at end of line; skips -- comments). */
function sql_statements(string $sql): array
{
    $out = [];
    foreach (preg_split('/;[ \t]*(--[^\n]*)?$/m', $sql) as $stmt) {
        $lines = array_filter(explode("\n", $stmt), fn($l) => !str_starts_with(ltrim($l), '--'));
        $stmt = trim(implode("\n", $lines));
        if ($stmt !== '') {
            $out[] = $stmt;
        }
    }
    return $out;
}

$c = DB::connectionConfig();
$mysqlSchema = file_get_contents(__DIR__ . '/schema.sql');
$count = 0;

try {
    if ($c['driver'] === 'pgsql') {
        $pdo = DB::pdo();
        // 1) tables
        foreach (sql_statements(file_get_contents(__DIR__ . '/schema.pgsql.sql')) as $stmt) {
            $pdo->exec($stmt);
            $count++;
        }
        // 2) seed rows: reuse the MySQL seed section, converted to PostgreSQL syntax
        $seed = substr($mysqlSchema, strpos($mysqlSchema, 'Seed data'));
        $seed = substr($seed, strpos($seed, "\n"));
        foreach (sql_statements($seed) as $stmt) {
            $stmt = str_replace('`', '"', $stmt);
            if (str_starts_with($stmt, 'INSERT IGNORE INTO')) {
                $stmt = 'INSERT INTO' . substr($stmt, strlen('INSERT IGNORE INTO')) . ' ON CONFLICT DO NOTHING';
            }
            $pdo->exec($stmt);
            $count++;
        }
        // 3) seeds use explicit ids → move each id sequence past the highest id
        foreach (['admins', 'login_attempts', 'menu_items', 'hero_slides', 'services', 'gallery', 'highlights', 'events', 'features', 'testimonials', 'enquiries'] as $t) {
            $pdo->exec("SELECT setval(pg_get_serial_sequence('$t', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM $t), 0), 1), (SELECT COUNT(*) > 0 FROM $t))");
        }
        echo "✔ PostgreSQL database `{$c['name']}` on {$c['host']} ready ($count statements).\n";
    } else {
        $pdo = DB::connect($c, false);
        $name = str_replace('`', '', $c['name']);
        $sql = str_replace('`bb_caterers`', "`$name`", $mysqlSchema);
        foreach (sql_statements($sql) as $stmt) {
            $pdo->exec($stmt);
            $count++;
        }
        echo "✔ MySQL database `$name` ready ($count statements).\n";
    }
    echo "  Admin login → username: admin   password: admin123 (change it after first login)\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Database error: ' . $e->getMessage() . "\n");
    exit(1);
}
