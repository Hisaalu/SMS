<?php
// File: /app/Core/Database.php

namespace NexaT\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

class Database
{
    private static ?self $instance = null;
    private PDO $connection;
    private array $config;
    private int $queryCount = 0;

    private function __construct()
    {
        $this->config = Config::get('database');
        $this->connect();
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function prepare(string $sql): PDOStatement
    {
        $this->queryCount++;
        return $this->connection->prepare($sql);
    }

    public function query(string $sql): PDOStatement
    {
        $this->queryCount++;
        return $this->connection->query($sql);
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
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
        $placeholders = array_map(static fn($f) => ':' . $f, $fields);

        $sql = 'INSERT INTO ' . $table
             . ' (' . implode(', ', $fields) . ')'
             . ' VALUES (' . implode(', ', $placeholders) . ')';

        $stmt = $this->prepare($sql);
        $stmt->execute($data);

        return (int)$this->connection->lastInsertId();
    }

    public function update(string $table, array $data, array $where): int
    {
        $set    = [];
        $params = [];

        foreach ($data as $key => $value) {
            $set[] = "{$key} = :set_{$key}";
            $params[":set_{$key}"] = $value;
        }

        $whereClause = [];
        foreach ($where as $key => $value) {
            $whereClause[] = "{$key} = :where_{$key}";
            $params[":where_{$key}"] = $value;
        }

        $sql = 'UPDATE ' . $table
             . ' SET ' . implode(', ', $set)
             . ' WHERE ' . implode(' AND ', $whereClause);

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

        $sql = 'DELETE FROM ' . $table . ' WHERE ' . implode(' AND ', $whereClause);

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
        if ($this->connection->inTransaction()) {
            $this->connection->rollback();
        }
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    private function connect(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host']     ?? '127.0.0.1',
            $this->config['port']     ?? 3306,
            $this->config['database'] ?? '',
            $this->config['charset']  ?? 'utf8mb4'
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ];

        if (!empty($this->config['ssl_ca']) && file_exists($this->config['ssl_ca'])) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $this->config['ssl_ca'];
        }

        try {
            $this->connection = new PDO(
                $dsn,
                $this->config['username'] ?? '',
                $this->config['password'] ?? '',
                $options
            );
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }
}