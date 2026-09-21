<?php
// File: /app/Controllers/AcademicReportController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AcademicReportService;
use NexaT\Services\AuditService;

class AcademicReportController extends Controller
{
    private $reportService;

    public function __construct()
    {
        parent::__construct();
        $this->reportService = new AcademicReportService();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('reports.academic.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $filters = [
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id' => $_GET['term_id'] ?? null,
            'class_id' => $_GET['class_id'] ?? null,
            'subject_id' => $_GET['subject_id'] ?? null,
        ];

        $filters = array_filter($filters);

        $reportFilters = $this->reportService->getReportFilters($schoolId);
        $stats = $this->reportService->getDashboardStats($schoolId, $filters);

        echo $this->view->render('reports/academic/report_card_view', [
            'data'             => $data,
            'academicYearId'   => $academicYearId,
            'termId'           => $termId,
            'academicYearName' => $academicYear['name'] ?? '',
            'termName'         => $term['name'] ?? '',
            'options'          => $options,
        ]);
    }

    public function reportCards(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('reports.report_cards.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $reportFilters = $this->reportService->getReportFilters($schoolId);

        $students = $this->db->fetchAll(
            "SELECT DISTINCT s.id, s.admission_number, s.first_name, s.last_name, cl.name as class_name
             FROM students s
             LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.status = 'active'
             LEFT JOIN classes cl ON se.class_id = cl.id
             WHERE s.school_id = :s
             ORDER BY s.last_name ASC",
            ['s' => $schoolId]
        );

        echo $this->view->renderWithLayout('reports/academic/report_cards', 'default', [
            'title' => 'Student Report Cards',
            'filters' => $reportFilters,
            'students' => $students,
        ]);
    }

    public function generateReportCard(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('reports.report_cards.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $studentId      = (int)($_GET['student_id'] ?? 0);
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);

        if (!$studentId || !$academicYearId || !$termId) {
            $_SESSION['flash_error'] = 'Please select student, academic year and term.';
            header('Location: ' . BASE_URL . '/reports/academic/report-cards');
            exit;
        }

        // Verify student belongs to this school
        $student = $this->db->fetch(
            "SELECT id FROM students WHERE id = :id AND school_id = :s",
            ['id' => $studentId, 's' => $schoolId]
        );
        if (!$student) {
            $_SESSION['flash_error'] = 'Student not found.';
            header('Location: ' . BASE_URL . '/reports/academic/report-cards');
            exit;
        }

        // Selected exams
        $examSelection = $_GET['examinations'] ?? [];
        if (in_array('all', $examSelection)) {
            $allExams = $this->db->fetchAll(
                "SELECT id FROM examinations 
                WHERE school_id = :s AND academic_year_id = :y AND academic_period_id = :t",
                ['s' => $schoolId, 'y' => $academicYearId, 't' => $termId]
            );
            $examinationIds = array_column($allExams, 'id');
        } else {
            $examinationIds = array_map('intval', $examSelection);
        }

        $options = [
            'report_name'        => $_GET['report_name']        ?? 'End of Term Report',
            'report_color'       => $_GET['report_color']       ?? 'bw',
            'report_format'      => $_GET['report_format']      ?? 'progression',

            // Defaults must match _report_config.php
            'show_positions'     => ($_GET['show_positions']    ?? 'no')  === 'yes',
            'show_photo'         => ($_GET['show_photo']        ?? 'yes') === 'yes',
            'show_division'      => ($_GET['show_division']     ?? 'no')  === 'yes',
            'show_grades'        => ($_GET['show_grades']       ?? 'yes') === 'yes',
            'grades_per_exam'    => ($_GET['grades_per_exam']   ?? 'yes') === 'yes',
            'show_initials'      => ($_GET['show_initials']     ?? 'yes') === 'yes',

            'grade_format'       => $_GET['grade_format']       ?? 'default',
            'opt_comments'       => ($_GET['opt_comments']      ?? 'yes') === 'yes',
            'show_skills'        => ($_GET['show_skills']       ?? 'no')  === 'yes',
            'hm_comment'         => $_GET['hm_comment']         ?? 'auto',
            'ct_comment'         => $_GET['ct_comment']         ?? 'auto',
            'show_names'         => ($_GET['show_names']        ?? 'no')  === 'yes',
            'auto_signatures'    => ($_GET['auto_signatures']   ?? 'no')  === 'yes',
            'show_fees'          => ($_GET['show_fees']         ?? 'no')  === 'yes',
            'show_next_term'     => ($_GET['show_next_term']    ?? 'no')  === 'yes',
            'show_remarks'       => ($_GET['show_remarks']      ?? 'no')  === 'yes',
            'show_performance'   => ($_GET['show_performance']  ?? 'no')  === 'yes',
            'mark_sheet'         => $_GET['mark_sheet']         ?? null,

            'final_grade_method' => $_GET['final_grade_method'] ?? 'average',
            'position_ranking'   => $_GET['position_ranking']   ?? 'aggregate',
        ];

        $data = $this->reportService->getMultiExamReportCard(
            $studentId, $academicYearId, $termId, $schoolId, $examinationIds, $options
        );

        if (empty($data)) {
            $_SESSION['flash_error'] = 'No data found for the selected criteria.';
            header('Location: ' . BASE_URL . '/reports/academic/report-cards');
            exit;
        }

        // Names for the view
        $academicYear = $this->db->fetch("SELECT name FROM academic_years WHERE id = :id", ['id' => $academicYearId]);
        $term         = $this->db->fetch("SELECT name FROM terms WHERE id = :id", ['id' => $termId]);

        // Audit
        if ($this->audit) {
            $this->audit->log(
                $this->auth->id(),
                'Report Card Generated',
                'reports',
                "Generated multi-exam report card for student ID: {$studentId}"
            );
        }

        echo $this->view->render('reports/academic/report_card_view', [
            'data'             => $data,
            'academicYearId'   => $academicYearId,
            'termId'           => $termId,
            'academicYearName' => $academicYear['name'] ?? '',
            'termName'         => $term['name'] ?? '',
            'options'          => $options,
        ]);
    }

    public function subjectAnalysis(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('reports.subject_analysis.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $filters = [
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id' => $_GET['term_id'] ?? null,
            'class_id' => $_GET['class_id'] ?? null,
            'subject_id' => $_GET['subject_id'] ?? null,
        ];
        $filters = array_filter($filters);

        $reportFilters = $this->reportService->getReportFilters($schoolId);
        $stats = !empty($filters['subject_id'])
            ? $this->reportService->getSubjectPerformance($schoolId, $filters)
            : [];

        echo $this->view->renderWithLayout('reports/academic/subject_analysis', 'default', [
            'title' => 'Subject Performance Analysis',
            'filters' => $reportFilters,
            'stats' => $stats,
            'selectedFilters' => $filters,
        ]);
    }

    public function classAnalysis(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('reports.class_analysis.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $filters = [
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id' => $_GET['term_id'] ?? null,
            'class_id' => $_GET['class_id'] ?? null,
            'stream_id' => $_GET['stream_id'] ?? null,
        ];
        $filters = array_filter($filters);

        $reportFilters = $this->reportService->getReportFilters($schoolId);
        $matrix = !empty($filters['academic_year_id'])
            ? $this->reportService->getClassResultsMatrix($schoolId, $filters)
            : ['students' => [], 'subjects' => [], 'marks' => []];

        echo $this->view->renderWithLayout('reports/academic/class_analysis', 'default', [
            'title' => 'Class Performance Analysis',
            'filters' => $reportFilters,
            'matrix' => $matrix,
            'selectedFilters' => $filters,
        ]);
    }

    public function batchReportCards(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('reports.report_cards.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $filters = $this->reportService->getReportFilters($schoolId);

        echo $this->view->renderWithLayout('reports/academic/batch_report_cards', 'default', [
            'title' => 'Batch Report Cards',
            'filters' => $filters,
        ]);
    }

    public function generateBatchReportCards(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('reports.report_cards.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId       = $this->auth->getUser()->school_id ?? 1;
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = !empty($_GET['stream_id']) ? (int)$_GET['stream_id'] : null;

        if (!$academicYearId || !$termId || !$classId) {
            $_SESSION['flash_error'] = 'Missing required parameters.';
            header('Location: ' . BASE_URL . '/reports/academic/batch-report-cards');
            exit;
        }

        // ----------------------------------------------
        // Selected exams
        // ----------------------------------------------
        $examSelection = $_GET['examinations'] ?? [];
        if (empty($examSelection)) $examSelection = ['all'];

        if (in_array('all', $examSelection)) {
            $allExams = $this->db->fetchAll(
                "SELECT id FROM examinations 
                WHERE school_id = :s AND academic_year_id = :y AND academic_period_id = :t",
                ['s' => $schoolId, 'y' => $academicYearId, 't' => $termId]
            );
            $examinationIds = array_column($allExams, 'id');
        } else {
            $examinationIds = array_map('intval', $examSelection);
        }

        if (empty($examinationIds)) {
            $_SESSION['flash_error'] = 'No examinations found for the selected year and term.';
            header('Location: ' . BASE_URL . '/reports/academic/batch-report-cards');
            exit;
        }

        // ----------------------------------------------
        // Report options
        // ----------------------------------------------
        $options = [
            'report_name'      => $_GET['report_name']      ?? 'End of Term Report',
            'report_color'     => $_GET['report_color']     ?? 'bw',
            'report_format'    => $_GET['report_format']    ?? 'progression',
            'show_positions'   => ($_GET['show_positions']  ?? 'no') === 'yes',
            'show_photo'       => ($_GET['show_photo']      ?? 'no') === 'yes',
            'show_division'    => ($_GET['show_division']   ?? 'no') === 'yes',
            'show_grades'      => ($_GET['show_grades']     ?? 'no') === 'yes',
            'grades_per_exam'  => ($_GET['grades_per_exam'] ?? 'no') === 'yes',
            'grade_format'     => $_GET['grade_format']     ?? 'default',
            'opt_comments'     => ($_GET['opt_comments']    ?? 'no') === 'yes',
            'show_skills'      => ($_GET['show_skills']     ?? 'no') === 'yes',
            'hm_comment'       => $_GET['hm_comment']       ?? 'no',
            'ct_comment'       => $_GET['ct_comment']       ?? 'no',
            'show_names'       => ($_GET['show_names']      ?? 'no') === 'yes',
            'auto_signatures'  => ($_GET['auto_signatures'] ?? 'no') === 'yes',
            'show_fees'        => ($_GET['show_fees']       ?? 'no') === 'yes',
            'show_next_term'   => ($_GET['show_next_term']  ?? 'no') === 'yes',
            'show_remarks'     => ($_GET['show_remarks']    ?? 'no') === 'yes',
            'show_performance' => ($_GET['show_performance']?? 'no') === 'yes',
            'final_grade_method' => $_GET['final_grade_method'] ?? 'average',
            'position_ranking'   => $_GET['position_ranking']   ?? 'aggregate',
            'show_initials'      => ($_GET['show_initials'] ?? 'yes') === 'yes',
        ];

        // ----------------------------------------------
        // Get students in the class
        // ----------------------------------------------
        $sql = "SELECT s.id
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                WHERE se.status = 'active'
                AND se.academic_year_id = :year_id
                AND se.class_id = :class_id
                AND s.school_id = :school_id";
        $params = [
            'year_id'    => $academicYearId,
            'class_id'   => $classId,
            'school_id'  => $schoolId,
        ];
        if ($streamId) {
            $sql .= " AND se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }
        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";

        $students = $this->db->fetchAll($sql, $params);

        // ----------------------------------------------
        // Names
        // ----------------------------------------------
        $academicYear = $this->db->fetch("SELECT name FROM academic_years WHERE id = :id", ['id' => $academicYearId]);
        $term         = $this->db->fetch("SELECT name FROM terms WHERE id = :id", ['id' => $termId]);

        // ----------------------------------------------
        // Build a report card dataset per student
        // ----------------------------------------------
        $allData = [];
        foreach ($students as $s) {
            $data = $this->reportService->getMultiExamReportCard(
                (int)$s['id'],
                $academicYearId,
                $termId,
                $schoolId,
                $examinationIds,
                $options
            );
            if (!empty($data) && !empty($data['subjects'])) {
                $allData[] = $data;
            }
        }

        // ----------------------------------------------
        // Render
        // ----------------------------------------------
        echo $this->view->render('reports/academic/batch_report_cards_view', [
            'allData'          => $allData,
            'academicYearName' => $academicYear['name'] ?? '',
            'termName'         => $term['name'] ?? '',
            'options'          => $options,
        ]);
    }
}