<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Pedido;
use App\Models\Pagamento;
use App\Models\Venda;

class WebhookController extends Controller
{
    /**
     * POST /webhook/infinitepay/{secret}
     *
     * Receives payment status notifications from InfinitePay.
     * On approval, reuses Venda::registrar() — the same flow used by the admin
     * when registering a manual sale — ensuring stock debit, mov_produtos, and
     * vendas record are all created consistently and atomically.
     */
    public function infinitepay(string $secret = ''): void
    {
        // Validate webhook secret
        $configSecret = defined('INFINITEPAY_WEBHOOK_SECRET') ? INFINITEPAY_WEBHOOK_SECRET : '';
        if ($configSecret !== '' && $secret !== $configSecret) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
            exit;
        }

        // Read raw body
        $rawBody = (string)file_get_contents('php://input');
        $payload = json_decode($rawBody, true);
        $ip      = $_SERVER['REMOTE_ADDR'] ?? '';

        $pagamentoModel = new Pagamento();
        $pedidoModel    = new Pedido();

        $logData = [
            'source'          => 'infinitepay',
            'order_nsu'       => $payload['order_nsu']       ?? null,
            'transaction_nsu' => $payload['transaction_nsu'] ?? null,
            'status'          => $payload['status']          ?? null,
            'payload'         => $payload,
            'raw_body'        => $rawBody,
            'paid_amount'     => isset($payload['paid_amount'])  ? round((float)$payload['paid_amount'] / 100, 2) : null,
            'receipt_url'     => $payload['receipt_url']     ?? null,
            'capture_method'  => $payload['capture_method']  ?? null,
            'installments'    => isset($payload['installments']) ? (int)$payload['installments'] : null,
            'ip'              => $ip,
            'processado'      => 0,
            'erro'            => null,
        ];

        header('Content-Type: application/json');

        if (!$payload || empty($payload['order_nsu'])) {
            $logData['erro'] = 'Payload inválido ou order_nsu ausente';
            $pagamentoModel->registrarWebhookLog($logData);
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Invalid payload']);
            exit;
        }

        $orderNsu       = (string)$payload['order_nsu'];
        $transactionNsu = (string)($payload['transaction_nsu'] ?? '');
        $ipStatus       = (string)($payload['status']          ?? '');

        // InfinitePay approved-payment webhooks omit "status" but carry
        // paid_amount > 0 and a capture_method — treat that as "approved".
        if ($ipStatus === '' && isset($payload['paid_amount']) && (float)$payload['paid_amount'] > 0) {
            $ipStatus = 'approved';
        }

        $logData['status'] = $ipStatus ?: null;

        // Primary idempotency: skip if this transaction_nsu was already processed
        if ($transactionNsu && $pagamentoModel->isDuplicate($transactionNsu)) {
            $logData['processado'] = 1;
            $logData['erro']       = 'Duplicate transaction_nsu — ignorado';
            $pagamentoModel->registrarWebhookLog($logData);
            echo json_encode(['ok' => true, 'msg' => 'Already processed']);
            exit;
        }

        // Find the internal order
        $pedido = $pedidoModel->findByNumero($orderNsu);
        if (!$pedido) {
            $logData['erro'] = 'Pedido não encontrado: ' . $orderNsu;
            $pagamentoModel->registrarWebhookLog($logData);
            http_response_code(404);
            echo json_encode(['ok' => false, 'msg' => 'Order not found']);
            exit;
        }

        $logData['pedido_id'] = $pedido['id'];
        $pagamento            = $pagamentoModel->findByOrderNsu($orderNsu);

        try {
            $novoStatus = match($ipStatus) {
                'approved'              => 'pago',
                'canceled', 'refunded'  => 'cancelado',
                'failed', 'declined'    => 'pagamento_recusado',
                'expired'               => 'pagamento_expirado',
                default                 => null,
            };

            if ($novoStatus !== null) {
                // Secondary idempotency: if order already in a paid/terminal state,
                // skip to avoid double stock debit, double venda record, etc.
                $terminalPagoStatuses = ['pago', 'separando', 'enviado', 'entregue'];
                if ($novoStatus === 'pago' && in_array($pedido['status'], $terminalPagoStatuses, true)) {
                    $logData['processado'] = 1;
                    $logData['erro']       = 'Pedido já está pago — webhook duplicado ignorado';
                    $pagamentoModel->registrarWebhookLog($logData);
                    echo json_encode(['ok' => true, 'msg' => 'Already processed']);
                    exit;
                }

                $valorPagoRaw  = isset($payload['paid_amount']) ? round((float)$payload['paid_amount'] / 100, 2) : null;
                $receiptUrl    = (string)($payload['receipt_url']    ?? '');
                $invoiceSlug   = (string)($payload['invoice_slug']   ?? '');
                $captureMethod = (string)($payload['capture_method'] ?? '');
                $parcelas      = (int)($payload['installments']      ?? 1);

                $formaParaPedido = $captureMethod ? $this->mapCaptureMethodPedido($captureMethod) : 'pendente';
                $formaParaVenda  = $captureMethod ? $this->mapCaptureMethodVenda($captureMethod)  : 'outro';

                $obs = match($novoStatus) {
                    'pago'               => 'Pagamento confirmado via InfinitePay. Método: ' . ($captureMethod ?: 'desconhecido'),
                    'cancelado'          => 'Pedido cancelado. Retorno InfinitePay: ' . $ipStatus,
                    'pagamento_recusado' => 'Pagamento recusado. Retorno InfinitePay: ' . $ipStatus,
                    'pagamento_expirado' => 'Pagamento expirado (PIX ou link vencido).',
                    default              => 'Status: ' . $ipStatus,
                };

                // ── Atomic transaction: status + payment + sale + stock + mov ──
                $pedidoModel->beginTransaction();
                try {
                    // 1. Update pedido status + history
                    $pedidoModel->atualizarStatus($pedido['id'], $novoStatus, $obs);

                    // 2. Update extra columns on pedidos
                    $pedidoUpdate = [];
                    if ($transactionNsu) $pedidoUpdate['transaction_id']  = $transactionNsu;
                    if ($invoiceSlug)    $pedidoUpdate['invoice_slug']    = $invoiceSlug;
                    if ($parcelas > 1)   $pedidoUpdate['parcelas']        = $parcelas;
                    if ($captureMethod)  $pedidoUpdate['forma_pagamento'] = $formaParaPedido;
                    if ($pedidoUpdate)   $pedidoModel->update($pedido['id'], $pedidoUpdate);

                    // 3. Update pagamentos record with full payment data + receipt_url
                    if ($pagamento) {
                        $pagamentoModel->atualizarStatus($pagamento['id'], $ipStatus, [
                            'transaction_nsu' => $transactionNsu,
                            'invoice_slug'    => $invoiceSlug,
                            'receipt_url'     => $receiptUrl ?: null,
                            'metodo'          => $formaParaPedido,
                            'valor_pago'      => $valorPagoRaw,
                            'payload_webhook' => $payload,
                        ]);
                    }

                    // 4. On approval: register the sale using the same flow as admin
                    //    manual sale — creates vendas + vendas_itens + mov_produtos
                    //    and validates/debits stock. $comTransacao=false because the
                    //    caller (this method) already owns the transaction.
                    if ($novoStatus === 'pago') {
                        $this->efetivarVenda($pedido, $pedidoModel, $formaParaVenda, $valorPagoRaw);
                    }

                    $pedidoModel->commit();

                } catch (\Throwable $txErr) {
                    $pedidoModel->rollback();
                    throw $txErr;
                }
                // ─────────────────────────────────────────────────────────────
            }

            // Log is always written AFTER the transaction (never inside it)
            $logData['processado'] = 1;
            $pagamentoModel->registrarWebhookLog($logData);
            echo json_encode(['ok' => true, 'msg' => 'Webhook processed']);

        } catch (\Throwable $e) {
            $logData['erro'] = get_class($e) . ': ' . $e->getMessage();
            $pagamentoModel->registrarWebhookLog($logData);
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Processing error: ' . $e->getMessage()]);
        }

        exit;
    }

    /**
     * Efetiva a venda do pedido online reutilizando Venda::registrar() —
     * o mesmo fluxo do admin manual: vendas + vendas_itens + mov_produtos + stock debit.
     * $comTransacao=false porque já estamos dentro de uma transação aberta.
     */
    private function efetivarVenda(array $pedido, Pedido $pedidoModel, string $formaVenda, ?float $valorPago): void
    {
        $itens = $pedidoModel->getItens($pedido['id']);

        $itensVenda = [];
        foreach ($itens as $item) {
            $itensVenda[] = [
                'produto_id'     => (int)$item['produto_id'],
                'quantidade'     => (int)$item['quantidade'],
                'preco_unitario' => (float)$item['preco_unitario'],
            ];
        }

        $desconto = (float)($pedido['desconto'] ?? 0);

        $cabecalhoVenda = [
            'pedido_id'       => (int)$pedido['id'],
            'cliente_id'      => (int)$pedido['cliente_id'],
            'data_venda'      => date('Y-m-d'),
            'forma_pagamento' => $formaVenda,
            'desconto'        => $desconto,
            'observacoes'     => 'Pedido online ' . $pedido['numero'] . ' — InfinitePay'
                                 . ($valorPago !== null ? ' — R$ ' . number_format($valorPago, 2, ',', '.') : ''),
        ];

        // Reutiliza o fluxo de venda manual sem gerenciar transação própria
        (new Venda())->registrar($cabecalhoVenda, $itensVenda, false);
    }

    /**
     * Maps InfinitePay capture_method to pedidos.forma_pagamento values.
     */
    private function mapCaptureMethodPedido(string $method): string
    {
        return match(strtolower($method)) {
            'credit_card' => 'cartao_credito',
            'debit_card'  => 'cartao_debito',
            'pix'         => 'pix',
            default       => $method,
        };
    }

    /**
     * Maps InfinitePay capture_method to vendas.forma_pagamento values.
     * Keeps compatibility with existing vendas records (pix, credito, debito, outro).
     */
    private function mapCaptureMethodVenda(string $method): string
    {
        return match(strtolower($method)) {
            'credit_card' => 'credito',
            'debit_card'  => 'debito',
            'pix'         => 'pix',
            default       => 'outro',
        };
    }
}
