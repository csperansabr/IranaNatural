<?php
namespace App\Models;

use App\Core\Model;

class ImportHistory extends Model
{
    protected string $table = 'import_history';

    public function recentes(int $limit = 50): array
    {
        return $this->query(
            "SELECT * FROM import_history ORDER BY criado_em DESC LIMIT ?",
            [$limit]
        );
    }

    public function erros(int $importId): array
    {
        return $this->query(
            "SELECT * FROM import_errors WHERE import_id = ? ORDER BY linha ASC",
            [$importId]
        );
    }

    public function addErro(int $importId, int $linha, string $campo, string $valor, string $msg): void
    {
        $this->exec(
            "INSERT INTO import_errors (import_id, linha, campo, valor, mensagem) VALUES (?,?,?,?,?)",
            [$importId, $linha, $campo, $valor, $msg]
        );
    }
}
