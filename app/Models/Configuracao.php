<?php
namespace App\Models;

use App\Core\Model;

class Configuracao extends Model
{
    protected string $table = 'configuracoes';

    private static array $cache = [];

    public function get(string $chave, mixed $default = null): mixed
    {
        if (!array_key_exists($chave, self::$cache)) {
            try {
                $row = $this->queryOne("SELECT valor FROM configuracoes WHERE chave = ?", [$chave]);
                self::$cache[$chave] = $row !== null ? $row['valor'] : $default;
            } catch (\Throwable $e) {
                self::$cache[$chave] = $default;
            }
        }
        return self::$cache[$chave];
    }

    public function set(string $chave, ?string $valor): void
    {
        $this->exec(
            "INSERT INTO configuracoes (chave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [$chave, $valor]
        );
        self::$cache[$chave] = $valor;
    }

    public function getAll(): array
    {
        try {
            $rows = $this->query("SELECT chave, valor, descricao FROM configuracoes ORDER BY chave ASC");
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $out[$row['chave']] = $row['valor'];
        }
        return $out;
    }

    public static function resetCache(): void
    {
        self::$cache = [];
    }
}
