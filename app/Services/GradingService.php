<?php
// File: /app/Services/GradingService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class GradingService
{
    private $db;
    private $gradingSystem;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Load a grading system
     */
    public function loadGradingSystem(int $systemId): self
    {
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $systemId]
        );
        
        if (!$system) {
            throw new \Exception("Grading system not found");
        }
        
        $this->gradingSystem = $system;
        $this->gradingSystem['rules'] = $this->getRules($systemId);
        
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
     * Get default grading system for a school
     */
    public function getDefaultSystem(int $schoolId): ?array
    {
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE school_id = :school_id AND is_default = 1 LIMIT 1",
            ['school_id' => $schoolId]
        );
        
        if (!$system) {
            // Get first active system
            $system = $this->db->fetch(
                "SELECT * FROM grading_systems WHERE school_id = :school_id AND status = 'active' LIMIT 1",
                ['school_id' => $schoolId]
            );
        }
        
        return $system;
    }
    
    /**
     * Get grade for a mark
     */
    public function getGrade(float $mark, int $systemId = null): ?array
    {
        if ($systemId) {
            $this->loadGradingSystem($systemId);
        }
        
        if (!$this->gradingSystem || empty($this->gradingSystem['rules'])) {
            // Try to get default system
            $schoolId = 1; // Should be passed from context
            $system = $this->getDefaultSystem($schoolId);
            if ($system) {
                $this->loadGradingSystem($system['id']);
            }
        }
        
        if (empty($this->gradingSystem['rules'])) {
            return null;
        }
        
        foreach ($this->gradingSystem['rules'] as $rule) {
            if ($mark >= $rule['min_mark'] && $mark <= $rule['max_mark']) {
                return [
                    'grade' => $rule['grade'],
                    'description' => $rule['description'],
                    'points' => $rule['points'],
                    'pass' => $rule['pass'],
                    'min_mark' => $rule['min_mark'],
                    'max_mark' => $rule['max_mark']
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Get grade from configured system
     */
    public function getGradeFromSystem(float $mark, int $systemId): ?array
    {
        $this->loadGradingSystem($systemId);
        return $this->getGrade($mark);
    }
}