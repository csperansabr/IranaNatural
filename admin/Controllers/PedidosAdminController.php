<?php
namespace Admin\Controllers;

use App\Models\Pedido;
use App\Models\Pagamento;
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
}
