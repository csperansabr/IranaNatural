<?php
namespace Admin\Controllers;

use App\Models\Pedido;
use App\Models\Pagamento;

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
        $flash     = $this->getFlash();
        $pageTitle = 'Pedido #' . $pedido['numero'];

        $this->render('pedidos/ver', compact('pedido', 'itens', 'pagamento', 'flash', 'pageTitle'));
    }

    public function atualizarStatus(): void
    {
        $id     = (int)($_POST['pedido_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $obs    = trim($_POST['obs'] ?? '');

        $statusValidos = ['pendente','pago','separando','enviado','entregue','cancelado'];
        if (!$id || !in_array($status, $statusValidos, true)) {
            $this->json(['ok' => false, 'msg' => 'Dados inválidos.'], 400);
            return;
        }

        $pedido = $this->pedidoModel->findById($id);
        if (!$pedido) {
            $this->json(['ok' => false, 'msg' => 'Pedido não encontrado.'], 404);
            return;
        }

        $user = $this->currentUser();
        $obsComUsuario = $obs ? $obs . ' (por ' . $user['nome'] . ')' : 'Atualizado por ' . $user['nome'];
        $this->pedidoModel->atualizarStatus($id, $status, $obsComUsuario);

        $this->json(['ok' => true, 'msg' => 'Status atualizado com sucesso!', 'label' => \App\Models\Pedido::statusLabel($status)]);
    }
}
