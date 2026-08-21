<?php
// File: /app/Core/Database.php

namespace NexaT\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance;
    private $connection;
    private $config;
    private $queryCount = 0;
    
    private function __construct()
    {
        $this->config = Config::get('database');
        $this->connect();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection(): PDO
    {
        return $this->connection;
    }
    
    public function prepare(string $sql): \PDOStatement
    {
        $this->queryCount++;
        return $this->connection->prepare($sql);
    }
    
    public function query(string $sql): \PDOStatement
    {
        $this->queryCount++;
        return $this->connection->query($sql);
    }
    
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function fetch(string $sql, array $params = [])
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function insert(string $table, array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":$field";
        }, $fields);
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->connection->lastInsertId();
    }
    
    public function update(string $table, array $data, array $where): int
    {
        $set = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }
        
        $whereClause = [];
        foreach ($where as $key => $value) {
            $whereClause[] = "{$key} = :where_{$key}";
            $params[":where_{$key}"] = $value;
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    public function delete(string $table, array $where): int
    {
        $whereClause = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $whereClause[] = "{$key} = :where_{$key}";
            $params[":where_{$key}"] = $value;
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }
    
    public function commit(): void
    {
        $this->connection->commit();
    }
    
    public function rollback(): void
    {
        $this->connection->rollback();
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}