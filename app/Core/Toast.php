<?php
// File: /app/Core/Toast.php

namespace NexaT\Core;

class Toast
{
    private const SESSION_KEY = '_nexa_toasts';

    public static function push(string $type, string $message, ?string $title = null, int $duration = 0): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            return;
        }

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
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
            return [];
        }

        $toasts = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);

        return is_array($toasts) ? $toasts : [];
    }
}