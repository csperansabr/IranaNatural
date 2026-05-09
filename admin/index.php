<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';
require_once ROOT . '/config/payment.php';
require_once ROOT . '/config/frete.php';

// Autoloader
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Core\\'          => ROOT . '/app/Core/',
        'App\\Models\\'        => ROOT . '/app/Models/',
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
use Admin\Controllers\AuthController;

Session::start();

// Parse URL segments
$url      = trim($_GET['url'] ?? '', '/');
$parts    = $url !== '' ? explode('/', $url) : [];
$module   = $parts[0] ?? 'dashboard';
$seg1     = $parts[1] ?? null;
$seg2     = $parts[2] ?? null;
$seg3     = $parts[3] ?? null;
$method   = $_SERVER['REQUEST_METHOD'];

// Logout
if ($module === 'logout') {
    (new AuthController())->logout();
    exit;
}

// Auth check (except login)
if ($module !== 'login' && !Session::has(ADMIN_SESSION)) {
    header('Location: /admin/login');
    exit;
}

// Map modules → controllers
$map = [
    'login'       => 'Admin\\Controllers\\AuthController',
    'dashboard'   => 'Admin\\Controllers\\DashboardController',
    'categorias'  => 'Admin\\Controllers\\CategoriasController',
    'insumos'     => 'Admin\\Controllers\\InsumosController',
    'compras'     => 'Admin\\Controllers\\ComprasController',
    'produtos'    => 'Admin\\Controllers\\ProdutosAdminController',
    'producao'    => 'Admin\\Controllers\\ProducaoController',
    'vendas'      => 'Admin\\Controllers\\VendasController',
    'clientes'    => 'Admin\\Controllers\\ClientesAdminController',
    'estoque'     => 'Admin\\Controllers\\EstoqueController',
    'banners'     => 'Admin\\Controllers\\BannersController',
    'depoimentos' => 'Admin\\Controllers\\DepoimentosController',
    'importacao'  => 'Admin\\Controllers\\ImportacaoController',
    'pedidos'       => 'Admin\\Controllers\\PedidosAdminController',
    'webhook_logs'  => 'Admin\\Controllers\\WebhookLogsController',
    'configuracoes' => 'Admin\\Controllers\\ConfiguracoesController',
];

$class = $map[$module] ?? null;
if (!$class) { http_response_code(404); echo '<h2>Módulo não encontrado.</h2>'; exit; }

$ctrl = new $class();

// ---- Routing por segmentos ----

// /admin/login
if ($module === 'login') {
    $ctrl->login(); exit;
}

// ── Módulo de Importação (routing personalizado) ──────────────────
if ($module === 'importacao') {
    if ($seg1 === null || $seg1 === '') { $ctrl->index(); exit; }
    if ($seg1 === 'historico') { $ctrl->historico(); exit; }
    $entidade = in_array($seg1, ['produtos', 'insumos', 'estoque'], true) ? $seg1 : null;
    if ($entidade === null) { http_response_code(404); echo '<h2>Não encontrado.</h2>'; exit; }
    if ($seg2 === 'preview'   && $method === 'POST') { $ctrl->preview($entidade); exit; }
    if ($seg2 === 'processar' && $method === 'POST') { $ctrl->processar($entidade); exit; }
    if ($seg2 === 'modelo')                          { $ctrl->modelo($entidade); exit; }
    $ctrl->form($entidade); exit;
}
// ─────────────────────────────────────────────────────────────────

// /admin/pedidos/status (AJAX POST)
if ($module === 'pedidos' && $seg1 === 'status' && $method === 'POST') {
    $ctrl->atualizarStatus(); exit;
}

// /admin/ ou /admin/dashboard
if ($seg1 === null || $seg1 === '') {
    $ctrl->index(); exit;
}

// /admin/{module}/nova ou novo
if (in_array($seg1, ['nova', 'novo'], true)) {
    $ctrl->novo(); exit;
}

// /admin/{module}/ajuste[/{tipo}]
if ($seg1 === 'ajuste') {
    if ($method === 'POST') { $ctrl->salvarAjuste(); }
    else                    { $ctrl->ajuste($seg2); }
    exit;
}

// /admin/produtos/verificar/{produto_id}/{qtd} — AJAX
if ($seg1 === 'verificar' && $seg2 !== null) {
    $ctrl->verificarInsumos((int)$seg2, (int)($seg3 ?? 1));
    exit;
}

// /admin/{module}/{id}/...
if (is_numeric($seg1)) {
    $id = (int)$seg1;
    switch ($seg2) {
        case null:
            $ctrl->ver($id); break;
        case 'editar':
            $ctrl->editar($id); break;
        case 'excluir':
            if ($method === 'POST') $ctrl->excluir($id);
            else header('Location: /admin/' . $module);
            break;
        case 'ficha':
            if ($method === 'POST') $ctrl->salvarFicha($id);
            else $ctrl->ficha($id);
            break;
        case 'ficha-excluir':
            if ($method === 'POST') $ctrl->excluirFichaItem($id, (int)($seg3 ?? 0));
            else header('Location: /admin/' . $module . "/{$id}/ficha");
            break;
        case 'imagem-principal':
            if ($method === 'POST') $ctrl->setPrincipalImagem($id, (int)($seg3 ?? 0));
            else header('Location: /admin/' . $module . "/{$id}/editar");
            break;
        case 'imagem-excluir':
            if ($method === 'POST') $ctrl->excluirImagemProduto($id, (int)($seg3 ?? 0));
            else header('Location: /admin/' . $module . "/{$id}/editar");
            break;
        case 'imagem-mover':
            if ($method === 'POST') $ctrl->moverImagem($id, (int)($seg3 ?? 0));
            else header('Location: /admin/' . $module . "/{$id}/editar");
            break;
        default:
            $ctrl->index(); break;
    }
    exit;
}

$ctrl->index();
