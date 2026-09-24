<?php
// File: /app/Services/DivisionService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class DivisionService
{
    private Database $db;
    private array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getForScheme(int $schemeId, bool $activeOnly = false): array
    {
        $key = 'scheme:' . $schemeId . ':' . ($activeOnly ? '1' : '0');
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $sql = "SELECT * FROM grading_divisions WHERE scheme_id = :id";
        if ($activeOnly) {
            $sql .= " AND status = 'active'";
        }
        $sql .= " ORDER BY display_order ASC, min_aggregate ASC";

        return $this->cache[$key] = $this->db->fetchAll($sql, ['id' => $schemeId]) ?: [];
    }

    public function getDivisionForAggregate(float $aggregate, ?int $schemeId): ?array
    {
        if (empty($schemeId)) {
            return null;
        }

        $row = $this->db->fetch(
            "SELECT * FROM grading_divisions
             WHERE scheme_id = :scheme_id
               AND status = 'active'
               AND :agg BETWEEN min_aggregate AND max_aggregate
             ORDER BY display_order ASC LIMIT 1",
            ['scheme_id' => $schemeId, 'agg' => $aggregate]
        );

        return $row ?: null;
    }

    public function find(int $id, int $schoolId): ?array
    {
        $row = $this->db->fetch(
            "SELECT gd.*
             FROM grading_divisions gd
             INNER JOIN division_schemes ds ON gd.scheme_id = ds.id
             WHERE gd.id = :id AND ds.school_id = :s",
            ['id' => $id, 's' => $schoolId]
        );

        return $row ?: null;
    }

    public function create(array $data, int $schoolId): int
    {
        $schemeId = (int) ($data['scheme_id'] ?? 0);

        $scheme = $this->db->fetch(
            "SELECT id FROM division_schemes WHERE id = :id AND school_id = :s",
            ['id' => $schemeId, 's' => $schoolId]
        );
        if (!$scheme) {
            return 0;
        }

        $this->cache = [];

        return (int) $this->db->insert('grading_divisions', [
            'scheme_id'     => $schemeId,
            'name'          => trim($data['name']),
            'code'          => strtoupper(trim($data['code'])),
            'min_aggregate' => (int) $data['min_aggregate'],
            'max_aggregate' => (int) $data['max_aggregate'],
            'description'   => trim($data['description'] ?? ''),
            'display_order' => (int) ($data['display_order'] ?? 0),
            'status'        => $data['status'] ?? 'active',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(int $id, array $data, int $schoolId): bool
    {
        $range = $this->find($id, $schoolId);
        if (!$range) {
            return false;
        }

        $this->cache = [];

        return $this->db->update('grading_divisions', [
            'name'          => trim($data['name']),
            'code'          => strtoupper(trim($data['code'])),
            'min_aggregate' => (int) $data['min_aggregate'],
            'max_aggregate' => (int) $data['max_aggregate'],
            'description'   => trim($data['description'] ?? ''),
            'display_order' => (int) ($data['display_order'] ?? 0),
            'status'        => $data['status'] ?? 'active',
            'updated_at'    => date('Y-m-d H:i:s'),
        ], ['id' => $id]) !== false;
    }

    public function delete(int $id, int $schoolId): bool
    {
        $range = $this->find($id, $schoolId);
        if (!$range) {
            return false;
        }

        $this->cache = [];

        return $this->db->delete('grading_divisions', ['id' => $id]) !== false;
    }

    public function hasOverlap(int $schemeId, int $min, int $max, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM grading_divisions
                WHERE scheme_id = :scheme_id
                  AND min_aggregate <= :max
                  AND max_aggregate >= :min";
        $params = ['scheme_id' => $schemeId, 'min' => $min, 'max' => $max];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        return (bool) $this->db->fetch($sql, $params);
    }
}