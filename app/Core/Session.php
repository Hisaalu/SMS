<?php
// File: /app/Core/Session.php

namespace NexaT\Core;

class Session
{
    private bool $started = false;
    private array $flash = [];

    public function __construct()
    {
        if (defined('SESSION_IDLE_TIMEOUT')) {
            $lifetime = SESSION_IDLE_TIMEOUT + 300;
            @ini_set('session.gc_maxlifetime', (string)$lifetime);
            @ini_set('session.cookie_lifetime', (string)SESSION_IDLE_TIMEOUT);
        }

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
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
        $this->started = false;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function save(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    public function touchActivity(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION['_last_activity'] = time();

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            $lifetime = defined('SESSION_IDLE_TIMEOUT') ? SESSION_IDLE_TIMEOUT : 900;

            setcookie(
                session_name(),
                session_id(),
                [
                    'expires'  => time() + $lifetime,
                    'path'     => $p['path'],
                    'domain'   => $p['domain'],
                    'secure'   => $p['secure'],
                    'httponly' => $p['httponly'],
                    'samesite' => $p['samesite'] ?? 'Lax',
                ]
            );
        }
    }

    public function lastActivity(): int
    {
        return (int)($_SESSION['_last_activity'] ?? 0);
    }

    public function loginTime(): int
    {
        return (int)($_SESSION['_login_time'] ?? 0);
    }
}