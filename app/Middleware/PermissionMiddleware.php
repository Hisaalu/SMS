<?php
// File: /app/Middleware/PermissionMiddleware.php

namespace NexaT\Middleware;

use NexaT\Core\Auth;

class PermissionMiddleware
{
    public function handle(array $params = []): void
    {
        $auth = new Auth();
        
        // Check if user is authenticated
        if (!$auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $user = $auth->getUser();
        $permission = $params['permission'] ?? null;
        
        if (!$permission) {
            throw new \Exception("Permission middleware requires a permission parameter");
        }
        
        // Super Admin bypass - all permissions granted
        if ($user->isSuperAdmin()) {
            return;
        }
        
        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }
}