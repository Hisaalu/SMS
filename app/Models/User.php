<?php
// File: /app/Models/User.php

namespace NexaT\Models;

use NexaT\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'username', 'email', 'password',
        'first_name', 'last_name', 'status',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    private $cachedRoles = null;
    private $cachedPermissions = null;

    protected function currentSchoolId(): ?int
    {
        if (!empty($this->schoolId)) {
            return (int)$this->schoolId;
        }

        if (!empty($_SESSION['school_id'])) {
            return (int)$_SESSION['school_id'];
        }

        return null;
    }

    public static function find(int $id): ?static
    {
        $instance = new static();
        $schoolId = $instance->currentSchoolId();

        if ($schoolId !== null) {
            $result = $instance->db->fetch(
                "SELECT * FROM {$instance->table}
                 WHERE {$instance->primaryKey} = ? AND school_id = ?",
                [$id, $schoolId]
            );
        } else {
            $result = $instance->db->fetch(
                "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
                [$id]
            );
        }

        return $instance->hydrate($result);
    }

    public static function firstWhere(string $field, $value): ?static
    {
        $instance = new static();
        $schoolId = $instance->currentSchoolId();

        if ($schoolId !== null) {
            $result = $instance->db->fetch(
                "SELECT * FROM {$instance->table}
                 WHERE {$field} = ? AND school_id = ? LIMIT 1",
                [$value, $schoolId]
            );
        } else {
            $result = $instance->db->fetch(
                "SELECT * FROM {$instance->table} WHERE {$field} = ? LIMIT 1",
                [$value]
            );
        }

        return $instance->hydrate($result);
    }

    public static function all(array $where = [], array $order = [], ?int $limit = null): array
    {
        $instance = new static();
        $schoolId = $instance->currentSchoolId();

        if ($schoolId !== null) {
            $where['school_id'] = $schoolId;
        }

        return parent::all($where, $order, $limit);
    }

    public static function findWithoutScope(int $id): ?static
    {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
            [$id]
        );

        return $instance->hydrate($result);
    }

    public static function findByEmailGlobal(string $email): ?static
    {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE email = ? LIMIT 1",
            [$email]
        );

        return $instance->hydrate($result);
    }

    public function roles(): array
    {
        if ($this->cachedRoles !== null) {
            return $this->cachedRoles;
        }

        return $this->cachedRoles = $this->db->fetchAll(
            "SELECT r.* FROM roles r
             INNER JOIN user_roles ur ON r.id = ur.role_id
             WHERE ur.user_id = ?",
            [$this->id]
        );
    }

    public function permissions(): array
    {
        if ($this->cachedPermissions !== null) {
            return $this->cachedPermissions;
        }

        return $this->cachedPermissions = $this->db->fetchAll(
            "SELECT DISTINCT p.* FROM permissions p
             INNER JOIN role_permissions rp ON p.id = rp.permission_id
             INNER JOIN user_roles ur ON rp.role_id = ur.role_id
             WHERE ur.user_id = ?",
            [$this->id]
        );
    }

    public function hasRole(string $roleSlug): bool
    {
        foreach ($this->roles() as $role) {
            if ($role['slug'] === $roleSlug) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        foreach ($this->roles() as $role) {
            if ($role['slug'] === 'super_admin') {
                return true;
            }
        }

        foreach ($this->permissions() as $permission) {
            if ($permission['slug'] === $permissionSlug) {
                return true;
            }
        }

        return false;
    }

    public function can(string $permissionSlug): bool
    {
        return $this->hasPermission($permissionSlug);
    }

    public function assignRole(int $roleId): bool
    {
        $existing = $this->db->fetch(
            "SELECT 1 AS found FROM user_roles
             WHERE user_id = ? AND role_id = ? LIMIT 1",
            [$this->id, $roleId]
        );

        if ($existing) {
            return false;
        }

        $this->clearCache();

        return (bool)$this->db->insert('user_roles', [
            'user_id'    => $this->id,
            'role_id'    => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function removeRole(int $roleId): bool
    {
        $this->clearCache();

        return $this->db->delete('user_roles', [
            'user_id' => $this->id,
            'role_id' => $roleId,
        ]);
    }

    public function syncRoles(array $roleIds): bool
    {
        $this->db->delete('user_roles', ['user_id' => $this->id]);
        $this->clearCache();

        if (empty($roleIds)) {
            return true;
        }

        $now = date('Y-m-d H:i:s');

        foreach ($roleIds as $roleId) {
            $this->db->insert('user_roles', [
                'user_id'    => $this->id,
                'role_id'    => (int)$roleId,
                'created_at' => $now,
            ]);
        }

        return true;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function reloadPermissions(): self
    {
        $this->clearCache();

        return $this;
    }

    public static function getSchoolUsers(): array
    {
        $instance = new static();
        $schoolId = $instance->currentSchoolId();

        if ($schoolId === null) {
            return $instance->db->fetchAll(
                "SELECT * FROM users ORDER BY created_at DESC"
            );
        }

        return $instance->db->fetchAll(
            "SELECT * FROM users WHERE school_id = ? ORDER BY created_at DESC",
            [$schoolId]
        );
    }

    private function hydrate(?array $row): ?static
    {
        if (!$row) {
            return null;
        }

        $this->attributes = $row;
        $this->original   = $row;
        $this->exists     = true;

        return $this;
    }

    private function clearCache(): void
    {
        $this->cachedRoles = null;
        $this->cachedPermissions = null;
    }
}