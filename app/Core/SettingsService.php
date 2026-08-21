<?php
// File: /app/Core/SettingsService.php

namespace NexaT\Core;

class SettingsService
{
    private $db;
    private $cache = [];
    private $settingsLoaded = false;
    private $themes = [];
    private $themeLoaded = false;
    
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
            default:
                return (string)$value;
        }
    }
    
    public function get(string $key, $default = null)
    {
        return $this->cache[$key] ?? $default;
    }
    
    public function set(string $key, $value, string $type = 'string'): bool
    {
        $value = $this->prepareValueForStorage($value);
        
        $result = $this->db->update('settings', [
            'setting_value' => $value,
            'setting_type' => $type,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['setting_key' => $key]);
        
        if ($result === 0) {
            $result = $this->db->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        if ($result !== false) {
            $this->cache[$key] = $value;
            return true;
        }
        
        return false;
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
    
    public function getTheme(): array
    {
        if ($this->themeLoaded) {
            return $this->themes;
        }
        
        $this->themes = [
            'primary' => $this->get('theme.primary', '#000000'),
            'secondary' => $this->get('theme.secondary', '#141414'),
            'accent' => $this->get('theme.accent', '#1D9BF0'),
            'background' => $this->get('theme.background', '#FFFFFF'),
            'surface' => $this->get('theme.surface', '#F7F9F9'),
            'text' => $this->get('theme.text', '#0F1419'),
            'muted' => $this->get('theme.muted', '#536471'),
            'border' => $this->get('theme.border', '#EFF3F4'),
            'success' => $this->get('theme.success', '#00BA7C'),
            'warning' => $this->get('theme.warning', '#FFD400'),
            'danger' => $this->get('theme.danger', '#F4212E'),
            'dark_mode' => $this->get('theme.dark_mode', false),
        ];
        
        $this->themeLoaded = true;
        return $this->themes;
    }
}