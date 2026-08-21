<?php
// File: /app/Models/Role.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'slug', 'description', 'status'];
    protected $guarded = ['id'];
    protected $timestamps = true;
    
    public function users()
    {
        $db = $this->db;
        $sql = "SELECT u.* FROM users u 
                INNER JOIN user_roles ur ON u.id = ur.user_id 
                WHERE ur.role_id = ?";
        return $db->fetchAll($sql, [$this->id]);
    }
    
    public function permissions()
    {
        $db = $this->db;
        $sql = "SELECT p.* FROM permissions p 
                INNER JOIN role_permissions rp ON p.id = rp.permission_id 
                WHERE rp.role_id = ?";
        return $db->fetchAll($sql, [$this->id]);
    }
    
    public function hasPermission($permissionSlug)
    {
        $db = $this->db;
        $sql = "SELECT COUNT(*) as count FROM role_permissions rp 
                INNER JOIN permissions p ON rp.permission_id = p.id 
                WHERE rp.role_id = ? AND p.slug = ?";
        $result = $db->fetch($sql, [$this->id, $permissionSlug]);
        return $result['count'] > 0;
    }
    
    public function assignPermission($permissionId)
    {
        $db = $this->db;
        $existing = $db->fetch(
            "SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?",
            [$this->id, $permissionId]
        );
        
        if (!$existing) {
            return $db->insert('role_permissions', [
                'role_id' => $this->id,
                'permission_id' => $permissionId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return false;
    }
    
    public function removePermission($permissionId)
    {
        $db = $this->db;
        return $db->delete('role_permissions', [
            'role_id' => $this->id,
            'permission_id' => $permissionId
        ]);
    }
    
    public function syncPermissions(array $permissionIds)
    {
        $db = $this->db;
        $db->delete('role_permissions', ['role_id' => $this->id]);
        
        foreach ($permissionIds as $permissionId) {
            $db->insert('role_permissions', [
                'role_id' => $this->id,
                'permission_id' => $permissionId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return true;
    }
    
    public function isSuperAdmin()
    {
        return $this->slug === 'super_admin';
    }
}