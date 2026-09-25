<?php
// File: /app/Services/NotificationService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Throwable;

class NotificationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Raise a notification for a single user.
     */
    public function notify(
        int $userId,
        int $schoolId,
        string $title,
        string $message = '',
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $icon = null
    ): int {
        try {
            return (int) $this->db->insert('notifications', [
                'school_id'  => $schoolId,
                'user_id'    => $userId,
                'type'       => $type,
                'title'      => mb_substr($title, 0, 180),
                'message'    => $message,
                'action_url' => $actionUrl,
                'icon'       => $icon,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Raise the same notification for many users at once.
     */
    public function notifyMany(
        array $userIds,
        int $schoolId,
        string $title,
        string $message = '',
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $icon = null
    ): int {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if (empty($userIds)) {
            return 0;
        }

        $now    = date('Y-m-d H:i:s');
        $title  = mb_substr($title, 0, 180);
        $count  = 0;

        try {
            foreach ($userIds as $uid) {
                if ($uid <= 0) continue;
                $this->db->insert('notifications', [
                    'school_id'  => $schoolId,
                    'user_id'    => $uid,
                    'type'       => $type,
                    'title'      => $title,
                    'message'    => $message,
                    'action_url' => $actionUrl,
                    'icon'       => $icon,
                    'created_at' => $now,
                ]);
                $count++;
            }
        } catch (Throwable $e) {
            return $count;
        }

        return $count;
    }

    /**
     * Raise the same notification for every user holding a given role.
     */
    public function notifyRole(
        int $roleId,
        int $schoolId,
        string $title,
        string $message = '',
        string $type = 'info',
        ?string $actionUrl = null,
        ?string $icon = null
    ): int {
        try {
            $rows = $this->db->fetchAll(
                "SELECT DISTINCT u.id
                 FROM users u
                 INNER JOIN user_roles ur ON ur.user_id = u.id
                 WHERE ur.role_id = :rid AND u.school_id = :sid AND u.status = 'active'",
                ['rid' => $roleId, 'sid' => $schoolId]
            );
        } catch (Throwable $e) {
            return 0;
        }

        $ids = array_map(fn($r) => (int) $r['id'], $rows);

        return $this->notifyMany($ids, $schoolId, $title, $message, $type, $actionUrl, $icon);
    }

    /**
     * Latest notifications for a user, with an unread counter.
     */
    public function forUser(int $userId, int $schoolId, int $limit = 10, bool $unreadOnly = false): array
    {
        $sql = "SELECT id, type, title, message, action_url, icon, read_at, created_at
                FROM notifications
                WHERE user_id = :uid AND school_id = :sid";
        $params = ['uid' => $userId, 'sid' => $schoolId];

        if ($unreadOnly) {
            $sql .= " AND read_at IS NULL";
        }

        $sql .= " ORDER BY id DESC LIMIT " . max(1, min($limit, 50));

        try {
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Full paginated list for the notifications page.
     */
    public function paginate(int $userId, int $schoolId, int $page = 1, int $perPage = 20, ?string $filter = null): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min($perPage, 100));
        $offset  = ($page - 1) * $perPage;

        $where  = "WHERE user_id = :uid AND school_id = :sid";
        $params = ['uid' => $userId, 'sid' => $schoolId];

        if ($filter === 'unread') {
            $where .= " AND read_at IS NULL";
        } elseif ($filter === 'read') {
            $where .= " AND read_at IS NOT NULL";
        }

        try {
            $total = (int) ($this->db->fetch(
                "SELECT COUNT(*) AS c FROM notifications {$where}",
                $params
            )['c'] ?? 0);

            $rows = $this->db->fetchAll(
                "SELECT id, type, title, message, action_url, icon, read_at, created_at
                 FROM notifications {$where}
                 ORDER BY id DESC
                 LIMIT {$perPage} OFFSET {$offset}",
                $params
            ) ?: [];
        } catch (Throwable $e) {
            return ['rows' => [], 'total' => 0, 'page' => $page, 'perPage' => $perPage];
        }

        return [
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
        ];
    }

    public function unreadCount(int $userId, int $schoolId): int
    {
        try {
            return (int) ($this->db->fetch(
                "SELECT COUNT(*) AS c FROM notifications
                 WHERE user_id = :uid AND school_id = :sid AND read_at IS NULL",
                ['uid' => $userId, 'sid' => $schoolId]
            )['c'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Mark a single notification as read — only if it belongs to the user.
     */
    public function markRead(int $notificationId, int $userId, int $schoolId): bool
    {
        try {
            $this->db->execute(
                "UPDATE notifications
                 SET read_at = NOW()
                 WHERE id = :id AND user_id = :uid AND school_id = :sid AND read_at IS NULL",
                ['id' => $notificationId, 'uid' => $userId, 'sid' => $schoolId]
            );

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function markAllRead(int $userId, int $schoolId): bool
    {
        try {
            $this->db->execute(
                "UPDATE notifications
                 SET read_at = NOW()
                 WHERE user_id = :uid AND school_id = :sid AND read_at IS NULL",
                ['uid' => $userId, 'sid' => $schoolId]
            );

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function delete(int $notificationId, int $userId, int $schoolId): bool
    {
        try {
            return $this->db->delete('notifications', [
                'id'        => $notificationId,
                'user_id'   => $userId,
                'school_id' => $schoolId,
            ]) !== false;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function clearAll(int $userId, int $schoolId): bool
    {
        try {
            $this->db->execute(
                "DELETE FROM notifications WHERE user_id = :uid AND school_id = :sid",
                ['uid' => $userId, 'sid' => $schoolId]
            );

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
}