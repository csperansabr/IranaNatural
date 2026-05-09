<?php
namespace Admin\Controllers;

use App\Models\Configuracao;

class ConfiguracoesController extends AdminController
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->salvar();
            return;
        }

        $config = (new Configuracao())->getAll();
        $flash  = $this->getFlash();
        $user   = $this->currentUser();
        $this->render('configuracoes/index', compact('config', 'flash', 'user'));
    }

    private function salvar(): void
    {
        $campos = [];

        $model = new Configuracao();
        foreach ($campos as $chave => $valor) {
            $model->set($chave, (string)$valor);
        }
        Configuracao::resetCache();

        $this->flash('success', 'Configurações salvas com sucesso.');
        header('Location: /admin/configuracoes');
        exit;
    }
}
