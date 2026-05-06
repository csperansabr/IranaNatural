<?php
namespace Admin\Controllers;

use App\Models\Insumo;
use App\Models\Produto;

class EstoqueController extends AdminController
{
    public function index(): void
    {
        $insumos  = (new Insumo())->findAll('ativo = 1', [], 'nome ASC');
        $produtos = (new Produto())->allAtivos();
        $flash    = $this->getFlash();
        $this->render('estoque/index', compact('insumos', 'produtos', 'flash'));
    }

    public function ajuste(?string $tipo = null): void
    {
        if ($tipo === 'produto') {
            $itens   = (new Produto())->allAtivos();
            $tipoAdj = 'produto';
        } else {
            $itens   = (new Insumo())->allAtivos();
            $tipoAdj = 'insumo';
        }
        $flash = $this->getFlash();
        $this->render('estoque/index', compact('flash'));
    }

    public function salvarAjuste(): void
    {
        $tipo    = trim($_POST['tipo'] ?? 'insumo');
        $itemId  = (int)($_POST['item_id'] ?? 0);
        $novoEst = (float)($_POST['novo_estoque'] ?? 0);
        $obs     = trim($_POST['observacoes'] ?? '');

        if (!$itemId) {
            $this->flash('error', 'Selecione o item.');
            $this->redirect('/admin/estoque');
            return;
        }

        if ($tipo === 'produto') {
            $p = (new Produto())->findById($itemId);
            if ($p) {
                $antes = (int)$p['estoque_atual'];
                (new Produto())->update($itemId, ['estoque_atual' => (int)$novoEst]);
                (new Produto())->exec(
                    "INSERT INTO mov_produtos (produto_id, tipo, quantidade, saldo_antes, saldo_apos, ref_tipo, observacoes)
                     VALUES (?, 'ajuste', ?, ?, ?, 'ajuste_manual', ?)",
                    [$itemId, abs((int)$novoEst - $antes), $antes, (int)$novoEst, $obs]
                );
                $this->flash('success', 'Estoque do produto ajustado.');
            }
        } else {
            (new Insumo())->ajustar($itemId, $novoEst, $obs);
            $this->flash('success', 'Estoque do insumo ajustado.');
        }

        $this->redirect('/admin/estoque');
    }
}
