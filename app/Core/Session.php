<?php
// File: /app/Core/Session.php

namespace NexaT\Core;

class Session
{
    private $started = false;
    private $flash = [];
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->started = true;
        
        if (isset($_SESSION['_flash'])) {
            $this->flash = $_SESSION['_flash'];
            unset($_SESSION['_flash']);
        }
    }
    
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
    
    public function flash(string $key, $value): void
    {
        $this->flash[$key] = $value;
        $_SESSION['_flash'][$key] = $value;
    }
    
    public function getFlash(string $key, $default = null)
    {
        return $this->flash[$key] ?? $default;
    }
    
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }
    
    public function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->started = false;
    }
    
    public function isStarted(): bool
    {
        return $this->started;
    }
    
    public function save(): void
    {
        session_write_close();
    }
}