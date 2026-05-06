<?php
namespace Admin\Controllers;

use App\Models\Producao;
use App\Models\Produto;
use App\Models\FichaTecnica;

class ProducaoController extends AdminController
{
    public function index(): void
    {
        $producoes = (new Producao())->allComDetalhes();
        $flash     = $this->getFlash();
        $this->render('producao/index', compact('producoes', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->criar(); return; }
        $produtos = (new Produto())->allAtivos();
        $flash    = $this->getFlash();
        $this->render('producao/form', compact('produtos', 'flash'));
    }

    private function criar(): void
    {
        $produtoId  = (int)($_POST['produto_id'] ?? 0);
        $qtd        = (int)($_POST['quantidade_produzida'] ?? 0);
        $qtdPerda   = (float)($_POST['quantidade_perda'] ?? 0);
        $dataProd   = trim($_POST['data_producao'] ?? date('Y-m-d'));

        if (!$produtoId || $qtd <= 0) {
            $this->flash('error', 'Selecione o produto e informe a quantidade.');
            $this->redirect('/admin/producao/nova');
            return;
        }

        try {
            (new Producao())->registrar([
                'produto_id'           => $produtoId,
                'quantidade_produzida' => $qtd,
                'quantidade_perda'     => $qtdPerda,
                'motivo_perda'         => trim($_POST['motivo_perda'] ?? ''),
                'data_producao'        => $dataProd,
                'responsavel'          => trim($_POST['responsavel'] ?? ''),
                'observacoes'          => trim($_POST['observacoes'] ?? ''),
            ]);
            $this->flash('success', "Produção registrada. Insumos debitados e estoque atualizado.");
            $this->redirect('/admin/producao');
        } catch (\Exception $e) {
            $this->flash('error', 'Erro: ' . $e->getMessage());
            $this->redirect('/admin/producao/nova');
        }
    }

    public function verificarInsumos(int $produtoId, int $qtd): void
    {
        if (!$produtoId || !$qtd) { $this->json(['erro' => 'Parâmetros inválidos'], 400); return; }
        $resultado = (new FichaTecnica())->verificarDisponibilidade($produtoId, $qtd);
        $this->json($resultado);
    }

    public function ver(int $id): void
    {
        $producao = (new Producao())->findById($id);
        if (!$producao) { $this->redirect('/admin/producao'); return; }
        $flash = $this->getFlash();
        $this->render('producao/index', compact('flash'));
    }
}
