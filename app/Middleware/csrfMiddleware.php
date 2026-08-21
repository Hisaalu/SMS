<?php

namespace NexaT\Middleware;

class CsrfMiddleware
{
    public function handle(array $params = []): void
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if ($token !== $_SESSION[CSRF_TOKEN_NAME]) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }
}