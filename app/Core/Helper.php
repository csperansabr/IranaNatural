<?php
namespace App\Core;

class Helper
{
    // Unit conversion table (to base unit)
    // Weight base: g | Volume base: ml | Unit/Pack base: unit (1:1)
    private static array $units = [
        'kg'  => ['family' => 'peso',    'factor' => 1000],
        'g'   => ['family' => 'peso',    'factor' => 1],
        'mg'  => ['family' => 'peso',    'factor' => 0.001],
        'l'   => ['family' => 'volume',  'factor' => 1000],
        'ml'  => ['family' => 'volume',  'factor' => 1],
        'un'  => ['family' => 'unidade', 'factor' => 1],
        'pct' => ['family' => 'unidade', 'factor' => 1],
        'cx'  => ['family' => 'unidade', 'factor' => 1],
    ];

    // Convert quantity from one unit to another (same family only)
    public static function convertUnit(float $qty, string $from, string $to): ?float
    {
        if ($from === $to) return $qty;
        $f = self::$units[$from] ?? null;
        $t = self::$units[$to]   ?? null;
        if (!$f || !$t || $f['family'] !== $t['family']) return null;
        return ($qty * $f['factor']) / $t['factor'];
    }

    // Return cost per $toUnit given cost per $fromUnit
    // e.g. costPerUnit(10.00, 'kg', 'g') → 0.01 (R$0.01 per gram)
    public static function costPerUnit(float $costPerFrom, string $fromUnit, string $toUnit): ?float
    {
        if ($fromUnit === $toUnit) return $costPerFrom;
        $converted = self::convertUnit(1.0, $fromUnit, $toUnit);
        if ($converted === null || $converted == 0) return null;
        return $costPerFrom / $converted;
    }

    public static function isSameFamily(string $unitA, string $unitB): bool
    {
        $a = self::$units[$unitA]['family'] ?? null;
        $b = self::$units[$unitB]['family'] ?? null;
        return $a !== null && $a === $b;
    }

    public static function slug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $map  = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
                 'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
                 'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ç'=>'c','ñ'=>'n'];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return trim($text, '-');
    }

    public static function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    public static function percent(float $value): string
    {
        return number_format($value, 2, ',', '.') . '%';
    }

    public static function date(string $date): string
    {
        return date('d/m/Y', strtotime($date));
    }

    public static function datetime(string $dt): string
    {
        return date('d/m/Y H:i', strtotime($dt));
    }

    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function url(string $path = ''): string
    {
        return APP_URL . '/' . ltrim($path, '/');
    }

    public static function asset(string $path): string
    {
        return APP_URL . '/assets/' . ltrim($path, '/');
    }

    public static function upload(string $path): string
    {
        return APP_URL . '/uploads/' . ltrim($path, '/');
    }

    public static function whatsapp(string $msg = ''): string
    {
        $msg = $msg ?: WHATSAPP_MSG;
        return 'https://wa.me/' . WHATSAPP . '?text=' . urlencode($msg);
    }

    public static function whatsappProduct(string $productName): string
    {
        $msg = "Olá! Tenho interesse no produto *{$productName}* da Iraná Natural. Poderia me dar mais informações?";
        return 'https://wa.me/' . WHATSAPP . '?text=' . urlencode($msg);
    }

    public static function uploadFile(array $file, string $subdir, array $allowed = ['jpg','jpeg','png','webp']): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) return null;
        if ($file['size'] > 5 * 1024 * 1024) return null;

        $dir = UPLOAD_DIR . $subdir;
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = uniqid() . '_' . time() . '.' . $ext;
        $dest     = $dir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return $subdir . '/' . $filename;
        }
        return null;
    }

    public static function excerpt(string $text, int $length = 120): string
    {
        $plain = strip_tags($text);
        if (mb_strlen($plain) <= $length) return $plain;
        return mb_substr($plain, 0, $length) . '…';
    }

    public static function paginate(int $total, int $perPage, int $current, string $baseUrl): array
    {
        $pages = (int) ceil($total / $perPage);
        return ['total' => $total, 'per_page' => $perPage, 'current' => $current,
                'pages' => $pages, 'base_url' => $baseUrl];
    }

    public static function unitLabel(string $unit): string
    {
        return match($unit) {
            'kg'  => 'kg', 'g' => 'g', 'mg' => 'mg',
            'l'   => 'L', 'ml' => 'mL',
            'un'  => 'unidade(s)', 'pct' => 'pacote(s)', 'cx' => 'caixa(s)',
            default => $unit,
        };
    }

    // Render basic Markdown to safe HTML (admin-authored content only)
    public static function md(string $text): string
    {
        if ($text === '') return '';
        // Escape HTML first to prevent XSS, then apply Markdown patterns
        $t = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // Bold
        $t = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $t);
        $t = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $t);
        // Italic (after bold to avoid conflict)
        $t = preg_replace('/\*([^\*\n]+?)\*/', '<em>$1</em>', $t);
        $t = preg_replace('/_([^_\n]+?)_/', '<em>$1</em>', $t);
        // Inline links [text](url)
        $t = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $t);
        // Unordered lists (consecutive - or * lines)
        $t = preg_replace_callback('/(?:^[-\*] .+(?:\n|$))+/m', function ($m) {
            $items = preg_split('/\n/', trim($m[0]));
            $html  = '<ul>';
            foreach ($items as $item) {
                $item = preg_replace('/^[-\*] /', '', $item);
                if ($item !== '') $html .= '<li>' . $item . '</li>';
            }
            return $html . '</ul>';
        }, $t);
        // Split into paragraphs by double newlines
        $parts  = preg_split('/\n{2,}/', $t);
        $result = '';
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;
            if (str_starts_with($part, '<ul>') || str_starts_with($part, '<ol>')) {
                $result .= $part;
            } else {
                $result .= '<p>' . str_replace("\n", '<br>', $part) . '</p>';
            }
        }
        return $result;
    }
}
