<?php
// File: /app/Services/SettingsService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class SettingsService
{
    private $db;
    private $cache = [];
    private $settingsLoaded = false;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }
    
    private function loadSettings(): void
    {
        if ($this->settingsLoaded) {
            return;
        }
        
        try {
            $results = $this->db->fetchAll("SELECT setting_key, setting_value, setting_type FROM settings");
            
            foreach ($results as $row) {
                $this->cache[$row['setting_key']] = $this->castValue(
                    $row['setting_value'],
                    $row['setting_type']
                );
            }
        } catch (\Exception $e) {
            $this->cache = [];
        }
        
        $this->settingsLoaded = true;
    }
    
    private function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
            case 'int':
                return (int)$value;
            case 'decimal':
            case 'float':
                return (float)$value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return explode(',', $value);
            default:
                return (string)$value;
        }
    }
    
    private function prepareValueForStorage($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return (string)$value;
    }
    
    public function get(string $key, $default = null)
    {
        return $this->cache[$key] ?? $default;
    }
    
    public function set(string $key, $value, string $type = 'string', string $category = 'general', string $description = ''): bool
    {
        $value = $this->prepareValueForStorage($value);
        
        $existing = $this->db->fetch(
            "SELECT id FROM settings WHERE setting_key = ?",
            [$key]
        );
        
        if ($existing) {
            $result = $this->db->update('settings', [
                'setting_value' => $value,
                'setting_type' => $type,
                'category' => $category,
                'description' => $description,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['setting_key' => $key]);
        } else {
            $result = $this->db->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => $type,
                'category' => $category,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        if ($result !== false) {
            $this->cache[$key] = $this->castValue($value, $type);
            return true;
        }
        
        return false;
    }
    
    public function getCategory(string $category): array
    {
        $result = [];
        foreach ($this->cache as $key => $value) {
            // Check category by looking up the setting
            $categoryCheck = $this->db->fetch(
                "SELECT category FROM settings WHERE setting_key = ?",
                [$key]
            );
            if ($categoryCheck && $categoryCheck['category'] === $category) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    
    public function getAll(): array
    {
        return $this->cache;
    }
}