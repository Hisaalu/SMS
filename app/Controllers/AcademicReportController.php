<?php
// File: /app/Controllers/AcademicReportController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AcademicReportService;

class AcademicReportController extends Controller
{
    private AcademicReportService $reportService;

    public function __construct()
    {
        parent::__construct();
        $this->reportService = new AcademicReportService();
    }

    public function index(): void
    {
        $this->requirePermission('reports.academic.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'subject_id'       => $_GET['subject_id']       ?? null,
        ]);

        echo $this->view->renderWithLayout('reports/academic/index', 'default', [
            'title'           => 'Academic Reports',
            'filters'         => $this->reportService->getReportFilters($schoolId),
            'stats'           => $this->reportService->getDashboardStats($schoolId, $filters),
            'selectedFilters' => $filters,
        ]);
    }

    public function reportCards(): void
    {
        $this->requirePermission('reports.report_cards.view');

        $schoolId = $this->schoolId();

        $students = $this->db->fetchAll(
            "SELECT DISTINCT s.id, s.admission_number, s.first_name, s.last_name, cl.name AS class_name
             FROM students s
             LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.status = 'active'
             LEFT JOIN classes cl ON se.class_id = cl.id
             WHERE s.school_id = :s
             ORDER BY s.last_name ASC",
            ['s' => $schoolId]
        );

        echo $this->view->renderWithLayout('reports/academic/report_cards', 'default', [
            'title'    => 'Student Report Cards',
            'filters'  => $this->reportService->getReportFilters($schoolId),
            'students' => $students,
        ]);
    }

    public function generateReportCard(): void
    {
        $this->requirePermission('reports.report_cards.view');

        $schoolId       = $this->schoolId();
        $studentId      = (int)($_GET['student_id'] ?? 0);
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);

        if (!$studentId || !$academicYearId || !$termId) {
            $this->flashError('Please select student, academic year and term.');
            $this->redirect('/reports/academic/report-cards');
        }

        $student = $this->db->fetch(
            "SELECT id FROM students WHERE id = :id AND school_id = :s",
            ['id' => $studentId, 's' => $schoolId]
        );

        if (!$student) {
            $this->flashError('Student not found.');
            $this->redirect('/reports/academic/report-cards');
        }

        $enrollment = $this->db->fetch(
            "SELECT id FROM student_enrollments
             WHERE student_id = :sid AND academic_year_id = :yid AND status = 'active'
             LIMIT 1",
            ['sid' => $studentId, 'yid' => $academicYearId]
        );

        if (!$enrollment) {
            $this->flashError(
                'This student is not enrolled for the selected academic year. '
                . 'Please pick a different year or enroll the student first.'
            );
            $this->redirect('/reports/academic/report-cards');
        }

        $examinationIds = $this->resolveExaminationIds(
            $_GET['examinations'] ?? ['all'],
            $schoolId,
            $academicYearId,
            $termId
        );

        if (empty($examinationIds)) {
            $this->flashError(
                'No examinations found for the selected academic year and term. '
                . 'Please create or select different exams.'
            );
            $this->redirect('/reports/academic/report-cards');
        }

        $options = $this->collectReportOptions($_GET);

        $data = $this->reportService->getMultiExamReportCard(
            $studentId, $academicYearId, $termId, $schoolId, $examinationIds, $options
        );

        if (empty($data) || empty($data['subjects'])) {
            $this->flashError(
                'No marks found for this student under the selected criteria. '
                . 'Check that the student has marks entered for the selected exams.'
            );
            $this->redirect('/reports/academic/report-cards');
        }

        $academicYear = $this->db->fetch("SELECT name FROM academic_years WHERE id = :id", ['id' => $academicYearId]);
        $term         = $this->db->fetch("SELECT name FROM terms WHERE id = :id", ['id' => $termId]);

        $this->audit('Report Card Generated', 'reports', "Generated multi-exam report card for student ID: {$studentId}");

        echo $this->view->render('reports/academic/report_card_view', [
            'card'             => $data,
            'academicYearId'   => $academicYearId,
            'termId'           => $termId,
            'academicYearName' => $academicYear['name'] ?? '',
            'termName'         => $term['name'] ?? '',
            'options'          => $options,
        ]);
    }

    public function subjectAnalysis(): void
    {
        $this->requirePermission('reports.subject_analysis.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'subject_id'       => $_GET['subject_id']       ?? null,
        ]);

        $stats = !empty($filters['subject_id'])
            ? $this->reportService->getSubjectPerformance($schoolId, $filters)
            : [];

        echo $this->view->renderWithLayout('reports/academic/subject_analysis', 'default', [
            'title'           => 'Subject Performance Analysis',
            'filters'         => $this->reportService->getReportFilters($schoolId),
            'stats'           => $stats,
            'selectedFilters' => $filters,
        ]);
    }

    public function classAnalysis(): void
    {
        $this->requirePermission('reports.class_analysis.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'stream_id'        => $_GET['stream_id']        ?? null,
        ]);

        $matrix = !empty($filters['academic_year_id'])
            ? $this->reportService->getClassResultsMatrix($schoolId, $filters)
            : ['students' => [], 'subjects' => [], 'marks' => []];

        echo $this->view->renderWithLayout('reports/academic/class_analysis', 'default', [
            'title'           => 'Class Performance Analysis',
            'filters'         => $this->reportService->getReportFilters($schoolId),
            'matrix'          => $matrix,
            'selectedFilters' => $filters,
        ]);
    }

    public function batchReportCards(): void
    {
        $this->requirePermission('reports.report_cards.view');

        $schoolId = $this->schoolId();

        echo $this->view->renderWithLayout('reports/academic/batch_report_cards', 'default', [
            'title'   => 'Batch Report Cards',
            'filters' => $this->reportService->getReportFilters($schoolId),
        ]);
    }

    public function generateBatchReportCards(): void
    {
        $this->requirePermission('reports.report_cards.view');

        $schoolId       = $this->schoolId();
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = !empty($_GET['stream_id']) ? (int)$_GET['stream_id'] : null;

        if (!$academicYearId || !$termId || !$classId) {
            $this->flashError('Missing required parameters.');
            $this->redirect('/reports/academic/batch-report-cards');
        }

        $examinationIds = $this->resolveExaminationIds(
            $_GET['examinations'] ?? ['all'],
            $schoolId,
            $academicYearId,
            $termId
        );

        if (empty($examinationIds)) {
            $this->flashError('No examinations found for the selected year and term.');
            $this->redirect('/reports/academic/batch-report-cards');
        }

        $options  = $this->collectReportOptions($_GET);
        $students = $this->loadClassStudents($schoolId, $academicYearId, $classId, $streamId);

        $academicYear = $this->db->fetch("SELECT name FROM academic_years WHERE id = :id", ['id' => $academicYearId]);
        $term         = $this->db->fetch("SELECT name FROM terms WHERE id = :id", ['id' => $termId]);

        $allData = [];
        foreach ($students as $s) {
            $data = $this->reportService->getMultiExamReportCard(
                (int)$s['id'], $academicYearId, $termId, $schoolId, $examinationIds, $options
            );
            if (!empty($data) && !empty($data['subjects'])) {
                $allData[] = $data;
            }
        }

        echo $this->view->render('reports/academic/batch_report_cards_view', [
            'allData'          => $allData,
            'academicYearName' => $academicYear['name'] ?? '',
            'termName'         => $term['name'] ?? '',
            'options'          => $options,
        ]);
    }

    private function resolveExaminationIds(array $selection, int $schoolId, int $yearId, int $termId): array
    {
        if (empty($selection)) {
            $selection = ['all'];
        }

        if (in_array('all', $selection, true)) {
            $rows = $this->db->fetchAll(
                "SELECT id FROM examinations
                 WHERE school_id = :s AND academic_year_id = :y AND academic_period_id = :t",
                ['s' => $schoolId, 'y' => $yearId, 't' => $termId]
            );
            return array_column($rows, 'id');
        }

        return array_values(array_filter(array_map('intval', $selection)));
    }

    private function loadClassStudents(int $schoolId, int $yearId, int $classId, ?int $streamId): array
    {
        $sql = "SELECT s.id
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                WHERE se.status = 'active'
                  AND se.academic_year_id = :year_id
                  AND se.class_id = :class_id
                  AND s.school_id = :school_id";

        $params = [
            'year_id'   => $yearId,
            'class_id'  => $classId,
            'school_id' => $schoolId,
        ];

        if ($streamId) {
            $sql .= " AND se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }

        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    private function collectReportOptions(array $input): array
    {
        return [
            'report_name'        => $input['report_name']        ?? 'End of Term Report',
            'report_color'       => $input['report_color']       ?? 'bw',
            'report_format'      => $input['report_format']      ?? 'progression',
            'show_positions'     => ($input['show_positions']    ?? 'no')  === 'yes',
            'show_photo'         => ($input['show_photo']        ?? 'yes') === 'yes',
            'show_division'      => ($input['show_division']     ?? 'no')  === 'yes',
            'show_grades'        => ($input['show_grades']       ?? 'yes') === 'yes',
            'grades_per_exam'    => ($input['grades_per_exam']   ?? 'yes') === 'yes',
            'show_initials'      => ($input['show_initials']     ?? 'yes') === 'yes',
            'grade_format'       => $input['grade_format']       ?? 'default',
            'opt_comments'       => ($input['opt_comments']      ?? 'yes') === 'yes',
            'show_skills'        => ($input['show_skills']       ?? 'no')  === 'yes',
            'hm_comment'         => $input['hm_comment']         ?? 'auto',
            'ct_comment'         => $input['ct_comment']         ?? 'auto',
            'show_names'         => ($input['show_names']        ?? 'no')  === 'yes',
            'auto_signatures'    => ($input['auto_signatures']   ?? 'no')  === 'yes',
            'show_fees'          => ($input['show_fees']         ?? 'no')  === 'yes',
            'show_next_term'     => ($input['show_next_term']    ?? 'no')  === 'yes',
            'show_remarks'       => ($input['show_remarks']      ?? 'no')  === 'yes',
            'show_performance'   => ($input['show_performance']  ?? 'no')  === 'yes',
            'mark_sheet'         => $input['mark_sheet']         ?? null,
            'final_grade_method' => $input['final_grade_method'] ?? 'average',
            'position_ranking'   => $input['position_ranking']   ?? 'aggregate',
        ];
    }
}