<?php
// File: /app/Controllers/GradingSystemController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class GradingSystemController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $systems = $this->db->fetchAll(
            "SELECT * FROM grading_systems ORDER BY name ASC"
        );
        
        echo $this->view->renderWithLayout('examinations/grading/index', 'default', [
            'title' => 'Grading Systems',
            'systems' => $systems
        ]);
    }
    
    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        echo $this->view->renderWithLayout('examinations/grading/create', 'default', [
            'title' => 'Create Grading System'
        ]);
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Name is required.';
            header('Location: ' . BASE_URL . '/grading/systems/create');
            exit;
        }
        
        $systemId = $this->db->insert('grading_systems', [
            'name' => $name,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($systemId) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Grading System Created',
                    'examinations',
                    "Created grading system: {$name}"
                );
            }
            $_SESSION['flash_success'] = 'Grading system created successfully.';
            header('Location: ' . BASE_URL . '/grading/systems');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to create grading system.';
        header('Location: ' . BASE_URL . '/grading/systems/create');
        exit;
    }
    
    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$system) {
            $_SESSION['flash_error'] = 'Grading system not found.';
            header('Location: ' . BASE_URL . '/grading/systems');
            exit;
        }
        
        echo $this->view->renderWithLayout('examinations/grading/edit', 'default', [
            'title' => 'Edit Grading System',
            'system' => $system
        ]);
    }
    
    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$system) {
            $_SESSION['flash_error'] = 'Grading system not found.';
            header('Location: ' . BASE_URL . '/grading/systems');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Name is required.';
            header('Location: ' . BASE_URL . '/grading/systems/' . $id . '/edit');
            exit;
        }
        
        $result = $this->db->update('grading_systems', [
            'name' => $name,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
        
        if ($result !== false) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Grading System Updated',
                    'examinations',
                    "Updated grading system: {$name}"
                );
            }
            $_SESSION['flash_success'] = 'Grading system updated successfully.';
            header('Location: ' . BASE_URL . '/grading/systems');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to update grading system.';
        header('Location: ' . BASE_URL . '/grading/systems/' . $id . '/edit');
        exit;
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $id]
        );
        
        if (!$system) {
            http_response_code(404);
            echo json_encode(['error' => 'Grading system not found']);
            exit;
        }
        
        // Check if grading rules exist
        $rules = $this->db->fetch(
            "SELECT COUNT(*) as count FROM grading_rules WHERE system_id = :id",
            ['id' => $id]
        );
        
        if ($rules && $rules['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete grading system with existing rules.']);
            exit;
        }
        
        $deleted = $this->db->delete('grading_systems', ['id' => $id]);
        
        if ($deleted) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Grading System Deleted',
                    'examinations',
                    "Deleted grading system: {$system['name']}"
                );
            }
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete grading system']);
        exit;
    }
}