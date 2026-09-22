<?php
// File: /app/Core/MiddlewareManager.php

namespace NexaT\Core;

use RuntimeException;

class MiddlewareManager
{
    private static ?self $instance = null;
    private array $middlewares = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function register(string $name, string $class): void
    {
        $this->middlewares[$name] = $class;
    }

    public function handle(string $name, array $params = []): void
    {
        if (str_contains($name, ':')) {
            [$name, $param] = explode(':', $name, 2);
            $params['permission'] = $param;
        }

        if (!isset($this->middlewares[$name])) {
            throw new RuntimeException("Middleware not found: {$name}");
        }

        $class = $this->middlewares[$name];

        if (!class_exists($class)) {
            throw new RuntimeException("Middleware class does not exist: {$class}");
        }

        (new $class())->handle($params);
    }
}