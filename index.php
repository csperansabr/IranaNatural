<?php
declare(strict_types=1);

define('ROOT', __DIR__);

require_once ROOT . '/config/database.php';
require_once ROOT . '/config/app.php';

// PSR-4-like autoloader
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Core\\'        => ROOT . '/app/Core/',
        'App\\Controllers\\' => ROOT . '/app/Controllers/',
        'App\\Models\\'      => ROOT . '/app/Models/',
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
$router->get('contato',               'ContatoController', 'index');
$router->post('contato/enviar',       'ContatoController', 'enviar');

$url    = trim($_GET['url'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $method);
