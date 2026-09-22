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
            'primary'    => $this->get('theme.primary',    '#000000'),
            'secondary'  => $this->get('theme.secondary',  '#141414'),
            'accent'     => $this->get('theme.accent',     '#1D9BF0'),
            'background' => $this->get('theme.background', '#FFFFFF'),
            'surface'    => $this->get('theme.surface',    '#F7F9F9'),
            'text'       => $this->get('theme.text',       '#0F1419'),
            'muted'      => $this->get('theme.muted',      '#536471'),
            'border'     => $this->get('theme.border',     '#EFF3F4'),
            'success'    => $this->get('theme.success',    '#00BA7C'),
            'warning'    => $this->get('theme.warning',    '#FFD400'),
            'danger'     => $this->get('theme.danger',     '#F4212E'),
            'dark_mode'  => $this->get('theme.dark_mode',  false),
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
            'boolean', 'bool'      => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int'       => (int)$value,
            'decimal', 'float'     => (float)$value,
            'json'                 => json_decode((string)$value, true),
            default                => (string)$value,
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