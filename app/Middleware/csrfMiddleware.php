<?php
// File: /app/Middleware/CsrfMiddleware.php

namespace NexaT\Middleware;

class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(array $params = []): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (in_array($method, self::SAFE_METHODS, true)) {
            return;
        }

        $token = $_POST[CSRF_TOKEN_NAME]
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? '';

        $sessionToken = $_SESSION[CSRF_TOKEN_NAME] ?? '';

        if ($sessionToken === '' || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            exit('CSRF token validation failed');
        }
    }
}