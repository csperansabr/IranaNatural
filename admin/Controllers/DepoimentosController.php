<?php
namespace Admin\Controllers;

use App\Models\Depoimento;
use App\Core\Helper;

class DepoimentosController extends AdminController
{
    private Depoimento $model;

    public function __construct() { $this->model = new Depoimento(); }

    public function index(): void
    {
        $depoimentos = $this->model->findAll('', [], 'ordem ASC');
        $flash       = $this->getFlash();
        $this->render('depoimentos/index', compact('depoimentos', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar(); return; }
        $dep   = null;
        $flash = $this->getFlash();
        $this->render('depoimentos/form', compact('dep', 'flash'));
    }

    public function editar(int $id): void
    {
        $dep = $this->model->findById($id);
        if (!$dep) { $this->redirect('/admin/depoimentos'); return; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar($id); return; }
        $flash = $this->getFlash();
        $this->render('depoimentos/form', compact('dep', 'flash'));
    }

    private function salvar(?int $id = null): void
    {
        $nome      = trim($_POST['nome'] ?? '');
        $texto     = trim($_POST['texto'] ?? '');
        $avaliacao = min(5, max(1, (int)($_POST['avaliacao'] ?? 5)));
        $ordem     = (int)($_POST['ordem'] ?? 0);
        $ativo     = isset($_POST['ativo']) ? 1 : 0;

        if (!$nome || !$texto) {
            $this->flash('error', 'Nome e texto são obrigatórios.');
            $this->redirect($id ? "/admin/depoimentos/{$id}/editar" : '/admin/depoimentos/novo');
            return;
        }

        $data = ['nome' => $nome, 'texto' => $texto, 'avaliacao' => $avaliacao, 'ordem' => $ordem, 'ativo' => $ativo];

        if (!empty($_FILES['foto']['name'])) {
            $caminho = Helper::uploadFile($_FILES['foto'], 'depoimentos');
            if ($caminho) $data['foto'] = $caminho;
        }

        if ($id) {
            $this->model->update($id, $data);
            $this->flash('success', 'Depoimento atualizado.');
            $this->redirect("/admin/depoimentos/{$id}/editar");
        } else {
            $newId = $this->model->insert($data);
            $this->flash('success', 'Depoimento criado.');
            $this->redirect("/admin/depoimentos/{$newId}/editar");
        }
    }

    public function excluir(int $id): void
    {
        $this->model->delete($id);
        $this->flash('success', 'Depoimento excluído.');
        $this->redirect('/admin/depoimentos');
    }
}
