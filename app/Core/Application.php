<?php
// File: /app/Core/Application.php

namespace NexaT\Core;

class Application
{
    private static $instance;
    private $container = [];
    private $booted = false;
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        
        $this->container['config'] = new Config();
        $this->container['router'] = Router::getInstance();
        $this->container['session'] = new Session();
        $this->container['db'] = Database::getInstance();
        $this->container['auth'] = new Auth();
        $this->container['settings'] = new SettingsService();
        
        $errorHandler = new ErrorHandler();
        $errorHandler->register();
        
        $this->registerMiddleware();
        
        $this->booted = true;
    }
    
    private function registerMiddleware(): void
    {
        $middleware = MiddlewareManager::getInstance();
        
        // Register core middleware
        $middleware->register('auth', \NexaT\Middleware\AuthMiddleware::class);
        $middleware->register('guest', \NexaT\Middleware\GuestMiddleware::class);
        $middleware->register('permission', \NexaT\Middleware\PermissionMiddleware::class);
        
        // Register all permissions as middleware aliases
        $this->registerPermissionMiddleware();
    }
    
    private function registerPermissionMiddleware(): void
    {
        $middleware = MiddlewareManager::getInstance();
        
        // Common permissions to register as middleware
        $permissions = [
            // Dashboard
            'dashboard.view',
            'dashboard.manage',
            
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            
            // Roles
            'roles.view',
            'roles.manage',
            
            // Permissions
            'permissions.view',
            'permissions.manage',
            
            // Settings
            'settings.view',
            'settings.manage',
            
            // School
            'school.view',
            'school.edit',
            
            // Branding
            'branding.view',
            'branding.manage',
            
            // Students
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',
            
            // Staff
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            
            // Classes
            'classes.view',
            'classes.create',
            'classes.edit',
            'classes.delete',
            
            // Subjects
            'subjects.view',
            'subjects.create',
            'subjects.edit',
            'subjects.delete',
            
            // Results
            'results.view',
            'results.enter',
            'results.edit',
            'results.publish',
            
            // Attendance
            'attendance.view',
            'attendance.manage',
            
            // Finance
            'fees.view',
            'fees.manage',
            'payments.view',
            'payments.create',
            
            // Reports
            'reports.view',
            'reports.generate',
            'reports.manage',
            
            // System
            'system.view',
            'system.manage',
            
            // Audit
            'audit.view',
            'audit.manage'
        ];
        
        foreach ($permissions as $permission) {
            $middleware->register($permission, \NexaT\Middleware\PermissionMiddleware::class);
        }
        
        // Try to load permissions from database
        try {
            $db = Database::getInstance();
            $dbPermissions = $db->fetchAll("SELECT slug FROM permissions");
            foreach ($dbPermissions as $perm) {
                $middleware->register($perm['slug'], \NexaT\Middleware\PermissionMiddleware::class);
            }
        } catch (\Exception $e) {
            // Permissions table might not exist yet, that's ok
        }
    }
    
    public function run(): void
    {
        try {
            $this->container['router']->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function get(string $key)
    {
        return $this->container[$key] ?? null;
    }
    
    private function handleException(\Exception $e): void
    {
        if (DEBUG_MODE) {
            echo "<h1>Application Error</h1>";
            echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
            exit;
        }
        
        http_response_code(500);
        echo "An error occurred. Please try again later.";
        exit;
    }
}