<?php
// File: /app/Models/User.php

namespace NexaT\Models;

use NexaT\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['school_id', 'username', 'email', 'password', 'first_name', 'last_name', 'status'];
    protected $guarded = ['id'];
    protected $timestamps = true;
    private $cachedRoles = null;
    private $cachedPermissions = null;
    
    /**
     * Override all() to always filter by school_id
     */
    public static function all(array $where = [], array $order = [], int $limit = null): array
    {
        $instance = new static();
        
        // Always add school_id filter
        $where['school_id'] = $instance->schoolId;
        
        return parent::all($where, $order, $limit);
    }
    
    /**
     * Override find() to ensure school isolation
     */
    public static function find(int $id): ?self
    {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ? AND school_id = ?",
            [$id, $instance->schoolId]
        );
        
        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    /**
     * Override firstWhere() to ensure school isolation
     */
    public static function firstWhere(string $field, $value): ?self
    {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$field} = ? AND school_id = ? LIMIT 1",
            [$value, $instance->schoolId]
        );
        
        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    public function roles()
    {
        if ($this->cachedRoles !== null) {
            return $this->cachedRoles;
        }
        
        $db = $this->db;
        $sql = "SELECT r.* FROM roles r 
                INNER JOIN user_roles ur ON r.id = ur.role_id 
                WHERE ur.user_id = ?";
        $this->cachedRoles = $db->fetchAll($sql, [$this->id]);
        return $this->cachedRoles;
    }
    
    public function permissions()
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }
        
        $db = $this->db;
        $sql = "SELECT DISTINCT p.* FROM permissions p 
                INNER JOIN role_permissions rp ON p.id = rp.permission_id 
                INNER JOIN user_roles ur ON rp.role_id = ur.role_id 
                WHERE ur.user_id = ?";
        $this->cachedPermissions = $db->fetchAll($sql, [$this->id]);
        return $this->cachedPermissions;
    }
    
    public function hasRole($roleSlug)
    {
        $roles = $this->roles();
        foreach ($roles as $role) {
            if ($role['slug'] === $roleSlug) {
                return true;
            }
        }
        return false;
    }
    
    public function hasPermission($permissionSlug)
    {
        // FIRST: Check if user has super_admin role - bypass all checks
        $roles = $this->roles();
        foreach ($roles as $role) {
            if ($role['slug'] === 'super_admin') {
                return true;
            }
        }
        
        // THEN: Check specific permissions
        $permissions = $this->permissions();
        foreach ($permissions as $permission) {
            if ($permission['slug'] === $permissionSlug) {
                return true;
            }
        }
        
        return false;
    }
    
    public function can($permissionSlug)
    {
        return $this->hasPermission($permissionSlug);
    }
    
    public function assignRole($roleId)
    {
        $db = $this->db;
        $existing = $db->fetch(
            "SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$this->id, $roleId]
        );
        
        if (!$existing) {
            $this->clearCache();
            return $db->insert('user_roles', [
                'user_id' => $this->id,
                'role_id' => $roleId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return false;
    }
    
    public function removeRole($roleId)
    {
        $db = $this->db;
        $this->clearCache();
        return $db->delete('user_roles', [
            'user_id' => $this->id,
            'role_id' => $roleId
        ]);
    }
    
    public function syncRoles(array $roleIds)
    {
        $db = $this->db;
        $db->delete('user_roles', ['user_id' => $this->id]);
        $this->clearCache();
        
        foreach ($roleIds as $roleId) {
            $db->insert('user_roles', [
                'user_id' => $this->id,
                'role_id' => $roleId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return true;
    }
    
    private function clearCache()
    {
        $this->cachedRoles = null;
        $this->cachedPermissions = null;
    }
    
    public function isSuperAdmin()
    {
        $roles = $this->roles();
        foreach ($roles as $role) {
            if ($role['slug'] === 'super_admin') {
                return true;
            }
        }
        return false;
    }
    
    public function reloadPermissions()
    {
        $this->cachedRoles = null;
        $this->cachedPermissions = null;
        return $this;
    }
    
    /**
     * Get users from the same school only
     */
    public static function getSchoolUsers(): array
    {
        $instance = new static();
        return $instance->db->fetchAll(
            "SELECT * FROM users WHERE school_id = ? ORDER BY created_at DESC",
            [$instance->schoolId]
        );
    }
}