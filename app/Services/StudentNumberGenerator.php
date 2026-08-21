<?php
// File: /app/Services/StudentNumberGenerator.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Exception;

class StudentNumberGenerator
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generates sequential numbers in the format: SHORTNAME/YY/SEQUENTIAL_NO
     * Example: NYAK/26/001
     */
    public function generate(int $schoolId, string $type = 'admission'): string
    {
        // 1. Fetch short_name from settings table
        $setting = $this->db->fetch(
            "SELECT setting_value FROM settings WHERE setting_key = 'school.short_name' LIMIT 1"
        );
        $prefix = !empty($setting['setting_value']) ? strtoupper(trim($setting['setting_value'])) : 'NYAK';

        // 2. Format 2-digit year (e.g., 2026 -> 26)
        $yearCode = date('y');
        $pattern = "{$prefix}/{$yearCode}/%";
        $column = ($type === 'registration') ? 'registration_number' : 'admission_number';

        $nextNumber = 1;

        try {
            // 3. Fetch latest record matching the current pattern
            $lastRecord = $this->db->fetch(
                "SELECT {$column} FROM students 
                 WHERE school_id = :school_id AND {$column} LIKE :pattern 
                 ORDER BY id DESC LIMIT 1",
                [
                    'school_id' => $schoolId,
                    'pattern'   => $pattern
                ]
            );

            if ($lastRecord && !empty($lastRecord[$column])) {
                $parts = explode('/', $lastRecord[$column]);
                $lastSeq = (int) end($parts);
                $nextNumber = $lastSeq + 1;
            }
        } catch (Exception $e) {
            // Fall back to 1 if column or table state is uninitialized
            $nextNumber = 1;
        }

        // 4. Zero-pad sequential number to 3 digits (001, 002, etc.)
        $sequenceStr = str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);

        return "{$prefix}/{$yearCode}/{$sequenceStr}";
    }
}