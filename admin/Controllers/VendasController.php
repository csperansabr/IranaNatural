<?php
namespace Admin\Controllers;

use App\Models\Venda;
use App\Models\Produto;
use App\Models\Cliente;

class VendasController extends AdminController
{
    public function index(): void
    {
        $vendas = (new Venda())->allComDetalhes();
        $flash  = $this->getFlash();
        $this->render('vendas/index', compact('vendas', 'flash'));
    }

    public function novo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->criar(); return; }
        $produtos = (new Produto())->allAtivos();
        $clientes = (new Cliente())->allAtivos();
        $flash    = $this->getFlash();
        $this->render('vendas/form', compact('produtos', 'clientes', 'flash'));
    }

    private function criar(): void
    {
        // Itens: arrays produto_id[], quantidade[], preco_unitario[]
        $pids    = $_POST['produto_id']     ?? [];
        $qtds    = $_POST['quantidade']     ?? [];
        $precos  = $_POST['preco_unitario'] ?? [];
        $data    = trim($_POST['data_venda'] ?? date('Y-m-d'));
        $pgto    = trim($_POST['forma_pagamento'] ?? 'pix');
        $desc    = (float)($_POST['desconto'] ?? 0);
        $obs     = trim($_POST['observacoes'] ?? '');

        // Resolve cliente
        $tipoCliente = trim($_POST['tipo_cliente'] ?? 'sem');
        $clienteId   = null;
        $clienteNome = null;

        if ($tipoCliente === 'existente') {
            $cid = (int)($_POST['cliente_id'] ?? 0);
            if ($cid > 0) $clienteId = $cid;
        } elseif ($tipoCliente === 'novo') {
            $cnome = trim($_POST['cliente_novo_nome'] ?? '');
            if ($cnome) {
                $clienteModel = new Cliente();
                $cemail    = trim($_POST['cliente_novo_email'] ?? '');
                $ccpf      = trim($_POST['cliente_novo_cpf'] ?? '');
                $ctelefone = trim($_POST['cliente_novo_telefone'] ?? '');
                $cdatanasc = trim($_POST['cliente_novo_data_nascimento'] ?? '');

                // Vincular a cadastro existente se e-mail ou CPF já estiver na base
                $existente = null;
                if ($cemail) $existente = $clienteModel->findByEmail($cemail);
                if (!$existente && $ccpf) $existente = $clienteModel->findByCpf($ccpf);

                if ($existente) {
                    $clienteId = (int)$existente['id'];
                } else {
                    $clienteId = $clienteModel->cadastrarPeloAdmin([
                        'nome'            => $cnome,
                        'email'           => $cemail,
                        'cpf'             => $ccpf,
                        'telefone'        => $ctelefone,
                        'data_nascimento' => $cdatanasc,
                    ]);
                    $clienteNome = $cnome;
                    // Salvar endereço se preenchido
                    $cep = trim($_POST['cliente_novo_cep'] ?? '');
                    if ($cep) {
                        $clienteModel->salvarEndereco($clienteId, [
                            'cep'         => $cep,
                            'logradouro'  => trim($_POST['cliente_novo_logradouro']  ?? ''),
                            'numero'      => trim($_POST['cliente_novo_numero']      ?? ''),
                            'complemento' => trim($_POST['cliente_novo_complemento'] ?? ''),
                            'bairro'      => trim($_POST['cliente_novo_bairro']      ?? ''),
                            'cidade'      => trim($_POST['cliente_novo_cidade']      ?? ''),
                            'estado'      => trim($_POST['cliente_novo_estado']      ?? ''),
                        ]);
                    }
                }
            }
        }

        // Monta itens
        $itens = [];
        foreach ($pids as $i => $pid) {
            if (!$pid || empty($qtds[$i]) || (int)$qtds[$i] <= 0) continue;
            $itens[] = [
                'produto_id'     => (int)$pid,
                'quantidade'     => (int)$qtds[$i],
                'preco_unitario' => (float)($precos[$i] ?? 0),
            ];
        }

        if (empty($itens)) {
            $this->flash('error', 'Adicione ao menos um produto à venda.');
            $this->redirect('/admin/vendas/nova');
            return;
        }

        try {
            $id = (new Venda())->registrar(
                [
                    'cliente_id'      => $clienteId,
                    'cliente_nome'    => $clienteNome,
                    'data_venda'      => $data,
                    'forma_pagamento' => $pgto,
                    'desconto'        => $desc,
                    'observacoes'     => $obs,
                ],
                $itens
            );
            $this->flash('success', 'Venda registrada com sucesso!');
            $this->redirect('/admin/vendas');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            $this->redirect('/admin/vendas/nova');
        }
    }

    public function ver(int $id): void
    {
        $venda = (new Venda())->findById($id);
        if (!$venda) { $this->redirect('/admin/vendas'); return; }
        $itens = (new Venda())->getItens($id);
        $flash = $this->getFlash();
        $this->render('vendas/index', compact('flash'));
    }
}
