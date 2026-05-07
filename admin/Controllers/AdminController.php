<?php
namespace Admin\Controllers;

use App\Core\Session;

abstract class AdminController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile   = ROOT . "/admin/Views/{$view}.php";
        $layoutFile = ROOT . "/admin/Views/layouts/default.php";

        if (!file_exists($viewFile)) {
            die("Admin view não encontrada: {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash('flash_type', $type);
        Session::flash('flash_msg', $message);
    }

    protected function getFlash(): array
    {
        return [
            'type' => Session::flash('flash_type'),
            'msg'  => Session::flash('flash_msg'),
        ];
    }

    protected function currentUser(): array
    {
        return Session::get(ADMIN_SESSION, []);
    }

    // Default stubs — controllers sobrescrevem os que precisam
    public function index(): void  { $this->render('dashboard/index'); }
    public function novo(): void   { $this->render('dashboard/index'); }
    public function ver(int $id): void    { $this->redirect('/admin/dashboard'); }
    public function editar(int $id): void { $this->redirect('/admin/dashboard'); }
    public function excluir(int $id): void { $this->redirect('/admin/dashboard'); }
    public function ajuste(?string $tipo): void { $this->redirect('/admin/estoque'); }
    public function salvarAjuste(): void   { $this->redirect('/admin/estoque'); }
    public function verificarInsumos(int $produtoId, int $qtd): void { $this->json([]); }
    public function ficha(int $id): void   { $this->redirect('/admin/dashboard'); }
    public function salvarFicha(int $id): void   { $this->redirect('/admin/dashboard'); }
    public function excluirFichaItem(int $prodId, int $itemId): void { $this->redirect('/admin/produtos'); }
    public function setPrincipalImagem(int $prodId, int $imgId): void { $this->redirect('/admin/produtos/' . $prodId . '/editar'); }
    public function excluirImagemProduto(int $prodId, int $imgId): void { $this->redirect('/admin/produtos/' . $prodId . '/editar'); }
    public function moverImagem(int $prodId, int $imgId): void { $this->redirect('/admin/produtos/' . $prodId . '/editar'); }

    // Import stubs — overridden by ImportacaoController
    public function form(string $entidade): void       { $this->redirect('/admin/importacao'); }
    public function preview(string $entidade): void    { $this->json(['error' => 'Não implementado'], 501); }
    public function processar(string $entidade): void  { $this->json(['error' => 'Não implementado'], 501); }
    public function modelo(string $entidade): void     { $this->redirect('/admin/importacao'); }
    public function historico(): void                  { $this->redirect('/admin/importacao'); }
}
