<?php
// File: /app/Controllers/PromotionRuleController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class PromotionRuleController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('promotion.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $rules = $this->db->fetchAll(
            "SELECT * FROM promotion_rules WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $schoolId]
        );
        
        echo $this->view->renderWithLayout('examinations/promotion/index', 'default', [
            'title' => 'Promotion Rules',
            'rules' => $rules
        ]);
    }
    
    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('promotion.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        echo $this->view->renderWithLayout('examinations/promotion/create', 'default', [
            'title' => 'Create Promotion Rule'
        ]);
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('promotion.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $passMark = (float)($_POST['pass_mark'] ?? 50);
        $minSubjectsPassed = (int)($_POST['min_subjects_passed'] ?? 0);
        $requireAllSubjects = isset($_POST['require_all_subjects']) ? 1 : 0;
        
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Rule name is required.';
            header('Location: ' . BASE_URL . '/promotion/rules/create');
            exit;
        }
        
        $ruleId = $this->db->insert('promotion_rules', [
            'school_id' => $schoolId,
            'name' => $name,
            'description' => $description,
            'pass_mark' => $passMark,
            'min_subjects_passed' => $minSubjectsPassed,
            'require_all_subjects' => $requireAllSubjects,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($ruleId) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Promotion Rule Created',
                    'examinations',
                    "Created promotion rule: {$name}"
                );
            }
            $_SESSION['flash_success'] = 'Promotion rule created successfully.';
            header('Location: ' . BASE_URL . '/promotion/rules');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to create promotion rule.';
        header('Location: ' . BASE_URL . '/promotion/rules/create');
        exit;
    }
    
    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('promotion.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $rule = $this->db->fetch(
            "SELECT * FROM promotion_rules WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$rule) {
            $_SESSION['flash_error'] = 'Promotion rule not found.';
            header('Location: ' . BASE_URL . '/promotion/rules');
            exit;
        }
        
        echo $this->view->renderWithLayout('examinations/promotion/edit', 'default', [
            'title' => 'Edit Promotion Rule',
            'rule' => $rule
        ]);
    }
    
    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('promotion.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $existing = $this->db->fetch(
            "SELECT * FROM promotion_rules WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$existing) {
            $_SESSION['flash_error'] = 'Promotion rule not found.';
            header('Location: ' . BASE_URL . '/promotion/rules');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $passMark = (float)($_POST['pass_mark'] ?? 50);
        $minSubjectsPassed = (int)($_POST['min_subjects_passed'] ?? 0);
        $requireAllSubjects = isset($_POST['require_all_subjects']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';
        
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Rule name is required.';
            header('Location: ' . BASE_URL . '/promotion/rules/' . $id . '/edit');
            exit;
        }
        
        $result = $this->db->update('promotion_rules', [
            'name' => $name,
            'description' => $description,
            'pass_mark' => $passMark,
            'min_subjects_passed' => $minSubjectsPassed,
            'require_all_subjects' => $requireAllSubjects,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $id]);
        
        if ($result !== false) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Promotion Rule Updated',
                    'examinations',
                    "Updated promotion rule: {$name}"
                );
            }
            $_SESSION['flash_success'] = 'Promotion rule updated successfully.';
            header('Location: ' . BASE_URL . '/promotion/rules');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to update promotion rule.';
        header('Location: ' . BASE_URL . '/promotion/rules/' . $id . '/edit');
        exit;
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('promotion.manage')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $rule = $this->db->fetch(
            "SELECT * FROM promotion_rules WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$rule) {
            http_response_code(404);
            echo json_encode(['error' => 'Promotion rule not found']);
            exit;
        }
        
        $deleted = $this->db->delete('promotion_rules', ['id' => $id]);
        
        if ($deleted) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Promotion Rule Deleted',
                    'examinations',
                    "Deleted promotion rule: {$rule['name']}"
                );
            }
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete promotion rule']);
        exit;
    }
}