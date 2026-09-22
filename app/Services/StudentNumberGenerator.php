<?php
// File: /app/Services/StudentNumberGenerator.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Throwable;

class StudentNumberGenerator
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function generate(int $schoolId, string $type = 'admission'): string
    {
        $setting = $this->db->fetch(
            "SELECT setting_value FROM settings WHERE setting_key = 'school.short_name' LIMIT 1"
        );
        $prefix = !empty($setting['setting_value'])
            ? strtoupper(trim($setting['setting_value']))
            : 'NYAK';

        $yearCode = date('y');
        $pattern = "{$prefix}/{$yearCode}/%";
        $column = $type === 'registration' ? 'registration_number' : 'admission_number';

        $nextNumber = 1;

        try {
            $lastRecord = $this->db->fetch(
                "SELECT {$column} FROM students
                 WHERE school_id = :school_id AND {$column} LIKE :pattern
                 ORDER BY id DESC LIMIT 1",
                ['school_id' => $schoolId, 'pattern' => $pattern]
            );

            if ($lastRecord && !empty($lastRecord[$column])) {
                $parts = explode('/', $lastRecord[$column]);
                $nextNumber = ((int)end($parts)) + 1;
            }
        } catch (Throwable $e) {
            $nextNumber = 1;
        }

        return sprintf('%s/%s/%03d', $prefix, $yearCode, $nextNumber);
    }
}