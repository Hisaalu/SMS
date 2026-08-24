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
        // 1. If not logged in at all, redirect to login page
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();

        // 2. If logged in but lacks permission
        if (!$user || (method_exists($user, 'can') && !$user->can($permission))) {
            http_response_code(403);
            
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $errorView = defined('VIEWS_PATH') 
                ? VIEWS_PATH . '/errors/403.php' 
                : ROOT_PATH . '/app/Views/errors/403.php';

            if (file_exists($errorView)) {
                require $errorView;
            } else {
                echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
                echo "<h1 style='color:#e74c3c;'>403 Access Denied</h1>";
                echo "<p>You need the permission <strong>'{$permission}'</strong> to view this page.</p>";
                echo "<a href='" . BASE_URL . "/dashboard' style='color:#3498db;'>Return to Dashboard</a>";
                echo "</div>";
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