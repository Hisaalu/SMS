<?php
// File: /app/Services/AuditService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class AuditService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(int $userId, string $action, string $module, string $description = ''): void
    {
        try {
            $this->db->insert('audit_logs', [
                'user_id'     => $userId ?: null,
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'ip_address'  => $_SERVER['REMOTE_ADDR']     ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('[AuditService] Failed to write audit log: ' . $e->getMessage());
        }
    }

    public function getRecent(int $schoolId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT al.*, u.username, u.email
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE u.school_id = :school_id OR al.user_id IS NULL
             ORDER BY al.created_at DESC
             LIMIT {$limit}",
            ['school_id' => $schoolId]
        );
    }
}