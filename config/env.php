<?php
/**
 * Minimal .env loader — zero dependencies, PHP 8.x, shared-hosting compatible.
 *
 * Rules:
 *   - Lines starting with # are comments
 *   - KEY=VALUE (no spaces around =)
 *   - Values can be quoted with single or double quotes
 *   - Variables already set in the environment (e.g. by Apache SetEnv) take precedence
 *   - Loaded once; subsequent requires are no-ops (ROOT constant guards it)
 */
(function (): void {
    $file = ROOT . '/.env';
    if (!is_file($file)) {
        return;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }

        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);

        // Strip surrounding matching quotes
        if (strlen($val) >= 2) {
            $q = $val[0];
            if (($q === '"' || $q === "'") && str_ends_with($val, $q)) {
                $val = substr($val, 1, -1);
            }
        }

        // Never override values already set by the server/OS environment
        if (getenv($key) === false && !isset($_ENV[$key])) {
            putenv("$key=$val");
            $_ENV[$key]    = $val;
            $_SERVER[$key] = $val;
        }
    }
})();

/**
 * Read an environment variable.
 * Checks $_ENV → $_SERVER → getenv() — covers all PHP SAPI configurations.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
    if ($value === null) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
    }
    return $value;
}
