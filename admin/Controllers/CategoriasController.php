<?php
namespace Admin\Controllers;

use App\Models\Categoria;
use App\Core\Helper;

class CategoriasController extends AdminController
{
    private Categoria $model;

    public function __construct() { $this->model = new Categoria(); }

    public function index(): void
    {
        $categorias = $this->model->findAll('', [], 'nome ASC');
        $flash = $this->getFlash();
        $this->render('categorias/index', compact('categorias', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->salvar();
            return;
        }
        $categoria = null;
        $flash = $this->getFlash();
        $this->render('categorias/form', compact('categoria', 'flash'));
    }

    public function editar(int $id): void
    {
        $categoria = $this->model->findById($id);
        if (!$categoria) { $this->redirect('/admin/categorias'); return; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->salvar($id);
            return;
        }
        $flash = $this->getFlash();
        $this->render('categorias/form', compact('categoria', 'flash'));
    }

    private function salvar(?int $id = null): void
    {
        $nome  = trim($_POST['nome'] ?? '');
        $desc  = trim($_POST['descricao'] ?? '');
        $ordem = (int)($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (!$nome) {
            $this->flash('error', 'Nome é obrigatório.');
            $this->redirect($id ? "/admin/categorias/{$id}/editar" : '/admin/categorias/nova');
            return;
        }

        $slug = Helper::slug($nome);
        $data = ['nome' => $nome, 'slug' => $slug, 'descricao' => $desc, 'ordem' => $ordem, 'ativo' => $ativo];

        // Imagem
        if (!empty($_FILES['imagem']['name'])) {
            $caminho = Helper::uploadFile($_FILES['imagem'], 'categorias');
            if ($caminho) $data['imagem'] = $caminho;
        }

        if ($id) {
            $this->model->update($id, $data);
            $this->flash('success', 'Categoria atualizada com sucesso.');
            $this->redirect("/admin/categorias/{$id}/editar");
        } else {
            $newId = $this->model->insert($data);
            $this->flash('success', 'Categoria criada com sucesso.');
            $this->redirect("/admin/categorias/{$newId}/editar");
        }
    }

    public function excluir(int $id): void
    {
        $this->model->delete($id);
        $this->flash('success', 'Categoria excluída.');
        $this->redirect('/admin/categorias');
    }
}
