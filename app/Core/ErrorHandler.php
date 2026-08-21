<?php
// File: /app/Core/ErrorHandler.php

namespace NexaT\Core;

class ErrorHandler
{
    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }
    
    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }
        
        if (DEBUG_MODE) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
        
        return true;
    }
    
    public function handleException(\Throwable $e): void
    {
        if (DEBUG_MODE) {
            throw $e;
        }
        
        http_response_code(500);
        echo "An error occurred. Please try again later.";
        exit;
    }
}