<?php
// File: /app/Controllers/SettingsController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Core\SettingsService;
use NexaT\Services\AuditService;

class SettingsController extends Controller
{
    // Changed from private to protected to match NexaT\Core\Controller access level
    protected $settings;
    protected $audit;
    
    public function __construct()
    {
        parent::__construct();
        $this->settings = new SettingsService();
        $this->audit = new AuditService();
    }
    
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('settings.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $categories = $this->getSettingsCategories();
        
        $data = [
            'categories' => $categories
        ];
        
        echo $this->view->renderWithLayout('settings/index', 'default', $data);
    }
    
    public function school(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('school.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolInfo = [
            'name'                => $this->settings->get('school.name', ''),
            'short_name'          => $this->settings->get('school.short_name', ''),
            'motto'               => $this->settings->get('school.motto', ''),
            'slogan'              => $this->settings->get('school.slogan', ''),
            'description'         => $this->settings->get('school.description', ''),
            'type'                => $this->settings->get('school.type', 'Secondary'),
            'registration_number' => $this->settings->get('school.registration_number', ''),
            'physical_address'    => $this->settings->get('school.physical_address', ''),
            'postal_address'      => $this->settings->get('school.postal_address', ''),
            'telephone'           => $this->settings->get('school.telephone', ''),
            'email'               => $this->settings->get('school.email', ''),
            'website'             => $this->settings->get('school.website', ''),
        ];
        
        $data = ['school' => $schoolInfo];
        echo $this->view->renderWithLayout('settings/school', 'default', $data);
    }
    
    public function updateSchool(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('school.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        // 1. Basic Server-Side Validation
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');

        if (empty($name) || empty($email) || empty($telephone)) {
            $this->view->flash('error', 'School Name, Email, and Telephone are required fields.');
            header('Location: ' . BASE_URL . '/settings/school');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view->flash('error', 'Please provide a valid email address.');
            header('Location: ' . BASE_URL . '/settings/school');
            exit;
        }

        // 2. Persist Settings
        $fields = [
            'name', 'short_name', 'motto', 'slogan', 'description', 
            'type', 'registration_number', 'physical_address', 
            'postal_address', 'telephone', 'email', 'website'
        ];

        foreach ($fields as $field) {
            $key = 'school.' . $field;
            $value = trim($_POST[$field] ?? '');
            $this->settings->set($key, $value, 'string');
        }

        // 3. Audit Logging
        $this->audit->log(
            $this->auth->id(),
            'School Profile Updated',
            'settings',
            'School profile details updated'
        );

        $this->view->flash('success', 'School profile updated successfully.');
        header('Location: ' . BASE_URL . '/settings/school');
        exit;
    }

    public function branding(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('branding.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $branding = [
            'logo' => $this->settings->get('branding.logo', ''),
            'favicon' => $this->settings->get('branding.favicon', ''),
            'login_logo' => $this->settings->get('branding.login_logo', ''),
            'header_logo' => $this->settings->get('branding.header_logo', ''),
            'footer_logo' => $this->settings->get('branding.footer_logo', ''),
            'report_logo' => $this->settings->get('branding.report_logo', ''),
            'school_stamp' => $this->settings->get('branding.school_stamp', ''),
            'login_background' => $this->settings->get('branding.login_background', ''),
            'show_nexat_branding' => $this->settings->get('branding.show_nexat', true),
        ];
        
        $data = ['branding' => $branding];
        echo $this->view->renderWithLayout('settings/branding', 'default', $data);
    }

    public function updateBranding(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('branding.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        // Define upload directory inside public/
        $uploadDir = ROOT_PATH . '/public/uploads/branding/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Map form input names to settings keys
        $fileFields = [
            'logo'          => 'branding.logo',
            'favicon'       => 'branding.favicon',
            'school_stamp'  => 'branding.school_stamp',
            'report_logo'   => 'branding.report_logo',
        ];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'webp'];

        foreach ($fileFields as $inputName => $settingKey) {
            // 1. HANDLE DELETION
            if (!empty($_POST["delete_{$inputName}"])) {
                $currentRelativePath = $this->settings->get($settingKey, '');

                if (!empty($currentRelativePath)) {
                    $physicalPath = ROOT_PATH . '/public/' . ltrim($currentRelativePath, '/');
                    if (file_exists($physicalPath) && is_file($physicalPath)) {
                        @unlink($physicalPath);
                    }
                }

                // Clear value in database
                $this->settings->set($settingKey, '', 'string');
            }

            // 2. HANDLE NEW UPLOAD
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                $fileTmp   = $_FILES[$inputName]['tmp_name'];
                $fileName  = $_FILES[$inputName]['name'];
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($extension, $allowedExtensions)) {
                    // Remove old image file if overwriting with a new file
                    $oldRelativePath = $this->settings->get($settingKey, '');
                    if (!empty($oldRelativePath)) {
                        $oldPhysicalPath = ROOT_PATH . '/public/' . ltrim($oldRelativePath, '/');
                        if (file_exists($oldPhysicalPath) && is_file($oldPhysicalPath)) {
                            @unlink($oldPhysicalPath);
                        }
                    }

                    // Generate unique file name to avoid browser caching issues
                    $newFileName = $inputName . '_' . time() . '.' . $extension;
                    $targetPath  = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmp, $targetPath)) {
                        // Save relative path for front-end rendering
                        $relativePath = 'uploads/branding/' . $newFileName;
                        $this->settings->set($settingKey, $relativePath, 'string');
                    }
                }
            }
        }

        // Update footer branding toggle
        $showNexat = isset($_POST['show_nexat_branding']) ? 'true' : 'false';
        $this->settings->set('branding.show_nexat', $showNexat, 'boolean');

        $this->audit->log(
            $this->auth->id(),
            'Branding Updated',
            'settings',
            'Updated branding images and configuration'
        );

        $this->view->flash('success', 'Branding settings updated successfully!');
        header('Location: ' . BASE_URL . '/settings/branding');
        exit;
    }
    
    public function appearance(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('branding.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $theme = $this->settings->getTheme();
        
        $data = ['theme' => $theme];
        echo $this->view->renderWithLayout('settings/appearance', 'default', $data);
    }

    public function updateAppearance(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('branding.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $themeKeys = ['primary', 'secondary', 'accent', 'background', 'surface', 'text', 'muted', 'border', 'success', 'warning', 'danger'];

        foreach ($themeKeys as $key) {
            if (isset($_POST[$key]) && $_POST[$key] !== '') {
                $this->settings->set('theme.' . $key, trim($_POST[$key]), 'string');
            }
        }

        $darkMode = isset($_POST['dark_mode']) ? 'true' : 'false';
        $this->settings->set('theme.dark_mode', $darkMode, 'boolean');

        $this->audit->log(
            $this->auth->id(),
            'Appearance Updated',
            'settings',
            'Theme colors and appearance settings updated'
        );

        $this->view->flash('success', 'Appearance settings saved successfully!');
        header('Location: ' . BASE_URL . '/settings/appearance');
        exit;
    }
    
    private function getSettingsCategories(): array
    {
        return [
            'System' => ['system.timezone', 'system.currency', 'system.date_format'],
            'School' => ['school.name', 'school.short_name', 'school.motto'],
            'Theme'  => ['theme.primary', 'theme.accent', 'theme.dark_mode'],
        ];
    }
}