<?php
// File: /app/Services/DivisionService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class DivisionService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all divisions for a school, ordered by display_order.
     */
    public function getAll(int $schoolId, bool $activeOnly = false): array
    {
        $sql = "SELECT * FROM grading_divisions WHERE school_id = :s";
        if ($activeOnly) {
            $sql .= " AND status = 'active'";
        }
        $sql .= " ORDER BY display_order ASC, min_aggregate ASC";

        return $this->db->fetchAll($sql, ['s' => $schoolId]);
    }

    /**
     * Find the division for a given aggregate score.
     */
    public function getDivisionForAggregate(float $aggregate, int $schoolId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM grading_divisions 
             WHERE school_id = :s 
             AND :agg BETWEEN min_aggregate AND max_aggregate 
             AND status = 'active'
             ORDER BY display_order ASC LIMIT 1",
            ['s' => $schoolId, 'agg' => $aggregate]
        ) ?: null;
    }

    public function find(int $id, int $schoolId): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM grading_divisions WHERE id = :id AND school_id = :s",
            ['id' => $id, 's' => $schoolId]
        ) ?: null;
    }

    public function create(array $data, int $schoolId): int
    {
        return $this->db->insert('grading_divisions', [
            'school_id'     => $schoolId,
            'name'          => trim($data['name']),
            'code'          => strtoupper(trim($data['code'])),
            'min_aggregate' => (int)$data['min_aggregate'],
            'max_aggregate' => (int)$data['max_aggregate'],
            'description'   => trim($data['description'] ?? ''),
            'display_order' => (int)($data['display_order'] ?? 0),
            'status'        => $data['status'] ?? 'active',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(int $id, array $data, int $schoolId): bool
    {
        return $this->db->update('grading_divisions', [
            'name'          => trim($data['name']),
            'code'          => strtoupper(trim($data['code'])),
            'min_aggregate' => (int)$data['min_aggregate'],
            'max_aggregate' => (int)$data['max_aggregate'],
            'description'   => trim($data['description'] ?? ''),
            'display_order' => (int)($data['display_order'] ?? 0),
            'status'        => $data['status'] ?? 'active',
            'updated_at'    => date('Y-m-d H:i:s'),
        ], ['id' => $id, 'school_id' => $schoolId]) !== false;
    }

    public function delete(int $id, int $schoolId): bool
    {
        return $this->db->delete('grading_divisions', [
            'id' => $id, 'school_id' => $schoolId
        ]) !== false;
    }

    /**
     * Check for overlapping aggregate ranges.
     */
    public function hasOverlap(int $min, int $max, int $schoolId, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM grading_divisions 
                WHERE school_id = :s 
                AND min_aggregate <= :max 
                AND max_aggregate >= :min";
        $params = ['s' => $schoolId, 'min' => $min, 'max' => $max];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (bool)$this->db->fetch($sql, $params);
    }
}