<?php
namespace Admin\Controllers;

use App\Models\Banner;
use App\Core\Helper;

class BannersController extends AdminController
{
    private Banner $model;

    public function __construct() { $this->model = new Banner(); }

    public function index(): void
    {
        $banners = $this->model->findAll('', [], 'ordem ASC');
        $flash   = $this->getFlash();
        $this->render('banners/index', compact('banners', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar(); return; }
        $banner = null;
        $flash  = $this->getFlash();
        $this->render('banners/form', compact('banner', 'flash'));
    }

    public function editar(int $id): void
    {
        $banner = $this->model->findById($id);
        if (!$banner) { $this->redirect('/admin/banners'); return; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvar($id); return; }
        $flash = $this->getFlash();
        $this->render('banners/form', compact('banner', 'flash'));
    }

    private function salvar(?int $id = null): void
    {
        $titulo    = trim($_POST['titulo'] ?? '');
        $subtitulo = trim($_POST['subtitulo'] ?? '');
        $link      = trim($_POST['link'] ?? '');
        $ordem     = (int)($_POST['ordem'] ?? 0);
        $ativo     = isset($_POST['ativo']) ? 1 : 0;

        $data = ['titulo' => $titulo, 'subtitulo' => $subtitulo, 'link' => $link, 'ordem' => $ordem, 'ativo' => $ativo];

        if (!empty($_FILES['imagem']['name'])) {
            $caminho = Helper::uploadFile($_FILES['imagem'], 'banners');
            if ($caminho) {
                $data['imagem'] = $caminho;
            } elseif (!$id) {
                $this->flash('error', 'Imagem inválida ou muito grande (máx 5MB).');
                $this->redirect('/admin/banners/novo');
                return;
            }
        } elseif (!$id) {
            $this->flash('error', 'Imagem é obrigatória para novo banner.');
            $this->redirect('/admin/banners/novo');
            return;
        }

        if ($id) {
            $this->model->update($id, $data);
            $this->flash('success', 'Banner atualizado.');
            $this->redirect("/admin/banners/{$id}/editar");
        } else {
            $newId = $this->model->insert($data);
            $this->flash('success', 'Banner criado.');
            $this->redirect("/admin/banners/{$newId}/editar");
        }
    }

    public function excluir(int $id): void
    {
        $this->model->delete($id);
        $this->flash('success', 'Banner excluído.');
        $this->redirect('/admin/banners');
    }
}
