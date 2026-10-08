<?php
// File: /app/Core/Toast.php

namespace NexaT\Core;

class Toast
{
    private const SESSION_KEY = '_nexa_toasts';

    public static function push(string $type, string $message, ?string $title = null, int $duration = 0): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (session_status() === PHP_SESSION_NONE) {
            return;
        }

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $signature = $type . '|' . $message . '|' . ($title ?? '');
        foreach ($_SESSION[self::SESSION_KEY] as $existing) {
            $existingSig = ($existing['type'] ?? '')
                        . '|' . ($existing['message'] ?? '')
                        . '|' . ($existing['title'] ?? '');
            if ($existingSig === $signature) {
                return; 
            }
        }

        $_SESSION[self::SESSION_KEY][] = [
            'type'     => $type,
            'message'  => $message,
            'title'    => $title,
            'duration' => $duration,
        ];
    }

    public static function success(string $message, ?string $title = null): void
    {
        self::push('success', $message, $title);
    }

    public static function error(string $message, ?string $title = null): void
    {
        self::push('error', $message, $title);
    }

    public static function warning(string $message, ?string $title = null): void
    {
        self::push('warning', $message, $title);
    }

    public static function info(string $message, ?string $title = null): void
    {
        self::push('info', $message, $title);
    }

    public static function pull(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        if (session_status() === PHP_SESSION_NONE) {
            return [];
        }

        $toasts = $_SESSION[self::SESSION_KEY] ?? [];
        
        unset($_SESSION[self::SESSION_KEY]);
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
            @session_start();
        }

        if (!is_array($toasts)) {
            return [];
        }

        $seen   = [];
        $unique = [];
        foreach ($toasts as $t) {
            $sig = ($t['type'] ?? '') . '|' . ($t['message'] ?? '') . '|' . ($t['title'] ?? '');
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            $unique[] = $t;
        }

        return $unique;
    }
}