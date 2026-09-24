<?php
// File: /app/Services/DivisionSchemeService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class DivisionSchemeService
{
    private Database $db;
    private array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(int $schoolId, bool $activeOnly = false): array
    {
        $key = $schoolId . ':' . ($activeOnly ? '1' : '0');
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $sql = "SELECT ds.*,
                       gs.name AS system_name,
                       gs.class_id,
                       c.name  AS class_name,
                       (SELECT COUNT(*) FROM grading_divisions gd WHERE gd.scheme_id = ds.id) AS range_count
                FROM division_schemes ds
                INNER JOIN grading_systems gs ON ds.grading_system_id = gs.id
                LEFT  JOIN classes c          ON gs.class_id = c.id
                WHERE ds.school_id = :s";

        if ($activeOnly) {
            $sql .= " AND ds.status = 'active'";
        }

        $sql .= " ORDER BY ds.display_order ASC, ds.name ASC";

        $rows = $this->db->fetchAll($sql, ['s' => $schoolId]);

        return $this->cache[$key] = $rows ?: [];
    }

    public function getForSystem(int $gradingSystemId, int $schoolId, bool $activeOnly = true): array
    {
        $sql = "SELECT ds.*,
                       (SELECT COUNT(*) FROM grading_divisions gd WHERE gd.scheme_id = ds.id) AS range_count
                FROM division_schemes ds
                WHERE ds.grading_system_id = :sys AND ds.school_id = :s";

        if ($activeOnly) {
            $sql .= " AND ds.status = 'active'";
        }

        $sql .= " ORDER BY ds.display_order ASC, ds.name ASC";

        return $this->db->fetchAll($sql, ['sys' => $gradingSystemId, 's' => $schoolId]) ?: [];
    }

    public function find(int $id, int $schoolId): ?array
    {
        $row = $this->db->fetch(
            "SELECT ds.*,
                    gs.name AS system_name,
                    gs.class_id,
                    c.name  AS class_name
             FROM division_schemes ds
             INNER JOIN grading_systems gs ON ds.grading_system_id = gs.id
             LEFT  JOIN classes c          ON gs.class_id = c.id
             WHERE ds.id = :id AND ds.school_id = :s",
            ['id' => $id, 's' => $schoolId]
        );

        return $row ?: null;
    }

    public function create(array $data, int $schoolId): int
    {
        $this->cache = [];

        return (int) $this->db->insert('division_schemes', [
            'school_id'         => $schoolId,
            'grading_system_id' => (int) $data['grading_system_id'],
            'name'              => trim($data['name']),
            'description'       => trim($data['description'] ?? ''),
            'display_order'     => (int) ($data['display_order'] ?? 0),
            'status'            => $data['status'] ?? 'active',
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(int $id, array $data, int $schoolId): bool
    {
        $this->cache = [];

        return $this->db->update('division_schemes', [
            'grading_system_id' => (int) $data['grading_system_id'],
            'name'              => trim($data['name']),
            'description'       => trim($data['description'] ?? ''),
            'display_order'     => (int) ($data['display_order'] ?? 0),
            'status'            => $data['status'] ?? 'active',
            'updated_at'        => date('Y-m-d H:i:s'),
        ], ['id' => $id, 'school_id' => $schoolId]) !== false;
    }

    public function delete(int $id, int $schoolId): bool
    {
        $this->db->execute(
            "DELETE FROM grading_divisions WHERE scheme_id = :id",
            ['id' => $id]
        );

        $this->cache = [];

        return $this->db->delete('division_schemes', [
            'id'        => $id,
            'school_id' => $schoolId,
        ]) !== false;
    }
}