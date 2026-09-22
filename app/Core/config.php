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

        self::$loaded = true;

        foreach (['app' => 'config.php', 'database' => 'database.php'] as $key => $file) {
            $path = CONFIG_PATH . '/' . $file;
            if (is_file($path)) {
                self::$config[$key] = require $path;
            }
        }
    }
}