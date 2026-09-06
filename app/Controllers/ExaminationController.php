<?php
// File: /app/Controllers/ExaminationController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class ExaminationController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Use the parent's $audit property
        $this->audit = new AuditService();
    }
    
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('examinations.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        // Use examinations table if it exists, otherwise use examinations
        try {
            $examinations = $this->db->fetchAll(
                "SELECT es.*, 
                        at.name as assessment_type_name, 
                        ay.name as academic_year_name, 
                        t.name as term_name
                 FROM examinations es
                 LEFT JOIN assessment_types at ON es.assessment_type_id = at.id
                 LEFT JOIN academic_years ay ON es.academic_year_id = ay.id
                 LEFT JOIN terms t ON es.academic_period_id = t.id
                 WHERE es.school_id = :school_id
                 ORDER BY es.created_at DESC",
                ['school_id' => $schoolId]
            );
        } catch (\Exception $e) {
            // Fallback to examinations table if examinations doesn't exist
            $examinations = $this->db->fetchAll(
                "SELECT e.*, 
                        at.name as assessment_type_name, 
                        ay.name as academic_year_name, 
                        t.name as term_name
                 FROM examinations e
                 LEFT JOIN assessment_types at ON e.assessment_type_id = at.id
                 LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
                 LEFT JOIN terms t ON e.academic_period_id = t.id
                 WHERE e.school_id = :school_id
                 ORDER BY e.created_at DESC",
                ['school_id' => $schoolId]
            );
        }
        
        echo $this->view->renderWithLayout('examinations/index', 'default', [
            'title' => 'Examinations',
            'examinations' => $examinations
        ]);
    }
    
    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('examinations.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        // Get all data for dropdowns
        $academicYears = $this->db->fetchAll(
            "SELECT * FROM academic_years WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
        
        $terms = $this->db->fetchAll(
            "SELECT * FROM terms WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
        
        $assessmentTypes = $this->db->fetchAll(
            "SELECT * FROM assessment_types WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
        
        echo $this->view->renderWithLayout('examinations/create', 'default', [
            'title' => 'Create Examination',
            'academicYears' => $academicYears,
            'terms' => $terms,
            'assessmentTypes' => $assessmentTypes
        ]);
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('examinations.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
        $academicPeriodId = (int)($_POST['academic_period_id'] ?? 0);
        $assessmentTypeId = (int)($_POST['assessment_type_id'] ?? 0);
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        
        if (empty($name) || !$academicYearId || !$academicPeriodId || !$assessmentTypeId) {
            $_SESSION['flash_error'] = 'Name, Academic Year, Term, and Assessment Type are required.';
            header('Location: ' . BASE_URL . '/examinations/create');
            exit;
        }
        
        // Try to use examinations table first
        try {
            $examId = $this->db->insert('examinations', [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
                'academic_period_id' => $academicPeriodId,
                'assessment_type_id' => $assessmentTypeId,
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'draft',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Fallback to examinations table
            $examId = $this->db->insert('examinations', [
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
                'academic_period_id' => $academicPeriodId,
                'assessment_type_id' => $assessmentTypeId,
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'draft',
                'is_published' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        if ($examId) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Examination Created',
                    'examinations',
                    "Created examination: {$name}"
                );
            }
            $_SESSION['flash_success'] = 'Examination created successfully.';
            header('Location: ' . BASE_URL . '/examinations');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to create examination.';
        header('Location: ' . BASE_URL . '/examinations/create');
        exit;
    }
    
    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('examinations.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        // Try to get from examinations first
        $examination = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$examination) {
            // Fallback to examinations table
            $examination = $this->db->fetch(
                "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );
        }
        
        if (!$examination) {
            $_SESSION['flash_error'] = 'Examination not found.';
            header('Location: ' . BASE_URL . '/examinations');
            exit;
        }
        
        $academicYears = $this->db->fetchAll(
            "SELECT * FROM academic_years WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
        
        $terms = $this->db->fetchAll(
            "SELECT * FROM terms WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
        
        $assessmentTypes = $this->db->fetchAll(
            "SELECT * FROM assessment_types WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
        
        echo $this->view->renderWithLayout('examinations/edit', 'default', [
            'title' => 'Edit Examination',
            'examination' => $examination,
            'academicYears' => $academicYears,
            'terms' => $terms,
            'assessmentTypes' => $assessmentTypes
        ]);
    }
    
    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('examinations.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        // Try to get from examinations first
        $existing = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$existing) {
            // Fallback to examinations table
            $existing = $this->db->fetch(
                "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );
        }
        
        if (!$existing) {
            $_SESSION['flash_error'] = 'Examination not found.';
            header('Location: ' . BASE_URL . '/examinations');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
        $academicPeriodId = (int)($_POST['academic_period_id'] ?? 0);
        $assessmentTypeId = (int)($_POST['assessment_type_id'] ?? 0);
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        $status = $_POST['status'] ?? 'draft';
        
        if (empty($name) || !$academicYearId || !$academicPeriodId || !$assessmentTypeId) {
            $_SESSION['flash_error'] = 'Name, Academic Year, Term, and Assessment Type are required.';
            header('Location: ' . BASE_URL . '/examinations/' . $id . '/edit');
            exit;
        }
        
        // Try to update examinations first
        try {
            $result = $this->db->update('examinations', [
                'academic_year_id' => $academicYearId,
                'academic_period_id' => $academicPeriodId,
                'assessment_type_id' => $assessmentTypeId,
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);
        } catch (\Exception $e) {
            // Fallback to examinations table
            $result = $this->db->update('examinations', [
                'academic_year_id' => $academicYearId,
                'academic_period_id' => $academicPeriodId,
                'assessment_type_id' => $assessmentTypeId,
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $id]);
        }
        
        if ($result !== false) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Examination Updated',
                    'examinations',
                    "Updated examination: {$name}"
                );
            }
            $_SESSION['flash_success'] = 'Examination updated successfully.';
            header('Location: ' . BASE_URL . '/examinations');
            exit;
        }
        
        $_SESSION['flash_error'] = 'Failed to update examination.';
        header('Location: ' . BASE_URL . '/examinations/' . $id . '/edit');
        exit;
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('examinations.delete')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $id = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        // Try to get from examinations first
        $exam = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        
        if (!$exam) {
            // Fallback to examinations table
            $exam = $this->db->fetch(
                "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );
        }
        
        if (!$exam) {
            http_response_code(404);
            echo json_encode(['error' => 'Examination not found']);
            exit;
        }
        
        // Check if marks exist - try both tables
        try {
            $marks = $this->db->fetch(
                "SELECT COUNT(*) as count FROM marks m 
                 INNER JOIN examination_subjects es ON m.examination_subject_id = es.id 
                 WHERE es.examination_set_id = :exam_id",
                ['exam_id' => $id]
            );
        } catch (\Exception $e) {
            $marks = ['count' => 0];
        }
        
        if ($marks && $marks['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete examination with existing marks.']);
            exit;
        }
        
        // Try to delete from examinations first
        try {
            $deleted = $this->db->delete('examinations', ['id' => $id]);
        } catch (\Exception $e) {
            $deleted = $this->db->delete('examinations', ['id' => $id]);
        }
        
        if ($deleted) {
            if ($this->audit) {
                $this->audit->log(
                    $this->auth->id(),
                    'Examination Deleted',
                    'examinations',
                    "Deleted examination: {$exam['name']}"
                );
            }
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete examination']);
        exit;
    }
}