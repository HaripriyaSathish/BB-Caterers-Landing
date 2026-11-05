<?php
declare(strict_types=1);

/**
 * Tiny .env loader (no Composer needed).
 * Supports KEY=value, KEY="quoted value", KEY='single quoted', # comments and blank lines.
 * Real environment variables (set by the server) always win over the file.
 */
function load_env(string $file): void
{
    if (!is_file($file) || !is_readable($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
            continue;
        }
        $q = $value[0] ?? '';
        if (($q === '"' || $q === "'") && str_ends_with($value, $q) && strlen($value) >= 2) {
            $value = substr($value, 1, -1);
            if ($q === '"') {
                $value = str_replace(['\\n', '\\"', '\\\\'], ["\n", '"', '\\'], $value);
            }
        } else {
            $value = trim(preg_replace('/\s+#.*$/', '', $value)); // strip trailing comment
        }
        if (getenv($key) === false && !array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/** Read an environment value with type casting for true/false/null and a default. */
function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true', '(true)'   => true,
        'false', '(false)' => false,
        'null', '(null)'   => null,
        'empty', '(empty)' => '',
        default            => $value,
    };
}
