<?php
// File: /app/Controllers/AssessmentTypeController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class AssessmentTypeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('assessment.types.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $types = $this->db->fetchAll(
            "SELECT * FROM assessment_types WHERE school_id = :school_id ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
        
        echo $this->view->renderWithLayout('examinations/assessment/types', 'default', [
            'title' => 'Assessment Types',
            'types' => $types
        ]);
    }
    
    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('assessment.types.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        echo $this->view->renderWithLayout('examinations/assessment/create', 'default', [
            'title' => 'Create Assessment Type'
        ]);
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('assessment.types.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $maxMarks = (float)($_POST['max_marks'] ?? 100);
        $weight = (float)($_POST['weight'] ?? 0);
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        if (empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Name and code are required.';
            header('Location: ' . BASE_URL . '/assessment/types/create');
            exit;
        }
        
        $existing = $this->db->fetch(
            "SELECT id FROM assessment_types WHERE school_id = :school_id AND (name = :name OR code = :code)",
            ['school_id' => $schoolId, 'name' => $name, 'code' => $code]
        );
        
        if ($existing) {
            $_SESSION['flash_error'] = 'An assessment type with this name or code already exists.';
            header('Location: ' . BASE_URL . '/assessment/types/create');
            exit;
        }
        
        $typeId = $this->db->insert('assessment_types', [
            'school_id' => $schoolId,
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'max_marks' => $maxMarks,
            'weight' => $weight,
            'display_order' => $displayOrder,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($typeId) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Assessment Type Created',
                    'examinations',
                    "Created assessment type: {$name} ({$code})"
                );
            }
            $_SESSION['flash_success'] = 'Assessment type created successfully.';
            header('Location: ' . BASE_URL . '/assessment/types');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to create assessment type.';
        header('Location: ' . BASE_URL . '/assessment/types/create');
        exit;
    }
    
    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('assessment.types.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $type = $this->db->fetch(
            "SELECT * FROM assessment_types WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$type) {
            $_SESSION['flash_error'] = 'Assessment type not found.';
            header('Location: ' . BASE_URL . '/assessment/types');
            exit;
        }
        
        echo $this->view->renderWithLayout('examinations/assessment/edit', 'default', [
            'title' => 'Edit Assessment Type',
            'type' => $type
        ]);
    }
    
    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('assessment.types.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $existing = $this->db->fetch(
            "SELECT * FROM assessment_types WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$existing) {
            $_SESSION['flash_error'] = 'Assessment type not found.';
            header('Location: ' . BASE_URL . '/assessment/types');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $maxMarks = (float)($_POST['max_marks'] ?? 100);
        $weight = (float)($_POST['weight'] ?? 0);
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        if (empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Name and code are required.';
            header('Location: ' . BASE_URL . '/assessment/types/' . $id . '/edit');
            exit;
        }
        
        $result = $this->db->update('assessment_types', [
            'name' => $name,
            'code' => $code,
            'description' => $description,
            'max_marks' => $maxMarks,
            'weight' => $weight,
            'display_order' => $displayOrder,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
        
        if ($result !== false) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Assessment Type Updated',
                    'examinations',
                    "Updated assessment type: {$name} ({$code})"
                );
            }
            $_SESSION['flash_success'] = 'Assessment type updated successfully.';
            header('Location: ' . BASE_URL . '/assessment/types');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to update assessment type.';
        header('Location: ' . BASE_URL . '/assessment/types/' . $id . '/edit');
        exit;
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('assessment.types.delete')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $type = $this->db->fetch(
            "SELECT * FROM assessment_types WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$type) {
            http_response_code(404);
            echo json_encode(['error' => 'Assessment type not found']);
            exit;
        }
        
        // Check if in use
        $usage = $this->db->fetch(
            "SELECT COUNT(*) as count FROM examinations WHERE assessment_type_id = :id",
            ['id' => $id]
        );
        
        if ($usage && $usage['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete assessment type: It is used in ' . $usage['count'] . ' examinations.']);
            exit;
        }
        
        $deleted = $this->db->delete('assessment_types', ['id' => $id]);
        
        if ($deleted) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Assessment Type Deleted',
                    'examinations',
                    "Deleted assessment type: {$type['name']}"
                );
            }
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete assessment type']);
        exit;
    }
}