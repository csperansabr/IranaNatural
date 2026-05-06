<?php
namespace Admin\Controllers;

use App\Models\CompraInsumo;
use App\Models\Insumo;

class ComprasController extends AdminController
{
    public function index(): void
    {
        $compras = (new CompraInsumo())->allComDetalhes();
        $flash   = $this->getFlash();
        $this->render('compras/index', compact('compras', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->criar();
            return;
        }
        $insumos = (new Insumo())->allAtivos();
        $flash   = $this->getFlash();
        $this->render('compras/form', compact('insumos', 'flash'));
    }

    private function criar(): void
    {
        $insumoId   = (int)($_POST['insumo_id']  ?? 0);
        $qtd        = (float)($_POST['quantidade'] ?? 0);
        $valorTotal = (float)($_POST['valor_total'] ?? 0);
        $data       = trim($_POST['data_compra'] ?? date('Y-m-d'));

        if (!$insumoId || $qtd <= 0 || $valorTotal <= 0) {
            $this->flash('error', 'Preencha todos os campos corretamente.');
            $this->redirect('/admin/compras/nova');
            return;
        }

        try {
            $id = (new CompraInsumo())->registrarCompra([
                'insumo_id'   => $insumoId,
                'quantidade'  => $qtd,
                'valor_total' => $valorTotal,
                'data_compra' => $data,
                'fornecedor'  => trim($_POST['fornecedor'] ?? ''),
                'observacoes' => trim($_POST['observacoes'] ?? ''),
            ]);
            $this->flash('success', 'Compra registrada. Custo médio e custos dos produtos atualizados automaticamente.');
            $this->redirect('/admin/compras');
        } catch (\Exception $e) {
            $this->flash('error', 'Erro ao registrar compra: ' . $e->getMessage());
            $this->redirect('/admin/compras/nova');
        }
    }
}
