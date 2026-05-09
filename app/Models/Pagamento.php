<?php
namespace App\Models;

use App\Core\Model;

class Pagamento extends Model
{
    protected string $table = 'pagamentos';

    public function criar(int $pedidoId, array $dados): int
    {
        return $this->insert([
            'pedido_id'      => $pedidoId,
            'order_nsu'      => $dados['order_nsu'],
            'invoice_slug'   => $dados['invoice_slug']    ?? null,
            'checkout_url'   => $dados['checkout_url']    ?? null,
            'metodo'         => $dados['metodo'],
            'parcelas'       => (int)($dados['parcelas']  ?? 1),
            'valor_original' => (float)($dados['valor_original'] ?? 0),
            'valor_desconto' => (float)($dados['valor_desconto'] ?? 0),
            'valor_cobrado'  => (float)($dados['valor_cobrado']  ?? 0),
            'status'         => 'pending',
            'payload_criacao'=> isset($dados['payload_criacao']) ? json_encode($dados['payload_criacao']) : null,
        ]);
    }

    public function findByPedido(int $pedidoId): ?array
    {
        return $this->queryOne("SELECT * FROM pagamentos WHERE pedido_id = ?", [$pedidoId]);
    }

    public function findByOrderNsu(string $orderNsu): ?array
    {
        return $this->queryOne("SELECT * FROM pagamentos WHERE order_nsu = ?", [$orderNsu]);
    }

    public function atualizarStatus(int $id, string $status, array $dados = []): void
    {
        $fields = ['status' => $status];

        if (!empty($dados['transaction_nsu'])) $fields['transaction_nsu'] = $dados['transaction_nsu'];
        if (!empty($dados['invoice_slug']))    $fields['invoice_slug']    = $dados['invoice_slug'];
        if (!empty($dados['receipt_url']))     $fields['receipt_url']     = $dados['receipt_url'];
        if (!empty($dados['metodo']))          $fields['metodo']          = $dados['metodo'];
        if (isset($dados['valor_pago']))       $fields['valor_pago']      = (float)$dados['valor_pago'];
        if (!empty($dados['payload_webhook'])) $fields['payload_webhook'] = json_encode($dados['payload_webhook']);
        if ($status === 'approved')            $fields['pago_em']         = date('Y-m-d H:i:s');

        $this->update($id, $fields);
    }

    public function registrarWebhookLog(array $dados): void
    {
        $this->exec(
            "INSERT INTO webhook_logs
             (source, order_nsu, pedido_id, transaction_nsu, status,
              payload, raw_body, paid_amount, receipt_url, capture_method, installments,
              ip, processado, erro)
             VALUES (?,?,?,?,?, ?,?,?,?,?,?, ?,?,?)",
            [
                $dados['source']          ?? 'infinitepay',
                $dados['order_nsu']       ?? null,
                $dados['pedido_id']       ?? null,
                $dados['transaction_nsu'] ?? null,
                $dados['status']          ?? null,

                isset($dados['payload'])  ? json_encode($dados['payload']) : null,
                $dados['raw_body']        ?? null,
                $dados['paid_amount']     ?? null,
                $dados['receipt_url']     ?? null,
                $dados['capture_method']  ?? null,
                $dados['installments']    ?? null,

                $dados['ip']              ?? null,
                (int)($dados['processado'] ?? 0),
                $dados['erro']            ?? null,
            ]
        );
    }

    public function getAllWebhookLogs(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['order_nsu'])) {
            $where[]  = "order_nsu LIKE ?";
            $params[] = '%' . $filtros['order_nsu'] . '%';
        }
        if (isset($filtros['status']) && $filtros['status'] !== '') {
            $where[]  = "status = ?";
            $params[] = $filtros['status'];
        }
        if (isset($filtros['processado']) && $filtros['processado'] !== '') {
            $where[]  = "processado = ?";
            $params[] = (int)$filtros['processado'];
        }
        if (!empty($filtros['data_ini'])) {
            $where[]  = "DATE(criado_em) >= ?";
            $params[] = $filtros['data_ini'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[]  = "DATE(criado_em) <= ?";
            $params[] = $filtros['data_fim'];
        }

        return $this->query(
            "SELECT id, source, order_nsu, pedido_id, transaction_nsu, status,
                    paid_amount, capture_method, installments, ip,
                    processado, erro, criado_em
             FROM webhook_logs
             WHERE " . implode(' AND ', $where) . "
             ORDER BY criado_em DESC
             LIMIT 500",
            $params
        );
    }

    public function findWebhookLogById(int $id): ?array
    {
        return $this->queryOne("SELECT * FROM webhook_logs WHERE id = ?", [$id]);
    }

    public function isDuplicate(string $transactionNsu): bool
    {
        if ($transactionNsu === '') return false;
        $row = $this->queryOne(
            "SELECT id FROM webhook_logs WHERE transaction_nsu = ? AND processado = 1",
            [$transactionNsu]
        );
        return $row !== null;
    }
}
