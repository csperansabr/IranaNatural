<?php
declare(strict_types=1);

define('ROOT', __DIR__);

require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/payment.php';
require_once ROOT . '/config/frete.php';

// PSR-4-like autoloader
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Core\\'          => ROOT . '/app/Core/',
        'App\\Controllers\\'   => ROOT . '/app/Controllers/',
        'App\\Models\\'        => ROOT . '/app/Models/',
        'App\\Services\\'      => ROOT . '/app/Services/',
        'Admin\\Controllers\\' => ROOT . '/admin/Controllers/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) require_once $file;
            return;
        }
    }
});

use App\Core\Session;
use App\Core\Router;

Session::start();

$router = new Router();

// Rotas públicas
$router->get('/',                     'HomeController',    'index');
$router->get('produtos',              'ProdutosController','index');
$router->get('produtos/{cat}',        'ProdutosController','categoria');
$router->get('produtos/{cat}/{slug}', 'ProdutosController','show');
$router->get('sobre',                 'SobreController',   'index');
$router->get('contato',               'ContatoController',    'index');
$router->post('contato/enviar',       'ContatoController',    'enviar');
$router->get('como-comprar',          'ComoComprarController', 'index');
$router->get('politica-privacidade',  'PoliticaPrivacidadeController', 'index');
$router->get('envio',                 'EnvioController',       'index');
$router->get('pagamento',             'PagamentoController',   'index');
$router->get('garantia',              'GarantiaController',    'index');
$router->get('trocas',                 'TrocasController',      'index');

// ── Módulo de Clientes ──────────────────────────────────────────
$router->get('minha-conta',                    'ClienteController', 'painel');
$router->get('minha-conta/login',              'ClienteController', 'login');
$router->post('minha-conta/login',             'ClienteController', 'login');
$router->get('minha-conta/logout',             'ClienteController', 'logout');
$router->get('minha-conta/cadastro',           'ClienteController', 'cadastro');
$router->post('minha-conta/cadastro',          'ClienteController', 'cadastro');
$router->get('minha-conta/recuperar-senha',    'ClienteController', 'recuperarSenha');
$router->post('minha-conta/recuperar-senha',   'ClienteController', 'recuperarSenha');
$router->get('minha-conta/nova-senha/{token}', 'ClienteController', 'novaSenha');
$router->post('minha-conta/nova-senha/{token}','ClienteController', 'novaSenha');
$router->get('minha-conta/editar',             'ClienteController', 'editarPerfil');
$router->post('minha-conta/editar',            'ClienteController', 'editarPerfil');

// ── Carrinho ────────────────────────────────────────────────────
$router->get('carrinho',              'CarrinhoController', 'index');
$router->post('carrinho/adicionar',   'CarrinhoController', 'adicionar');
$router->post('carrinho/atualizar',   'CarrinhoController', 'atualizar');
$router->post('carrinho/remover',     'CarrinhoController', 'remover');
$router->get('carrinho/mini',         'CarrinhoController', 'mini');

// ── Checkout ────────────────────────────────────────────────────
$router->get('checkout',                    'CheckoutController', 'index');
$router->get('checkout/endereco',           'CheckoutController', 'endereco');
$router->post('checkout/endereco',          'CheckoutController', 'endereco');
$router->get('checkout/confirmar',          'CheckoutController', 'confirmar');
$router->post('checkout/finalizar',         'CheckoutController', 'finalizar');
$router->get('checkout/sucesso',              'CheckoutController', 'sucesso');
$router->get('checkout/obrigado/{numero}',    'CheckoutController', 'obrigado');
$router->get('checkout/aguardando/{numero}', 'CheckoutController', 'aguardando');
$router->get('checkout/status/{numero}',     'CheckoutController', 'statusCheck');
$router->post('webhook/infinitepay/{secret}','WebhookController',  'infinitepay');
$router->post('api/frete/calcular',          'FreteController',     'calcular');

$url    = trim($_GET['url'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $method);
