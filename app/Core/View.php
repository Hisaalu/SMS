<?php
// File: /app/Core/View.php

namespace NexaT\Core;

class View
{
    private static $instance;
    private $data = [];
    private $sharedData = [];
    private $layoutPath = VIEWS_PATH . '/layouts';
    private $viewPath = VIEWS_PATH;
    
    private function __construct() {}
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function render(string $view, array $data = []): string
    {
        $viewPath = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$viewPath}");
        }
        
        $data = array_merge($this->sharedData, $this->data, $data);
        $data['view'] = $view;
        
        extract($data);
        
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
    
    public function renderWithLayout(string $view, string $layout = 'default', array $data = []): string
    {
        $content = $this->render($view, $data);
        $data['content'] = $content;
        
        $layoutPath = $this->layoutPath . '/' . $layout . '.php';
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layoutPath}");
        }
        
        $data = array_merge($this->sharedData, $this->data, $data);
        extract($data);
        
        ob_start();
        require $layoutPath;
        return ob_get_clean();
    }
    
    public function share(string $key, $value): self
    {
        $this->sharedData[$key] = $value;
        return $this;
    }
    
    public function flash(string $key, $value): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash'][$key] = $value;
    }

    public function getFlash(string $key): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
    
    public function __set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
}