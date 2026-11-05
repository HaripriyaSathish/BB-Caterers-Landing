<?php
/**
 * One-time copy of ALL content from your local MySQL database into Neon (PostgreSQL).
 *
 *   NEON_DATABASE_URL="postgresql://…" php database/copy_mysql_to_neon.php
 *
 * - Reads MySQL using DB_* from backend/.env (read-only: nothing in MySQL is changed).
 * - Creates the tables in Neon if needed, then REPLACES Neon's rows with the MySQL rows.
 * - Login attempts are not copied. Run it again any time to re-sync (Neon is overwritten).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$neonUrl = getenv('NEON_DATABASE_URL') ?: ($argv[1] ?? '');
if (!preg_match('#^postgres(ql)?://#', $neonUrl)) {
    fwrite(STDERR, "Set NEON_DATABASE_URL to your Neon connection string, e.g.\n  NEON_DATABASE_URL=\"postgresql://…\" php database/copy_mysql_to_neon.php\n");
    exit(1);
}

require dirname(__DIR__) . '/core/bootstrap.php';

// --- Source: local MySQL (ignore any DATABASE_URL so we never read Neon as the source)
$GLOBALS['config']['db']['url'] = '';
$src = DB::connectionConfig();
if ($src['driver'] !== 'mysql') {
    fwrite(STDERR, "Source must be MySQL (check DB_* in backend/.env).\n");
    exit(1);
}
$mysql = DB::connect($src);
echo "Source : MySQL {$src['name']} on {$src['host']}\n";

// --- Target: Neon. Point the app at Neon and run the normal installer for the tables.
$GLOBALS['config']['db']['url'] = $neonUrl;
$dst = DB::connectionConfig();
echo "Target : PostgreSQL {$dst['name']} on {$dst['host']}\n\n";
putenv('DATABASE_URL=' . $neonUrl);
// XAMPP ships pdo_pgsql as a DLL that is off by default — load it for the child process too
$ext = (PHP_OS_FAMILY === 'Windows' && extension_loaded('pdo_pgsql')) ? ' -d extension=pdo_pgsql' : '';
passthru(escapeshellarg(PHP_BINARY) . $ext . ' ' . escapeshellarg(__DIR__ . '/install.php'), $code);
if ($code !== 0) {
    exit($code);
}
$pg = DB::connect($dst);

$tables = ['admins', 'settings', 'menu_items', 'hero_slides', 'services', 'gallery', 'highlights', 'events', 'features', 'testimonials', 'enquiries'];

$pg->beginTransaction();
try {
    foreach ($tables as $t) {
        $rows = $mysql->query("SELECT * FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
        // Only copy columns that exist in the PostgreSQL table
        $pgCols = $pg->query("SELECT column_name FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = '$t'")
            ->fetchAll(PDO::FETCH_COLUMN);

        $pg->exec("DELETE FROM \"$t\"");
        $n = 0;
        foreach ($rows as $row) {
            $row = array_intersect_key($row, array_flip($pgCols));
            $cols = array_keys($row);
            $stmt = $pg->prepare(sprintf(
                'INSERT INTO "%s" (%s) VALUES (%s)',
                $t,
                implode(',', array_map(fn($c) => "\"$c\"", $cols)),
                implode(',', array_fill(0, count($cols), '?'))
            ));
            $stmt->execute(array_values($row));
            $n++;
        }
        if ($t !== 'settings') {
            $pg->exec("SELECT setval(pg_get_serial_sequence('$t', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM \"$t\"), 0), 1), (SELECT COUNT(*) > 0 FROM \"$t\"))");
        }
        printf("  %-14s %4d rows copied\n", $t, $n);
    }
    $pg->commit();
} catch (Throwable $e) {
    $pg->rollBack();
    fwrite(STDERR, "\nCopy failed, Neon left unchanged: " . $e->getMessage() . "\n");
    exit(1);
}

// Images referenced by the content must exist in backend/media (they are deployed with the code)
$missing = [];
foreach ($mysql->query("SELECT `value` FROM settings WHERE `value` LIKE 'media/%'")->fetchAll(PDO::FETCH_COLUMN) as $p) {
    if (!is_file(BASE_PATH . '/' . $p)) $missing[] = $p;
}
foreach (['hero_slides' => 'image', 'services' => 'image', 'gallery' => 'image', 'events' => 'image', 'testimonials' => 'photo'] as $t => $col) {
    foreach ($mysql->query("SELECT `$col` FROM `$t` WHERE `$col` IS NOT NULL AND `$col` <> ''")->fetchAll(PDO::FETCH_COLUMN) as $p) {
        if (!is_file(BASE_PATH . '/' . $p)) $missing[] = $p;
    }
}
echo $missing ? "\n⚠ Missing image files: " . implode(', ', array_unique($missing)) . "\n" : "\n✔ All referenced images are in backend/media (commit them to GitHub).\n";
echo "✔ Done. Neon now has the same content as your local MySQL.\n";
