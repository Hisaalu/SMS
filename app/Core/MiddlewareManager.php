<?php
// File: /app/Core/MiddlewareManager.php

namespace NexaT\Core;

class MiddlewareManager
{
    private static $instance;
    private $middlewares = [];
    
    private function __construct() {}
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function register(string $name, string $class): void
    {
        $this->middlewares[$name] = $class;
    }
    
    public function handle(string $name, array $params = []): void
    {
        // Check if middleware name contains a parameter (e.g., "permission:users.view")
        if (strpos($name, ':') !== false) {
            list($middlewareName, $param) = explode(':', $name, 2);
            $params['permission'] = $param;
            $name = $middlewareName;
        }
        
        if (!isset($this->middlewares[$name])) {
            throw new \Exception("Middleware not found: {$name}");
        }
        
        $class = $this->middlewares[$name];
        $middleware = new $class();
        $middleware->handle($params);
    }
}