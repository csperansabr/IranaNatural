<?php
/**
 * Iraná Natural — Importador CLI de Insumos
 * Funções auxiliares
 */
declare(strict_types=1);

/**
 * Converte string em float, suportando formato brasileiro (vírgula decimal).
 * Retorna null para strings vazias, "-" ou inválidas.
 */
function parseFloat(string $v): ?float
{
    $v = preg_replace('/[R\$\s]+/', '', $v);
    $v = trim($v);
    if ($v === '' || $v === '-') return null;

    $dotCount   = substr_count($v, '.');
    $commaCount = substr_count($v, ',');

    if ($commaCount === 1 && $dotCount >= 1) {
        // Formato BR: 1.234,56 → remove pontos (milhar), vírgula vira ponto
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
    } elseif ($commaCount >= 1 && $dotCount === 0) {
        $v = str_replace(',', '.', $v);
    } elseif ($dotCount >= 2) {
        $v = str_replace('.', '', $v);
    }

    return is_numeric($v) ? (float)$v : null;
}

/**
 * Converte string em data Y-m-d.
 * Aceita DD/MM/YYYY ou YYYY-MM-DD.
 */
function parseDate(string $v): ?string
{
    $v = trim($v);
    if ($v === '' || $v === '-') return null;
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $v, $m)) {
        return "{$m[3]}-{$m[2]}-{$m[1]}";
    }
    if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $v)) {
        return $v;
    }
    return null;
}

/**
 * Normaliza estoque: "-" → 0, negativo → 0.
 */
function sanitizeStock(string $v): float
{
    if (trim($v) === '' || trim($v) === '-') return 0.0;
    $f = parseFloat($v);
    if ($f === null) return 0.0;
    return max(0.0, $f);
}

/**
 * Remove BOM UTF-8 do início da string.
 */
function stripBOM(string $s): string
{
    if (str_starts_with($s, "\xEF\xBB\xBF")) {
        return substr($s, 3);
    }
    return $s;
}

/**
 * Normaliza unidade de medida para minúsculas sem espaços.
 */
function normUnit(string $u): string
{
    return strtolower(trim($u));
}

/**
 * Colore texto para terminal usando ANSI.
 * Retorna texto puro se não for TTY ou posix não disponível.
 */
function c(string $text, string $color): string
{
    static $isTty = null;
    if ($isTty === null) {
        $isTty = function_exists('posix_isatty') && posix_isatty(STDOUT);
    }
    if (!$isTty) return $text;
    $codes = [
        'red'    => "\033[31m",
        'green'  => "\033[32m",
        'yellow' => "\033[33m",
        'blue'   => "\033[34m",
        'cyan'   => "\033[36m",
        'white'  => "\033[37m",
        'bold'   => "\033[1m",
    ];
    return ($codes[$color] ?? '') . $text . "\033[0m";
}

/**
 * Barra de progresso inline para CLI (usa \r).
 */
function progressBar(int $current, int $total, int $width = 30): string
{
    $pct  = $total > 0 ? (int)round($current / $total * 100) : 0;
    $done = $total > 0 ? (int)round($current / $total * $width) : 0;
    $bar  = str_repeat('=', $done) . str_repeat('-', $width - $done);
    return sprintf("\r  [%s] %d/%d (%d%%)", $bar, $current, $total, $pct);
}

/**
 * Solicita entrada do usuário via STDIN.
 */
function prompt(string $question): string
{
    fwrite(STDOUT, $question);
    return trim((string)fgets(STDIN));
}

/**
 * Lê e parseia arquivo CSV com BOM opcional e delimitador configurável.
 */
function parseCsv(string $file, string $delimiter = ';'): array
{
    $rows      = [];
    $fh        = fopen($file, 'r');
    if (!$fh) return [];

    $firstLine = true;
    while (($line = fgets($fh)) !== false) {
        if ($firstLine) {
            $line      = stripBOM($line);
            $firstLine = false;
        }
        $line = rtrim($line, "\r\n");
        if (trim($line) === '') continue;

        $rows[] = str_getcsv($line, $delimiter, '"', '\\');
    }
    fclose($fh);
    return $rows;
}

/**
 * Remove códigos ANSI de string (para gravação em arquivo de log).
 */
function stripAnsi(string $s): string
{
    return (string)preg_replace('/\033\[[0-9;]*m/', '', $s);
}
