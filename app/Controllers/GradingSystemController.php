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

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $systems = $this->db->fetchAll(
            "SELECT gs.*, 
                    c.name as class_name,
                    ay.name as academic_year_name,
                    (SELECT COUNT(*) FROM grading_rules WHERE system_id = gs.id) as rule_count
             FROM grading_systems gs
             LEFT JOIN classes c ON gs.class_id = c.id
             LEFT JOIN academic_years ay ON gs.academic_year_id = ay.id
             WHERE gs.school_id = :school_id OR gs.school_id IS NULL
             ORDER BY gs.is_default DESC, c.name ASC, gs.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/grading/index', 'default', [
            'title' => 'Grading Systems',
            'systems' => $systems,
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC",
            ['s' => $schoolId]
        );
        $years = $this->db->fetchAll(
            "SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC",
            ['s' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/grading/create', 'default', [
            'title' => 'Create Grading System',
            'classes' => $classes,
            'years' => $years,
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $classId = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
        $academicYearId = !empty($_POST['academic_year_id']) ? (int)$_POST['academic_year_id'] : null;
        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Name is required.';
            header('Location: ' . BASE_URL . '/grading/systems/create');
            exit;
        }

        // If marking as default, unset other defaults
        if ($isDefault) {
            $this->db->execute(
                "UPDATE grading_systems SET is_default = 0 WHERE school_id = :s",
                ['s' => $schoolId]
            );
        }

        $systemId = $this->db->insert('grading_systems', [
            'school_id' => $schoolId,
            'class_id' => $classId,
            'academic_year_id' => $academicYearId,
            'name' => $name,
            'description' => $description,
            'is_default' => $isDefault,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
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
            $_SESSION['flash_success'] = 'Grading system created successfully. Now add its grade rules.';
            header('Location: ' . BASE_URL . '/grading/systems/' . $systemId . '/rules');
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

        $schoolId = $this->auth->getUser()->school_id ?? 1;
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

        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC",
            ['s' => $schoolId]
        );
        $years = $this->db->fetchAll(
            "SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC",
            ['s' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/grading/edit', 'default', [
            'title' => 'Edit Grading System',
            'system' => $system,
            'classes' => $classes,
            'years' => $years,
        ]);
    }

    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
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
        $classId = !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null;
        $academicYearId = !empty($_POST['academic_year_id']) ? (int)$_POST['academic_year_id'] : null;
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            $_SESSION['flash_error'] = 'Name is required.';
            header('Location: ' . BASE_URL . '/grading/systems/' . $id . '/edit');
            exit;
        }

        if ($isDefault) {
            $this->db->execute(
                "UPDATE grading_systems SET is_default = 0 WHERE school_id = :s AND id != :id",
                ['s' => $schoolId, 'id' => $id]
            );
        }

        $result = $this->db->update('grading_systems', [
            'class_id' => $classId,
            'academic_year_id' => $academicYearId,
            'name' => $name,
            'description' => $description,
            'is_default' => $isDefault,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
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