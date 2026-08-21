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
    protected $schoolId = 1;
    
    public function __construct(array $attributes = [])
    {
        $this->db = Database::getInstance();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->schoolId = $_SESSION['school_id'] ?? 1;

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
        if ($this->exists) {
            return $this->update();
        }
        return $this->insert();
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
        
        if ($id) {
            $this->attributes[$this->primaryKey] = $id;
            $this->exists = true;
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }
    
    private function update(): bool
    {
        $data = $this->getAttributesForSave();
        $id = $this->attributes[$this->primaryKey];
        
        if ($this->timestamps) {
            $data[$this->updatedAtField] = date('Y-m-d H:i:s');
        }
        
        $affected = $this->db->update($this->table, $data, [$this->primaryKey => $id]);
        
        if ($affected !== false) {
            $this->original = $this->attributes;
            return true;
        }
        
        return false;
    }
    
    private function getAttributesForSave(): array
    {
        $data = [];
        foreach ($this->attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $data[$key] = $value;
            }
        }
        return $data;
    }
    
    private function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) {
            return false;
        }
        if (!empty($this->fillable)) {
            return in_array($key, $this->fillable);
        }
        return true;
    }
    
    public static function find(int $id): ?self
    {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = ?",
            [$id]
        );
        
        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    public static function firstWhere(string $field, $value): ?self
    {
        $instance = new static();
        $result = $instance->db->fetch(
            "SELECT * FROM {$instance->table} WHERE {$field} = ? LIMIT 1",
            [$value]
        );
        
        if ($result) {
            $instance->attributes = $result;
            $instance->original = $result;
            $instance->exists = true;
            return $instance;
        }
        
        return null;
    }
    
    // NEW: all() method to get all records
    public static function all(array $where = [], array $order = [], int $limit = null): array
    {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table}";
        $params = [];
        
        // Add school_id filter if the table has school_id column
        $tableInfo = $instance->db->fetch("SHOW COLUMNS FROM {$instance->table} LIKE 'school_id'");
        if ($tableInfo) {
            $where['school_id'] = $instance->schoolId;
        }
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if (!empty($order)) {
            $orderClause = [];
            foreach ($order as $field => $direction) {
                $orderClause[] = "{$field} {$direction}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderClause);
        }
        
        if ($limit !== null) {
            $sql .= " LIMIT {$limit}";
        }
        
        $results = $instance->db->fetchAll($sql, $params);
        
        $models = [];
        foreach ($results as $result) {
            $model = new static();
            $model->attributes = $result;
            $model->original = $result;
            $model->exists = true;
            $models[] = $model;
        }
        
        return $models;
    }
    
    // NEW: where method for querying
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
    
    public function delete(array $where): int
    {
        return $this->db->delete($this->table, $where);
    }
}