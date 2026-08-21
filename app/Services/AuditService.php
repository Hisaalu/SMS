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
    
    public function log($userId, $action, $module = 'general', $description = null)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        return $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getLogs($limit = 100, $offset = 0, $filters = [])
    {
        $sql = "SELECT al.*, u.username, u.email 
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['module'])) {
            $sql .= " AND al.module = ?";
            $params[] = $filters['module'];
        }
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getLogCount($filters = [])
    {
        $sql = "SELECT COUNT(*) as count FROM audit_logs al WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['module'])) {
            $sql .= " AND al.module = ?";
            $params[] = $filters['module'];
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }
}