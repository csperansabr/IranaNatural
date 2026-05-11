<?php
namespace App\Models;

use App\Core\Model;

class EmailLog extends Model
{
    protected string $table = 'email_logs';

    public function listar(int $pedidoId): array
    {
        return $this->query(
            "SELECT * FROM email_logs WHERE pedido_id = ? ORDER BY criado_em DESC",
            [$pedidoId]
        );
    }

    public function resumo(int $dias = 7): array
    {
        return $this->query(
            "SELECT tipo, status, COUNT(*) AS qtd
             FROM email_logs
             WHERE criado_em >= DATE_SUB(NOW(), INTERVAL ? DAY)
             GROUP BY tipo, status
             ORDER BY tipo, status",
            [$dias]
        );
    }
}
