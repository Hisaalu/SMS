<?php
// File: /app/Services/GradingService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class GradingService
{
    private $db;
    private $gradingSystem;
    private $systemCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Load a grading system by ID
     */
    public function loadGradingSystem(int $systemId): self
    {
        if (isset($this->systemCache[$systemId])) {
            $this->gradingSystem = $this->systemCache[$systemId];
            return $this;
        }

        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $systemId]
        );

        if (!$system) {
            throw new \Exception("Grading system not found");
        }

        $system['rules'] = $this->getRules($systemId);
        $this->gradingSystem = $system;
        $this->systemCache[$systemId] = $system;

        return $this;
    }

    /**
     * Get rules for a grading system
     */
    public function getRules(int $systemId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM grading_rules WHERE system_id = :system_id ORDER BY min_mark DESC",
            ['system_id' => $systemId]
        );
    }

    /**
     * NEW: Get the grading system that applies to a specific class.
     * Priority:
     *   1. Class-specific grading system
     *   2. Class + Academic year specific grading system
     *   3. School default grading system
     *   4. Any active grading system
     */
    public function getSystemForClass(int $classId, int $schoolId, ?int $academicYearId = null): ?array
    {
        // 1. Try class-specific grading system (with academic year)
        if ($academicYearId) {
            $system = $this->db->fetch(
                "SELECT * FROM grading_systems 
                 WHERE school_id = :school_id 
                 AND class_id = :class_id 
                 AND academic_year_id = :academic_year_id 
                 AND (status = 'active' OR status IS NULL)
                 ORDER BY id DESC LIMIT 1",
                ['school_id' => $schoolId, 'class_id' => $classId, 'academic_year_id' => $academicYearId]
            );
            if ($system) {
                $system['rules'] = $this->getRules($system['id']);
                return $system;
            }
        }

        // 2. Try class-specific grading system (any year)
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems 
             WHERE school_id = :school_id 
             AND class_id = :class_id 
             AND (status = 'active' OR status IS NULL)
             ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId, 'class_id' => $classId]
        );
        if ($system) {
            $system['rules'] = $this->getRules($system['id']);
            return $system;
        }

        // 3. Fall back to school default
        return $this->getDefaultSystem($schoolId);
    }

    /**
     * Get default grading system for a school
     */
    public function getDefaultSystem(int $schoolId): ?array
    {
        // Try default first
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems 
             WHERE school_id = :school_id AND is_default = 1 
             ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );

        if (!$system) {
            // Any active system
            $system = $this->db->fetch(
                "SELECT * FROM grading_systems 
                 WHERE school_id = :school_id 
                 AND (status = 'active' OR status IS NULL)
                 ORDER BY id ASC LIMIT 1",
                ['school_id' => $schoolId]
            );
        }

        if (!$system) {
            // Absolute fallback (any system for this school)
            $system = $this->db->fetch(
                "SELECT * FROM grading_systems WHERE school_id = :school_id ORDER BY id ASC LIMIT 1",
                ['school_id' => $schoolId]
            );
        }

        if ($system) {
            $system['rules'] = $this->getRules($system['id']);
        }

        return $system;
    }

    /**
     * Get grade using a specific grading system ID
     */
    public function getGradeFromSystem(float $mark, int $systemId): ?array
    {
        $this->loadGradingSystem($systemId);
        return $this->getGrade($mark);
    }

    /**
     * NEW: Get grade for a mark in the context of a class
     * This is the method report cards should use.
     */
    public function getGradeForClass(float $mark, int $classId, int $schoolId, ?int $academicYearId = null): ?array
    {
        $system = $this->getSystemForClass($classId, $schoolId, $academicYearId);
        if (!$system) {
            return null;
        }
        return $this->findGrade($mark, $system['rules'] ?? []);
    }

    /**
     * Get grade for a mark (uses currently loaded system)
     */
    public function getGrade(float $mark, int $systemId = null): ?array
    {
        if ($systemId) {
            $this->loadGradingSystem($systemId);
        }

        if (empty($this->gradingSystem['rules'])) {
            return null;
        }

        return $this->findGrade($mark, $this->gradingSystem['rules']);
    }

    /**
     * Internal: find grade in a set of rules
     */
    private function findGrade(float $mark, array $rules): ?array
    {
        foreach ($rules as $rule) {
            if ($mark >= $rule['min_mark'] && $mark <= $rule['max_mark']) {
                return [
                    'grade' => $rule['grade'],
                    'description' => $rule['description'] ?? '',
                    'points' => $rule['points'] ?? 0,
                    'pass' => $rule['pass'] ?? false,
                    'min_mark' => $rule['min_mark'],
                    'max_mark' => $rule['max_mark'],
                ];
            }
        }
        return null;
    }
}