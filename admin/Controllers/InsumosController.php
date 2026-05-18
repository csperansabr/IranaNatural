<?php
namespace Admin\Controllers;

use App\Models\Insumo;

class InsumosController extends AdminController
{
    private Insumo $model;

    public function __construct() { $this->model = new Insumo(); }

    public function index(): void
    {
        $insumos = $this->model->findAll('', [], 'nome ASC');
        $flash   = $this->getFlash();
        $this->render('insumos/index', compact('insumos', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar(); return; }
        $insumo = null;
        $flash  = $this->getFlash();
        $this->render('insumos/form', compact('insumo', 'flash'));
    }

    public function editar(int $id): void
    {
        $insumo = $this->model->findById($id);
        if (!$insumo) { $this->redirect('/admin/insumos'); return; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar($id); return; }
        $flash = $this->getFlash();
        $this->render('insumos/form', compact('insumo', 'flash'));
    }

    private function salvar(?int $id = null): void
    {
        $nome   = trim($_POST['nome']   ?? '');
        $unidade= trim($_POST['unidade_medida'] ?? 'un');
        $desc   = trim($_POST['descricao'] ?? '');
        $estMin = (float)($_POST['estoque_minimo'] ?? 0);
        $forn   = trim($_POST['fornecedor'] ?? '');
        $ativo  = isset($_POST['ativo']) ? 1 : 0;

        if (!$nome) {
            $this->flash('error', 'Nome é obrigatório.');
            $this->redirect($id ? "/admin/insumos/{$id}/editar" : '/admin/insumos/novo');
            return;
        }

        $data = ['nome' => $nome, 'unidade_medida' => $unidade, 'descricao' => $desc,
                 'estoque_minimo' => $estMin, 'fornecedor' => $forn, 'ativo' => $ativo];

        if ($id) {
            $this->model->update($id, $data);
            $this->flash('success', 'Insumo atualizado.');
            $this->redirect("/admin/insumos/{$id}/editar");
        } else {
            $newId = $this->model->insert($data);
            $this->flash('success', 'Insumo cadastrado.');
            $this->redirect("/admin/insumos/{$newId}/editar");
        }
    }

    public function ver(int $id): void
    {
        $insumo = $this->model->findById($id);
        if (!$insumo) { $this->redirect('/admin/insumos'); return; }
        $movimentacoes = $this->model->movimentacoes($id);
        $flash = $this->getFlash();
        $this->render('insumos/form', compact('insumo', 'movimentacoes', 'flash'));
    }

    public function excluir(int $id): void
    {
        $insumo = $this->model->findById($id);
        if (!$insumo) { $this->redirect('/admin/insumos'); return; }

        $v = $this->model->contarVinculos($id);
        $total = $v['compras'] + $v['fichas'] + $v['movs'];

        if ($total > 0) {
            $partes = [];
            if ($v['compras'] > 0) $partes[] = "{$v['compras']} compra(s)";
            if ($v['fichas']  > 0) $partes[] = "{$v['fichas']} ficha(s) técnica(s)";
            if ($v['movs']    > 0) $partes[] = "{$v['movs']} movimentação(ões) de estoque";
            $this->flash('error', 'Não é possível excluir: o insumo possui registros vinculados (' . implode(', ', $partes) . '). Para ocultá-lo, desmarque "Insumo ativo" na edição.');
            $this->redirect('/admin/insumos');
            return;
        }

        $this->model->delete($id);
        $this->flash('success', 'Insumo "' . $insumo['nome'] . '" excluído com sucesso.');
        $this->redirect('/admin/insumos');
    }
}
