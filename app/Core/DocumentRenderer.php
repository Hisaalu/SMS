<?php
// File: /app/Core/DocumentRenderer.php

namespace NexaT\Core;

class DocumentRenderer
{
    public static function schoolProfile(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $settings = new SettingsService();

        return $cache = [
            'name'         => (string) $settings->get('school.name', ''),
            'short_name'   => (string) $settings->get('school.short_name', ''),
            'motto'        => (string) $settings->get('school.motto', ''),
            'slogan'       => (string) $settings->get('school.slogan', ''),
            'description'  => (string) $settings->get('school.description', ''),
            'type'         => (string) $settings->get('school.type', ''),
            'registration' => (string) $settings->get('school.registration_number', ''),
            'address'      => (string) $settings->get('school.physical_address', ''),
            'postal'       => (string) $settings->get('school.postal_address', ''),
            'po_box'       => (string) $settings->get('school.po_box', ''),
            'telephone'    => (string) $settings->get('school.telephone', ''),
            'email'        => (string) $settings->get('school.email', ''),
            'website'      => (string) $settings->get('school.website', ''),
            'logo'         => (string) $settings->get('branding.logo', ''),
            'report_logo'  => (string) $settings->get('branding.report_logo', ''),
            'stamp'        => (string) $settings->get('branding.school_stamp', ''),
            'show_powered' => (bool)   $settings->get('branding.show_nexat', false),
        ];
    }

    public static function resolveUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim($path, '/');

        if (is_file(ROOT_PATH . '/public/' . $clean) && !str_contains(BASE_URL, '/public')) {
            return rtrim(BASE_URL, '/') . '/public/' . $clean;
        }

        return rtrim(BASE_URL, '/') . '/' . $clean;
    }

    public static function logoExists(string $path): bool
    {
        if ($path === '') {
            return false;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return true;
        }
        $clean = ltrim($path, '/');

        return is_file(ROOT_PATH . '/public/' . $clean) || is_file(ROOT_PATH . '/' . $clean);
    }
}