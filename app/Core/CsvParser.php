<?php
namespace App\Core;

class CsvParser
{
    /**
     * Parse a CSV file and return an array of rows (each row is an array of strings).
     * Handles encoding detection, BOM removal, and auto-delimiter detection.
     *
     * @param string $filePath  Absolute path to the CSV file
     * @param int    $maxRows   Maximum data rows to return (not counting header)
     * @return array<int, array<int, string>>
     * @throws \RuntimeException
     */
    public static function parse(string $filePath, int $maxRows = 2000): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("Arquivo CSV não encontrado ou inacessível: {$filePath}");
        }

        // 1. Read raw content
        $raw = file_get_contents($filePath);
        if ($raw === false) {
            throw new \RuntimeException("Não foi possível ler o arquivo CSV.");
        }

        // 2. Detect encoding and convert to UTF-8
        $raw = self::toUtf8($raw);

        // 3. Remove BOM (UTF-8 BOM: EF BB BF)
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        // Normalise line endings
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        // 4. Auto-detect delimiter from first non-empty line
        $firstLine = '';
        $pos = strpos($raw, "\n");
        if ($pos === false) {
            $firstLine = $raw;
        } else {
            $firstLine = substr($raw, 0, $pos);
        }
        $delimiter = self::detectDelimiter($firstLine);

        // 5. Parse rows via fgetcsv
        // Write normalised content to a temporary in-memory stream for fgetcsv
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException("Não foi possível criar stream temporário para parsing CSV.");
        }
        fwrite($stream, $raw);
        rewind($stream);

        $rows      = [];
        $rowCount  = 0;
        $dataCount = 0;

        while (($line = fgetcsv($stream, 0, $delimiter, '"', '\\')) !== false) {
            // Trim each cell
            $line = array_map('trim', $line);

            // Skip fully empty rows
            $nonEmpty = array_filter($line, fn($cell) => $cell !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            // Skip comment rows (first non-empty cell starts with '#')
            if (isset($line[0]) && str_starts_with($line[0], '#')) {
                continue;
            }

            $rows[] = $line;
            $rowCount++;

            // Count data rows (skip header row at index 0)
            if ($rowCount > 1) {
                $dataCount++;
                if ($dataCount >= $maxRows) {
                    break;
                }
            }
        }
        fclose($stream);

        return $rows;
    }

    /**
     * Detect the most likely delimiter from a CSV header line.
     * Candidates: semicolon, comma, tab, pipe
     */
    private static function detectDelimiter(string $line): string
    {
        $candidates = [';', ',', "\t", '|'];
        $counts     = [];
        foreach ($candidates as $delim) {
            $counts[$delim] = substr_count($line, $delim);
        }
        arsort($counts);
        $best = array_key_first($counts);
        // Fall back to comma if nothing detected
        return ($counts[$best] > 0) ? $best : ',';
    }

    /**
     * Convert a raw byte string to UTF-8.
     * Detects UTF-8, ISO-8859-1, and Windows-1252.
     */
    private static function toUtf8(string $raw): string
    {
        // If already valid UTF-8, return as-is
        if (mb_check_encoding($raw, 'UTF-8')) {
            return $raw;
        }

        // Try mbstring detection
        $detected = mb_detect_encoding($raw, ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'UTF-16'], true);
        if ($detected && $detected !== 'UTF-8') {
            $converted = mb_convert_encoding($raw, 'UTF-8', $detected);
            if ($converted !== false) {
                return $converted;
            }
        }

        // Fallback: assume Windows-1252 (superset of ISO-8859-1)
        $converted = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        return $converted !== false ? $converted : $raw;
    }
}
