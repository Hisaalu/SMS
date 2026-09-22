<?php
// File: /app/Core/Model.php

namespace NexaT\Core;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $timestamps = true;
    protected $createdAtField = 'created_at';
    protected $updatedAtField = 'updated_at';
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;
    protected $schoolId = null;

    private static $columnsCache = [];

    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->schoolId = isset($_SESSION['school_id']) ? (int)$_SESSION['school_id'] : null;

        $this->fill($attributes);
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }

        return $this;
    }

    public function save(): bool
    {
        return $this->exists ? $this->update() : $this->insert();
    }

    public function delete(array $where): int
    {
        return $this->db->delete($this->table, $where);
    }

    public static function find(int $id): ?static
    {
        $instance = new static();
        $row = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
            [$id]
        );

        return $row ? $instance->hydrate($row) : null;
    }

    public static function firstWhere(string $field, $value): ?static
    {
        $instance = new static();
        $row = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$field} = ? LIMIT 1",
            [$value]
        );

        return $row ? $instance->hydrate($row) : null;
    }

    public static function all(array $where = [], array $order = [], ?int $limit = null): array
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table}";
        $params = [];

        if ($instance->hasColumn('school_id')) {
            $where['school_id'] = $instance->schoolId;
        }

        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if (!empty($order)) {
            $parts = [];
            foreach ($order as $field => $direction) {
                $safeDir = strtoupper((string)$direction) === 'DESC' ? 'DESC' : 'ASC';
                $parts[] = "{$field} {$safeDir}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $parts);
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        $rows = $instance->db->fetchAll($sql, $params);

        $models = [];
        foreach ($rows as $row) {
            $models[] = (new static())->hydrate($row);
        }

        return $models;
    }

    public static function where(string $field, $value): array
    {
        return static::all([$field => $value]);
    }

    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
        }
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    protected function hasColumn(string $column): bool
    {
        $table = $this->table;

        if (!isset(self::$columnsCache[$table])) {
            $rows = $this->db->fetchAll("SHOW COLUMNS FROM `{$table}`");
            self::$columnsCache[$table] = array_column($rows, 'Field');
        }

        return in_array($column, self::$columnsCache[$table], true);
    }

    private function insert(): bool
    {
        $data = $this->getAttributesForSave();

        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data[$this->createdAtField] = $now;
            $data[$this->updatedAtField] = $now;
        }

        $id = $this->db->insert($this->table, $data);

        if (!$id) {
            return false;
        }

        $this->attributes[$this->primaryKey] = $id;
        $this->exists = true;
        $this->original = $this->attributes;

        return true;
    }

    private function update(): bool
    {
        $data = $this->getAttributesForSave();
        $id = $this->attributes[$this->primaryKey];

        if ($this->timestamps) {
            $data[$this->updatedAtField] = date('Y-m-d H:i:s');
        }

        $affected = $this->db->update($this->table, $data, [$this->primaryKey => $id]);

        if ($affected === false) {
            return false;
        }

        $this->original = $this->attributes;

        return true;
    }

    private function getAttributesForSave(): array
    {
        return array_filter(
            $this->attributes,
            fn($key) => $this->isFillable($key),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded, true)) {
            return false;
        }

        if (empty($this->fillable)) {
            return true;
        }

        return in_array($key, $this->fillable, true);
    }

    private function hydrate(array $row): static
    {
        $this->attributes = $row;
        $this->original = $row;
        $this->exists = true;

        return $this;
    }
}