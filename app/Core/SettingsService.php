<?php
// File: /app/Core/SettingsService.php

namespace NexaT\Core;

use Throwable;

class SettingsService
{
    private Database $db;
    private array $cache = [];
    private bool $loaded = false;
    private ?array $themeCache = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->load();
    }

    public function get(string $key, $default = null)
    {
        return $this->cache[$key] ?? $default;
    }

    public function set(string $key, $value, string $type = 'string'): bool
    {
        $stored = $this->prepareValueForStorage($value);
        $now    = date('Y-m-d H:i:s');

        $updated = $this->db->update('settings', [
            'setting_value' => $stored,
            'setting_type'  => $type,
            'updated_at'    => $now,
        ], ['setting_key' => $key]);

        if ($updated === 0) {
            $existing = $this->db->fetch(
                "SELECT id FROM settings WHERE setting_key = ? LIMIT 1",
                [$key]
            );

            if (!$existing) {
                $this->db->insert('settings', [
                    'setting_key'   => $key,
                    'setting_value' => $stored,
                    'setting_type'  => $type,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        $this->cache[$key] = $this->castValue($stored, $type);
        $this->themeCache = null;

        return true;
    }

    public function getTheme(): array
    {
        if ($this->themeCache !== null) {
            return $this->themeCache;
        }

        return $this->themeCache = [
            'primary'    => $this->get('theme.primary',    '#0F172A'),
            'secondary'  => $this->get('theme.secondary',  '#141414'),
            'accent'     => $this->get('theme.accent',     '#2563EB'),
            'background' => $this->get('theme.background', '#F8FAFC'),
            'surface'    => $this->get('theme.surface',    '#FFFFFF'),
            'text'       => $this->get('theme.text',       '#334155'),
            'muted'      => $this->get('theme.muted',      '#64748B'),
            'border'     => $this->get('theme.border',     '#E2E8F0'),
            'success'    => $this->get('theme.success',    '#00BA7C'),
            'warning'    => $this->get('theme.warning',    '#FFD400'),
            'danger'     => $this->get('theme.danger',     '#F4212E'),

            'sidebar_bg'          => $this->get('theme.sidebar_bg',          $this->get('theme.surface', '#FFFFFF')),
            'sidebar_text'        => $this->get('theme.sidebar_text',        $this->get('theme.text', '#334155')),
            'sidebar_active_bg'   => $this->get('theme.sidebar_active_bg',   $this->get('theme.accent', '#2563EB')),
            'sidebar_active_text' => $this->get('theme.sidebar_active_text', '#FFFFFF'),
            'navbar_bg'           => $this->get('theme.navbar_bg',           $this->get('theme.surface', '#FFFFFF')),
            'navbar_text'         => $this->get('theme.navbar_text',         $this->get('theme.primary', '#0F172A')),

            'font_family'        => $this->get('theme.font_family',        'system'),
            'font_size_base'     => (float) $this->get('theme.font_size_base',     0.875),
            'font_size_small'    => (float) $this->get('theme.font_size_small',    0.75),
            'font_size_heading'  => (float) $this->get('theme.font_size_heading',  1.25),
            'line_height'        => (float) $this->get('theme.line_height',        1.5),
            'font_weight_normal' => (int)   $this->get('theme.font_weight_normal', 400),
            'font_weight_medium' => (int)   $this->get('theme.font_weight_medium', 500),
            'font_weight_bold'   => (int)   $this->get('theme.font_weight_bold',   700),

            'sidebar_width'  => (int)   $this->get('theme.sidebar_width', 270),
            'border_radius'  => (float) $this->get('theme.border_radius', 0.5),

            'dark_mode'           => (bool) $this->get('theme.dark_mode', false),
            'use_system_setting'  => (bool) $this->get('theme.use_system_setting', false),
        ];
    }

    public function all(): array
    {
        return $this->cache;
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        try {
            $rows = $this->db->fetchAll(
                "SELECT setting_key, setting_value, setting_type FROM settings"
            );

            foreach ($rows as $row) {
                $this->cache[$row['setting_key']] = $this->castValue(
                    $row['setting_value'],
                    $row['setting_type']
                );
            }
        } catch (Throwable $e) {
            $this->cache = [];
        }
    }

    private function castValue($value, string $type)
    {
        return match ($type) {
            'boolean', 'bool'  => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int'   => (int)$value,
            'decimal', 'float' => (float)$value,
            'json'             => json_decode((string)$value, true),
            default            => (string)$value,
        };
    }

    private function prepareValueForStorage($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string)$value;
    }
}