<?php
// File: /app/Controllers/AttendanceController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AttendanceService;
use NexaT\Services\NotificationService;
use Throwable;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;
    private NotificationService $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->attendanceService = new AttendanceService();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $this->requirePermission('attendance.view');

        $schoolId = $this->schoolId();
        $date     = $_GET['attendance_date'] ?? date('Y-m-d');
        $classId  = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $status   = !empty($_GET['status']) ? $_GET['status'] : null;

        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes
             WHERE school_id = :school_id OR school_id IS NULL
             ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        $params = ['school_id' => $schoolId, 'attendance_date' => $date];

        $sql = "SELECT r.*, c.name AS class_name, s.name AS stream_name,
                       sess.name AS session_name, u.first_name, u.last_name
                FROM attendance_registers r
                LEFT JOIN classes c ON r.class_id = c.id
                LEFT JOIN streams s ON r.stream_id = s.id
                LEFT JOIN attendance_sessions sess ON r.attendance_session_id = sess.id
                LEFT JOIN users u ON r.recorded_by = u.id
                WHERE r.school_id = :school_id AND r.attendance_date = :attendance_date";

        if ($classId) {
            $sql .= " AND r.class_id = :class_id";
            $params['class_id'] = $classId;
        }

        if ($status) {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC";

        $registers = $this->db->fetchAll($sql, $params);

        foreach ($registers as &$reg) {
            $reg['recorder_name'] = trim(($reg['first_name'] ?? '') . ' ' . ($reg['last_name'] ?? '')) ?: 'System';
        }
        unset($reg);

        $submittedCount = (int)($this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM attendance_registers
             WHERE school_id = :school_id AND attendance_date = :date AND status = 'submitted'",
            ['school_id' => $schoolId, 'date' => $date]
        )['cnt'] ?? 0);

        $draftCount = (int)($this->db->fetch(
            "SELECT COUNT(*) AS cnt FROM attendance_registers
             WHERE school_id = :school_id AND attendance_date = :date AND status = 'draft'",
            ['school_id' => $schoolId, 'date' => $date]
        )['cnt'] ?? 0);

        $avgStat = $this->db->fetch(
            "SELECT COUNT(ar.id) AS total,
                    SUM(CASE WHEN st.counts_as_present = 1 THEN 1 ELSE 0 END) AS present
             FROM attendance_records ar
             INNER JOIN attendance_registers r ON ar.register_id = r.id
             INNER JOIN attendance_statuses st ON ar.attendance_status_id = st.id
             WHERE r.school_id = :school_id AND r.attendance_date = :date",
            ['school_id' => $schoolId, 'date' => $date]
        );

        $avgAttendance = ($avgStat && $avgStat['total'] > 0)
            ? round(($avgStat['present'] / $avgStat['total']) * 100, 1) . '%'
            : '0%';

        echo $this->view->renderWithLayout('attendance/index', 'default', [
            'title'          => 'Attendance Overview',
            'registers'      => $registers,
            'classes'        => $classes,
            'submittedCount' => $submittedCount,
            'draftCount'     => $draftCount,
            'avgAttendance'  => $avgAttendance,
        ]);
    }

    public function take(): void
    {
        $this->requirePermission('attendance.record');

        $schoolId = $this->schoolId();

        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes
             WHERE school_id = :school_id OR school_id IS NULL
             ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        $streams = $this->db->fetchAll(
            "SELECT id, name, class_id FROM streams
             WHERE school_id = :school_id OR school_id IS NULL
             ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        $sessions = $this->db->fetchAll(
            "SELECT * FROM attendance_sessions
             WHERE (school_id = :school_id OR school_id IS NULL) AND status = 'active'
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );

        $statuses = $this->attendanceService->getStatuses($schoolId);

        $selectedClassId   = $_GET['class_id']   ?? null;
        $selectedStreamId  = $_GET['stream_id']  ?? null;
        $selectedSessionId = $_GET['attendance_session_id'] ?? ($sessions[0]['id'] ?? null);
        $selectedDate      = $_GET['attendance_date'] ?? date('Y-m-d');

        $students = [];
        $existingRegister = null;
        $existingRecords = [];

        if ($selectedClassId) {
            $students = $this->attendanceService->getEnrolledStudents(
                (int)$selectedClassId,
                $selectedStreamId ? (int)$selectedStreamId : null,
                $schoolId
            );

            $query = "SELECT * FROM attendance_registers
                      WHERE school_id = :school_id
                        AND class_id = :class_id
                        AND attendance_session_id = :session_id
                        AND attendance_date = :date";

            $params = [
                'school_id'  => $schoolId,
                'class_id'   => $selectedClassId,
                'session_id' => $selectedSessionId,
                'date'       => $selectedDate,
            ];

            if (!empty($selectedStreamId)) {
                $query .= " AND stream_id = :stream_id";
                $params['stream_id'] = $selectedStreamId;
            } else {
                $query .= " AND stream_id IS NULL";
            }

            $existingRegister = $this->db->fetch($query, $params);

            if ($existingRegister) {
                foreach ($this->db->fetchAll(
                    "SELECT * FROM attendance_records WHERE register_id = :register_id",
                    ['register_id' => $existingRegister['id']]
                ) as $r) {
                    $existingRecords[$r['student_id']] = $r;
                }
            }
        }

        echo $this->view->renderWithLayout('attendance/take', 'default', [
            'title'             => 'Take Attendance',
            'classes'           => $classes,
            'streams'           => $streams,
            'sessions'          => $sessions,
            'statuses'          => $statuses,
            'students'          => $students,
            'selectedClassId'   => $selectedClassId,
            'selectedStreamId'  => $selectedStreamId,
            'selectedSessionId' => $selectedSessionId,
            'selectedDate'      => $selectedDate,
            'existingRegister'  => $existingRegister,
            'existingRecords'   => $existingRecords,
        ]);
    }

    public function save(): void
    {
        $this->requirePermission('attendance.record');

        $schoolId  = $this->schoolId();
        $userId    = $this->auth->id();

        $classId   = (int)($_POST['class_id'] ?? 0);
        $streamId  = !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null;
        $sessionId = (int)($_POST['attendance_session_id'] ?? 0);
        $date      = $_POST['attendance_date'] ?? date('Y-m-d');
        $records   = $_POST['records'] ?? [];

        if (!$classId || !$sessionId) {
            $this->flashError('Class and Session selection are mandatory.');
            $this->redirect('/attendance/take');
        }

        $activeYear = $this->db->fetch(
            "SELECT id FROM academic_years
             WHERE school_id = :school_id OR school_id IS NULL
             ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );

        if (!$activeYear) {
            $this->flashError('No academic year found. Please configure an academic year first.');
            $this->redirect('/attendance/take');
        }

        $activeTerm = $this->db->fetch(
            "SELECT id FROM terms
             WHERE school_id = :school_id OR school_id IS NULL
             ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );

        try {
            $register = $this->attendanceService->getOrCreateRegister([
                'school_id'             => $schoolId,
                'academic_year_id'      => $activeYear['id'],
                'academic_period_id'    => $activeTerm['id'] ?? null,
                'class_id'              => $classId,
                'stream_id'             => $streamId,
                'attendance_session_id' => $sessionId,
                'attendance_date'       => $date,
            ], $userId);

            foreach ($records as $studentId => $data) {
                $statusId = (int)($data['status_id'] ?? 0);
                if (!$statusId) {
                    continue;
                }

                $enrollmentId = !empty($data['enrollment_id']) ? (int)$data['enrollment_id'] : null;

                if (!$enrollmentId) {
                    $enr = $this->db->fetch(
                        "SELECT id FROM student_enrollments
                         WHERE student_id = :student_id AND status = 'active' LIMIT 1",
                        ['student_id' => (int)$studentId]
                    );
                    $enrollmentId = $enr['id'] ?? null;
                }

                $payload = [
                    'student_enrollment_id' => $enrollmentId,
                    'attendance_status_id'  => $statusId,
                    'reason'                => trim($data['reason']  ?? ''),
                    'remarks'               => trim($data['remarks'] ?? ''),
                    'updated_at'            => date('Y-m-d H:i:s'),
                ];

                $existing = $this->db->fetch(
                    "SELECT id FROM attendance_records
                     WHERE register_id = :reg_id AND student_id = :student_id",
                    ['reg_id' => $register['id'], 'student_id' => (int)$studentId]
                );

                if ($existing) {
                    $this->db->update('attendance_records', $payload, ['id' => $existing['id']]);
                } else {
                    $payload['school_id']    = $schoolId;
                    $payload['register_id']  = $register['id'];
                    $payload['student_id']   = (int)$studentId;
                    $payload['created_at']   = date('Y-m-d H:i:s');
                    $this->db->insert('attendance_records', $payload);
                }
            }

            $this->db->update('attendance_registers', [
                'status'       => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s'),
                'recorded_by'  => $userId,
                'updated_at'   => date('Y-m-d H:i:s'),
            ], ['id' => $register['id']]);

            $this->audit('Attendance Saved', 'attendance', "Recorded register ID {$register['id']}");

            $classInfo = $this->db->fetch(
                "SELECT name FROM classes WHERE id = :id",
                ['id' => $classId]
            );
            $className = $classInfo['name'] ?? 'Unknown Class';

            $studentCount = count($records);
            $presentCount = 0;
            foreach ($records as $data) {
                $statusId = (int)($data['status_id'] ?? 0);
                if ($statusId) {
                    $status = $this->db->fetch(
                        "SELECT counts_as_present FROM attendance_statuses WHERE id = :id",
                        ['id' => $statusId]
                    );
                    if (!empty($status['counts_as_present'])) {
                        $presentCount++;
                    }
                }
            }

            $this->notifications->notify(
                (int) $userId,
                $schoolId,
                'Attendance Recorded',
                "Attendance for {$className} ({$studentCount} students, {$presentCount} present) has been submitted for {$date}.",
                'success',
                BASE_URL . '/attendance/view/' . $register['id'],
                'fas fa-clipboard-check'
            );

            if ($studentCount > 0 && ($presentCount / $studentCount) < 0.5) {
                $this->notifications->notify(
                    (int) $userId,
                    $schoolId,
                    'Low Attendance Alert',
                    "Warning: {$className} has low attendance ({$presentCount}/{$studentCount} present) on {$date}.",
                    'warning',
                    BASE_URL . '/attendance/view/' . $register['id'],
                    'fas fa-exclamation-triangle'
                );
            }

            $this->flashSuccess('Attendance register recorded successfully.');
            $this->redirect('/attendance');

        } catch (Throwable $e) {
            $this->flashError('Error saving attendance: ' . $e->getMessage());
            $this->redirect('/attendance/take');
        }
    }

    public function view($params): void
    {
        $this->requirePermission('attendance.view');

        $id = (int)($params['id'] ?? 0);

        $register = $this->db->fetch(
            "SELECT r.*, c.name AS class_name, s.name AS stream_name,
                    sess.name AS session_name, u.first_name, u.last_name
             FROM attendance_registers r
             LEFT JOIN classes c ON r.class_id = c.id
             LEFT JOIN streams s ON r.stream_id = s.id
             LEFT JOIN attendance_sessions sess ON r.attendance_session_id = sess.id
             LEFT JOIN users u ON r.recorded_by = u.id
             WHERE r.id = :id",
            ['id' => $id]
        );

        if (!$register) {
            $this->flashError('Register not found.');
            $this->redirect('/attendance');
        }

        $records = $this->db->fetchAll(
            "SELECT ar.*, st.first_name, st.last_name, st.admission_number,
                    ast.name AS status_name, ast.code AS status_code,
                    ast.counts_as_present, ast.counts_as_absent, ast.counts_as_late
             FROM attendance_records ar
             INNER JOIN students st ON ar.student_id = st.id
             INNER JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
             WHERE ar.register_id = :id
             ORDER BY st.first_name ASC",
            ['id' => $id]
        );

        echo $this->view->renderWithLayout('attendance/view', 'default', [
            'title'    => 'Register Details',
            'register' => $register,
            'records'  => $records,
        ]);
    }

    public function edit($params): void
    {
        $this->requirePermission('attendance.record');

        $registerId = (int)($params['id'] ?? 0);
        $register = $this->db->fetch(
            "SELECT * FROM attendance_registers WHERE id = :id",
            ['id' => $registerId]
        );

        if (!$register) {
            $this->flashError('Register not found.');
            $this->redirect('/attendance');
        }

        $this->redirect('/attendance/take?' . http_build_query([
            'class_id'              => $register['class_id'],
            'stream_id'             => $register['stream_id'],
            'attendance_session_id' => $register['attendance_session_id'],
            'attendance_date'       => $register['attendance_date'],
        ]));
    }
}