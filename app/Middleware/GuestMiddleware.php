<?php
// File: /app/Middleware/GuestMiddleware.php

namespace NexaT\Middleware;

use NexaT\Core\Auth;

class GuestMiddleware
{
    public function handle(array $params = []): void
    {
        $auth = new Auth();

        if ($auth->check()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }
}