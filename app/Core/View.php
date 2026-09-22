<?php
// File: /app/Core/View.php

namespace NexaT\Core;

use RuntimeException;
use Throwable;

class View
{
    private static ?self $instance = null;
    private array $data = [];
    private array $sharedData = [];
    private string $layoutPath;
    private string $viewPath;

    private function __construct()
    {
        $this->layoutPath = VIEWS_PATH . '/layouts';
        $this->viewPath   = VIEWS_PATH;
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function render(string $view, array $data = []): string
    {
        $viewFile = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException("View not found: {$viewFile}");
        }

        $data = array_merge($this->sharedData, $this->data, $data);
        $data['view'] = $view;

        return $this->capture($viewFile, $data);
    }

    public function renderWithLayout(string $view, string $layout = 'default', array $data = []): string
    {
        $content = $this->render($view, $data);
        $data['content'] = $content;

        $layoutFile = $this->layoutPath . '/' . $layout . '.php';

        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout not found: {$layoutFile}");
        }

        $data = array_merge($this->sharedData, $this->data, $data);

        return $this->capture($layoutFile, $data);
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

        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $message;
    }

    public function __set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    private function capture(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $file;
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean() ?: '';
    }
}