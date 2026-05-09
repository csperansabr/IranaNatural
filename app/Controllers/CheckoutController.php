<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Helper;
use App\Core\Mailer;
use App\Core\InfinitePayProvider;
use App\Models\Carrinho;
use App\Models\Pedido;
use App\Models\Pagamento;
use App\Models\Cliente;
use App\Services\FreteService;

class CheckoutController extends Controller
{
    private Carrinho $carrinhoModel;
    private Pedido   $pedidoModel;
    private Cliente  $clienteModel;

    public function __construct()
    {
        $this->carrinhoModel = new Carrinho();
        $this->pedidoModel   = new Pedido();
        $this->clienteModel  = new Cliente();
    }

    // ── GET /checkout — Etapa 1: Resumo do carrinho ──────────────
    public function index(): void
    {
        ClienteController::requerLogin(APP_URL . '/checkout');

        $carrinho = $this->getCarrinho();
        $itens    = $this->carrinhoModel->getItens($carrinho['id']);

        if (empty($itens)) {
            Session::flash('flash_erro', 'Seu carrinho está vazio.');
            $this->redirect(APP_URL . '/carrinho');
            return;
        }

        $total = $this->carrinhoModel->getTotal($carrinho['id']);

        $meta = ['title' => 'Checkout — ' . APP_NAME, 'url' => APP_URL . '/checkout'];
        $this->render('checkout/resumo', compact('meta', 'itens', 'total'));
    }

    // ── GET/POST /checkout/endereco — Etapa 2 ───────────────────
    public function endereco(): void
    {
        ClienteController::requerLogin(APP_URL . '/checkout/endereco');

        $carrinho = $this->getCarrinho();
        $itens    = $this->carrinhoModel->getItens($carrinho['id']);
        if (empty($itens)) { $this->redirect(APP_URL . '/carrinho'); return; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->salvarEndereco();
            return;
        }

        $clienteId = (int)Session::get('cliente_id');
        $endereco  = Session::get('checkout_endereco')
                  ?? $this->clienteModel->getEndereco($clienteId)
                  ?? [];

        $total = $this->carrinhoModel->getTotal($carrinho['id']);
        $csrf  = Session::csrfToken();
        $erro  = Session::flash('flash_erro');

        $meta = ['title' => 'Endereço de Entrega — ' . APP_NAME, 'url' => APP_URL . '/checkout/endereco'];
        $this->render('checkout/endereco', compact('meta', 'endereco', 'csrf', 'erro', 'total', 'itens'));
    }

    private function salvarEndereco(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('flash_erro', 'Erro de segurança.');
            $this->redirect(APP_URL . '/checkout/endereco');
            return;
        }

        $campos = ['cep','logradouro','numero','complemento','bairro','cidade','estado'];
        $addr   = [];
        foreach ($campos as $c) $addr[$c] = trim($_POST[$c] ?? '');

        if (!$addr['cep'] || !$addr['logradouro'] || !$addr['numero'] || !$addr['bairro'] || !$addr['cidade'] || !$addr['estado']) {
            Session::flash('flash_erro', 'Preencha todos os campos obrigatórios do endereço.');
            $this->redirect(APP_URL . '/checkout/endereco');
            return;
        }
        if (strlen(preg_replace('/\D/', '', $addr['cep'])) !== 8) {
            Session::flash('flash_erro', 'CEP inválido.');
            $this->redirect(APP_URL . '/checkout/endereco');
            return;
        }

        $addr['cep'] = preg_replace('/\D/', '', $addr['cep']);
        Session::set('checkout_endereco', $addr);

        if (!empty($_POST['salvar_cadastro'])) {
            $this->clienteModel->salvarEndereco((int)Session::get('cliente_id'), $addr);
        }

        $this->redirect(APP_URL . '/checkout/confirmar');
    }

    // ── GET /checkout/confirmar — Etapa 3 ────────────────────────
    public function confirmar(): void
    {
        ClienteController::requerLogin(APP_URL . '/checkout/confirmar');

        if (!Session::has('checkout_endereco')) {
            $this->redirect(APP_URL . '/checkout');
            return;
        }

        $carrinho = $this->getCarrinho();
        $itens    = $this->carrinhoModel->getItens($carrinho['id']);
        if (empty($itens)) { $this->redirect(APP_URL . '/carrinho'); return; }

        $total    = $this->carrinhoModel->getTotal($carrinho['id']);
        $endereco = Session::get('checkout_endereco');
        $csrf     = Session::csrfToken();
        $erro     = Session::flash('flash_erro');

        $meta = ['title' => 'Confirmar Pedido — ' . APP_NAME, 'url' => APP_URL . '/checkout/confirmar'];
        $this->render('checkout/confirmar', compact('meta', 'itens', 'total', 'endereco', 'csrf', 'erro'));
    }

    // ── POST /checkout/finalizar ──────────────────────────────────
    public function finalizar(): void
    {
        ClienteController::requerLogin(APP_URL . '/checkout');

        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('flash_erro', 'Erro de segurança. Recarregue e tente novamente.');
            $this->redirect(APP_URL . '/checkout/confirmar');
            return;
        }

        if (!Session::has('checkout_endereco')) {
            $this->redirect(APP_URL . '/checkout');
            return;
        }

        $clienteId = (int)Session::get('cliente_id');
        $carrinho  = $this->getCarrinho();
        $itens     = $this->carrinhoModel->getItens($carrinho['id']);
        if (empty($itens)) { $this->redirect(APP_URL . '/carrinho'); return; }

        $endereco = Session::get('checkout_endereco');
        $obs      = trim($_POST['observacoes'] ?? '');

        // Frete selecionado
        $freteTipo          = trim($_POST['frete_tipo']          ?? '');
        $freteTransportadora= trim($_POST['frete_transportadora'] ?? '');
        $freteValor         = (float)str_replace(',', '.', $_POST['frete_valor'] ?? '0');
        $fretePrazo         = trim($_POST['frete_prazo']         ?? '');
        $freteCodigo        = (int)($_POST['frete_codigo']        ?? 0);
        $freteRespCliente   = (int)($_POST['frete_resp_cliente']  ?? 0);

        $freteErro = (new FreteService())->validarSelecao($freteTipo, $freteValor);
        if ($freteErro) {
            Session::flash('flash_erro', $freteErro);
            $this->redirect(APP_URL . '/checkout/confirmar');
            return;
        }

        $pedidoItens = [];
        $subtotal    = 0.0;
        foreach ($itens as $item) {
            $precoUnit    = (float)$item['preco_unitario'];
            $subtotalItem = round($precoUnit * (int)$item['quantidade'], 2);
            $pedidoItens[] = [
                'produto_id'     => (int)$item['produto_id'],
                'nome_produto'   => $item['nome'],
                'quantidade'     => (int)$item['quantidade'],
                'preco_unitario' => $precoUnit,
                'subtotal'       => $subtotalItem,
            ];
            $subtotal += $subtotalItem;
        }
        $total = round($subtotal + $freteValor, 2);

        if ($total < 1.00) {
            Session::flash('flash_erro', 'O valor mínimo para pedidos via InfinitePay é R$ 1,00. Adicione mais itens ao carrinho.');
            $this->redirect(APP_URL . '/checkout/confirmar');
            return;
        }

        // 1. Create internal order
        try {
            $pedidoId = $this->pedidoModel->criar([
                'cliente_id'           => $clienteId,
                'forma_pagamento'      => 'pendente',
                'parcelas'             => 1,
                'frete'                => $freteValor,
                'desconto'             => 0,
                'desconto_pix_pct'     => 0,
                'observacoes'          => $obs,
                'entrega_cep'          => $endereco['cep'],
                'entrega_logradouro'   => $endereco['logradouro'],
                'entrega_numero'       => $endereco['numero'],
                'entrega_complemento'  => $endereco['complemento'] ?? '',
                'entrega_bairro'       => $endereco['bairro'],
                'entrega_cidade'       => $endereco['cidade'],
                'entrega_estado'       => $endereco['estado'],
                'tipo_frete'           => $freteTipo,
                'transportadora'       => $freteTransportadora,
                'prazo_entrega'        => $fretePrazo,
                'codigo_transportadora'=> $freteCodigo ?: null,
                'resp_entrega_cliente' => $freteRespCliente,
            ], $pedidoItens);
        } catch (\Throwable $e) {
            Session::flash('flash_erro', 'Não foi possível registrar seu pedido. Tente novamente.');
            $this->redirect(APP_URL . '/checkout/confirmar');
            return;
        }

        $pedido  = $this->pedidoModel->findComCliente($pedidoId);
        $cliente = $this->clienteModel->findById($clienteId);

        // 2. Create InfinitePay checkout link
        try {
            $provider   = new InfinitePayProvider();
            $ipResponse = $provider->criarCheckout($pedido, $pedidoItens, $cliente, $endereco);

            // 3. Save payment record
            $totalComFrete = round($subtotal + $freteValor, 2);
            (new Pagamento())->criar($pedidoId, [
                'order_nsu'      => $pedido['numero'],
                'invoice_slug'   => $ipResponse['invoice_slug'] ?? null,
                'checkout_url'   => $ipResponse['checkout_url'],
                'metodo'         => 'pendente',
                'parcelas'       => 1,
                'valor_original' => $totalComFrete,
                'valor_desconto' => 0,
                'valor_cobrado'  => $totalComFrete,
                'payload_criacao'=> $ipResponse['_request_payload'] ?? null,
            ]);

            if (!empty($ipResponse['invoice_slug'])) {
                $this->pedidoModel->update($pedidoId, ['invoice_slug' => $ipResponse['invoice_slug']]);
            }

        } catch (\Throwable $e) {
            $this->pedidoModel->atualizarStatus($pedidoId, 'cancelado', 'Falha ao gerar link de pagamento: ' . $e->getMessage());
            Session::flash('flash_erro', 'Não foi possível processar o pagamento no momento. Tente novamente em alguns minutos.');
            $this->redirect(APP_URL . '/checkout/confirmar');
            return;
        }

        // 4. Clear cart and checkout session
        $this->carrinhoModel->limpar($carrinho['id']);
        Session::set('ultimo_pedido', $pedido['numero']);
        Session::delete('checkout_endereco');

        // 5. Redirect to InfinitePay hosted checkout
        $this->redirect($ipResponse['checkout_url']);
    }

    // ── GET /checkout/aguardando/{numero} ─────────────────────────
    public function aguardando(string $numero): void
    {
        ClienteController::requerLogin();

        $pedido = $this->pedidoModel->findByNumero($numero);
        if (!$pedido || $pedido['cliente_id'] !== (int)Session::get('cliente_id')) {
            $this->redirect(APP_URL . '/minha-conta');
            return;
        }

        if (in_array($pedido['status'], ['pago', 'separando', 'enviado', 'entregue'], true)) {
            $this->redirect(APP_URL . '/checkout/obrigado/' . $numero);
            return;
        }
        if (in_array($pedido['status'], ['cancelado', 'pagamento_recusado', 'pagamento_expirado'], true)) {
            Session::flash('flash_erro', 'Seu pagamento não foi confirmado. Status: ' . Pedido::statusLabel($pedido['status']));
            $this->redirect(APP_URL . '/carrinho');
            return;
        }

        $pagamento   = (new Pagamento())->findByPedido((int)$pedido['id']);
        $checkoutUrl = $pagamento['checkout_url'] ?? null;

        $meta = ['title' => 'Processando Pagamento — ' . APP_NAME, 'url' => APP_URL . '/checkout/aguardando/' . $numero];
        $this->render('checkout/aguardando', compact('meta', 'pedido', 'numero', 'checkoutUrl'));
    }

    // ── GET /checkout/status/{numero} — AJAX poll ─────────────────
    public function statusCheck(string $numero): void
    {
        ClienteController::requerLogin();

        $pedido = $this->pedidoModel->findByNumero($numero);
        if (!$pedido || $pedido['cliente_id'] !== (int)Session::get('cliente_id')) {
            $this->json(['status' => 'not_found'], 404);
            return;
        }

        $this->json([
            'status'       => $pedido['status'],
            'status_label' => Pedido::statusLabel($pedido['status']),
            'pago'         => in_array($pedido['status'], ['pago','separando','enviado','entregue'], true),
            'falhou'       => in_array($pedido['status'], ['cancelado','pagamento_recusado','pagamento_expirado'], true),
        ]);
    }

    // ── GET /checkout/obrigado/{numero} ───────────────────────────
    public function obrigado(string $numero): void
    {
        ClienteController::requerLogin();

        $pedido = $this->pedidoModel->findByNumero($numero);
        if (!$pedido || $pedido['cliente_id'] !== (int)Session::get('cliente_id')) {
            $this->redirect(APP_URL . '/minha-conta');
            return;
        }

        $itens = $this->pedidoModel->getItens($pedido['id']);

        if ($pedido['status'] === 'pago' && !Session::has('email_obrigado_' . $numero)) {
            $cliente = $this->clienteModel->findById((int)$pedido['cliente_id']);
            try {
                Mailer::pedidoCliente($pedido, $itens, $cliente);
            } catch (\Throwable $e) {
                // Non-blocking
            }
            Session::set('email_obrigado_' . $numero, 1);
        }

        $meta = ['title' => 'Pedido Confirmado — ' . APP_NAME, 'url' => APP_URL . '/checkout/obrigado/' . $numero];
        $this->render('checkout/obrigado', compact('meta', 'pedido', 'itens'));
    }

    // ── GET /checkout/sucesso ─────────────────────────────────────
    public function sucesso(): void
    {
        ClienteController::requerLogin();

        $numero = Session::get('ultimo_pedido');
        if (!$numero) {
            $this->redirect(APP_URL . '/minha-conta');
            return;
        }

        $pedido = $this->pedidoModel->findByNumero($numero);
        if (!$pedido || $pedido['cliente_id'] !== (int)Session::get('cliente_id')) {
            $this->redirect(APP_URL . '/minha-conta');
            return;
        }

        $itens = $this->pedidoModel->getItens($pedido['id']);

        $meta = ['title' => 'Pedido Recebido — ' . APP_NAME, 'url' => APP_URL . '/checkout/sucesso'];
        $this->render('checkout/sucesso', compact('meta', 'pedido', 'itens'));
    }

    // ── Helper ───────────────────────────────────────────────────
    private function getCarrinho(): array
    {
        $sessaoId  = session_id();
        $clienteId = Session::has('cliente_id') ? (int)Session::get('cliente_id') : null;
        return $this->carrinhoModel->getOuCriar($sessaoId, $clienteId);
    }
}
