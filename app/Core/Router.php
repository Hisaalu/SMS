<?php
// File: /app/Core/Router.php

namespace NexaT\Core;

use RuntimeException;

class Router
{
    private static ?self $instance = null;
    private array $routes = [];
    private array $groupStack = [];
    private string $basePath = '/';
    private array $namedRoutes = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function setBasePath(string $path): void
    {
        $this->basePath = rtrim($path, '/') . '/';
    }

    public function get(string $path, $handler): void    { $this->addRoute('GET',    $path, $handler); }
    public function post(string $path, $handler): void   { $this->addRoute('POST',   $path, $handler); }
    public function put(string $path, $handler): void    { $this->addRoute('PUT',    $path, $handler); }
    public function delete(string $path, $handler): void { $this->addRoute('DELETE', $path, $handler); }
    public function patch(string $path, $handler): void  { $this->addRoute('PATCH',  $path, $handler); }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if (BASE_URL && str_starts_with($uri, BASE_URL)) {
            $uri = substr($uri, strlen(BASE_URL));
        }

        if ($uri === '' || $uri === false) {
            $uri = '/';
        }

        if ($method === 'HEAD') {
            $method = 'GET';
        }

        foreach ($this->routes[$method] ?? [] as $route) {
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = $route['params'] === []
                ? []
                : array_combine($route['params'], $matches);

            $manager = MiddlewareManager::getInstance();
            foreach ($route['middleware'] as $middleware) {
                $manager->handle($middleware, $params);
            }

            $this->executeHandler($route['handler'], $params);
            return;
        }

        http_response_code(404);
        echo '404 - Page Not Found';
    }

    private function addRoute(string $method, string $path, $handler): void
    {
        $prefix = '';
        $middleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, (array)$group['middleware']);
            }
        }

        if ($prefix !== '') {
            $path = $prefix . '/' . trim($path, '/');
        }

        $cleanPath = '/' . ltrim($path, '/');
        $pattern = preg_replace('/{([^}]+)}/', '([^/]+)', $cleanPath);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[$method][$cleanPath] = [
            'handler'    => $handler,
            'middleware' => $middleware,
            'pattern'    => $pattern,
            'params'     => $this->extractParamNames($cleanPath),
        ];
    }

    private function extractParamNames(string $path): array
    {
        preg_match_all('/{([^}]+)}/', $path, $matches);
        return $matches[1] ?? [];
    }

    private function executeHandler($handler, array $params): void
    {
        if (is_array($handler)) {
            $this->executeControllerAction($handler[0], $handler[1], $params);
            return;
        }

        if (is_callable($handler)) {
            $handler($params);
            return;
        }

        throw new RuntimeException('Invalid route handler.');
    }

    private function executeControllerAction(string $className, string $method, array $params): void
    {
        $controller = 'NexaT\\Controllers\\' . $className;

        if (!class_exists($controller)) {
            $file = dirname(__DIR__) . '/Controllers/' . $className . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        }

        if (!class_exists($controller)) {
            throw new RuntimeException("Controller {$controller} not found.");
        }

        $instance = new $controller();

        if (!method_exists($instance, $method)) {
            throw new RuntimeException("Method {$method} not found in {$controller}.");
        }

        $instance->$method($params);
    }
}