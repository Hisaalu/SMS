<?php
// File: /app/Controllers/NotificationController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\NotificationService;

class NotificationController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->notifications = new NotificationService();
    }

    /**
     * Full-page list of the user's notifications.
     */
    public function index(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('/login');
            return;
        }

        $schoolId = $this->schoolId();
        $userId   = (int) $this->auth->id();

        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $filter = (string) ($_GET['filter'] ?? '');

        $result = $this->notifications->paginate($userId, $schoolId, $page, 20, $filter);

        echo $this->view->renderWithLayout('notifications/index', 'default', [
            'title'     => 'Notifications',
            'items'     => $result['rows'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'perPage'   => $result['perPage'],
            'filter'    => $filter,
            'unread'    => $this->notifications->unreadCount($userId, $schoolId),
        ]);
    }

    /**
     * JSON payload for the bell dropdown.
     */
    public function feed(): void
    {
        if (!$this->auth->check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $schoolId = $this->schoolId();
        $userId   = (int) $this->auth->id();

        $items = $this->notifications->forUser($userId, $schoolId, 10);
        $count = $this->notifications->unreadCount($userId, $schoolId);

        // Normalize timestamps for the client
        foreach ($items as &$it) {
            $it['id']         = (int) $it['id'];
            $it['is_unread']  = empty($it['read_at']);
            $it['time_ago']   = $this->timeAgo($it['created_at'] ?? '');
        }
        unset($it);

        $this->json([
            'unread' => $count,
            'items'  => $items,
        ]);
    }

    public function markRead(): void
    {
        if (!$this->auth->check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $schoolId = $this->schoolId();
        $userId   = (int) $this->auth->id();

        if ($id <= 0) {
            $this->json(['error' => 'Invalid id'], 400);
        }

        $ok = $this->notifications->markRead($id, $userId, $schoolId);

        $this->json([
            'success' => $ok,
            'unread'  => $this->notifications->unreadCount($userId, $schoolId),
        ]);
    }

    public function markAllRead(): void
    {
        if (!$this->auth->check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $schoolId = $this->schoolId();
        $userId   = (int) $this->auth->id();

        $ok = $this->notifications->markAllRead($userId, $schoolId);

        $this->json([
            'success' => $ok,
            'unread'  => 0,
        ]);
    }

    public function delete(): void
    {
        if (!$this->auth->check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $schoolId = $this->schoolId();
        $userId   = (int) $this->auth->id();

        if ($id <= 0) {
            $this->json(['error' => 'Invalid id'], 400);
        }

        $ok = $this->notifications->delete($id, $userId, $schoolId);

        $this->json([
            'success' => $ok,
            'unread'  => $this->notifications->unreadCount($userId, $schoolId),
        ]);
    }

    public function clearAll(): void
    {
        if (!$this->auth->check()) {
            $this->json(['error' => 'Unauthorized'], 401);
        }

        $schoolId = $this->schoolId();
        $userId   = (int) $this->auth->id();

        $ok = $this->notifications->clearAll($userId, $schoolId);

        $this->json([
            'success' => $ok,
            'unread'  => 0,
        ]);
    }

    private function timeAgo(?string $datetime): string
    {
        if (empty($datetime)) {
            return '';
        }

        $diff = time() - strtotime($datetime);

        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   return floor($diff / 60) . 'm ago';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';

        return date('d M', strtotime($datetime));
    }
}