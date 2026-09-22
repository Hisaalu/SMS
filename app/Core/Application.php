<?php
// File: /app/Core/Application.php

namespace NexaT\Core;

use NexaT\Middleware\AuthMiddleware;
use NexaT\Middleware\GuestMiddleware;
use NexaT\Middleware\PermissionMiddleware;
use Throwable;

class Application
{
    private static ?self $instance = null;
    private array $container = [];
    private bool $booted = false;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->container['config']   = new Config();
        $this->container['router']   = Router::getInstance();
        $this->container['session']  = new Session();
        $this->container['db']       = Database::getInstance();
        $this->container['auth']     = new Auth();
        $this->container['settings'] = new SettingsService();

        (new ErrorHandler())->register();

        $this->registerMiddleware();

        $this->booted = true;
    }

    public function run(): void
    {
        try {
            $this->container['router']->dispatch();
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    public function get(string $key)
    {
        return $this->container[$key] ?? null;
    }

    private function registerMiddleware(): void
    {
        $manager = MiddlewareManager::getInstance();

        $manager->register('auth',       AuthMiddleware::class);
        $manager->register('guest',      GuestMiddleware::class);
        $manager->register('permission', PermissionMiddleware::class);

        $this->registerPermissionMiddleware($manager);
    }

    private function registerPermissionMiddleware(MiddlewareManager $manager): void
    {
        foreach ($this->permissionSlugs() as $slug) {
            $manager->register($slug, PermissionMiddleware::class);
        }

        try {
            $rows = Database::getInstance()->fetchAll("SELECT slug FROM permissions");
            foreach ($rows as $row) {
                $manager->register($row['slug'], PermissionMiddleware::class);
            }
        } catch (Throwable $e) {
        }
    }

    private function permissionSlugs(): array
    {
        return [
            'dashboard.view', 'dashboard.manage',

            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.manage',
            'permissions.view', 'permissions.manage',

            'settings.view', 'settings.manage',
            'school.view', 'school.edit',
            'branding.view', 'branding.manage',

            'students.view', 'students.create', 'students.edit', 'students.delete',
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',

            'classes.view', 'classes.create', 'classes.edit', 'classes.delete',
            'subjects.view', 'subjects.create', 'subjects.edit', 'subjects.delete',

            'results.view', 'results.enter', 'results.edit', 'results.publish',
            'attendance.view', 'attendance.manage',

            'fees.view', 'fees.manage',
            'payments.view', 'payments.create',

            'reports.view', 'reports.generate', 'reports.manage',

            'system.view', 'system.manage',
            'audit.view', 'audit.manage',
        ];
    }

    private function handleException(Throwable $e): void
    {
        if (DEBUG_MODE) {
            echo '<h1>Application Error</h1>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ':' . $e->getLine() . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
            exit;
        }

        http_response_code(500);
        echo 'An error occurred. Please try again later.';
        exit;
    }
}