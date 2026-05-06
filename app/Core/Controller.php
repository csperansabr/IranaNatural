<?php
namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'default'): void
    {
        extract($data);
        $viewFile   = ROOT . "/app/Views/{$view}.php";
        $layoutFile = ROOT . "/app/Views/layouts/{$layout}.php";

        if (!file_exists($viewFile)) {
            die("View não encontrada: {$view}");
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

    protected function notFound(): void
    {
        http_response_code(404);
        $content = '';
        $layout = ROOT . '/app/Views/layouts/default.php';
        ob_start();
        require ROOT . '/app/Views/errors/404.php';
        $content = ob_get_clean();
        require $layout;
    }
}
