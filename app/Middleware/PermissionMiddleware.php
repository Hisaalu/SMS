<?php
// File: /app/Middleware/PermissionMiddleware.php

namespace NexaT\Middleware;

use NexaT\Core\Auth;
use RuntimeException;

class PermissionMiddleware
{
    public function handle(array $params = []): void
    {
        $auth = new Auth();

        if (!$auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $permission = $params['permission'] ?? null;

        if (!$permission) {
            throw new RuntimeException('PermissionMiddleware requires a "permission" parameter.');
        }

        $user = $auth->getUser();

        if ($user->isSuperAdmin()) {
            return;
        }

        if (!$user->hasPermission($permission)) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }
}