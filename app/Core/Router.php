<?php
// File: /app/Core/Router.php

namespace NexaT\Core;

class Router
{
    private static $instance;
    private $routes = [];
    private $groupStack = [];
    private $basePath = '/NexaT/';
    private $namedRoutes = [];
    
    private function __construct() {}
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function setBasePath(string $path): void
    {
        $this->basePath = rtrim($path, '/') . '/';
    }
    
    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    public function patch(string $path, $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }
    
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
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
        
        if ($prefix) {
            $path = $prefix . '/' . trim($path, '/');
        }
        
        // Remove base path from route if it starts with it
        $cleanPath = '/' . ltrim($path, '/');
        
        $pattern = preg_replace('/{([^}]+)}/', '([^/]+)', $cleanPath);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[$method][$cleanPath] = [
            'handler' => $handler,
            'middleware' => $middleware,
            'pattern' => $pattern,
            'params' => $this->extractParamNames($cleanPath)
        ];
    }
    
    private function extractParamNames(string $path): array
    {
        preg_match_all('/{([^}]+)}/', $path, $matches);
        return $matches[1] ?? [];
    }
    
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path from URI for matching
        if (BASE_URL && strpos($uri, BASE_URL) === 0) {
            $uri = substr($uri, strlen(BASE_URL));
        }
        
        if (empty($uri) || $uri === '/') {
            $uri = '/';
        }
        
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        
        foreach ($this->routes[$method] ?? [] as $routePath => $route) {
            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches);
                $params = array_combine($route['params'], $matches);
                
                $middlewareManager = MiddlewareManager::getInstance();
                foreach ($route['middleware'] as $middleware) {
                    $middlewareManager->handle($middleware, $params);
                }
                
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }
        
        http_response_code(404);
        echo "404 - Page Not Found";
        exit;
    }
    
    private function executeHandler($handler, array $params): void
    {
        if (is_array($handler)) {
            $controller = 'NexaT\\Controllers\\' . $handler[0];
            $method = $handler[1];
            
            if (!class_exists($controller)) {
                throw new \Exception("Controller {$controller} not found");
            }
            
            $instance = new $controller();
            
            if (!method_exists($instance, $method)) {
                throw new \Exception("Method {$method} not found in {$controller}");
            }
            
            $instance->$method($params);
        } elseif (is_callable($handler)) {
            $handler($params);
        } else {
            throw new \Exception("Invalid route handler");
        }
    }
}