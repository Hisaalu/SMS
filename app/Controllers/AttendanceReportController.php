<?php
// File: /app/Controllers/AttendanceReportController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AttendanceReportService;
use Throwable;

class AttendanceReportController extends Controller
{
    private AttendanceReportService $reportService;

    public function __construct()
    {
        parent::__construct();
        $this->reportService = new AttendanceReportService();
    }

    public function registers(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'class_id'   => $_GET['class_id']   ?? null,
            'stream_id'  => $_GET['stream_id']  ?? null,
            'session_id' => $_GET['session_id'] ?? null,
            'status'     => $_GET['status']     ?? null,
        ]);

        echo $this->view->renderWithLayout('reports/attendance/registers', 'default', [
            'title'           => 'Attendance Registers',
            'registers'       => $this->reportService->getRegisters($schoolId, $filters),
            'filters'         => $this->getFilterOptions($schoolId),
            'selectedFilters' => $filters,
        ]);
    }

    public function printRegisters(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'class_id'   => $_GET['class_id']   ?? null,
            'stream_id'  => $_GET['stream_id']  ?? null,
            'session_id' => $_GET['session_id'] ?? null,
            'status'     => $_GET['status']     ?? null,
        ]);

        $registers = $this->reportService->getRegisters($schoolId, $filters);

        $meta = [];
        foreach ([
            'date_from'  => 'From',
            'date_to'    => 'To',
            'class_id'   => 'Class',
            'stream_id'  => 'Stream',
            'session_id' => 'Session',
            'status'     => 'Status',
        ] as $key => $label) {
            if (empty($filters[$key])) continue;
            $value = $filters[$key];
            if (in_array($key, ['class_id','stream_id','session_id'], true)) {
                $table = $key === 'class_id' ? 'classes' : ($key === 'stream_id' ? 'streams' : 'attendance_sessions');
                $row = $this->db->fetch("SELECT name FROM {$table} WHERE id = :id", ['id' => (int)$value]);
                $value = $row['name'] ?? $value;
            }
            $meta[] = ['label' => $label, 'value' => (string)$value];
        }
        $meta[] = ['label' => 'Registers', 'value' => count($registers)];

        $user = $this->auth->getUser();
        $printedBy = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
        if ($printedBy === '') $printedBy = 'System';

        $this->audit('Attendance Registers Printed', 'attendance', 'Printed attendance registers list.');

        echo $this->view->renderWithLayout('reports/attendance/print_registers', 'print', [
            'title'     => 'Attendance Registers',
            'registers' => $registers,
            'meta'      => $meta,
            'printedBy' => $printedBy,
        ]);
    }

    public function exportRegisters(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'class_id'   => $_GET['class_id']   ?? null,
            'stream_id'  => $_GET['stream_id']  ?? null,
            'session_id' => $_GET['session_id'] ?? null,
            'status'     => $_GET['status']     ?? null,
        ]);

        $registers = $this->reportService->getRegisters($schoolId, $filters);

        $this->audit('Attendance Registers Exported', 'attendance', 'Exported attendance registers to CSV.');

        $filename = 'attendance_registers_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Attendance Registers']);
        fputcsv($out, ['Generated At', date('Y-m-d H:i:s')]);
        fputcsv($out, []);
        fputcsv($out, ['#', 'DATE', 'CLASS', 'STREAM', 'SESSION', 'STATUS', 'STUDENTS', 'PRESENT', 'ABSENT', 'LATE', '%']);

        foreach ($registers as $i => $r) {
            $total   = (int)$r['total_students'];
            $present = (int)$r['present_count'];
            $pct     = $total > 0 ? round(($present / $total) * 100, 1) : 0;
            fputcsv($out, [
                $i + 1,
                $r['attendance_date'] ?? '',
                $r['class_name']      ?? '',
                $r['stream_name']     ?? '',
                $r['session_name']    ?? '',
                ucfirst((string)($r['status'] ?? '')),
                $total,
                $present,
                (int)$r['absent_count'],
                (int)$r['late_count'],
                $pct . ' %',
            ]);
        }

        fclose($out);
        exit;
    }

    public function reports(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'class_id'   => $_GET['class_id']   ?? null,
            'stream_id'  => $_GET['stream_id']  ?? null,
            'session_id' => $_GET['session_id'] ?? null,
            'status'     => $_GET['status']     ?? null,
        ]);

        echo $this->view->renderWithLayout('reports/attendance/reports', 'default', [
            'title'           => 'Attendance Reports',
            'summary'         => $this->reportService->getSummary($schoolId, $filters),
            'filters'         => $this->getFilterOptions($schoolId),
            'selectedFilters' => $filters,
        ]);
    }

    public function printReports(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'class_id'   => $_GET['class_id']   ?? null,
            'stream_id'  => $_GET['stream_id']  ?? null,
            'session_id' => $_GET['session_id'] ?? null,
            'status'     => $_GET['status']     ?? null,
        ]);

        $summary = $this->reportService->getSummary($schoolId, $filters);

        $meta = [];
        foreach ([
            'date_from' => 'From',
            'date_to'   => 'To',
            'class_id'  => 'Class',
            'stream_id' => 'Stream',
            'session_id'=> 'Session',
            'status'    => 'Status',
        ] as $key => $label) {
            if (empty($filters[$key])) continue;
            $value = $filters[$key];
            if (in_array($key, ['class_id','stream_id','session_id'], true)) {
                $table = $key === 'class_id' ? 'classes' : ($key === 'stream_id' ? 'streams' : 'attendance_sessions');
                $row = $this->db->fetch("SELECT name FROM {$table} WHERE id = :id", ['id' => (int)$value]);
                $value = $row['name'] ?? $value;
            }
            $meta[] = ['label' => $label, 'value' => (string)$value];
        }
        $meta[] = ['label' => 'Registers', 'value' => (int)($summary['totals']['registers'] ?? 0)];

        $user = $this->auth->getUser();
        $printedBy = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
        if ($printedBy === '') $printedBy = 'System';

        $this->audit('Attendance Report Printed', 'attendance', 'Printed attendance summary report.');

        echo $this->view->renderWithLayout('reports/attendance/print_reports', 'print', [
            'title'     => 'Attendance Reports',
            'summary'   => $summary,
            'meta'      => $meta,
            'printedBy' => $printedBy,
        ]);
    }

    public function exportReports(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'class_id'   => $_GET['class_id']   ?? null,
            'stream_id'  => $_GET['stream_id']  ?? null,
            'session_id' => $_GET['session_id'] ?? null,
            'status'     => $_GET['status']     ?? null,
        ]);

        $summary = $this->reportService->getSummary($schoolId, $filters);

        $this->audit('Attendance Report Exported', 'attendance', 'Exported attendance summary report.');

        $filename = 'attendance_report_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Attendance Report']);
        fputcsv($out, ['Generated At', date('Y-m-d H:i:s')]);
        fputcsv($out, []);

        fputcsv($out, ['OVERALL TOTALS']);
        fputcsv($out, ['Registers', 'Records', 'Present', 'Absent', 'Late', 'Average %']);
        $t = $summary['totals'];
        fputcsv($out, [
            $t['registers'],
            $t['records'],
            $t['present'],
            $t['absent'],
            $t['late'],
            $t['avg_percent'] . ' %',
        ]);

        fputcsv($out, []);
        fputcsv($out, ['BY CLASS']);
        fputcsv($out, ['CLASS', 'REGISTERS', 'RECORDS', 'PRESENT', 'ABSENT', 'LATE', 'AVERAGE %']);
        foreach ($summary['by_class'] as $row) {
            fputcsv($out, [
                $row['class_name'] ?? '-',
                (int)$row['registers'],
                (int)$row['records'],
                (int)$row['present_count'],
                (int)$row['absent_count'],
                (int)$row['late_count'],
                $row['avg_percent'] . ' %',
            ]);
        }

        fputcsv($out, []);
        fputcsv($out, ['BY STUDENT']);
        fputcsv($out, ['ADM NO', 'STUDENT', 'CLASS', 'RECORDS', 'PRESENT', 'ABSENT', 'LATE', 'AVERAGE %']);
        foreach ($summary['by_student'] as $row) {
            fputcsv($out, [
                $row['admission_number'] ?? '',
                strtoupper(trim(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? ''))),
                $row['class_name'] ?? '',
                (int)$row['records'],
                (int)$row['present_count'],
                (int)$row['absent_count'],
                (int)$row['late_count'],
                $row['avg_percent'] . ' %',
            ]);
        }

        fclose($out);
        exit;
    }

    private function getFilterOptions(int $schoolId): array
    {
        return [
            'classes'  => $this->db->fetchAll(
                "SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'streams'  => $this->db->fetchAll(
                "SELECT id, name, class_id FROM streams WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'sessions' => $this->db->fetchAll(
                "SELECT id, name FROM attendance_sessions WHERE school_id = :s ORDER BY display_order ASC",
                ['s' => $schoolId]
            ),
        ];
    }

    public function studentMonitor(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();

        $search        = trim($_GET['search'] ?? '');
        $classId       = (int)($_GET['class_id'] ?? 0);
        $streamId      = (int)($_GET['stream_id'] ?? 0);
        $categoryId    = (int)($_GET['category_id'] ?? 0);

        $where  = ["s.school_id = :s"];
        $params = ['s' => $schoolId];

        if ($search !== '') {
            $where[] = "(s.first_name LIKE :s1 OR s.last_name LIKE :s2 OR s.admission_number LIKE :s3)";
            $like = "%{$search}%";
            $params += ['s1' => $like, 's2' => $like, 's3' => $like];
        }
        if ($classId > 0) {
            $where[] = "se.class_id = :class_id";
            $params['class_id'] = $classId;
        }
        if ($streamId > 0) {
            $where[] = "se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }
        if ($categoryId > 0) {
            $where[] = "se.student_category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $students = $this->db->fetchAll(
            "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender,
                    cl.name AS class_name,
                    st.name AS stream_name,
                    sc.name AS category_name,
                    ss.name AS status_name
             FROM students s
             LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
             LEFT JOIN classes cl            ON se.class_id = cl.id
             LEFT JOIN streams st            ON se.stream_id = st.id
             LEFT JOIN student_categories sc ON se.student_category_id = sc.id
             LEFT JOIN student_statuses ss   ON se.student_status_id = ss.id
             {$whereClause}
             ORDER BY s.last_name ASC, s.first_name ASC",
            $params
        );

        echo $this->view->renderWithLayout('reports/attendance/student_monitor', 'default', [
            'title'           => 'Student Attendance Monitor',
            'students'        => $students,
            'filters'         => $this->getFilterOptions($schoolId),
            'categories'      => $this->db->fetchAll(
                "SELECT id, name FROM student_categories WHERE school_id = :s AND status = 'active' ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'selectedFilters' => [
                'search'      => $search,
                'class_id'    => $classId,
                'stream_id'   => $streamId,
                'category_id' => $categoryId,
            ],
        ]);
    }

    public function viewStudentMonitor(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId  = $this->schoolId();
        $studentId = (int)($_GET['student_id'] ?? 0);

        if (!$studentId) {
            $this->flashError('Select a student to monitor.');
            $this->redirect('/reports/attendance/student-monitor');
            return;
        }

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'session_id' => $_GET['session_id'] ?? null,
        ]);

        $data = $this->reportService->getStudentMonitor($schoolId, $studentId, $filters);

        if (empty($data['student'])) {
            $this->flashError('Student not found.');
            $this->redirect('/reports/attendance/student-monitor');
            return;
        }

        echo $this->view->renderWithLayout('reports/attendance/student_monitor_view', 'default', [
            'title'           => 'Student Attendance Monitor',
            'data'            => $data,
            'filters'         => $this->getFilterOptions($schoolId),
            'selectedFilters' => array_merge(['student_id' => $studentId], $filters),
            'backUrl'         => BASE_URL . '/reports/attendance/student-monitor',
        ]);
    }

    public function printStudentMonitor(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId  = $this->schoolId();
        $studentId = (int)($_GET['student_id'] ?? 0);

        $filters = array_filter([
            'date_from'  => $_GET['date_from']  ?? null,
            'date_to'    => $_GET['date_to']    ?? null,
            'session_id' => $_GET['session_id'] ?? null,
        ]);

        if (!$studentId) {
            $this->flashError('Select a student before printing.');
            $this->redirect('/reports/attendance/student-monitor');
            return;
        }

        $data = $this->reportService->getStudentMonitor($schoolId, $studentId, $filters);

        if (empty($data['student'])) {
            $this->flashError('Student not found.');
            $this->redirect('/reports/attendance/student-monitor');
            return;
        }

        $meta = [
            ['label' => 'Student', 'value' => strtoupper(trim(($data['student']['last_name'] ?? '') . ' ' . ($data['student']['first_name'] ?? '')))],
            ['label' => 'Adm No',  'value' => (string)($data['student']['admission_number'] ?? '')],
            ['label' => 'Class',   'value' => (string)($data['student']['class_name'] ?? '')],
        ];
        if (!empty($filters['date_from'])) $meta[] = ['label' => 'From', 'value' => $filters['date_from']];
        if (!empty($filters['date_to']))   $meta[] = ['label' => 'To',   'value' => $filters['date_to']];

        $user = $this->auth->getUser();
        $printedBy = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
        if ($printedBy === '') $printedBy = 'System';

        $this->audit('Student Attendance Monitor Printed', 'attendance', "Printed monitor for student #{$studentId}");

        echo $this->view->renderWithLayout('reports/attendance/print_student_monitor', 'print', [
            'title'     => 'Student Attendance Monitor',
            'data'      => $data,
            'meta'      => $meta,
            'printedBy' => $printedBy,
        ]);
    }
}