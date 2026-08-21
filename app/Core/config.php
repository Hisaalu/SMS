<?php
// File: /app/Core/Config.php

namespace NexaT\Core;

class Config
{
    private static $config = [];
    private static $loaded = false;
    
    public function __construct()
    {
        if (!self::$loaded) {
            $this->loadConfig();
        }
    }
    
    private function loadConfig(): void
    {
        $configFiles = [
            'app' => CONFIG_PATH . '/config.php',
            'database' => CONFIG_PATH . '/database.php'
        ];
        
        foreach ($configFiles as $key => $file) {
            if (file_exists($file)) {
                self::$config[$key] = require $file;
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            (new self())->loadConfig();
        }
        
        $keys = explode('.', $key);
        $current = self::$config;
        
        foreach ($keys as $segment) {
            if (!isset($current[$segment])) {
                return $default;
            }
            $current = $current[$segment];
        }
        
        return $current;
    }
}