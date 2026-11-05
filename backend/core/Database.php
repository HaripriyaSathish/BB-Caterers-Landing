<?php
declare(strict_types=1);

/**
 * Small PDO wrapper that works with MySQL (local XAMPP / MySQL 8) and PostgreSQL (Neon on Render).
 *
 * Queries in this project are written with MySQL-style `backtick` identifiers; for PostgreSQL they are
 * rewritten to "double quotes" automatically. Values are always bound as parameters, never inlined,
 * so backticks only ever appear around identifiers.
 */
final class DB
{
    private static ?PDO $pdo = null;
    private static string $driver = 'mysql';

    public static function driver(): string
    {
        self::pdo();
        return self::$driver;
    }

    public static function isPgsql(): bool
    {
        return self::driver() === 'pgsql';
    }

    /** Connection settings from DATABASE_URL (Neon style) or the individual DB_* values. */
    public static function connectionConfig(): array
    {
        $cfg = (array) config('db', []);
        $url = (string) ($cfg['url'] ?? '');
        if ($url !== '') {
            $p = parse_url($url);
            if (!$p || empty($p['host'])) {
                throw new RuntimeException('DATABASE_URL is not a valid connection string.');
            }
            parse_str($p['query'] ?? '', $q);
            $scheme = strtolower($p['scheme'] ?? 'postgresql');
            return [
                'driver'   => in_array($scheme, ['postgres', 'postgresql', 'pgsql'], true) ? 'pgsql' : 'mysql',
                'host'     => $p['host'],
                'port'     => (int) ($p['port'] ?? (str_starts_with($scheme, 'postg') ? 5432 : 3306)),
                'name'     => ltrim($p['path'] ?? '', '/'),
                'user'     => urldecode($p['user'] ?? ''),
                'password' => urldecode($p['pass'] ?? ''),
                'options'  => $q, // e.g. sslmode=require&channel_binding=require
            ];
        }
        return [
            'driver'   => ($cfg['driver'] ?? 'mysql') === 'pgsql' ? 'pgsql' : 'mysql',
            'host'     => $cfg['host'] ?? '127.0.0.1',
            'port'     => (int) ($cfg['port'] ?? 3306),
            'name'     => $cfg['name'] ?? '',
            'user'     => $cfg['user'] ?? '',
            'password' => (string) ($cfg['password'] ?? ''),
            'options'  => array_filter(['sslmode' => $cfg['sslmode'] ?? null]),
        ];
    }

    /** Open a PDO connection (optionally without selecting a database — used by the MySQL installer). */
    public static function connect(array $c, bool $withDatabase = true): PDO
    {
        $opts = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        if ($c['driver'] === 'pgsql') {
            // Neon's "-pooler" endpoint runs PgBouncer: prepare client-side so no server statements are kept.
            $opts[PDO::ATTR_EMULATE_PREPARES] = true;
            $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $c['host'], $c['port'], $c['name']);
            foreach ((array) $c['options'] as $k => $v) {
                if (preg_match('/^[a-z_]+$/', (string) $k) && preg_match('/^[A-Za-z0-9_.-]+$/', (string) $v)) {
                    $dsn .= ";$k=$v"; // sslmode, channel_binding, … are passed straight to libpq
                }
            }
            // Neon routes by SNI; older libpq builds can't send it, so name the endpoint explicitly
            // (endpoint id = first part of the host without "-pooler"). Harmless for newer clients.
            if (preg_match('/^(ep-[a-z0-9-]+?)(-pooler)?\./', (string) $c['host'], $m) && str_ends_with((string) $c['host'], '.neon.tech')) {
                $dsn .= ";options='endpoint=" . $m[1] . "'";
            }
            try {
                $pdo = new PDO($dsn, $c['user'], $c['password'], $opts);
            } catch (PDOException $e) {
                // Older libpq builds (e.g. XAMPP) don't know channel_binding; sslmode=require still encrypts
                if (!str_contains($e->getMessage(), 'channel_binding')) {
                    throw $e;
                }
                $pdo = new PDO(preg_replace('/;channel_binding=[^;]*/', '', $dsn), $c['user'], $c['password'], $opts);
            }
            $pdo->exec("SET TIME ZONE '" . str_replace("'", '', (string) config('timezone', 'Asia/Kolkata')) . "'");
        } else {
            $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $c['host'], $c['port'])
                . ($withDatabase && $c['name'] !== '' ? ';dbname=' . $c['name'] : '');
            $pdo = new PDO($dsn, $c['user'], $c['password'], $opts);
            $pdo->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
        }
        return $pdo;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = self::connectionConfig();
            self::$driver = $c['driver'];
            self::$pdo = self::connect($c);
        }
        return self::$pdo;
    }

    /** Translate MySQL-style identifier quoting for the active driver. */
    public static function sql(string $sql): string
    {
        return self::isPgsql() ? str_replace('`', '"', $sql) : $sql;
    }

    public static function prepare(string $sql): PDOStatement
    {
        return self::pdo()->prepare(self::sql($sql));
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function value(string $sql, array $params = [])
    {
        $v = self::query($sql, $params)->fetchColumn();
        return $v === false ? null : $v;
    }

    /** Insert a row; column names must come from trusted code, never from user input. */
    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(',', array_map(fn($c) => "`$c`", $cols)),
            implode(',', array_fill(0, count($cols), '?'))
        );
        if (self::isPgsql()) {
            return (int) self::value($sql . ' RETURNING id', array_values($data));
        }
        self::query($sql, array_values($data));
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, int $id): void
    {
        if (self::isPgsql() && !array_key_exists('updated_at', $data) && $table !== 'login_attempts') {
            $data['updated_at'] = date('Y-m-d H:i:s'); // MySQL does this with ON UPDATE CURRENT_TIMESTAMP
        }
        $set = implode(',', array_map(fn($c) => "`$c` = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;
        self::query("UPDATE `$table` SET $set WHERE id = ?", $params);
    }

    /** Insert or update a row by its unique key column (settings / media files). */
    public static function upsert(string $table, string $keyCol, array $data): void
    {
        $cols = array_keys($data);
        $colSql = implode(',', array_map(fn($c) => "`$c`", $cols));
        $ph = implode(',', array_fill(0, count($cols), '?'));
        $updates = array_filter($cols, fn($c) => $c !== $keyCol);
        if (self::isPgsql()) {
            $set = implode(',', array_map(fn($c) => "`$c` = EXCLUDED.`$c`", $updates));
            $sql = "INSERT INTO `$table` ($colSql) VALUES ($ph) ON CONFLICT (`$keyCol`) DO UPDATE SET $set";
        } else {
            $set = implode(',', array_map(fn($c) => "`$c` = VALUES(`$c`)", $updates));
            $sql = "INSERT INTO `$table` ($colSql) VALUES ($ph) ON DUPLICATE KEY UPDATE $set";
        }
        self::query($sql, array_values($data));
    }

    /** Case-insensitive LIKE for the active driver. */
    public static function ilike(): string
    {
        return self::isPgsql() ? 'ILIKE' : 'LIKE';
    }
}
