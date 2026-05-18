<?php
namespace Admin\Controllers;

use App\Models\Pedido;
use App\Models\Pagamento;
use App\Models\Venda;
use App\Core\InfinitePayProvider;
use App\Core\Mailer;
use App\Core\Session;

class PedidosAdminController extends AdminController
{
    private Pedido $pedidoModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
    }

    public function index(): void
    {
        $filtros = [
            'numero'    => trim($_GET['numero']    ?? ''),
            'cliente'   => trim($_GET['cliente']   ?? ''),
            'status'    => trim($_GET['status']    ?? ''),
            'data_ini'  => trim($_GET['data_ini']  ?? ''),
            'data_fim'  => trim($_GET['data_fim']  ?? ''),
        ];

        $pedidos   = $this->pedidoModel->allComDetalhes($filtros);
        $flash     = $this->getFlash();
        $pageTitle = 'Pedidos Online';

        $this->render('pedidos/index', compact('pedidos', 'flash', 'filtros', 'pageTitle'));
    }

    public function ver(int $id): void
    {
        $pedido = $this->pedidoModel->findComCliente($id);
        if (!$pedido) {
            $this->flash('error', 'Pedido não encontrado.');
            $this->redirect('/admin/pedidos');
            return;
        }

        $itens     = $this->pedidoModel->getItens($id);
        $pagamento = (new Pagamento())->findByPedido($id);
        $historico = $this->pedidoModel->getHistorico($id);
        $flash     = $this->getFlash();
        $pageTitle = 'Pedido #' . $pedido['numero'];

        $this->render('pedidos/ver', compact('pedido', 'itens', 'pagamento', 'historico', 'flash', 'pageTitle'));
    }

    public function atualizarStatus(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            $this->json(['ok' => false, 'msg' => 'Requisição inválida. Recarregue a página.'], 403);
            return;
        }

        $id        = (int)($_POST['pedido_id'] ?? 0);
        $status    = trim($_POST['status'] ?? '');
        $obs       = trim($_POST['obs'] ?? '');
        $notificar = ($_POST['notificar_cliente'] ?? '') === '1';

        $statusValidos = ['pendente','pago','separando','enviado','entregue','cancelado'];
        if (!$id || !in_array($status, $statusValidos, true)) {
            $this->json(['ok' => false, 'msg' => 'Dados inválidos.'], 400);
            return;
        }

        $pedido = $this->pedidoModel->findComCliente($id);
        if (!$pedido) {
            $this->json(['ok' => false, 'msg' => 'Pedido não encontrado.'], 404);
            return;
        }

        $user = $this->currentUser();
        $this->pedidoModel->atualizarStatus(
            $id,
            $status,
            $obs,
            'admin',
            (int)($user['id'] ?? 0) ?: null,
            (string)($user['nome'] ?? '')
        );

        $emailEnviado = false;
        if ($notificar && !empty($pedido['cliente_email'])) {
            try {
                $cliente = [
                    'nome'  => $pedido['cliente_nome']  ?? '',
                    'email' => $pedido['cliente_email'] ?? '',
                ];
                Mailer::statusAtualizado($pedido, $status, $obs, $cliente);
                $emailEnviado = true;
            } catch (\Throwable $e) {
                error_log('[Admin/Pedidos] E-mail statusAtualizado falhou: ' . $e->getMessage());
            }
        }

        $msg = 'Status atualizado com sucesso!';
        if ($notificar) {
            $msg .= $emailEnviado
                ? ' E-mail enviado ao cliente.'
                : ' (Falha no envio do e-mail — verifique as configurações.)';
        }

        $this->json([
            'ok'    => true,
            'msg'   => $msg,
            'label' => Pedido::statusLabel($status),
            'class' => Pedido::statusClass($status),
        ]);
    }

    /**
     * POST /admin/pedidos/{id}/consultar-pagamento
     *
     * Manual InfinitePay payment status check for cases where the webhook
     * failed or was delayed. Reuses the same sale/stock flow as the webhook.
     */
    public function consultarPagamento(int $id): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            $this->json(['ok' => false, 'msg' => 'Requisição inválida. Recarregue a página.'], 403);
            return;
        }

        $pedido = $this->pedidoModel->findComCliente($id);
        if (!$pedido) {
            $this->json(['ok' => false, 'msg' => 'Pedido não encontrado.'], 404);
            return;
        }

        $pagamentoModel = new Pagamento();
        $pagamento      = $pagamentoModel->findByPedido($id);

        if (!$pagamento) {
            $this->json(['ok' => false, 'msg' => 'Nenhum registro de pagamento encontrado para este pedido.'], 422);
            return;
        }

        $handle         = $pagamento['handle'] ?? INFINITEPAY_HANDLE;
        $orderNsu       = (string)($pagamento['order_nsu']       ?? '');
        $transactionNsu = (string)($pagamento['transaction_nsu'] ?? '');
        $slug           = (string)($pagamento['invoice_slug']     ?? '');

        $user    = $this->currentUser();
        $adminId = (int)($user['id']   ?? 0) ?: null;
        $adminNome = (string)($user['nome'] ?? 'Admin');
        $ip      = $_SERVER['REMOTE_ADDR'] ?? null;

        $logData = [
            'source'          => 'admin_manual_check',
            'order_nsu'       => $orderNsu,
            'pedido_id'       => $pedido['id'],
            'transaction_nsu' => $transactionNsu ?: null,
            'status'          => null,
            'payload'         => null,
            'raw_body'        => null,
            'paid_amount'     => null,
            'receipt_url'     => null,
            'capture_method'  => null,
            'installments'    => null,
            'ip'              => $ip,
            'processado'      => 0,
            'erro'            => null,
        ];

        try {
            $apiResponse = (new InfinitePayProvider())->consultarStatus($handle, $orderNsu, $transactionNsu, $slug);

            $ipStatus = (string)($apiResponse['status'] ?? '');

            // Same approved-detection as the webhook (some responses omit status but carry paid_amount)
            if ($ipStatus === '' && isset($apiResponse['paid_amount']) && (float)$apiResponse['paid_amount'] > 0) {
                $ipStatus = 'approved';
            }

            $logData['status']   = $ipStatus ?: null;
            $logData['payload']  = $apiResponse;

            $respTransactionNsu = (string)($apiResponse['transaction_nsu'] ?? $transactionNsu);
            $receiptUrl         = (string)($apiResponse['receipt_url']     ?? '');
            $invoiceSlug        = (string)($apiResponse['invoice_slug']    ?? $slug);
            $captureMethod      = (string)($apiResponse['capture_method']  ?? '');
            $parcelas           = (int)  ($apiResponse['installments']     ?? 1);
            $valorPagoRaw       = isset($apiResponse['paid_amount'])
                ? round((float)$apiResponse['paid_amount'] / 100, 2)
                : null;

            $logData['transaction_nsu'] = $respTransactionNsu ?: null;
            $logData['paid_amount']     = $valorPagoRaw;
            $logData['receipt_url']     = $receiptUrl ?: null;
            $logData['capture_method']  = $captureMethod ?: null;
            $logData['installments']    = $parcelas > 1 ? $parcelas : null;

            $novoStatus = match($ipStatus) {
                'approved'              => 'pago',
                'canceled', 'refunded'  => 'cancelado',
                'failed', 'declined'    => 'pagamento_recusado',
                'expired'               => 'pagamento_expirado',
                default                 => null,
            };

            // Status not actionable — return current info to admin
            if ($novoStatus === null) {
                $logData['processado'] = 1;
                $logData['erro']       = 'Status não mapeado: ' . ($ipStatus ?: '(vazio)');
                $pagamentoModel->registrarWebhookLog($logData);

                $statusDisplay = $ipStatus ?: 'desconhecido';
                $this->json(['ok' => true, 'msg' => "InfinitePay retornou status: {$statusDisplay}. Nenhuma ação foi necessária."]);
                return;
            }

            // Idempotency: primary — check if transaction_nsu was already processed
            if ($respTransactionNsu && $pagamentoModel->isDuplicate($respTransactionNsu)) {
                $logData['processado'] = 1;
                $logData['erro']       = 'transaction_nsu já processado anteriormente';
                $pagamentoModel->registrarWebhookLog($logData);
                $this->json(['ok' => true, 'msg' => 'Pagamento já estava registrado. Nenhuma duplicidade criada.']);
                return;
            }

            // Idempotency: secondary — order already in a terminal paid state
            $terminalPagoStatuses = ['pago', 'separando', 'enviado', 'entregue'];
            if ($novoStatus === 'pago' && in_array($pedido['status'], $terminalPagoStatuses, true)) {
                $logData['processado'] = 1;
                $logData['erro']       = 'Pedido já está pago — consulta ignorada';
                $pagamentoModel->registrarWebhookLog($logData);
                $this->json(['ok' => true, 'msg' => 'O pedido já estava com pagamento confirmado. Nenhuma duplicidade criada.']);
                return;
            }

            $formaParaPedido = match(strtolower($captureMethod)) {
                'credit_card' => 'cartao_credito',
                'debit_card'  => 'cartao_debito',
                'pix'         => 'pix',
                default       => $captureMethod ?: 'pendente',
            };
            $formaParaVenda = match(strtolower($captureMethod)) {
                'credit_card' => 'credito',
                'debit_card'  => 'debito',
                'pix'         => 'pix',
                default       => 'outro',
            };

            $obs = match($novoStatus) {
                'pago'               => 'Pagamento confirmado via consulta manual InfinitePay. Método: ' . ($captureMethod ?: 'desconhecido') . ". Admin: {$adminNome}.",
                'cancelado'          => "Pedido cancelado. Retorno InfinitePay: {$ipStatus}. Admin: {$adminNome}.",
                'pagamento_recusado' => "Pagamento recusado. Retorno InfinitePay: {$ipStatus}. Admin: {$adminNome}.",
                'pagamento_expirado' => "Pagamento expirado (PIX ou link vencido). Admin: {$adminNome}.",
                default              => "Status: {$ipStatus}. Admin: {$adminNome}.",
            };

            // ── Atomic transaction: status + payment + sale + stock + mov ──
            $this->pedidoModel->beginTransaction();
            try {
                $this->pedidoModel->atualizarStatus($pedido['id'], $novoStatus, $obs, 'admin', $adminId, $adminNome);

                $pedidoUpdate = [];
                if ($respTransactionNsu) $pedidoUpdate['transaction_id']  = $respTransactionNsu;
                if ($invoiceSlug)        $pedidoUpdate['invoice_slug']    = $invoiceSlug;
                if ($parcelas > 1)       $pedidoUpdate['parcelas']        = $parcelas;
                if ($captureMethod)      $pedidoUpdate['forma_pagamento'] = $formaParaPedido;
                if ($pedidoUpdate)       $this->pedidoModel->update($pedido['id'], $pedidoUpdate);

                $pagamentoModel->atualizarStatus($pagamento['id'], $ipStatus, [
                    'transaction_nsu' => $respTransactionNsu ?: null,
                    'invoice_slug'    => $invoiceSlug ?: null,
                    'receipt_url'     => $receiptUrl  ?: null,
                    'metodo'          => $formaParaPedido,
                    'valor_pago'      => $valorPagoRaw,
                    'payload_webhook' => $apiResponse,
                ]);

                if ($novoStatus === 'pago') {
                    // Efetiva a venda (vendas + vendas_itens + mov_produtos + stock debit)
                    $itens = $this->pedidoModel->getItens($pedido['id']);
                    $itensVenda = [];
                    foreach ($itens as $item) {
                        $itensVenda[] = [
                            'produto_id'     => (int)$item['produto_id'],
                            'quantidade'     => (int)$item['quantidade'],
                            'preco_unitario' => (float)$item['preco_unitario'],
                        ];
                    }
                    (new Venda())->registrar([
                        'pedido_id'       => (int)$pedido['id'],
                        'cliente_id'      => (int)$pedido['cliente_id'],
                        'data_venda'      => date('Y-m-d'),
                        'forma_pagamento' => $formaParaVenda,
                        'desconto'        => (float)($pedido['desconto'] ?? 0),
                        'observacoes'     => 'Pedido online ' . $pedido['numero'] . ' — InfinitePay (consulta manual admin)'
                                            . ($valorPagoRaw !== null ? ' — R$ ' . number_format($valorPagoRaw, 2, ',', '.') : ''),
                    ], $itensVenda, false);

                    // Auto-advance to separando in the same transaction
                    $this->pedidoModel->atualizarStatus(
                        $pedido['id'],
                        'separando',
                        "Pagamento confirmado via consulta manual InfinitePay. Pedido encaminhado para separação. Admin: {$adminNome}.",
                        'admin',
                        $adminId,
                        $adminNome
                    );
                }

                $this->pedidoModel->commit();

            } catch (\Throwable $txErr) {
                $this->pedidoModel->rollback();
                throw $txErr;
            }
            // ─────────────────────────────────────────────────────────────

            $logData['processado'] = 1;
            $pagamentoModel->registrarWebhookLog($logData);

            error_log("[Admin/Pedidos] consultarPagamento: pedido #{$pedido['numero']} → status={$novoStatus}. Admin={$adminNome} IP={$ip}");

            // Post-commit emails (idempotency-guarded)
            if ($novoStatus === 'pago') {
                $clienteEmail = [
                    'nome'     => (string)($pedido['cliente_nome']     ?? ''),
                    'email'    => (string)($pedido['cliente_email']    ?? ''),
                    'cpf'      => (string)($pedido['cliente_cpf']      ?? ''),
                    'telefone' => (string)($pedido['cliente_telefone'] ?? ''),
                ];
                $pedidoParaEmail = array_merge($pedido, [
                    'status'          => 'pago',
                    'forma_pagamento' => $formaParaPedido,
                ]);

                if (!$this->pedidoModel->isEmailEnviado($pedido['id'], 'pago')) {
                    try {
                        $itensEmail = $this->pedidoModel->getItens($pedido['id']);
                        Mailer::pagamentoConfirmado($pedidoParaEmail, $itensEmail, $clienteEmail);
                        $this->pedidoModel->marcarEmailEnviado($pedido['id'], 'pago');
                    } catch (\Throwable $e) {
                        error_log('[Admin/Pedidos] E-mail pagamentoConfirmado falhou: ' . $e->getMessage());
                    }
                }

                if (!$this->pedidoModel->isEmailEnviado($pedido['id'], 'separando')) {
                    try {
                        Mailer::statusAtualizado($pedidoParaEmail, 'separando', '', $clienteEmail);
                        $this->pedidoModel->marcarEmailEnviado($pedido['id'], 'separando');
                    } catch (\Throwable $e) {
                        error_log('[Admin/Pedidos] E-mail separando falhou: ' . $e->getMessage());
                    }
                }
            }

            $statusLabel = Pedido::statusLabel($novoStatus);
            $this->json([
                'ok'    => true,
                'msg'   => "Pagamento confirmado! Pedido atualizado para: {$statusLabel}. Página será recarregada.",
                'reload' => true,
            ]);

        } catch (\Throwable $e) {
            $logData['erro'] = get_class($e) . ': ' . $e->getMessage();
            (new Pagamento())->registrarWebhookLog($logData);

            error_log('[Admin/Pedidos] consultarPagamento erro: ' . $e->getMessage());
            $this->json(['ok' => false, 'msg' => 'Erro ao consultar InfinitePay: ' . $e->getMessage()], 500);
        }
    }
}
