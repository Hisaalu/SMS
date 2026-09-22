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

    private ?array $cachedPermissions = null;

    public function users(): array
    {
        return $this->db->fetchAll(
            "SELECT u.* FROM users u
             INNER JOIN user_roles ur ON u.id = ur.user_id
             WHERE ur.role_id = ?",
            [$this->id]
        );
    }

    public function permissions(): array
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }

        return $this->cachedPermissions = $this->db->fetchAll(
            "SELECT p.* FROM permissions p
             INNER JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = ?",
            [$this->id]
        );
    }

    public function hasPermission(string $permissionSlug): bool
    {
        $row = $this->db->fetch(
            "SELECT 1 AS found FROM role_permissions rp
             INNER JOIN permissions p ON rp.permission_id = p.id
             WHERE rp.role_id = ? AND p.slug = ?
             LIMIT 1",
            [$this->id, $permissionSlug]
        );

        return $row !== null;
    }

    public function assignPermission(int $permissionId): bool
    {
        $existing = $this->db->fetch(
            "SELECT 1 AS found FROM role_permissions
             WHERE role_id = ? AND permission_id = ? LIMIT 1",
            [$this->id, $permissionId]
        );

        if ($existing) {
            return false;
        }

        $this->cachedPermissions = null;

        return (bool)$this->db->insert('role_permissions', [
            'role_id'       => $this->id,
            'permission_id' => $permissionId,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function removePermission(int $permissionId): bool
    {
        $this->cachedPermissions = null;

        return $this->db->delete('role_permissions', [
            'role_id'       => $this->id,
            'permission_id' => $permissionId,
        ]);
    }

    public function syncPermissions(array $permissionIds): bool
    {
        $this->db->delete('role_permissions', ['role_id' => $this->id]);
        $this->cachedPermissions = null;

        if (empty($permissionIds)) {
            return true;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($permissionIds as $permissionId) {
            $this->db->insert('role_permissions', [
                'role_id'       => $this->id,
                'permission_id' => (int)$permissionId,
                'created_at'    => $now,
            ]);
        }

        return true;
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === 'super_admin';
    }
}