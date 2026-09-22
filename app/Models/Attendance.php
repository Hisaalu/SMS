<?php
// File: /app/Models/Attendance.php

namespace NexaT\Models;

use NexaT\Core\Model;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'register_id', 'student_id', 'student_enrollment_id',
        'attendance_status_id', 'reason', 'remarks',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;
}

class AttendanceRegister extends Model
{
    protected $table = 'attendance_registers';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'academic_year_id', 'academic_period_id',
        'class_id', 'stream_id', 'attendance_session_id',
        'attendance_date', 'recorded_by', 'submitted_at', 'status', 'remarks',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public function records(): array
    {
        return $this->db->fetchAll(
            "SELECT ar.*, s.first_name, s.last_name, s.admission_number,
                    ass.name AS status_name, ass.code AS status_code,
                    ass.counts_as_present, ass.counts_as_absent,
                    ass.counts_as_late, ass.requires_reason
             FROM attendance_records ar
             INNER JOIN students s ON ar.student_id = s.id
             INNER JOIN attendance_statuses ass ON ar.attendance_status_id = ass.id
             WHERE ar.register_id = :register_id",
            ['register_id' => $this->id]
        );
    }

    public function getStatusCounts(): array
    {
        return $this->db->fetchAll(
            "SELECT ass.name, ass.code, COUNT(*) AS count
             FROM attendance_records ar
             INNER JOIN attendance_statuses ass ON ar.attendance_status_id = ass.id
             WHERE ar.register_id = :register_id
             GROUP BY ar.attendance_status_id",
            ['register_id' => $this->id]
        );
    }
}

class AttendanceSession extends Model
{
    protected $table = 'attendance_sessions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'name', 'code', 'description',
        'start_time', 'end_time', 'status', 'display_order',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public static function getActive(?int $schoolId = null): array
    {
        $instance = new static();
        $schoolId = $schoolId ?? $instance->schoolId;

        return $instance->db->fetchAll(
            "SELECT * FROM attendance_sessions
             WHERE school_id = :school_id AND status = 'active'
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
}

class AttendanceStatus extends Model
{
    protected $table = 'attendance_statuses';
    protected $primaryKey = 'id';
    protected $fillable = [
        'school_id', 'name', 'code', 'description',
        'counts_as_present', 'counts_as_absent', 'counts_as_late',
        'requires_reason', 'status', 'display_order',
    ];
    protected $guarded = ['id'];
    protected $timestamps = true;

    public static function getActive(?int $schoolId = null): array
    {
        $instance = new static();
        $schoolId = $schoolId ?? $instance->schoolId;

        return $instance->db->fetchAll(
            "SELECT * FROM attendance_statuses
             WHERE school_id = :school_id AND status = 'active'
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );
    }
}