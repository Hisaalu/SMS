<?php

namespace NexaT\Services;

use NexaT\Core\Database;

class AttendanceService
{
    private $db;
    private $settings;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->settings = new SettingsService();
    }
    
    public function getStatuses(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM attendance_statuses WHERE school_id = :school_id AND status = 'active' ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
    
    public function getAllStatuses(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM attendance_statuses WHERE school_id = :school_id ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
    
    public function getSessions(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM attendance_sessions WHERE school_id = :school_id AND status = 'active' ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
    
    public function getAllSessions(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM attendance_sessions WHERE school_id = :school_id ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
    
    public function getEnrolledStudents(int $classId, ?int $streamId, int $schoolId): array
    {
        $sql = "SELECT s.id, s.admission_number, s.first_name, s.last_name, se.id as enrollment_id
                FROM student_enrollments se
                INNER JOIN students s ON se.student_id = s.id
                WHERE se.class_id = :class_id 
                AND se.status = 'active'";
        
        $params = [
            'class_id' => $classId
        ];

        if ($streamId !== null) {
            $sql .= " AND se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }

        $sql .= " ORDER BY s.first_name ASC, s.last_name ASC";

        return $this->db->fetchAll($sql, $params);
    }
    
    public function getOrCreateRegister(array $data, int $userId): array
    {
        $schoolId = $data['school_id'] ?? 1;
        $classId = $data['class_id'];
        $streamId = !empty($data['stream_id']) ? (int)$data['stream_id'] : null;
        $sessionId = $data['attendance_session_id'];
        $date = $data['attendance_date'];

        // Build query conditionally to avoid PDO parameter mismatch with NULL
        $sql = "SELECT * FROM attendance_registers 
                WHERE (school_id = :school_id OR school_id IS NULL) 
                AND class_id = :class_id 
                AND attendance_session_id = :session_id 
                AND attendance_date = :date";

        $params = [
            'school_id' => $schoolId,
            'class_id' => $classId,
            'session_id' => $sessionId,
            'date' => $date
        ];

        if ($streamId !== null) {
            $sql .= " AND stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        } else {
            $sql .= " AND stream_id IS NULL";
        }

        $existing = $this->db->fetch($sql, $params);

        if ($existing) {
            return $existing;
        }

        // Insert new register
        $insertData = [
            'school_id' => $schoolId,
            'academic_year_id' => $data['academic_year_id'] ?? 1,
            'academic_period_id' => $data['academic_period_id'] ?? 1,
            'class_id' => $classId,
            'stream_id' => $streamId, // PDO driver handles PHP null as SQL NULL
            'attendance_session_id' => $sessionId,
            'attendance_date' => $date,
            'recorded_by' => $userId,
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->insert('attendance_registers', $insertData);
        
        return $this->db->fetch("SELECT * FROM attendance_registers WHERE id = :id", ['id' => $id]);
    }
    
    public function saveAttendance(array $data, int $userId): array
    {
        $this->db->beginTransaction();
        
        try {
            $schoolId = $data['school_id'] ?? 1;
            $defaultStatusId = (int)$this->settings->get('attendance.default_status_id', 1);
            
            $register = $this->getOrCreateRegister($data, $userId);
            $students = $this->getEnrolledStudents($data['class_id'], $data['stream_id'] ?? null, $schoolId);
            
            $records = [];
            $statusId = $data['attendance_status_id'] ?? $defaultStatusId;
            $reason = $data['reason'] ?? null;
            $remarks = $data['remarks'] ?? null;
            
            foreach ($students as $student) {
                $existing = $this->db->fetch(
                    "SELECT * FROM attendance_records WHERE register_id = :register_id AND student_id = :student_id",
                    ['register_id' => $register['id'], 'student_id' => $student['id']]
                );
                
                $recordData = [
                    'school_id' => $schoolId,
                    'register_id' => $register['id'],
                    'student_id' => $student['id'],
                    'student_enrollment_id' => $student['enrollment_id'],
                    'attendance_status_id' => $statusId,
                    'reason' => $reason,
                    'remarks' => $remarks,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($existing) {
                    $this->db->update('attendance_records', $recordData, ['id' => $existing['id']]);
                    $recordData['id'] = $existing['id'];
                } else {
                    $recordData['created_at'] = date('Y-m-d H:i:s');
                    $recordId = $this->db->insert('attendance_records', $recordData);
                    $recordData['id'] = $recordId;
                }
                
                $records[] = $recordData;
            }
            
            $this->db->update('attendance_registers', [
                'status' => 'submitted',
                'submitted_at' => date('Y-m-d H:i:s'),
                'recorded_by' => $userId,
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $register['id']]);
            
            $this->db->commit();
            
            return [
                'register' => $register,
                'records' => $records,
                'total' => count($records)
            ];
            
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getStudentAttendance(int $studentId, int $schoolId, array $filters = []): array
    {
        $sql = "SELECT ar.*, ass.name as status_name, ass.code as status_code, 
                       ass.counts_as_present, ass.counts_as_absent, ass.counts_as_late,
                       r.attendance_date, r.attendance_session_id, s.name as session_name
                FROM attendance_records ar
                INNER JOIN attendance_registers r ON ar.register_id = r.id
                INNER JOIN attendance_statuses ass ON ar.attendance_status_id = ass.id
                INNER JOIN attendance_sessions s ON r.attendance_session_id = s.id
                WHERE ar.student_id = :student_id AND r.school_id = :school_id";
        
        $params = ['student_id' => $studentId, 'school_id' => $schoolId];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND r.attendance_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND r.attendance_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['academic_year_id'])) {
            $sql .= " AND r.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = $filters['academic_year_id'];
        }
        
        if (!empty($filters['academic_period_id'])) {
            $sql .= " AND r.academic_period_id = :academic_period_id";
            $params['academic_period_id'] = $filters['academic_period_id'];
        }
        
        $sql .= " ORDER BY r.attendance_date DESC, s.display_order ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getStudentAttendanceSummary(int $studentId, int $schoolId, array $filters = []): array
    {
        $records = $this->getStudentAttendance($studentId, $schoolId, $filters);
        
        $total = count($records);
        $present = 0;
        $absent = 0;
        $late = 0;
        
        foreach ($records as $record) {
            if ($record['counts_as_present']) {
                $present++;
            }
            if ($record['counts_as_absent']) {
                $absent++;
            }
            if ($record['counts_as_late']) {
                $late++;
            }
        }
        
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'percentage' => $percentage,
            'records' => $records
        ];
    }
    
    public function getClassAttendanceSummary(int $classId, int $schoolId, int $academicYearId = null, int $academicPeriodId = null): array
    {
        $params = ['class_id' => $classId, 'school_id' => $schoolId];
        $sql = "SELECT 
                    COUNT(DISTINCT r.id) as total_days,
                    COUNT(DISTINCT ar.student_id) as total_students,
                    SUM(CASE WHEN ass.counts_as_present THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN ass.counts_as_absent THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN ass.counts_as_late THEN 1 ELSE 0 END) as late_count
                FROM attendance_registers r
                INNER JOIN attendance_records ar ON r.id = ar.register_id
                INNER JOIN attendance_statuses ass ON ar.attendance_status_id = ass.id
                WHERE r.class_id = :class_id AND r.school_id = :school_id";
        
        if ($academicYearId) {
            $sql .= " AND r.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = $academicYearId;
        }
        
        if ($academicPeriodId) {
            $sql .= " AND r.academic_period_id = :academic_period_id";
            $params['academic_period_id'] = $academicPeriodId;
        }
        
        return $this->db->fetch($sql, $params) ?? [
            'total_days' => 0,
            'total_students' => 0,
            'present_count' => 0,
            'absent_count' => 0,
            'late_count' => 0
        ];
    }
}