<?php
// File: /app/Services/AttendanceReportService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Throwable;

class AttendanceReportService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getRegisters(int $schoolId, array $filters = []): array
    {
        $where = ["r.school_id = :school_id"];
        $params = ['school_id' => $schoolId];

        if (!empty($filters['date_from'])) {
            $where[] = "r.attendance_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "r.attendance_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['class_id'])) {
            $where[] = "r.class_id = :class_id";
            $params['class_id'] = (int)$filters['class_id'];
        }
        if (!empty($filters['stream_id'])) {
            $where[] = "r.stream_id = :stream_id";
            $params['stream_id'] = (int)$filters['stream_id'];
        }
        if (!empty($filters['session_id'])) {
            $where[] = "r.attendance_session_id = :session_id";
            $params['session_id'] = (int)$filters['session_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "r.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        return $this->safeFetchAll(
            "SELECT r.*,
                    c.name  AS class_name,
                    s.name  AS stream_name,
                    sess.name AS session_name,
                    u.first_name AS recorder_first,
                    u.last_name  AS recorder_last,
                    (SELECT COUNT(*) FROM attendance_records ar WHERE ar.register_id = r.id) AS total_students,
                    (SELECT COUNT(*) FROM attendance_records ar
                     INNER JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
                     WHERE ar.register_id = r.id AND ast.counts_as_present = 1) AS present_count,
                    (SELECT COUNT(*) FROM attendance_records ar
                     INNER JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
                     WHERE ar.register_id = r.id AND ast.counts_as_absent = 1) AS absent_count,
                    (SELECT COUNT(*) FROM attendance_records ar
                     INNER JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
                     WHERE ar.register_id = r.id AND ast.counts_as_late = 1) AS late_count
             FROM attendance_registers r
             LEFT JOIN classes c              ON r.class_id = c.id
             LEFT JOIN streams s              ON r.stream_id = s.id
             LEFT JOIN attendance_sessions sess ON r.attendance_session_id = sess.id
             LEFT JOIN users u                ON r.recorded_by = u.id
             WHERE {$whereClause}
             ORDER BY r.attendance_date DESC, r.id DESC",
            $params
        );
    }

    public function getSummary(int $schoolId, array $filters = []): array
    {
        $where = ["r.school_id = :school_id"];
        $params = ['school_id' => $schoolId];

        if (!empty($filters['date_from'])) {
            $where[] = "r.attendance_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "r.attendance_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['class_id'])) {
            $where[] = "r.class_id = :class_id";
            $params['class_id'] = (int)$filters['class_id'];
        }
        if (!empty($filters['stream_id'])) {
            $where[] = "r.stream_id = :stream_id";
            $params['stream_id'] = (int)$filters['stream_id'];
        }
        if (!empty($filters['session_id'])) {
            $where[] = "r.attendance_session_id = :session_id";
            $params['session_id'] = (int)$filters['session_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "r.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        $totalsRow = $this->safeFetch(
            "SELECT
                COUNT(DISTINCT r.id) AS registers,
                COUNT(ar.id)         AS records,
                SUM(CASE WHEN ast.counts_as_present = 1 THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN ast.counts_as_absent  = 1 THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN ast.counts_as_late    = 1 THEN 1 ELSE 0 END) AS late_count
             FROM attendance_registers r
             LEFT JOIN attendance_records ar   ON ar.register_id = r.id
             LEFT JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
             WHERE {$whereClause}",
            $params
        );

        $records = (int)($totalsRow['records'] ?? 0);
        $present = (int)($totalsRow['present_count'] ?? 0);

        $totals = [
            'registers'   => (int)($totalsRow['registers'] ?? 0),
            'records'     => $records,
            'present'     => $present,
            'absent'      => (int)($totalsRow['absent_count'] ?? 0),
            'late'        => (int)($totalsRow['late_count'] ?? 0),
            'avg_percent' => $records > 0 ? round(($present / $records) * 100, 1) : 0,
        ];

        $byClass = $this->safeFetchAll(
            "SELECT
                c.id   AS class_id,
                c.name AS class_name,
                COUNT(DISTINCT r.id) AS registers,
                COUNT(ar.id)         AS records,
                SUM(CASE WHEN ast.counts_as_present = 1 THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN ast.counts_as_absent  = 1 THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN ast.counts_as_late    = 1 THEN 1 ELSE 0 END) AS late_count
             FROM attendance_registers r
             LEFT JOIN classes c               ON r.class_id = c.id
             LEFT JOIN attendance_records ar   ON ar.register_id = r.id
             LEFT JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
             WHERE {$whereClause}
             GROUP BY c.id, c.name
             ORDER BY c.name ASC",
            $params
        );

        foreach ($byClass as &$c) {
            $rec = (int)$c['records'];
            $c['avg_percent'] = $rec > 0 ? round(((int)$c['present_count'] / $rec) * 100, 1) : 0;
        }
        unset($c);

        $byStudent = $this->safeFetchAll(
            "SELECT
                st.id, st.admission_number, st.first_name, st.last_name,
                c.name AS class_name,
                COUNT(ar.id) AS records,
                SUM(CASE WHEN ast.counts_as_present = 1 THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN ast.counts_as_absent  = 1 THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN ast.counts_as_late    = 1 THEN 1 ELSE 0 END) AS late_count
             FROM attendance_records ar
             INNER JOIN attendance_registers r    ON ar.register_id = r.id
             INNER JOIN students st               ON ar.student_id = st.id
             LEFT JOIN attendance_statuses ast    ON ar.attendance_status_id = ast.id
             LEFT JOIN student_enrollments se     ON se.student_id = st.id AND se.status = 'active'
             LEFT JOIN classes c                  ON se.class_id = c.id
             WHERE {$whereClause}
             GROUP BY st.id, st.admission_number, st.first_name, st.last_name, c.name
             ORDER BY st.last_name ASC, st.first_name ASC
             LIMIT 100",
            $params
        );

        foreach ($byStudent as &$s) {
            $rec = (int)$s['records'];
            $s['avg_percent'] = $rec > 0 ? round(((int)$s['present_count'] / $rec) * 100, 1) : 0;
        }
        unset($s);

        return [
            'totals'     => $totals,
            'by_class'   => $byClass,
            'by_student' => $byStudent,
        ];
    }

    private function safeFetch(string $sql, array $params = []): ?array
    {
        try {
            $row = $this->db->fetch($sql, $params);
            return $row !== false && $row !== null ? $row : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function safeFetchAll(string $sql, array $params = []): array
    {
        try {
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getStudentMonitor(int $schoolId, int $studentId, array $filters = []): array
    {
        $student = $this->safeFetch(
            "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender,
                    s.school_id
             FROM students s
             WHERE s.id = :id AND s.school_id = :s
             LIMIT 1",
            ['id' => $studentId, 's' => $schoolId]
        );

        if (!$student) {
            return [
                'student' => null,
                'summary' => [
                    'records' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'avg_percent' => 0,
                ],
                'records' => [],
                'by_session' => [],
            ];
        }

        $enrollment = $this->safeFetch(
            "SELECT cl.name AS class_name, str.name AS stream_name
             FROM student_enrollments se
             LEFT JOIN classes cl ON se.class_id = cl.id
             LEFT JOIN streams str ON se.stream_id = str.id
             WHERE se.student_id = :student_id
             ORDER BY se.id DESC
             LIMIT 1",
            ['student_id' => $studentId]
        );

        $student['class_name']  = $enrollment['class_name']  ?? null;
        $student['stream_name'] = $enrollment['stream_name'] ?? null;

        $where = ["ar.student_id = :student_id", "r.school_id = :school_id"];
        $params = ['student_id' => $studentId, 'school_id' => $schoolId];

        if (!empty($filters['date_from'])) {
            $where[] = "r.attendance_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "r.attendance_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['session_id'])) {
            $where[] = "r.attendance_session_id = :session_id";
            $params['session_id'] = (int)$filters['session_id'];
        }

        $whereClause = implode(' AND ', $where);

        $records = $this->safeFetchAll(
            "SELECT ar.id,
                    r.attendance_date,
                    r.attendance_session_id,
                    sess.name AS session_name,
                    ast.name  AS status_name,
                    ast.code  AS status_code,
                    ast.counts_as_present,
                    ast.counts_as_absent,
                    ast.counts_as_late,
                    ar.reason,
                    ar.remarks
             FROM attendance_records ar
             INNER JOIN attendance_registers r   ON ar.register_id = r.id
             INNER JOIN attendance_statuses ast  ON ar.attendance_status_id = ast.id
             LEFT JOIN attendance_sessions sess  ON r.attendance_session_id = sess.id
             WHERE {$whereClause}
             ORDER BY r.attendance_date DESC, sess.display_order ASC",
            $params
        );

        $total = count($records);
        $present = $absent = $late = 0;
        $bySession = [];

        foreach ($records as $r) {
            if (!empty($r['counts_as_present'])) $present++;
            if (!empty($r['counts_as_absent']))  $absent++;
            if (!empty($r['counts_as_late']))    $late++;

            $sid = (int)($r['attendance_session_id'] ?? 0);
            if (!isset($bySession[$sid])) {
                $bySession[$sid] = [
                    'session_id'   => $sid,
                    'session_name' => $r['session_name'] ?? 'Daily',
                    'records'      => 0,
                    'present'      => 0,
                    'absent'       => 0,
                    'late'         => 0,
                ];
            }
            $bySession[$sid]['records']++;
            if (!empty($r['counts_as_present'])) $bySession[$sid]['present']++;
            if (!empty($r['counts_as_absent']))  $bySession[$sid]['absent']++;
            if (!empty($r['counts_as_late']))    $bySession[$sid]['late']++;
        }

        foreach ($bySession as &$s) {
            $s['avg_percent'] = $s['records'] > 0 ? round(($s['present'] / $s['records']) * 100, 1) : 0;
        }
        unset($s);

        return [
            'student'    => $student,
            'summary'    => [
                'records'     => $total,
                'present'     => $present,
                'absent'      => $absent,
                'late'        => $late,
                'avg_percent' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ],
            'records'    => $records,
            'by_session' => array_values($bySession),
        ];
    }
}