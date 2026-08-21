<?php
// File: /app/Core/Controller.php

namespace NexaT\Core;

abstract class Controller
{
    protected $view;
    protected $db;
    protected $auth;
    protected $settings;
    protected $audit;
    
    public function __construct()
    {
        $this->view = View::getInstance();
        $this->db = Database::getInstance();
        $this->auth = new Auth();
        $this->settings = new SettingsService();
        
        // Safely check if Audit model or service exists before instantiating
        if (class_exists('\\NexaT\\Models\\AuditLog')) {
            $this->audit = new \NexaT\Models\AuditLog();
        } elseif (class_exists('\\NexaT\\Services\\AuditLog')) {
            $this->audit = new \NexaT\Services\AuditLog();
        } else {
            $this->audit = null;
        }
        
        $this->view->share('schoolName', $this->settings->get('school.name', 'NexaT School'));
        $this->view->share('user', $this->auth->getUser());
        $this->view->share('theme', $this->settings->getTheme());
        $this->view->share('baseUrl', BASE_URL);
    }

    protected function authorize(string $permission): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can($permission)) {
            http_response_code(403);
            if (file_exists(VIEWS_PATH . '/errors/403.php')) {
                require VIEWS_PATH . '/errors/403.php';
            } else {
                echo "403 - Unauthorized Access";
            }
            exit;
        }
    }
    
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    protected function requireAuth(): void
    {
        if (!$this->auth || !$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}