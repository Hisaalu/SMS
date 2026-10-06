<?php
// File: /app/Core/Config.php

namespace NexaT\Core;

class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    public function __construct()
    {
        self::ensureLoaded();
    }

    public static function get(string $key, $default = null)
    {
        self::ensureLoaded();

        $current = self::$config;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    private static function ensureLoaded(): void
    {
        if (self::$loaded) {
            return;
        }

        $config = [];

        foreach (['app' => 'config.php', 'database' => 'database.php'] as $key => $file) {
            $path = CONFIG_PATH . '/' . $file;
            if (is_file($path)) {
                $result = require $path;
                if (is_array($result)) {
                    $config[$key] = $result;
                }
            }
        }

        if (empty($config['database'])) {
            throw new \RuntimeException(
                'Database configuration missing. Checked: ' . CONFIG_PATH . '/database.php. ' .
                'Make sure .env is loaded before Config::get() is called.'
            );
        }

        self::$config = $config;
        self::$loaded = true;

        self::defineSessionTimeouts($config);
    }

    private static function defineSessionTimeouts(array $config): void
    {
        $idle = (int)($config['app']['session']['idle_timeout'] ?? 0);
        if ($idle <= 0) {
            $idle = 15 * 60;
        }

        $absolute = (int)($config['app']['session']['absolute_timeout'] ?? 0);
        if ($absolute <= 0) {
            $absolute = 8 * 60 * 60;
        }

        if (!defined('SESSION_IDLE_TIMEOUT')) {
            define('SESSION_IDLE_TIMEOUT', $idle);
        }
        if (!defined('SESSION_ABSOLUTE_TIMEOUT')) {
            define('SESSION_ABSOLUTE_TIMEOUT', $absolute);
        }
    }
}