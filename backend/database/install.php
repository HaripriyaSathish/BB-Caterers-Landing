<?php
/**
 * CLI installer: creates the tables and seed content.
 *   php database/install.php
 * Works with MySQL (local, DB_* in .env) and PostgreSQL (Neon, DATABASE_URL).
 * Safe to run again — existing rows are never changed (INSERT IGNORE / ON CONFLICT DO NOTHING).
 * The only exception is the SEO content update (seo_content.php), which is applied
 * once per version and then remembered in the setting `seo_content_version`.
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

/** Columns added after the first release — older MySQL databases get them here. */
function mysql_add_missing_columns(PDO $pdo): int
{
    $add = [
        'services' => ['alt_text' => "VARCHAR(255) NULL AFTER `image`"],
        'gallery'  => ['alt_text' => "VARCHAR(255) NULL AFTER `image`"],
        'events'   => ['description' => "VARCHAR(255) NULL AFTER `title`", 'alt_text' => "VARCHAR(255) NULL AFTER `image`"],
    ];
    $n = 0;
    foreach ($add as $table => $cols) {
        foreach ($cols as $col => $def) {
            $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
            $st->execute([$table, $col]);
            if ((int) $st->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
                $n++;
            }
        }
    }
    return $n;
}

/**
 * Apply the SEO website copy (database/seo_content.php) once.
 * Overwrites the matching settings / rows, then records the version so admin edits made later are kept.
 */
function apply_seo_content(PDO $pdo, bool $pg): string
{
    $content = require __DIR__ . '/seo_content.php';
    $qi = fn(string $id) => $pg ? "\"$id\"" : "`$id`";

    $st = $pdo->prepare('SELECT ' . $qi('value') . ' FROM settings WHERE ' . $qi('key') . ' = ?');
    $st->execute(['seo_content_version']);
    $current = (int) ($st->fetchColumn() ?: 0);
    if ($current >= $content['version']) {
        return "SEO content v{$content['version']} already applied.";
    }

    $now = date('Y-m-d H:i:s');
    $upsert = $pg
        ? $pdo->prepare('INSERT INTO settings ("key","value",updated_at) VALUES (?,?,?) ON CONFLICT ("key") DO UPDATE SET "value" = EXCLUDED."value", updated_at = EXCLUDED.updated_at')
        : $pdo->prepare('INSERT INTO settings (`key`,`value`,`updated_at`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`)');

    $pdo->beginTransaction();
    try {
        foreach ($content['settings'] as $k => $v) {
            $upsert->execute([$k, $v, $now]);
        }
        foreach ($content['rows'] as $table => $rows) {
            foreach ($rows as $id => $cols) {
                $cols['updated_at'] = $now;
                $set = implode(', ', array_map(fn($c) => $qi($c) . ' = ?', array_keys($cols)));
                $pdo->prepare("UPDATE $table SET $set WHERE id = ?")->execute([...array_values($cols), $id]);
            }
        }
        if ((int) $pdo->query('SELECT COUNT(*) FROM faqs')->fetchColumn() === 0) {
            $ins = $pdo->prepare('INSERT INTO faqs (question, answer, sort_order) VALUES (?,?,?)');
            foreach ($content['faqs'] as $i => [$q, $a]) {
                $ins->execute([$q, $a, $i + 1]);
            }
        }
        $upsert->execute(['seo_content_version', (string) $content['version'], $now]);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
    return "SEO content v{$content['version']} applied.";
}

$c = DB::connectionConfig();
$mysqlSchema = file_get_contents(__DIR__ . '/schema.sql');
$seedAt = strpos($mysqlSchema, 'Seed data');
$count = 0;

try {
    if ($c['driver'] === 'pgsql') {
        $pdo = DB::pdo();
        // 1) tables (+ ADD COLUMN IF NOT EXISTS for older databases)
        foreach (sql_statements(file_get_contents(__DIR__ . '/schema.pgsql.sql')) as $stmt) {
            $pdo->exec($stmt);
            $count++;
        }
        // 2) seed rows: reuse the MySQL seed section, converted to PostgreSQL syntax
        $seed = substr($mysqlSchema, $seedAt);
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
        foreach (['admins', 'login_attempts', 'menu_items', 'hero_slides', 'services', 'gallery', 'highlights', 'events', 'features', 'faqs', 'testimonials', 'enquiries'] as $t) {
            $pdo->exec("SELECT setval(pg_get_serial_sequence('$t', 'id'), GREATEST(COALESCE((SELECT MAX(id) FROM $t), 0), 1), (SELECT COUNT(*) > 0 FROM $t))");
        }
        echo "✔ PostgreSQL database `{$c['name']}` on {$c['host']} ready ($count statements).\n";
        echo '  ' . apply_seo_content($pdo, true) . "\n";
    } else {
        $pdo = DB::connect($c, false);
        $name = str_replace('`', '', $c['name']);
        $sql = str_replace('`bb_caterers`', "`$name`", $mysqlSchema);
        $split = strpos($sql, 'Seed data');
        // 1) database + tables, 2) columns added later, 3) seed rows
        foreach (sql_statements(substr($sql, 0, $split)) as $stmt) {
            $pdo->exec($stmt);
            $count++;
        }
        $count += mysql_add_missing_columns($pdo);
        foreach (sql_statements(substr($sql, strpos($sql, "\n", $split))) as $stmt) {
            $pdo->exec($stmt);
            $count++;
        }
        echo "✔ MySQL database `$name` ready ($count statements).\n";
        echo '  ' . apply_seo_content($pdo, false) . "\n";
    }
    echo "  Admin login → username: admin   password: admin123 (change it after first login)\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Database error: ' . $e->getMessage() . "\n");
    exit(1);
}
