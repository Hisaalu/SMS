<?php
// File: /app/Controllers/GradingRuleController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class GradingRuleController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function index($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $systemId = $params['id'] ?? 0;
        
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $systemId]
        );
        
        if (!$system) {
            $_SESSION['flash_error'] = 'Grading system not found.';
            header('Location: ' . BASE_URL . '/grading/systems');
            exit;
        }
        
        $rules = $this->db->fetchAll(
            "SELECT * FROM grading_rules WHERE system_id = :system_id ORDER BY min_mark DESC",
            ['system_id' => $systemId]
        );
        
        echo $this->view->renderWithLayout('examinations/grading/rules', 'default', [
            'title' => 'Grading Rules - ' . $system['name'],
            'system' => $system,
            'rules' => $rules
        ]);
    }
    
    public function store($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $systemId = $params['id'] ?? 0;
        
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $systemId]
        );
        
        if (!$system) {
            $_SESSION['flash_error'] = 'Grading system not found.';
            header('Location: ' . BASE_URL . '/grading/systems');
            exit;
        }
        
        $grade       = trim($_POST['grade'] ?? '');
        $minMark     = (float)($_POST['min_mark'] ?? 0);
        $maxMark     = (float)($_POST['max_mark'] ?? 0);
        $score       = (float)($_POST['score'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $pass        = isset($_POST['pass']) ? 1 : 0;
        
        if (empty($grade) || $minMark > $maxMark) {
            $_SESSION['flash_error'] = 'Grade is required and min mark must be less than max mark.';
            header('Location: ' . BASE_URL . '/grading/systems/' . $systemId . '/rules');
            exit;
        }
        
        $overlap = $this->db->fetch(
            "SELECT id FROM grading_rules 
            WHERE system_id = :system_id 
            AND min_mark <= :max_mark 
            AND max_mark >= :min_mark",
            ['system_id' => $systemId, 'min_mark' => $minMark, 'max_mark' => $maxMark]
        );
        
        if ($overlap) {
            $_SESSION['flash_error'] = 'Grade range overlaps with an existing rule.';
            header('Location: ' . BASE_URL . '/grading/systems/' . $systemId . '/rules');
            exit;
        }
        
        $ruleId = $this->db->insert('grading_rules', [
            'system_id'   => $systemId,
            'grade'       => $grade,
            'min_mark'    => $minMark,
            'max_mark'    => $maxMark,
            'score'       => $score,
            'description' => $description,
            'pass'        => $pass,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s')
        ]);
        
        if ($ruleId) {
            if ($this->audit) {
                $this->audit->log($this->auth->id(), 'Grading Rule Created', 'examinations',
                    "Created grading rule: {$grade} ({$minMark}-{$maxMark})");
            }
            $_SESSION['flash_success'] = 'Grading rule created successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to create grading rule.';
        }
        
        header('Location: ' . BASE_URL . '/grading/systems/' . $systemId . '/rules');
        exit;
    }

    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $ruleId = $params['id'] ?? 0;
        
        $rule = $this->db->fetch(
            "SELECT * FROM grading_rules WHERE id = :id",
            ['id' => $ruleId]
        );
        
        if (!$rule) {
            $_SESSION['flash_error'] = 'Grading rule not found.';
            header('Location: ' . BASE_URL . '/grading/systems');
            exit;
        }
        
        $grade       = trim($_POST['grade'] ?? '');
        $minMark     = (float)($_POST['min_mark'] ?? 0);
        $maxMark     = (float)($_POST['max_mark'] ?? 0);
        $score       = (float)($_POST['score'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $pass        = isset($_POST['pass']) ? 1 : 0;
        
        if (empty($grade) || $minMark > $maxMark) {
            $_SESSION['flash_error'] = 'Grade is required and min mark must be less than max mark.';
            header('Location: ' . BASE_URL . '/grading/systems/' . $rule['system_id'] . '/rules');
            exit;
        }
        
        $result = $this->db->update('grading_rules', [
            'grade'       => $grade,
            'min_mark'    => $minMark,
            'max_mark'    => $maxMark,
            'score'       => $score,
            'description' => $description,
            'pass'        => $pass,
            'updated_at'  => date('Y-m-d H:i:s')
        ], ['id' => $ruleId]);
        
        if ($result !== false) {
            if ($this->audit) {
                $this->audit->log($this->auth->id(), 'Grading Rule Updated', 'examinations',
                    "Updated grading rule: {$grade}");
            }
            $_SESSION['flash_success'] = 'Grading rule updated successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to update grading rule.';
        }
        
        header('Location: ' . BASE_URL . '/grading/systems/' . $rule['system_id'] . '/rules');
        exit;
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $ruleId = $params['id'] ?? 0;
        
        $rule = $this->db->fetch(
            "SELECT * FROM grading_rules WHERE id = :id",
            ['id' => $ruleId]
        );
        
        if (!$rule) {
            http_response_code(404);
            echo json_encode(['error' => 'Grading rule not found']);
            exit;
        }
        
        $deleted = $this->db->delete('grading_rules', ['id' => $ruleId]);
        
        if ($deleted) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Grading Rule Deleted',
                    'examinations',
                    "Deleted grading rule: {$rule['grade']}"
                );
            }
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete grading rule']);
        exit;
    }
}