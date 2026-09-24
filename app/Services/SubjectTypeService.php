<?php
// File: /app/Services/SubjectTypeService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class SubjectTypeService
{
    private Database $db;
    private array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getForSystem(int $gradingSystemId, bool $activeOnly = true): array
    {
        $key = $gradingSystemId . ':' . ($activeOnly ? '1' : '0');
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $sql = "SELECT * FROM grading_subject_types WHERE grading_system_id = :sys";
        if ($activeOnly) {
            $sql .= " AND status = 'active'";
        }
        $sql .= " ORDER BY display_order ASC, name ASC";

        return $this->cache[$key] = $this->db->fetchAll($sql, ['sys' => $gradingSystemId]) ?: [];
    }

    public function find(int $id, int $schoolId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM grading_subject_types WHERE id = :id AND school_id = :s",
            ['id' => $id, 's' => $schoolId]
        );

        return $row ?: null;
    }

    public function create(array $data, int $schoolId): int
    {
        $this->cache = [];

        return (int) $this->db->insert('grading_subject_types', [
            'grading_system_id'   => (int) $data['grading_system_id'],
            'school_id'           => $schoolId,
            'name'                => trim($data['name']),
            'code'                => strtoupper(trim($data['code'])),
            'is_graded'           => !empty($data['is_graded'])           ? 1 : 0,
            'is_subsidiary'       => !empty($data['is_subsidiary'])       ? 1 : 0,
            'subsidiary_pass_mark'=> isset($data['subsidiary_pass_mark']) ? (int)$data['subsidiary_pass_mark'] : null,
            'subsidiary_score'    => isset($data['subsidiary_score'])     ? (int)$data['subsidiary_score']     : 1,
            'display_order'       => (int) ($data['display_order'] ?? 0),
            'status'              => $data['status'] ?? 'active',
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(int $id, array $data, int $schoolId): bool
    {
        $this->cache = [];

        return $this->db->update('grading_subject_types', [
            'name'                 => trim($data['name']),
            'code'                 => strtoupper(trim($data['code'])),
            'is_graded'            => !empty($data['is_graded'])           ? 1 : 0,
            'is_subsidiary'        => !empty($data['is_subsidiary'])       ? 1 : 0,
            'subsidiary_pass_mark' => isset($data['subsidiary_pass_mark']) ? (int)$data['subsidiary_pass_mark'] : null,
            'subsidiary_score'     => isset($data['subsidiary_score'])     ? (int)$data['subsidiary_score']     : 1,
            'display_order'        => (int) ($data['display_order'] ?? 0),
            'status'               => $data['status'] ?? 'active',
            'updated_at'           => date('Y-m-d H:i:s'),
        ], ['id' => $id, 'school_id' => $schoolId]) !== false;
    }

    public function delete(int $id, int $schoolId): bool
    {
        $this->cache = [];

        return $this->db->delete('grading_subject_types', [
            'id'        => $id,
            'school_id' => $schoolId,
        ]) !== false;
    }
}