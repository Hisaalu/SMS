<?php
// File: /app/Helpers/UrlHelper.php

namespace NexaT\Helpers;

class UrlHelper
{
    /**
     * Generate a full URL with base path
     */
    public static function url($path = '')
    {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        
        if (empty($path)) {
            return $base . '/';
        }
        
        return $base . '/' . $path;
    }
    
    /**
     * Generate a URL for assets
     */
    public static function asset($path)
    {
        $base = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        return $base . '/public/assets/' . $path;
    }
    
    /**
     * Redirect to a URL
     */
    public static function redirect($path)
    {
        header('Location: ' . self::url($path));
        exit;
    }
    
    /**
     * Get the current URL
     */
    public static function current()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Get the base path
     */
    public static function base()
    {
        return rtrim(BASE_URL, '/') . '/';
    }
}