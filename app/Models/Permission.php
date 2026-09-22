<?php
// File: /app/Models/Permission.php

namespace NexaT\Models;

use NexaT\Core\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'slug', 'module', 'description'];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public function roles(): array
    {
        return $this->db->fetchAll(
            "SELECT r.* FROM roles r
             INNER JOIN role_permissions rp ON r.id = rp.role_id
             WHERE rp.permission_id = ?",
            [$this->id]
        );
    }

    public static function getByModule(string $module): array
    {
        return static::all(['module' => $module]);
    }

    public static function getModules(): array
    {
        $instance = new static();
        $rows = $instance->db->fetchAll(
            "SELECT DISTINCT module FROM permissions ORDER BY module"
        );

        return array_column($rows, 'module');
    }
}