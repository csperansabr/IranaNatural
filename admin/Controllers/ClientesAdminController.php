<?php
namespace Admin\Controllers;

use App\Models\Cliente;
use App\Models\Pedido;

class ClientesAdminController extends AdminController
{
    public function index(): void
    {
        $busca    = trim($_GET['q'] ?? '');
        $clientes = (new Cliente())->allComFiltros($busca);
        $flash    = $this->getFlash();
        $this->render('clientes/index', compact('clientes', 'busca', 'flash'));
    }

    public function ver(int $id): void
    {
        $clienteModel = new Cliente();
        $cliente      = $clienteModel->findById($id);
        if (!$cliente) { $this->redirect('/admin/clientes'); return; }

        $endereco = $clienteModel->getEndereco($id);
        $pedidos  = (new Pedido())->doCliente($id);
        $flash    = $this->getFlash();
        $this->render('clientes/ver', compact('cliente', 'endereco', 'pedidos', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvarNovo(); return; }

        $flash = $this->getFlash();
        $old   = \App\Core\Session::flash('form_old') ?? [];
        $this->render('clientes/form', compact('flash', 'old') + ['modo' => 'criar', 'cliente' => null, 'endereco' => null]);
    }

    public function editar(int $id): void
    {
        $clienteModel = new Cliente();
        $cliente      = $clienteModel->findById($id);
        if (!$cliente) { $this->redirect('/admin/clientes'); return; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->salvarEdicao($id, $clienteModel); return; }

        $endereco = $clienteModel->getEndereco($id);

        // Formatar campos mascarados
        if ($endereco && $endereco['cep']) {
            $cep = preg_replace('/\D/', '', $endereco['cep']);
            if (strlen($cep) === 8) $endereco['cep'] = substr($cep, 0, 5) . '-' . substr($cep, 5);
        }
        if ($cliente['telefone']) {
            $cliente['telefone'] = Cliente::formatarTelefone($cliente['telefone']);
        }

        $flash = $this->getFlash();
        $old   = \App\Core\Session::flash('form_old') ?? [];
        $this->render('clientes/form', compact('cliente', 'endereco', 'flash', 'old') + ['modo' => 'editar']);
    }

    private function salvarNovo(): void
    {
        $data = $this->coletarDados();

        $erros = Cliente::validarDados($data, false);

        $clienteModel = new Cliente();
        if (!empty($data['email'])) {
            if ($clienteModel->findByEmail($data['email'])) $erros[] = 'Este e-mail já está cadastrado.';
        }
        if (!empty($data['cpf']) && Cliente::validarCpf($data['cpf'])) {
            if ($clienteModel->findByCpf($data['cpf'])) $erros[] = 'Este CPF já está cadastrado.';
        }

        if ($erros) {
            $this->flash('error', implode(' ', $erros));
            \App\Core\Session::flash('form_old', $data);
            $this->redirect('/admin/clientes/novo');
            return;
        }

        $id = $clienteModel->cadastrarPeloAdmin($data);
        if (!empty($data['cep']) || !empty($data['logradouro'])) {
            $clienteModel->salvarEndereco($id, $data);
        }

        $this->flash('success', 'Cliente cadastrado com sucesso!');
        $this->redirect('/admin/clientes/' . $id);
    }

    private function salvarEdicao(int $id, Cliente $clienteModel): void
    {
        $data = $this->coletarDados();

        $erros = Cliente::validarDados($data, false);

        if (!empty($data['email'])) {
            $ex = $clienteModel->findByEmail($data['email']);
            if ($ex && (int)$ex['id'] !== $id) $erros[] = 'Este e-mail já está em uso por outra conta.';
        }
        if (!empty($data['cpf']) && Cliente::validarCpf($data['cpf'])) {
            $ex = $clienteModel->findByCpf($data['cpf']);
            if ($ex && (int)$ex['id'] !== $id) $erros[] = 'Este CPF já está em uso por outra conta.';
        }

        if ($erros) {
            $this->flash('error', implode(' ', $erros));
            \App\Core\Session::flash('form_old', $data);
            $this->redirect('/admin/clientes/' . $id . '/editar');
            return;
        }

        $clienteModel->atualizarPeloAdmin($id, $data);
        $clienteModel->salvarEndereco($id, $data);

        $this->flash('success', 'Cliente atualizado com sucesso!');
        $this->redirect('/admin/clientes/' . $id);
    }

    private function coletarDados(): array
    {
        $campos = ['nome','cpf','email','telefone','data_nascimento',
                   'cep','logradouro','numero','bairro','cidade','estado'];
        $data   = [];
        foreach ($campos as $c) $data[$c] = trim($_POST[$c] ?? '');
        $data['complemento'] = trim($_POST['complemento'] ?? '');
        $data['ativo']       = isset($_POST['ativo']) ? 1 : 0;
        $data['senha']       = trim($_POST['senha'] ?? '');
        return $data;
    }
}
