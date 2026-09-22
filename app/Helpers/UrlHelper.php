<?php
// File: /app/Helpers/UrlHelper.php

namespace NexaT\Helpers;

class UrlHelper
{
    public static function url(string $path = ''): string
    {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');

        return $path === '' ? $base . '/' : $base . '/' . $path;
    }

    public static function asset(string $path): string
    {
        return rtrim(BASE_URL, '/') . '/public/assets/' . ltrim($path, '/');
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . self::url($path));
        exit;
    }

    public static function current(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';

        return $protocol . '://' . $host . $uri;
    }

    public static function base(): string
    {
        return rtrim(BASE_URL, '/') . '/';
    }
}