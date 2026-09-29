<?php

namespace ShieldLayer\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        $this->routes[$method][$normalizedPath] = $handler;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        if (isset($this->routes[$method][$uri])) {
            [$controllerClass, $action] = $this->routes[$method][$uri];

            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $action)) {
                    $controller->$action($request);
                    return;
                }
            }
        }

        // Fallback 404
        http_response_code(404);
        Response::json([
            'error' => true,
            'message' => 'Endpoint not found or route misconfigured',
            'requested_path' => $uri
        ], 404);
    }
}
