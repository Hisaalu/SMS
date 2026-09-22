<?php
// File: /app/Services/SettingsService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Throwable;

class SettingsService
{
    private Database $db;
    private array $cache = [];
    private array $categoryIndex = [];
    private bool $loaded = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->load();
    }

    public function get(string $key, $default = null)
    {
        return $this->cache[$key] ?? $default;
    }

    public function set(
        string $key,
        $value,
        string $type = 'string',
        string $category = 'general',
        string $description = ''
    ): bool {
        $stored = $this->prepareValueForStorage($value);
        $now = date('Y-m-d H:i:s');

        $existing = $this->db->fetch(
            "SELECT id, category FROM settings WHERE setting_key = ? LIMIT 1",
            [$key]
        );

        try {
            if ($existing) {
                $this->db->update('settings', [
                    'setting_value' => $stored,
                    'setting_type'  => $type,
                    'category'      => $category,
                    'description'   => $description,
                    'updated_at'    => $now,
                ], ['setting_key' => $key]);

                if ($existing['category'] !== $category) {
                    $this->reindexCategory($key, $existing['category'], $category);
                }
            } else {
                $this->db->insert('settings', [
                    'setting_key'   => $key,
                    'setting_value' => $stored,
                    'setting_type'  => $type,
                    'category'      => $category,
                    'description'   => $description,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);

                $this->categoryIndex[$category][] = $key;
            }
        } catch (Throwable $e) {
            return false;
        }

        $this->cache[$key] = $this->castValue($stored, $type);

        return true;
    }

    public function getCategory(string $category): array
    {
        $result = [];

        foreach ($this->categoryIndex[$category] ?? [] as $key) {
            $result[$key] = $this->cache[$key];
        }

        return $result;
    }

    public function getAll(): array
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
                "SELECT setting_key, setting_value, setting_type, category FROM settings"
            );

            foreach ($rows as $row) {
                $this->cache[$row['setting_key']] = $this->castValue(
                    $row['setting_value'],
                    $row['setting_type']
                );

                $this->categoryIndex[$row['category'] ?? 'general'][] = $row['setting_key'];
            }
        } catch (Throwable $e) {
            $this->cache = [];
            $this->categoryIndex = [];
        }
    }

    private function reindexCategory(string $key, string $oldCategory, string $newCategory): void
    {
        $this->categoryIndex[$oldCategory] = array_values(array_filter(
            $this->categoryIndex[$oldCategory] ?? [],
            fn($existing) => $existing !== $key
        ));

        $this->categoryIndex[$newCategory][] = $key;
    }

    private function castValue($value, string $type)
    {
        return match ($type) {
            'boolean', 'bool'  => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int'   => (int)$value,
            'decimal', 'float' => (float)$value,
            'json'             => json_decode((string)$value, true),
            'array'            => is_array($value) ? $value : explode(',', (string)$value),
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