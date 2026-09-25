<?php
// File: /app/Core/DatabaseSessionHandler.php

namespace NexaT\Core;

class DatabaseSessionHandler implements \SessionHandlerInterface
{
    private Database $db;
    private int $lifetime;

    public function __construct(Database $db, int $lifetime = 86400)
    {
        $this->db = $db;
        $this->lifetime = $lifetime;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        try {
            $row = $this->db->fetch(
                "SELECT session_data FROM php_sessions 
                 WHERE session_id = :id AND session_expires > :now",
                ['id' => $id, 'now' => time()]
            );
            return $row['session_data'] ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function write(string $id, string $data): bool
    {
        try {
            $expires = time() + $this->lifetime;
            $this->db->execute(
                "INSERT INTO php_sessions (session_id, session_data, session_expires) 
                 VALUES (:id, :data, :expires)
                 ON DUPLICATE KEY UPDATE 
                    session_data = :data2, 
                    session_expires = :expires2",
                [
                    'id' => $id,
                    'data' => $data,
                    'expires' => $expires,
                    'data2' => $data,
                    'expires2' => $expires,
                ]
            );
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function destroy(string $id): bool
    {
        try {
            $this->db->execute(
                "DELETE FROM php_sessions WHERE session_id = :id",
                ['id' => $id]
            );
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function gc(int $max_lifetime): int|false
    {
        try {
            return $this->db->execute(
                "DELETE FROM php_sessions WHERE session_expires < :now",
                ['now' => time()]
            );
        } catch (\Throwable $e) {
            return false;
        }
    }
}