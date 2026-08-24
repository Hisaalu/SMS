<?php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AttendanceService;
use NexaT\Services\AuditService;

class AttendanceController extends Controller
{
    protected $attendanceService;
    protected $audit;

    public function __construct()
    {
        parent::__construct();
        $this->attendanceService = new AttendanceService();
        $this->audit = new AuditService();
    }

    private function getSchoolId(): int
    {
        $user = $this->auth->getUser();
        return (!empty($user->school_id) && (int)$user->school_id > 0) ? (int)$user->school_id : 1;
    }

    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->getSchoolId();
        $date = $_GET['attendance_date'] ?? date('Y-m-d');
        $classId = !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;
        $status = !empty($_GET['status']) ? $_GET['status'] : null;

        // Fetch directly from 'classes' table
        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :school_id OR school_id IS NULL ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        $params = ['school_id' => $schoolId, 'attendance_date' => $date];
        $sql = "SELECT r.*, c.name as class_name, s.name as stream_name, 
                       sess.name as session_name, u.first_name, u.last_name
                FROM attendance_registers r
                LEFT JOIN classes c ON r.class_id = c.id
                LEFT JOIN streams s ON r.stream_id = s.id
                LEFT JOIN attendance_sessions sess ON r.attendance_session_id = sess.id
                LEFT JOIN users u ON r.recorded_by = u.id
                WHERE (r.school_id = :school_id OR r.school_id IS NULL) AND r.attendance_date = :attendance_date";

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
            $reg['recorder_name'] = trim(($reg['first_name'] ?? '') . ' ' . ($reg['last_name'] ?? ''));
            if (empty($reg['recorder_name'])) {
                $reg['recorder_name'] = 'System';
            }
        }

        $submittedCount = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM attendance_registers WHERE (school_id = :school_id OR school_id IS NULL) AND attendance_date = :date AND status = 'submitted'",
            ['school_id' => $schoolId, 'date' => $date]
        )['cnt'] ?? 0;

        $draftCount = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM attendance_registers WHERE (school_id = :school_id OR school_id IS NULL) AND attendance_date = :date AND status = 'draft'",
            ['school_id' => $schoolId, 'date' => $date]
        )['cnt'] ?? 0;

        $avgStat = $this->db->fetch(
            "SELECT COUNT(ar.id) as total, 
                    SUM(CASE WHEN st.counts_as_present = 1 THEN 1 ELSE 0 END) as present
             FROM attendance_records ar
             INNER JOIN attendance_registers r ON ar.register_id = r.id
             INNER JOIN attendance_statuses st ON ar.attendance_status_id = st.id
             WHERE (r.school_id = :school_id OR r.school_id IS NULL) AND r.attendance_date = :date",
            ['school_id' => $schoolId, 'date' => $date]
        );

        $avgAttendance = '0%';
        if ($avgStat && $avgStat['total'] > 0) {
            $avgAttendance = round(($avgStat['present'] / $avgStat['total']) * 100, 1) . '%';
        }

        echo $this->view->renderWithLayout('attendance/index', 'default', [
            'title' => 'Attendance Overview',
            'registers' => $registers,
            'classes' => $classes,
            'submittedCount' => $submittedCount,
            'draftCount' => $draftCount,
            'avgAttendance' => $avgAttendance
        ]);
    }

    public function take(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.record')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->getSchoolId();

        // Direct fetch from 'classes' and 'streams'
        $classes = $this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :school_id OR school_id IS NULL ORDER BY name ASC",
            ['school_id' => $schoolId]
        );
        
        $streams = $this->db->fetchAll(
            "SELECT id, name, class_id FROM streams WHERE school_id = :school_id OR school_id IS NULL ORDER BY name ASC",
            ['school_id' => $schoolId]
        );
        
        $sessions = $this->db->fetchAll(
            "SELECT * FROM attendance_sessions WHERE (school_id = :school_id OR school_id IS NULL) AND status = 'active' ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
        
        $statuses = $this->attendanceService->getStatuses($schoolId);

        $selectedClassId = $_GET['class_id'] ?? null;
        $selectedStreamId = $_GET['stream_id'] ?? null;
        $selectedSessionId = $_GET['attendance_session_id'] ?? ($sessions[0]['id'] ?? null);
        $selectedDate = $_GET['attendance_date'] ?? date('Y-m-d');

        $students = [];
        $existingRegister = null;
        $existingRecords = [];

        if ($selectedClassId) {
            $students = $this->attendanceService->getEnrolledStudents((int)$selectedClassId, $selectedStreamId ? (int)$selectedStreamId : null, $schoolId);

            $query = "SELECT * FROM attendance_registers 
                    WHERE (school_id = :school_id OR school_id IS NULL) 
                    AND class_id = :class_id 
                    AND attendance_session_id = :session_id 
                    AND attendance_date = :date";

            $params = [
                'school_id' => $schoolId,
                'class_id' => $selectedClassId,
                'session_id' => $selectedSessionId,
                'date' => $selectedDate
            ];

            if (!empty($selectedStreamId)) {
                $query .= " AND stream_id = :stream_id";
                $params['stream_id'] = $selectedStreamId;
            } else {
                $query .= " AND stream_id IS NULL";
            }

            $existingRegister = $this->db->fetch($query, $params);

            if ($existingRegister) {
                $recs = $this->db->fetchAll("SELECT * FROM attendance_records WHERE register_id = :register_id", ['register_id' => $existingRegister['id']]);
                foreach ($recs as $r) {
                    $existingRecords[$r['student_id']] = $r;
                }
            }
        }

        echo $this->view->renderWithLayout('attendance/take', 'default', [
            'title' => 'Take Attendance',
            'classes' => $classes,
            'streams' => $streams,
            'sessions' => $sessions,
            'statuses' => $statuses,
            'students' => $students,
            'selectedClassId' => $selectedClassId,
            'selectedStreamId' => $selectedStreamId,
            'selectedSessionId' => $selectedSessionId,
            'selectedDate' => $selectedDate,
            'existingRegister' => $existingRegister,
            'existingRecords' => $existingRecords
        ]);
    }

    public function save(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.record')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->getSchoolId();
        $userId = $this->auth->id();

        $classId = (int)($_POST['class_id'] ?? 0);
        $streamId = !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null;
        $sessionId = (int)($_POST['attendance_session_id'] ?? 0);
        $date = $_POST['attendance_date'] ?? date('Y-m-d');
        $records = $_POST['records'] ?? [];

        if (!$classId || !$sessionId) {
            $_SESSION['flash_error'] = 'Class and Session selection are mandatory.';
            header('Location: ' . BASE_URL . '/attendance/take');
            exit;
        }

        // Fetch active Academic Year dynamically
        $activeYear = $this->db->fetch(
            "SELECT id FROM academic_years WHERE school_id = :school_id OR school_id IS NULL ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );

        if (!$activeYear) {
            $activeYear = $this->db->fetch("SELECT id FROM academic_years ORDER BY id DESC LIMIT 1");
        }

        // Fetch active Term dynamically
        $activeTerm = $this->db->fetch(
            "SELECT id FROM terms WHERE school_id = :school_id OR school_id IS NULL ORDER BY id DESC LIMIT 1",
            ['school_id' => $schoolId]
        );

        if (!$activeTerm) {
            $activeTerm = $this->db->fetch("SELECT id FROM terms ORDER BY id DESC LIMIT 1");
        }

        if (!$activeYear) {
            $_SESSION['flash_error'] = 'No academic year found. Please configure an academic year first.';
            header('Location: ' . BASE_URL . '/attendance/take');
            exit;
        }

        $regData = [
            'school_id' => $schoolId,
            'academic_year_id' => $activeYear['id'],
            'academic_period_id' => $activeTerm['id'] ?? null,
            'class_id' => $classId,
            'stream_id' => $streamId,
            'attendance_session_id' => $sessionId,
            'attendance_date' => $date
        ];

        try {
            $register = $this->attendanceService->getOrCreateRegister($regData, $userId);

            foreach ($records as $studentId => $data) {
                $statusId = (int)($data['status_id'] ?? 0);
                $enrollmentId = !empty($data['enrollment_id']) ? (int)$data['enrollment_id'] : null;
                $reason = trim($data['reason'] ?? '');
                $remarks = trim($data['remarks'] ?? '');

                if (!$statusId) continue;

                // Fallback: If enrollment_id missing in POST, fetch active enrollment from database
                if (!$enrollmentId) {
                    $enr = $this->db->fetch(
                        "SELECT id FROM student_enrollments WHERE student_id = :student_id AND status = 'active' LIMIT 1",
                        ['student_id' => (int)$studentId]
                    );
                    $enrollmentId = $enr['id'] ?? null;
                }

                $existing = $this->db->fetch(
                    "SELECT id FROM attendance_records WHERE register_id = :reg_id AND student_id = :student_id",
                    ['reg_id' => $register['id'], 'student_id' => (int)$studentId]
                );

                if ($existing) {
                    $this->db->update(
                        'attendance_records',
                        [
                            'student_enrollment_id' => $enrollmentId,
                            'attendance_status_id' => $statusId,
                            'reason' => $reason,
                            'remarks' => $remarks,
                            'updated_at' => date('Y-m-d H:i:s')
                        ],
                        ['id' => $existing['id']]
                    );
                } else {
                    $this->db->insert(
                        'attendance_records',
                        [
                            'school_id' => $schoolId,
                            'register_id' => $register['id'],
                            'student_id' => (int)$studentId,
                            'student_enrollment_id' => $enrollmentId,
                            'attendance_status_id' => $statusId,
                            'reason' => $reason,
                            'remarks' => $remarks,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    );
                }
            }

            $this->db->update(
                'attendance_registers',
                [
                    'status' => 'submitted',
                    'submitted_at' => date('Y-m-d H:i:s'),
                    'recorded_by' => $userId,
                    'updated_at' => date('Y-m-d H:i:s')
                ],
                ['id' => $register['id']]
            );

            $this->audit->log($userId, 'Attendance Saved', 'attendance', "Recorded register ID {$register['id']}");
            $_SESSION['flash_success'] = 'Attendance register recorded successfully.';
            header('Location: ' . BASE_URL . '/attendance');
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error saving attendance: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/attendance/take');
            exit;
        }
    }

    public function view($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = $params['id'] ?? 0;
        $register = $this->db->fetch(
            "SELECT r.*, c.name as class_name, s.name as stream_name, sess.name as session_name,
                    u.first_name, u.last_name
            FROM attendance_registers r
            LEFT JOIN classes c ON r.class_id = c.id
            LEFT JOIN streams s ON r.stream_id = s.id
            LEFT JOIN attendance_sessions sess ON r.attendance_session_id = sess.id
            LEFT JOIN users u ON r.recorded_by = u.id
            WHERE r.id = :id",
            ['id' => $id]
        );

        if (!$register) {
            $_SESSION['flash_error'] = 'Register not found.';
            header('Location: ' . BASE_URL . '/attendance');
            exit;
        }

        // Select all status flags from attendance_statuses
        $records = $this->db->fetchAll(
            "SELECT ar.*, st.first_name, st.last_name, st.admission_number,
                    ast.name as status_name, ast.code as status_code,
                    ast.counts_as_present, ast.counts_as_absent, ast.counts_as_late
            FROM attendance_records ar
            INNER JOIN students st ON ar.student_id = st.id
            INNER JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
            WHERE ar.register_id = :id
            ORDER BY st.first_name ASC",
            ['id' => $id]
        );

        echo $this->view->renderWithLayout('attendance/view', 'default', [
            'title' => 'Register Details',
            'register' => $register,
            'records' => $records
        ]);
    }

    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('attendance.record')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $registerId = (int)($params['id'] ?? 0);
        $register = $this->db->fetch("SELECT * FROM attendance_registers WHERE id = :id", ['id' => $registerId]);

        if (!$register) {
            $_SESSION['flash_error'] = 'Register not found.';
            header('Location: ' . BASE_URL . '/attendance');
            exit;
        }

        // Redirect to take view with query params pre-populated
        $queryString = http_build_query([
            'class_id' => $register['class_id'],
            'stream_id' => $register['stream_id'],
            'attendance_session_id' => $register['attendance_session_id'],
            'attendance_date' => $register['attendance_date']
        ]);

        header('Location: ' . BASE_URL . '/attendance/take?' . $queryString);
        exit;
    }
}