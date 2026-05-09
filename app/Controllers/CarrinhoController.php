<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Helper;
use App\Models\Carrinho;
use App\Models\Produto;
use App\Models\Cliente;

class CarrinhoController extends Controller
{
    private Carrinho $carrinhoModel;
    private Produto  $produtoModel;

    public function __construct()
    {
        $this->carrinhoModel = new Carrinho();
        $this->produtoModel  = new Produto();
    }

    // ── GET /carrinho ────────────────────────────────────────────
    public function index(): void
    {
        $carrinho  = $this->getCarrinho();
        $itens     = $this->carrinhoModel->getItens($carrinho['id']);
        $total     = $this->carrinhoModel->getTotal($carrinho['id']);
        $flash     = Session::flash('flash_ok');
        $erro      = Session::flash('flash_erro');

        $cliente         = null;
        $endereco        = null;
        $dadosFaltando   = [];

        if (Session::has('cliente_id')) {
            $clienteId = (int)Session::get('cliente_id');
            $clienteModel = new Cliente();
            $cliente  = $clienteModel->findById($clienteId);
            $endereco = $clienteModel->getEndereco($clienteId);
            if ($cliente) {
                $dadosFaltando = Cliente::camposObrigatoriosFaltando($cliente, $endereco);
                // Formatar para exibição
                if ($cliente['telefone']) {
                    $cliente['telefone'] = Cliente::formatarTelefone($cliente['telefone']);
                }
                if ($cliente['cpf']) {
                    $cliente['cpf_fmt'] = $cliente['cpf'];
                }
                if ($endereco && $endereco['cep']) {
                    $cep = preg_replace('/\D/', '', $endereco['cep']);
                    if (strlen($cep) === 8) {
                        $endereco['cep_fmt'] = substr($cep, 0, 5) . '-' . substr($cep, 5);
                    }
                }
            }
        }

        $meta = [
            'title'       => 'Carrinho — ' . APP_NAME,
            'description' => 'Revise seus produtos e finalize sua compra.',
            'url'         => APP_URL . '/carrinho',
        ];
        $this->render('carrinho/index', compact('meta', 'itens', 'total', 'flash', 'erro',
                                                 'cliente', 'endereco', 'dadosFaltando'));
    }

    // ── POST /carrinho/adicionar ─────────────────────────────────
    public function adicionar(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            $this->json(['ok' => false, 'msg' => 'Erro de segurança.'], 403);
            return;
        }

        $produtoId = (int)($_POST['produto_id'] ?? 0);
        $qtd       = max(1, (int)($_POST['quantidade'] ?? 1));

        if (!$produtoId) {
            $this->json(['ok' => false, 'msg' => 'Produto inválido.'], 400);
            return;
        }

        $produto = $this->produtoModel->findById($produtoId);
        if (!$produto || !$produto['ativo']) {
            $this->json(['ok' => false, 'msg' => 'Produto não encontrado.'], 404);
            return;
        }
        if ($produto['estoque_atual'] < $qtd) {
            $this->json(['ok' => false, 'msg' => 'Estoque insuficiente.'], 400);
            return;
        }

        $clienteId = Session::has('cliente_id') ? (int)Session::get('cliente_id') : null;
        $carrinho  = $this->getCarrinho($clienteId);

        $this->carrinhoModel->addItem($carrinho['id'], $produtoId, $qtd, (float)$produto['preco_venda']);

        $count = $this->carrinhoModel->getCount($carrinho['id']);
        $total = $this->carrinhoModel->getTotal($carrinho['id']);

        $this->json([
            'ok'    => true,
            'msg'   => 'Produto adicionado ao carrinho!',
            'count' => $count,
            'total' => Helper::money($total),
        ]);
    }

    // ── POST /carrinho/atualizar ─────────────────────────────────
    public function atualizar(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            $this->json(['ok' => false, 'msg' => 'Erro de segurança.'], 403);
            return;
        }

        $itemId = (int)($_POST['item_id'] ?? 0);
        $qtd    = (int)($_POST['quantidade'] ?? 0);

        $item = $this->carrinhoModel->getItem($itemId);
        if (!$item) {
            $this->json(['ok' => false, 'msg' => 'Item não encontrado.'], 404);
            return;
        }

        $carrinho = $this->getCarrinho();
        if ($item['carrinho_id'] !== $carrinho['id']) {
            $this->json(['ok' => false, 'msg' => 'Acesso negado.'], 403);
            return;
        }

        if ($qtd > 0) {
            $produto = $this->produtoModel->findById($item['produto_id']);
            if ($produto && $produto['estoque_atual'] < $qtd) {
                $this->json(['ok' => false, 'msg' => 'Estoque insuficiente.'], 400);
                return;
            }
        }

        $this->carrinhoModel->updateItem($itemId, $qtd);

        $count    = $this->carrinhoModel->getCount($carrinho['id']);
        $total    = $this->carrinhoModel->getTotal($carrinho['id']);
        $subtotal = $qtd > 0 ? Helper::money($qtd * (float)$item['preco_unitario']) : 'R$ 0,00';

        $this->json([
            'ok'       => true,
            'count'    => $count,
            'total'    => Helper::money($total),
            'subtotal' => $subtotal,
            'removido' => $qtd <= 0,
        ]);
    }

    // ── POST /carrinho/remover ───────────────────────────────────
    public function remover(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            $this->json(['ok' => false, 'msg' => 'Erro de segurança.'], 403);
            return;
        }

        $itemId  = (int)($_POST['item_id'] ?? 0);
        $item    = $this->carrinhoModel->getItem($itemId);
        if (!$item) {
            $this->json(['ok' => false, 'msg' => 'Item não encontrado.'], 404);
            return;
        }

        $carrinho = $this->getCarrinho();
        if ($item['carrinho_id'] !== $carrinho['id']) {
            $this->json(['ok' => false, 'msg' => 'Acesso negado.'], 403);
            return;
        }

        $this->carrinhoModel->removeItem($itemId);

        $count = $this->carrinhoModel->getCount($carrinho['id']);
        $total = $this->carrinhoModel->getTotal($carrinho['id']);

        $this->json([
            'ok'    => true,
            'count' => $count,
            'total' => Helper::money($total),
        ]);
    }

    // ── GET /carrinho/mini (AJAX badge) ──────────────────────────
    public function mini(): void
    {
        $carrinho = $this->getCarrinho();
        $this->json([
            'count' => $this->carrinhoModel->getCount($carrinho['id']),
            'total' => Helper::money($this->carrinhoModel->getTotal($carrinho['id'])),
        ]);
    }

    // ── Helper: obtém ou cria carrinho da sessão atual ───────────
    private function getCarrinho(?int $clienteId = null): array
    {
        $sessaoId  = session_id();
        $clienteId = $clienteId ?? (Session::has('cliente_id') ? (int)Session::get('cliente_id') : null);
        return $this->carrinhoModel->getOuCriar($sessaoId, $clienteId);
    }
}
