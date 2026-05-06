<?php
namespace Admin\Controllers;

use App\Models\Venda;
use App\Models\Produto;

class VendasController extends AdminController
{
    public function index(): void
    {
        $vendas = (new Venda())->allComDetalhes();
        $flash  = $this->getFlash();
        $this->render('vendas/index', compact('vendas', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->criar(); return; }
        $produtos = (new Produto())->allAtivos();
        $flash    = $this->getFlash();
        $this->render('vendas/form', compact('produtos', 'flash'));
    }

    private function criar(): void
    {
        // Itens: arrays produto_id[], quantidade[], preco_unitario[]
        $pids    = $_POST['produto_id']     ?? [];
        $qtds    = $_POST['quantidade']     ?? [];
        $precos  = $_POST['preco_unitario'] ?? [];
        $data    = trim($_POST['data_venda'] ?? date('Y-m-d'));
        $pgto    = trim($_POST['forma_pagamento'] ?? 'pix');
        $desc    = (float)($_POST['desconto'] ?? 0);
        $obs     = trim($_POST['observacoes'] ?? '');

        // Monta itens
        $itens = [];
        foreach ($pids as $i => $pid) {
            if (!$pid || empty($qtds[$i]) || (int)$qtds[$i] <= 0) continue;
            $itens[] = [
                'produto_id'     => (int)$pid,
                'quantidade'     => (int)$qtds[$i],
                'preco_unitario' => (float)($precos[$i] ?? 0),
            ];
        }

        if (empty($itens)) {
            $this->flash('error', 'Adicione ao menos um produto à venda.');
            $this->redirect('/admin/vendas/nova');
            return;
        }

        try {
            $id = (new Venda())->registrar(
                ['data_venda' => $data, 'forma_pagamento' => $pgto, 'desconto' => $desc, 'observacoes' => $obs],
                $itens
            );
            $this->flash('success', 'Venda registrada com sucesso!');
            $this->redirect('/admin/vendas');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/admin/vendas/nova');
        }
    }

    public function ver(int $id): void
    {
        $venda = (new Venda())->findById($id);
        if (!$venda) { $this->redirect('/admin/vendas'); return; }
        $itens = (new Venda())->getItens($id);
        $flash = $this->getFlash();
        $this->render('vendas/index', compact('flash'));
    }
}
