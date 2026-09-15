<?php
// File: /app/Controllers/ResultController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class ResultController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function examination($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $examinationId = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        // Get examination details
        $examination = $this->db->fetch(
            "SELECT e.*, at.name as assessment_type_name 
             FROM examinations e
             LEFT JOIN assessment_types at ON e.assessment_type_id = at.id
             WHERE e.id = :id AND e.school_id = :school_id",
            ['id' => $examinationId, 'school_id' => $schoolId]
        );
        
        if (!$examination) {
            $_SESSION['flash_error'] = 'Examination not found.';
            header('Location: ' . BASE_URL . '/examinations');
            exit;
        }
        
        // Get results with student details
        $results = $this->db->fetchAll(
            "SELECT s.id as student_id, s.admission_number, s.first_name, s.last_name,
                    m.marks_obtained, m.remarks, m.entered_at,
                    cl.name as class_name, st.name as stream_name,
                    ss.name as status_name
             FROM marks m
             INNER JOIN students s ON m.student_id = s.id
             LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.status = 'active'
             LEFT JOIN classes cl ON se.class_id = cl.id
             LEFT JOIN streams st ON se.stream_id = st.id
             LEFT JOIN student_statuses ss ON se.student_status_id = ss.id
             WHERE m.examination_id = :examination_id
             AND m.school_id = :school_id
             ORDER BY s.last_name ASC",
            ['examination_id' => $examinationId, 'school_id' => $schoolId]
        );
        
        // Calculate statistics
        $totalStudents = count($results);
        $totalMarks = 0;
        $passed = 0;
        $failed = 0;
        
        foreach ($results as $result) {
            $totalMarks += $result['marks_obtained'];
            if ($result['marks_obtained'] >= 50) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        $average = $totalStudents > 0 ? round($totalMarks / $totalStudents, 2) : 0;
        
        echo $this->view->renderWithLayout('examinations/results/examination', 'default', [
            'title' => 'Results - ' . $examination['name'],
            'examination' => $examination,
            'results' => $results,
            'totalStudents' => $totalStudents,
            'average' => $average,
            'passed' => $passed,
            'failed' => $failed
        ]);
    }
    
    public function student($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $studentId = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        // Get student details
        $student = $this->db->fetch(
            "SELECT s.*, sc.name as category_name, st.name as status_name
             FROM students s
             LEFT JOIN student_categories sc ON s.current_category_id = sc.id
             LEFT JOIN student_statuses st ON s.current_status_id = st.id
             WHERE s.id = :id AND s.school_id = :school_id",
            ['id' => $studentId, 'school_id' => $schoolId]
        );
        
        if (!$student) {
            $_SESSION['flash_error'] = 'Student not found.';
            header('Location: ' . BASE_URL . '/students');
            exit;
        }
        
        // Get all results for this student
        $results = $this->db->fetchAll(
            "SELECT m.*, e.name as examination_name, e.code as examination_code,
                    at.name as assessment_type, e.academic_year_id, e.academic_period_id,
                    ay.name as academic_year, t.name as term
             FROM marks m
             INNER JOIN examinations e ON m.examination_id = e.id
             LEFT JOIN assessment_types at ON e.assessment_type_id = at.id
             LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
             LEFT JOIN terms t ON e.academic_period_id = t.id
             WHERE m.student_id = :student_id AND m.school_id = :school_id
             ORDER BY e.id DESC, e.created_at DESC",
            ['student_id' => $studentId, 'school_id' => $schoolId]
        );
        
        // Calculate totals
        $totalExams = count($results);
        $totalMarks = 0;
        
        foreach ($results as $result) {
            $totalMarks += $result['marks_obtained'];
        }
        
        $average = $totalExams > 0 ? round($totalMarks / $totalExams, 2) : 0;
        
        echo $this->view->renderWithLayout('examinations/results/student', 'default', [
            'title' => 'Student Results',
            'student' => $student,
            'results' => $results,
            'totalExams' => $totalExams,
            'average' => $average
        ]);
    }
    
    public function publish($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.publish')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $examinationId = $params['id'] ?? 0;
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $exam = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $examinationId, 'school_id' => $schoolId]
        );
        
        if (!$exam) {
            http_response_code(404);
            echo json_encode(['error' => 'Examination not found']);
            exit;
        }
        
        $result = $this->db->update('examinations', [
            'status' => 'published',
            'is_published' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $examinationId]);
        
        if ($result !== false) {
            $this->audit->log(
                $this->auth->id(),
                'Results Published',
                'examinations',
                "Published results for examination: {$exam['name']}"
            );
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to publish results']);
        exit;
    }

    /**
     * Display class results selector page
     */
    public function classSelector(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $academicYears = $this->db->fetchAll(
            "SELECT * FROM academic_years WHERE school_id = :school_id ORDER BY id DESC",
            ['school_id' => $schoolId]
        );

        $terms = $this->db->fetchAll(
            "SELECT * FROM terms WHERE school_id = :school_id ORDER BY term_number ASC",
            ['school_id' => $schoolId]
        );

        $classes = $this->db->fetchAll(
            "SELECT * FROM classes WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        $examinations = $this->db->fetchAll(
            "SELECT * FROM examinations WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/results/selector', 'default', [
            'title' => 'Class Results',
            'academicYears' => $academicYears,
            'terms' => $terms,
            'classes' => $classes,
            'examinations' => $examinations
        ]);
    }

   public function classResults($params = []): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $examinationId  = (int)($_GET['examination_id'] ?? $params['examinationId'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = (int)($_GET['stream_id'] ?? 0);
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);
        $subjectId      = $_GET['subject_id'] ?? 'all';
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $examination   = null;
        $results       = [];
        $students      = [];
        $resultsMatrix = [];

        // Fetch dropdown filter options
        $academicYears = $this->db->fetchAll("SELECT * FROM academic_years WHERE school_id = :school_id ORDER BY id DESC", ['school_id' => $schoolId]);
        $terms         = $this->db->fetchAll("SELECT * FROM terms WHERE school_id = :school_id ORDER BY term_number ASC", ['school_id' => $schoolId]);
        $classes       = $this->db->fetchAll("SELECT * FROM classes WHERE school_id = :school_id ORDER BY name ASC", ['school_id' => $schoolId]);
        $streams       = $this->db->fetchAll("SELECT * FROM streams WHERE school_id = :school_id ORDER BY name ASC", ['school_id' => $schoolId]);
        $examinations  = $this->db->fetchAll("SELECT * FROM examinations WHERE school_id = :school_id ORDER BY name ASC", ['school_id' => $schoolId]);
        $subjects      = $this->db->fetchAll("SELECT * FROM subjects WHERE school_id = :school_id AND status = 'active' ORDER BY code ASC, name ASC", ['school_id' => $schoolId]);

        if ($examinationId > 0 && $classId > 0) {
            $examination = $this->db->fetch(
                "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
                ['id' => $examinationId, 'school_id' => $schoolId]
            );

            if ($examination) {
                // Build Dynamic Enrollment Query (Handles Class & Optional Stream)
                $enrollmentWhere = "se.academic_year_id = :academic_year_id 
                                    AND se.class_id = :class_id 
                                    AND se.status = 'active' 
                                    AND s.school_id = :school_id";
                
                $enrollmentParams = [
                    'academic_year_id' => $academicYearId,
                    'class_id'         => $classId,
                    'school_id'        => $schoolId
                ];

                if ($streamId > 0) {
                    $enrollmentWhere .= " AND se.stream_id = :stream_id";
                    $enrollmentParams['stream_id'] = $streamId;
                }

                // 1. Fetch Students enrolled in this Class (and Stream if selected)
                $students = $this->db->fetchAll(
                    "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender
                    FROM students s
                    INNER JOIN student_enrollments se ON s.id = se.student_id
                    WHERE {$enrollmentWhere}
                    ORDER BY s.last_name ASC, s.first_name ASC",
                    $enrollmentParams
                );

                if (!empty($students)) {
                    $studentIds   = array_column($students, 'id');
                    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

                    if ($subjectId !== 'all' && (int)$subjectId > 0) {
                        // 2A. Single Subject View
                        $results = $this->db->fetchAll(
                            "SELECT m.*, s.admission_number, s.first_name, s.last_name, s.gender, sub.name as subject_name
                            FROM marks m
                            INNER JOIN students s ON m.student_id = s.id
                            INNER JOIN subjects sub ON m.subject_id = sub.id
                            WHERE m.academic_year_id = ?
                            AND m.term_id = ?
                            AND m.subject_id = ?
                            AND m.student_id IN ({$placeholders})
                            AND m.school_id = ?
                            ORDER BY s.last_name ASC, s.first_name ASC",
                            array_merge([$academicYearId, $termId, (int)$subjectId], $studentIds, [$schoolId])
                        );
                    } else {
                        // 2B. All Subjects Grid Matrix View
                        $rawMarks = $this->db->fetchAll(
                            "SELECT m.student_id, m.subject_id, m.marks_obtained
                            FROM marks m
                            WHERE m.academic_year_id = ?
                            AND m.term_id = ?
                            AND m.student_id IN ({$placeholders})
                            AND m.school_id = ?",
                            array_merge([$academicYearId, $termId], $studentIds, [$schoolId])
                        );

                        foreach ($rawMarks as $row) {
                            $resultsMatrix[$row['student_id']][$row['subject_id']] = $row['marks_obtained'];
                        }
                    }
                }
            }
        }

        echo $this->view->renderWithLayout('examinations/results/selector', 'default', [
            'title'         => 'Class Results',
            'academicYears' => $academicYears,
            'terms'         => $terms,
            'classes'       => $classes,
            'streams'       => $streams,
            'examinations'  => $examinations,
            'subjects'      => $subjects,
            'examination'   => $examination,
            'students'      => $students,
            'results'       => $results,
            'resultsMatrix' => $resultsMatrix
        ]);
    }

    public function getStreamsByClass($params = []): void
    {
        header('Content-Type: application/json');

        if (!$this->auth->check()) {
            echo json_encode([]);
            exit;
        }

        $classId  = (int)($_GET['class_id'] ?? 0);
        $schoolId = $this->auth->getUser()->school_id ?? 1;

        if ($classId <= 0) {
            echo json_encode([]);
            exit;
        }

        $streams = $this->db->fetchAll(
            "SELECT id, name FROM streams WHERE class_id = :class_id AND school_id = :school_id ORDER BY name ASC",
            ['class_id' => $classId, 'school_id' => $schoolId]
        );

        echo json_encode($streams ?: []);
        exit;
    }
}