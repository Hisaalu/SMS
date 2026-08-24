<?php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\AttendanceStatus;
use NexaT\Services\AuditService;

class AttendanceStatusController extends Controller
{
    protected $audit;
    
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $statuses = $this->db->fetchAll(
            "SELECT * FROM attendance_statuses WHERE school_id = :school_id ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
        
        echo $this->view->renderWithLayout('attendance/statuses/index', 'default', [
            'title' => 'Attendance Statuses',
            'statuses' => $statuses
        ]);
    }
    
    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        echo $this->view->renderWithLayout('attendance/statuses/create', 'default', [
            'title' => 'Create Attendance Status'
        ]);
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $countsAsPresent = isset($_POST['counts_as_present']) ? 1 : 0;
        $countsAsAbsent = isset($_POST['counts_as_absent']) ? 1 : 0;
        $countsAsLate = isset($_POST['counts_as_late']) ? 1 : 0;
        $requiresReason = isset($_POST['requires_reason']) ? 1 : 0;
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        if (empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Name and code are required.';
            header('Location: ' . BASE_URL . '/attendance/statuses/create');
            exit;
        }
        
        $existing = $this->db->fetch(
            "SELECT id FROM attendance_statuses WHERE school_id = :school_id AND (name = :name OR code = :code)",
            ['school_id' => $schoolId, 'name' => $name, 'code' => $code]
        );
        
        if ($existing) {
            $_SESSION['flash_error'] = 'A status with this name or code already exists.';
            header('Location: ' . BASE_URL . '/attendance/statuses/create');
            exit;
        }
        
        $result = $this->db->insert('attendance_statuses', [
            'school_id' => $schoolId,
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'counts_as_present' => $countsAsPresent,
            'counts_as_absent' => $countsAsAbsent,
            'counts_as_late' => $countsAsLate,
            'requires_reason' => $requiresReason,
            'status' => 'active',
            'display_order' => $displayOrder,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $this->audit->log(
                $this->auth->id(),
                'Attendance Status Created',
                'attendance',
                "Created attendance status: {$name} ({$code})"
            );
            $_SESSION['flash_success'] = 'Attendance status created successfully.';
            header('Location: ' . BASE_URL . '/attendance/statuses');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to create attendance status.';
        header('Location: ' . BASE_URL . '/attendance/statuses/create');
        exit;
    }
    
    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $status = $this->db->fetch(
            "SELECT * FROM attendance_statuses WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$status) {
            $_SESSION['flash_error'] = 'Status not found.';
            header('Location: ' . BASE_URL . '/attendance/statuses');
            exit;
        }
        
        echo $this->view->renderWithLayout('attendance/statuses/edit', 'default', [
            'title' => 'Edit Attendance Status',
            'status' => (object)$status
        ]);
    }
    
    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $status = $this->db->fetch(
            "SELECT * FROM attendance_statuses WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$status) {
            $_SESSION['flash_error'] = 'Status not found.';
            header('Location: ' . BASE_URL . '/attendance/statuses');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $countsAsPresent = isset($_POST['counts_as_present']) ? 1 : 0;
        $countsAsAbsent = isset($_POST['counts_as_absent']) ? 1 : 0;
        $countsAsLate = isset($_POST['counts_as_late']) ? 1 : 0;
        $requiresReason = isset($_POST['requires_reason']) ? 1 : 0;
        $statusValue = $_POST['status'] ?? 'active';
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        if (empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Name and code are required.';
            header('Location: ' . BASE_URL . '/attendance/statuses/' . $id . '/edit');
            exit;
        }
        
        $result = $this->db->update('attendance_statuses', [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'counts_as_present' => $countsAsPresent,
            'counts_as_absent' => $countsAsAbsent,
            'counts_as_late' => $countsAsLate,
            'requires_reason' => $requiresReason,
            'status' => $statusValue,
            'display_order' => $displayOrder,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
        
        if ($result !== false) {
            $this->audit->log(
                $this->auth->id(),
                'Attendance Status Updated',
                'attendance',
                "Updated attendance status: {$name} ({$code})"
            );
            $_SESSION['flash_success'] = 'Attendance status updated successfully.';
            header('Location: ' . BASE_URL . '/attendance/statuses');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to update attendance status.';
        header('Location: ' . BASE_URL . '/attendance/statuses/' . $id . '/edit');
        exit;
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.manage')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $status = $this->db->fetch(
            "SELECT * FROM attendance_statuses WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$status) {
            http_response_code(404);
            echo json_encode(['error' => 'Status not found']);
            exit;
        }
        
        $usage = $this->db->fetch(
            "SELECT COUNT(*) as count FROM attendance_records WHERE attendance_status_id = :id",
            ['id' => $id]
        );
        
        if ($usage && $usage['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete status: It is used in ' . $usage['count'] . ' attendance records.']);
            exit;
        }
        
        $name = $status['name'];
        $deleted = $this->db->delete('attendance_statuses', ['id' => $id]);
        
        if ($deleted) {
            $this->audit->log(
                $this->auth->id(),
                'Attendance Status Deleted',
                'attendance',
                "Deleted attendance status: {$name}"
            );
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete status']);
        exit;
    }
}