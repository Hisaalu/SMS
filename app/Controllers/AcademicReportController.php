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

    public function subjectAnalysis(): void
    {
        $this->requirePermission('reports.subject_analysis.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'stream_id'        => $_GET['stream_id']        ?? null,
            'subject_id'       => $_GET['subject_id']       ?? null,
            'examination_id'   => $_GET['examination_id']   ?? null,
        ]);

        $matrix = (!empty($filters['academic_year_id']) && !empty($filters['class_id']) && !empty($filters['subject_id']))
            ? $this->reportService->getSubjectAnalysisMatrix($schoolId, $filters)
            : ['subject' => null, 'class' => null, 'students' => [], 'marks' => [], 'summary' => [], 'headers' => []];

        echo $this->view->renderWithLayout('reports/academic/subject_analysis', 'default', [
            'title'           => 'Subject Performance Analysis',
            'filters'         => $this->reportService->getReportFilters($schoolId),
            'matrix'          => $matrix,
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
            'examination_id'   => $_GET['examination_id']   ?? null,
        ]);

        $matrix = !empty($filters['academic_year_id']) && !empty($filters['class_id'])
            ? $this->reportService->getClassAnalysisMatrix($schoolId, $filters)
            : ['students' => [], 'subjects' => [], 'marks' => [], 'totals' => [], 'headers' => []];

        echo $this->view->renderWithLayout('reports/academic/class_analysis', 'default', [
            'title'           => 'Class Performance Analysis',
            'filters'         => $this->reportService->getReportFilters($schoolId),
            'matrix'          => $matrix,
            'selectedFilters' => $filters,
        ]);
    }

    public function ReportCards(): void
    {
        $this->requirePermission('reports.report_cards.view');

        $schoolId = $this->schoolId();

        echo $this->view->renderWithLayout('reports/academic/report_cards', 'default', [
            'title'   => 'Report Cards',
            'filters' => $this->reportService->getReportFilters($schoolId),
        ]);
    }

    public function generateReportCards(): void
    {
        $this->requirePermission('reports.report_cards.view');

        $schoolId       = $this->schoolId();
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = !empty($_GET['stream_id']) ? (int)$_GET['stream_id'] : null;

        if (!$academicYearId || !$termId || !$classId) {
            $this->flashError('Missing required parameters.');
            $this->redirect('/reports/academic/report-cards');
        }

        $examinationIds = $this->resolveExaminationIds(
            $_GET['examinations'] ?? ['all'],
            $schoolId,
            $academicYearId,
            $termId
        );

        if (empty($examinationIds)) {
            $this->flashError('No examinations found for the selected year and term.');
            $this->redirect('/reports/academic/report-cards');
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

        echo $this->view->render('reports/academic/report_cards_view', [
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
            'report_name'         => $input['report_name']         ?? 'End of Term Report',
            'report_color'        => $input['report_color']        ?? 'bw',
            'report_format'       => $input['report_format']       ?? 'progression',
            'show_positions'      => ($input['show_positions']     ?? 'no')  === 'yes',
            'show_photo'          => ($input['show_photo']         ?? 'yes') === 'yes',
            'show_division'       => ($input['show_division']      ?? 'no')  === 'yes',
            'show_grades'         => ($input['show_grades']        ?? 'yes') === 'yes',
            'grades_per_exam'     => ($input['grades_per_exam']    ?? 'yes') === 'yes',
            'show_initials'       => ($input['show_initials']      ?? 'yes') === 'yes',
            'show_other_subjects' => ($input['show_skills']        ?? 'no')  === 'yes',
            'best_subjects_count' => max(0, (int)($input['best_subjects_count'] ?? 0)),
            'grade_format'        => $input['grade_format']        ?? 'default',
            'opt_comments'        => ($input['opt_comments']       ?? 'yes') === 'yes',
            'show_skills'         => ($input['show_skills']        ?? 'no')  === 'yes',
            'hm_comment'          => $input['hm_comment']          ?? 'auto',
            'ct_comment'          => $input['ct_comment']          ?? 'auto',
            'show_names'          => ($input['show_names']         ?? 'no')  === 'yes',
            'auto_signatures'     => ($input['auto_signatures']    ?? 'no')  === 'yes',
            'show_fees'           => ($input['show_fees']          ?? 'no')  === 'yes',
            'show_next_term'      => ($input['show_next_term']     ?? 'no')  === 'yes',
            'show_remarks'        => ($input['show_remarks']       ?? 'no')  === 'yes',
            'show_performance'    => ($input['show_performance']   ?? 'no')  === 'yes',
            'mark_sheet'          => $input['mark_sheet']          ?? null,
            'final_grade_method'  => $input['final_grade_method']  ?? 'average',
            'position_ranking'    => $input['position_ranking']    ?? 'aggregate',
        ];
    }

    public function printClassAnalysis(): void
    {
        $this->requirePermission('reports.class_analysis.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'stream_id'        => $_GET['stream_id']        ?? null,
            'examination_id'   => $_GET['examination_id']   ?? null,
        ]);

        $matrix = !empty($filters['academic_year_id']) && !empty($filters['class_id'])
            ? $this->reportService->getClassAnalysisMatrix($schoolId, $filters)
            : ['students' => [], 'subjects' => [], 'marks' => [], 'totals' => [], 'headers' => []];

        $meta = [];
        if (!empty($filters['academic_year_id'])) {
            $row = $this->db->fetch("SELECT name FROM academic_years WHERE id = :id AND school_id = :s", [
                'id' => (int)$filters['academic_year_id'], 's' => $schoolId
            ]);
            if ($row) $meta[] = ['label' => 'Academic Year', 'value' => $row['name']];
        }
        if (!empty($filters['term_id'])) {
            $row = $this->db->fetch("SELECT name FROM terms WHERE id = :id AND school_id = :s", [
                'id' => (int)$filters['term_id'], 's' => $schoolId
            ]);
            if ($row) $meta[] = ['label' => 'Term', 'value' => $row['name']];
        }
        if (!empty($filters['class_id'])) {
            $row = $this->db->fetch("SELECT name FROM classes WHERE id = :id AND school_id = :s", [
                'id' => (int)$filters['class_id'], 's' => $schoolId
            ]);
            if ($row) $meta[] = ['label' => 'Class', 'value' => $row['name']];
        }
        if (!empty($filters['stream_id'])) {
            $row = $this->db->fetch("SELECT name FROM streams WHERE id = :id AND school_id = :s", [
                'id' => (int)$filters['stream_id'], 's' => $schoolId
            ]);
            if ($row) $meta[] = ['label' => 'Stream', 'value' => $row['name']];
        }
        if (!empty($filters['examination_id'])) {
            $row = $this->db->fetch("SELECT name FROM examinations WHERE id = :id AND school_id = :s", [
                'id' => (int)$filters['examination_id'], 's' => $schoolId
            ]);
            if ($row) $meta[] = ['label' => 'Examination', 'value' => $row['name']];
        }
        $meta[] = ['label' => 'Students', 'value' => count($matrix['students'])];

        $user = $this->auth->getUser();
        $printedBy = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
        if ($printedBy === '') $printedBy = 'System';

        $this->audit('Class Analysis Printed', 'reports', 'Printed class performance analysis.');

        echo $this->view->renderWithLayout('reports/academic/print_class_analysis', 'print', [
            'title'     => 'Class Performance Analysis',
            'matrix'    => $matrix,
            'meta'      => $meta,
            'printedBy' => $printedBy,
        ]);
    }

    public function exportClassAnalysis(): void
    {
        $this->requirePermission('reports.class_analysis.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'stream_id'        => $_GET['stream_id']        ?? null,
            'examination_id'   => $_GET['examination_id']   ?? null,
        ]);

        $matrix = !empty($filters['academic_year_id']) && !empty($filters['class_id'])
            ? $this->reportService->getClassAnalysisMatrix($schoolId, $filters)
            : ['students' => [], 'subjects' => [], 'marks' => [], 'totals' => [], 'headers' => []];

        $this->audit('Class Analysis Exported', 'reports', 'Exported class performance analysis to CSV.');

        $filename = 'class_analysis_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Class Performance Analysis']);
        fputcsv($out, ['Generated At', date('Y-m-d H:i:s')]);
        fputcsv($out, []);

        $headers = ['#', 'ADM NO', 'STUDENT', 'SEX'];
        foreach ($matrix['subjects'] as $subj) {
            $isContrib = !isset($subj['contributes']) || !empty($subj['contributes']);
            $headers[] = strtoupper($subj['code'] ?: $subj['name']) . ($isContrib ? '' : ' *');
        }
        $headers = array_merge($headers, ['TOTAL', 'AVG', 'AGG', 'DIV']);
        fputcsv($out, $headers);

        foreach ($matrix['students'] as $i => $st) {
            $sid = (int)$st['id'];
            $t   = $matrix['totals'][$sid] ?? null;

            $rawSex = strtoupper(trim($st['gender'] ?? ''));
            $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true) ? 'F'
                    : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');

            $row = [
                $i + 1,
                $st['admission_number'] ?? '',
                strtoupper(trim(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? ''))),
                $sex,
            ];

            foreach ($matrix['subjects'] as $subj) {
                $mark = $matrix['marks'][$sid][(int)$subj['id']] ?? null;
                $row[] = $mark !== null ? (int)$mark : '';
            }

                        $row[] = $t ? (int)$t['total'] : '';
            $row[] = $t ? $t['average'] : '';

            if (!$t) {
                $row[] = '';
            } elseif (!empty($t['has_missing'])) {
                $row[] = 'X';
            } else {
                $row[] = (int)$t['aggregate'];
            }

            if (!$t) {
                $row[] = '';
            } elseif (!empty($t['has_missing'])) {
                $row[] = 'U';
            } elseif (!empty($t['division_code'])) {
                $row[] = strtoupper((string)$t['division_code']);
            } else {
                $row[] = '';
            }

            fputcsv($out, $row);
        }

        fclose($out);
        exit;
    }

    public function printSubjectAnalysis(): void
    {
        $this->requirePermission('reports.subject_analysis.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'stream_id'        => $_GET['stream_id']        ?? null,
            'subject_id'       => $_GET['subject_id']       ?? null,
            'examination_id'   => $_GET['examination_id']   ?? null,
        ]);

        $matrix = (!empty($filters['academic_year_id']) && !empty($filters['class_id']) && !empty($filters['subject_id']))
            ? $this->reportService->getSubjectAnalysisMatrix($schoolId, $filters)
            : ['subject' => null, 'class' => null, 'students' => [], 'marks' => [], 'summary' => [], 'headers' => []];

        $meta = [];
        foreach ([
            'academic_year_id' => 'Academic Year',
            'term_id'          => 'Term',
            'class_id'         => 'Class',
            'stream_id'        => 'Stream',
            'examination_id'   => 'Examination',
        ] as $key => $label) {
            if (empty($filters[$key])) continue;
            $table = match ($key) {
                'academic_year_id' => 'academic_years',
                'term_id'          => 'terms',
                'class_id'         => 'classes',
                'stream_id'        => 'streams',
                'examination_id'   => 'examinations',
            };
            $row = $this->db->fetch(
                "SELECT name FROM {$table} WHERE id = :id AND school_id = :s",
                ['id' => (int)$filters[$key], 's' => $schoolId]
            );
            if ($row) $meta[] = ['label' => $label, 'value' => $row['name']];
        }
        if (!empty($matrix['subject'])) {
            $meta[] = ['label' => 'Subject', 'value' => ($matrix['subject']['code'] ?: $matrix['subject']['name'])];
        }
        $meta[] = ['label' => 'Students', 'value' => count($matrix['students'])];

        $user = $this->auth->getUser();
        $printedBy = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
        if ($printedBy === '') $printedBy = 'System';

        $this->audit('Subject Analysis Printed', 'reports', 'Printed subject performance analysis.');

        echo $this->view->renderWithLayout('reports/academic/print_subject_analysis', 'print', [
            'title'     => 'Subject Performance Analysis',
            'matrix'    => $matrix,
            'meta'      => $meta,
            'printedBy' => $printedBy,
        ]);
    }

    public function exportSubjectAnalysis(): void
    {
        $this->requirePermission('reports.subject_analysis.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'academic_year_id' => $_GET['academic_year_id'] ?? null,
            'term_id'          => $_GET['term_id']          ?? null,
            'class_id'         => $_GET['class_id']         ?? null,
            'stream_id'        => $_GET['stream_id']        ?? null,
            'subject_id'       => $_GET['subject_id']       ?? null,
            'examination_id'   => $_GET['examination_id']   ?? null,
        ]);

        $matrix = (!empty($filters['academic_year_id']) && !empty($filters['class_id']) && !empty($filters['subject_id']))
            ? $this->reportService->getSubjectAnalysisMatrix($schoolId, $filters)
            : ['subject' => null, 'class' => null, 'students' => [], 'marks' => [], 'summary' => [], 'headers' => []];

        $this->audit('Subject Analysis Exported', 'reports', 'Exported subject performance analysis to CSV.');

        $filename = 'subject_analysis_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Subject Performance Analysis']);
        fputcsv($out, ['Generated At', date('Y-m-d H:i:s')]);
        if (!empty($matrix['subject'])) {
            fputcsv($out, ['Subject', $matrix['subject']['code'] . ' - ' . $matrix['subject']['name']]);
        }
        fputcsv($out, []);

        fputcsv($out, [
            '#', 'ADM NO', 'STUDENT', 'SEX',
            'MARK', 'GRADE', 'SCORE', 'REMARK',
        ]);

        foreach ($matrix['students'] as $i => $st) {
            $sid = (int)$st['id'];
            $m   = $matrix['marks'][$sid] ?? [];

            $rawSex = strtoupper(trim($st['gender'] ?? ''));
            $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true) ? 'F'
                    : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');

            fputcsv($out, [
                $i + 1,
                $st['admission_number'] ?? '',
                strtoupper(trim(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? ''))),
                $sex,
                $m['mark']     ?? '',
                $m['grade']    ?? '',
                $m['score']    ?? '',
                $m['remark']   ?? '',
            ]);
        }

        fclose($out);
        exit;
    }
}