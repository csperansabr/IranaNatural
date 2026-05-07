<?php
namespace App\Core;

class XlsxParser
{
    /**
     * Parse an XLSX file and return an array of rows (each row is an array of strings).
     * Pure PHP implementation using ZipArchive + SimpleXML.
     *
     * @param string $filePath  Absolute path to the XLSX file
     * @param int    $maxRows   Maximum data rows to return (not counting header)
     * @return array<int, array<int, string>>
     * @throws \RuntimeException
     */
    public static function parse(string $filePath, int $maxRows = 2000): array
    {
        // 1. Check ZipArchive is available
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException("A extensão PHP ZipArchive é necessária para ler arquivos XLSX.");
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("Arquivo XLSX não encontrado ou inacessível: {$filePath}");
        }

        // 2. Open as ZIP
        $zip = new \ZipArchive();
        $res = $zip->open($filePath, \ZipArchive::RDONLY);
        if ($res !== true) {
            throw new \RuntimeException("Não foi possível abrir o arquivo XLSX (código ZIP: {$res}).");
        }

        // 3. Read shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $sharedStrings = self::parseSharedStrings($ssXml);
        }

        // 4. Find first worksheet path via workbook relationships
        $sheetPath = self::findFirstSheetPath($zip);

        // 5. Read the worksheet XML
        $wsXml = $zip->getFromName($sheetPath);
        if ($wsXml === false) {
            // Try sheet1.xml as fallback
            $wsXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            if ($wsXml === false) {
                $zip->close();
                throw new \RuntimeException("Não foi possível encontrar dados na planilha XLSX.");
            }
        }

        $zip->close();

        // 6. Parse worksheet rows
        $rows = self::parseWorksheet($wsXml, $sharedStrings, $maxRows);

        return $rows;
    }

    /**
     * Parse xl/sharedStrings.xml and return an indexed array of strings.
     * Handles both <si><t>text</t></si> and <si><r><t>text</t></r></si> formats.
     */
    private static function parseSharedStrings(string $xml): array
    {
        $strings = [];

        // Suppress XML errors
        $prev = libxml_use_internal_errors(true);
        $sst  = simplexml_load_string($xml);
        libxml_use_internal_errors($prev);

        if ($sst === false) {
            return $strings;
        }

        foreach ($sst->si as $si) {
            // Simple case: <si><t>value</t></si>
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            // Rich text: <si><r><t>part1</t></r><r><t>part2</t></r></si>
            $text = '';
            if (isset($si->r)) {
                foreach ($si->r as $r) {
                    if (isset($r->t)) {
                        $text .= (string) $r->t;
                    }
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    /**
     * Determine the path of the first worksheet by reading workbook relationships.
     */
    private static function findFirstSheetPath(\ZipArchive $zip): string
    {
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($relsXml === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $prev = libxml_use_internal_errors(true);
        $rels  = simplexml_load_string($relsXml);
        libxml_use_internal_errors($prev);

        if ($rels === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $worksheetType = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet';

        foreach ($rels->Relationship as $rel) {
            $type   = (string) $rel['Type'];
            $target = (string) $rel['Target'];

            if ($type === $worksheetType) {
                // Target may be relative like 'worksheets/sheet1.xml'
                if (!str_starts_with($target, 'xl/')) {
                    $target = 'xl/' . $target;
                }
                return $target;
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    /**
     * Parse the worksheet XML and return rows as arrays of strings.
     */
    private static function parseWorksheet(string $xml, array $sharedStrings, int $maxRows): array
    {
        $prev = libxml_use_internal_errors(true);
        $ws   = simplexml_load_string($xml);
        libxml_use_internal_errors($prev);

        if ($ws === false || !isset($ws->sheetData)) {
            return [];
        }

        $rows      = [];
        $dataCount = 0;
        $rowIndex  = 0;

        foreach ($ws->sheetData->row as $row) {
            $rowData = [];
            $maxCol  = -1;

            // First pass: find the maximum column index used in this row
            foreach ($row->c as $cell) {
                $ref    = (string) ($cell['r'] ?? '');
                $colStr = preg_replace('/[0-9]/', '', $ref);
                if ($colStr !== '') {
                    $colIdx = self::colToIndex($colStr);
                    if ($colIdx > $maxCol) {
                        $maxCol = $colIdx;
                    }
                }
            }

            if ($maxCol < 0) {
                // No cells with references — skip
                continue;
            }

            // Initialise row array with empty strings
            $rowData = array_fill(0, $maxCol + 1, '');

            // Second pass: fill in actual values
            foreach ($row->c as $cell) {
                $ref    = (string) ($cell['r'] ?? '');
                $colStr = preg_replace('/[0-9]/', '', $ref);
                if ($colStr === '') continue;

                $colIdx = self::colToIndex($colStr);
                $type   = (string) ($cell['t'] ?? '');
                $value  = '';

                switch ($type) {
                    case 's':
                        // Shared string index
                        $idx   = (int) ((string) ($cell->v ?? '-1'));
                        $value = $sharedStrings[$idx] ?? '';
                        break;

                    case 'b':
                        // Boolean
                        $raw   = (string) ($cell->v ?? '0');
                        $value = ($raw === '1') ? 'Sim' : 'Não';
                        break;

                    case 'inlineStr':
                        // Inline string
                        $value = (string) ($cell->is->t ?? '');
                        break;

                    default:
                        // Numeric or formula result
                        $value = (string) ($cell->v ?? '');
                        break;
                }

                $rowData[$colIdx] = trim($value);
            }

            // Skip fully empty rows
            $nonEmpty = array_filter($rowData, fn($c) => $c !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $rows[] = $rowData;
            $rowIndex++;

            if ($rowIndex > 1) {
                $dataCount++;
                if ($dataCount >= $maxRows) {
                    break;
                }
            }
        }

        return $rows;
    }

    /**
     * Convert a column letter(s) to a zero-based column index.
     * A=0, B=1, ..., Z=25, AA=26, AB=27, etc.
     */
    private static function colToIndex(string $col): int
    {
        $col   = strtoupper($col);
        $index = 0;
        $len   = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }
}
