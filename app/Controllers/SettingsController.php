<?php
// File: /app/Controllers/SettingsController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class SettingsController extends Controller
{
    private const SCHOOL_FIELDS = [
        'name', 'short_name', 'motto', 'slogan', 'description',
        'type', 'registration_number', 'physical_address',
        'postal_address', 'telephone', 'email', 'website',
    ];

    private const THEME_KEYS = [
        'primary', 'secondary', 'accent', 'background', 'surface',
        'text', 'muted', 'border', 'success', 'warning', 'danger',
    ];

    private const BRANDING_FIELDS = [
        'logo'         => 'branding.logo',
        'favicon'      => 'branding.favicon',
        'school_stamp' => 'branding.school_stamp',
        'report_logo'  => 'branding.report_logo',
    ];

    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'webp'];

    public function index(): void
    {
        $this->requirePermission('settings.view');

        echo $this->view->renderWithLayout('settings/index', 'default', [
            'categories' => $this->settingsCategories(),
        ]);
    }

    public function school(): void
    {
        $this->requirePermission('school.edit');

        $schoolInfo = [];
        foreach (self::SCHOOL_FIELDS as $field) {
            $default = $field === 'type' ? 'Secondary' : '';
            $schoolInfo[$field] = $this->settings->get('school.' . $field, $default);
        }

        echo $this->view->renderWithLayout('settings/school', 'default', [
            'school' => $schoolInfo,
        ]);
    }

    public function updateSchool(): void
    {
        $this->requirePermission('school.edit');

        $name      = trim($_POST['name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');

        if ($name === '' || $email === '' || $telephone === '') {
            $this->flashError('School Name, Email, and Telephone are required fields.');
            $this->redirect('/settings/school');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flashError('Please provide a valid email address.');
            $this->redirect('/settings/school');
        }

        foreach (self::SCHOOL_FIELDS as $field) {
            $this->settings->set('school.' . $field, trim($_POST[$field] ?? ''), 'string');
        }

        $this->audit('School Profile Updated', 'settings', 'School profile details updated');
        $this->flashSuccess('School profile updated successfully.');
        $this->redirect('/settings/school');
    }

    public function branding(): void
    {
        $this->requirePermission('branding.manage');

        $branding = [
            'logo'                => $this->settings->get('branding.logo', ''),
            'favicon'             => $this->settings->get('branding.favicon', ''),
            'login_logo'          => $this->settings->get('branding.login_logo', ''),
            'header_logo'         => $this->settings->get('branding.header_logo', ''),
            'footer_logo'         => $this->settings->get('branding.footer_logo', ''),
            'report_logo'         => $this->settings->get('branding.report_logo', ''),
            'school_stamp'        => $this->settings->get('branding.school_stamp', ''),
            'login_background'    => $this->settings->get('branding.login_background', ''),
            'show_nexat_branding' => $this->settings->get('branding.show_nexat', true),
        ];

        echo $this->view->renderWithLayout('settings/branding', 'default', [
            'branding' => $branding,
        ]);
    }

    public function updateBranding(): void
    {
        $this->requirePermission('branding.manage');

        $uploadDir = ROOT_PATH . '/public/uploads/branding/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach (self::BRANDING_FIELDS as $inputName => $settingKey) {
            $this->handleBrandingField($inputName, $settingKey, $uploadDir);
        }

        $this->settings->set(
            'branding.show_nexat',
            isset($_POST['show_nexat_branding']) ? 'true' : 'false',
            'boolean'
        );

        $this->audit('Branding Updated', 'settings', 'Updated branding images and configuration');
        $this->flashSuccess('Branding settings updated successfully!');
        $this->redirect('/settings/branding');
    }

    public function appearance(): void
    {
        $this->requirePermission('branding.manage');

        echo $this->view->renderWithLayout('settings/appearance', 'default', [
            'theme' => $this->settings->getTheme(),
        ]);
    }

    public function updateAppearance(): void
    {
        $this->requirePermission('branding.manage');

        foreach (self::THEME_KEYS as $key) {
            if (!empty($_POST[$key])) {
                $this->settings->set('theme.' . $key, trim($_POST[$key]), 'string');
            }
        }

        $this->settings->set(
            'theme.dark_mode',
            isset($_POST['dark_mode']) ? 'true' : 'false',
            'boolean'
        );

        $this->audit('Appearance Updated', 'settings', 'Theme colors and appearance settings updated');
        $this->flashSuccess('Appearance settings saved successfully!');
        $this->redirect('/settings/appearance');
    }

    private function handleBrandingField(string $inputName, string $settingKey, string $uploadDir): void
    {
        if (!empty($_POST["delete_{$inputName}"])) {
            $this->deleteBrandingFile($settingKey);
        }

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $extension = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            return;
        }

        $this->deleteBrandingFile($settingKey);

        $newFileName = $inputName . '_' . time() . '.' . $extension;
        $targetPath  = $uploadDir . $newFileName;

        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
            $this->settings->set($settingKey, 'uploads/branding/' . $newFileName, 'string');
        }
    }

    private function deleteBrandingFile(string $settingKey): void
    {
        $relativePath = $this->settings->get($settingKey, '');
        if ($relativePath === '') {
            return;
        }

        $physicalPath = ROOT_PATH . '/public/' . ltrim($relativePath, '/');
        if (file_exists($physicalPath) && is_file($physicalPath)) {
            @unlink($physicalPath);
        }

        $this->settings->set($settingKey, '', 'string');
    }

    private function settingsCategories(): array
    {
        return [
            'System' => ['system.timezone', 'system.currency', 'system.date_format'],
            'School' => ['school.name', 'school.short_name', 'school.motto'],
            'Theme'  => ['theme.primary', 'theme.accent', 'theme.dark_mode'],
        ];
    }
}