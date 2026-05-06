<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $pattern, string $controller, string $action): void
    {
        $this->routes[] = ['GET', $pattern, $controller, $action];
    }

    public function post(string $pattern, string $controller, string $action): void
    {
        $this->routes[] = ['POST', $pattern, $controller, $action];
    }

    public function dispatch(string $url, string $method): void
    {
        $url = trim($url, '/');

        foreach ($this->routes as [$httpMethod, $pattern, $controllerName, $action]) {
            if ($method !== $httpMethod) continue;

            $pattern = trim($pattern, '/');
            $regex   = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
            $regex   = '#^' . $regex . '$#u';

            if (preg_match($regex, $url, $matches)) {
                array_shift($matches);
                $class      = "App\\Controllers\\{$controllerName}";
                $controller = new $class();
                $controller->$action(...$matches);
                return;
            }
        }

        // Fallback 404
        (new \App\Controllers\HomeController())->notFound();
    }
}
