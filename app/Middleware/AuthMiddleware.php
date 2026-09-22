<?php
// File: /app/Middleware/AuthMiddleware.php

namespace NexaT\Middleware;

use NexaT\Core\Auth;

class AuthMiddleware
{
    public function handle(array $params = []): void
    {
        $auth = new Auth();

        if (!$auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}