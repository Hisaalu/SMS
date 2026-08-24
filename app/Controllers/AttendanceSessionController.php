<?php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class AttendanceSessionController extends Controller
{
    protected $audit;

    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $sessions = $this->db->fetchAll(
            "SELECT * FROM attendance_sessions WHERE school_id = :school_id OR school_id IS NULL ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('attendance/sessions/index', 'default', [
            'title' => 'Attendance Sessions',
            'sessions' => $sessions
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        echo $this->view->renderWithLayout('attendance/sessions/create', 'default', [
            'title' => 'Create Attendance Session'
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $startTime = $_POST['start_time'] ?? null;
        $endTime = $_POST['end_time'] ?? null;
        $displayOrder = (int)($_POST['display_order'] ?? 0);

        if (empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Name and code are required.';
            header('Location: ' . BASE_URL . '/attendance/sessions/create');
            exit;
        }

        $this->db->insert('attendance_sessions', [
            'school_id' => $schoolId,
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'start_time' => $startTime ?: null,
            'end_time' => $endTime ?: null,
            'status' => 'active',
            'display_order' => $displayOrder,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_success'] = 'Attendance session created successfully.';
        header('Location: ' . BASE_URL . '/attendance/sessions');
        exit;
    }

    public function edit($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = $params['id'] ?? 0;
        $session = $this->db->fetch("SELECT * FROM attendance_sessions WHERE id = :id", ['id' => $id]);

        if (!$session) {
            $_SESSION['flash_error'] = 'Session not found.';
            header('Location: ' . BASE_URL . '/attendance/sessions');
            exit;
        }

        echo $this->view->renderWithLayout('attendance/sessions/edit', 'default', [
            'title' => 'Edit Attendance Session',
            'session' => (object)$session
        ]);
    }

    public function update($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = $params['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $startTime = $_POST['start_time'] ?? null;
        $endTime = $_POST['end_time'] ?? null;
        $statusValue = $_POST['status'] ?? 'active';
        $displayOrder = (int)($_POST['display_order'] ?? 0);

        $this->db->update('attendance_sessions', [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'start_time' => $startTime ?: null,
            'end_time' => $endTime ?: null,
            'status' => $statusValue,
            'display_order' => $displayOrder,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        $_SESSION['flash_success'] = 'Attendance session updated successfully.';
        header('Location: ' . BASE_URL . '/attendance/sessions');
        exit;
    }

    public function delete($params): void
    {
        if (!$this->auth->check()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $id = $params['id'] ?? 0;
        $deleted = $this->db->delete('attendance_sessions', ['id' => $id]);

        if ($deleted) {
            echo json_encode(['success' => true]);
            exit;
        }

        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete session']);
        exit;
    }
}