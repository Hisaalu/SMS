<?php
// File: /app/Services/GradingService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Exception;

class GradingService
{
    private Database $db;
    private array $gradingSystem = [];
    private array $systemCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

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
            throw new Exception('Grading system not found');
        }

        $system['rules'] = $this->getRules($systemId);
        $this->gradingSystem = $system;
        $this->systemCache[$systemId] = $system;

        return $this;
    }

    public function getRules(int $systemId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM grading_rules WHERE system_id = :system_id ORDER BY min_mark DESC",
            ['system_id' => $systemId]
        );
    }

    public function getSystemForClass(int $classId, int $schoolId, ?int $academicYearId = null): ?array
    {
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

        return $this->getDefaultSystem($schoolId);
    }

    public function getDefaultSystem(int $schoolId): ?array
    {
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems
             WHERE school_id = :school_id AND is_default = 1
             ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );

        if (!$system) {
            $system = $this->db->fetch(
                "SELECT * FROM grading_systems
                 WHERE school_id = :school_id
                   AND (status = 'active' OR status IS NULL)
                 ORDER BY id ASC LIMIT 1",
                ['school_id' => $schoolId]
            );
        }

        if (!$system) {
            $system = $this->db->fetch(
                "SELECT * FROM grading_systems
                 WHERE school_id = :school_id
                 ORDER BY id ASC LIMIT 1",
                ['school_id' => $schoolId]
            );
        }

        if ($system) {
            $system['rules'] = $this->getRules($system['id']);
        }

        return $system ?: null;
    }

    public function getGradeFromSystem(float $mark, int $systemId): ?array
    {
        $this->loadGradingSystem($systemId);
        return $this->getGrade($mark);
    }

    public function getGradeForClass(float $mark, int $classId, int $schoolId, ?int $academicYearId = null): ?array
    {
        $system = $this->getSystemForClass($classId, $schoolId, $academicYearId);
        if (!$system) {
            return null;
        }
        return $this->findGrade($mark, $system['rules'] ?? []);
    }

    public function getGrade(float $mark, ?int $systemId = null): ?array
    {
        if ($systemId) {
            $this->loadGradingSystem($systemId);
        }

        if (empty($this->gradingSystem['rules'])) {
            return null;
        }

        return $this->findGrade($mark, $this->gradingSystem['rules']);
    }

    public function getDivisionForAggregate(float $aggregate, int $schoolId): ?array
    {
        return (new DivisionService())->getDivisionForAggregate($aggregate, $schoolId);
    }

    private function findGrade(float $mark, array $rules): ?array
    {
        foreach ($rules as $rule) {
            if ($mark >= (float)$rule['min_mark'] && $mark <= (float)$rule['max_mark']) {
                return [
                    'grade'       => $rule['grade'],
                    'description' => $rule['description'] ?? '',
                    'score'       => $rule['score'] ?? 0,
                    'pass'        => (bool)($rule['pass'] ?? false),
                    'min_mark'    => $rule['min_mark'],
                    'max_mark'    => $rule['max_mark'],
                ];
            }
        }
        return null;
    }
}