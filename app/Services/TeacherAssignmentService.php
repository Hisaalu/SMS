<?php

namespace NexaT\Services;

use NexaT\Core\Database;
use Exception;

class TeacherAssignmentService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function assignTeacher(array $data): bool
    {
        // Check for conflicting active assignments
        $existing = $this->db->fetch(
            "SELECT id FROM teacher_assignments 
             WHERE school_id = :school_id 
               AND staff_id = :staff_id 
               AND academic_year_id = :academic_year_id 
               AND class_id = :class_id 
               AND subject_id = :subject_id 
               AND status = 'active'",
            [
                'school_id'        => $data['school_id'],
                'staff_id'         => $data['staff_id'],
                'academic_year_id' => $data['academic_year_id'],
                'class_id'         => $data['class_id'],
                'subject_id'       => $data['subject_id']
            ]
        );

        if ($existing) {
            throw new Exception("Teacher is already assigned to this subject and class for the selected academic year.");
        }

        return $this->db->query(
            "INSERT INTO teacher_assignments 
            (school_id, staff_id, assignment_type_id, academic_year_id, academic_period_id, class_id, stream_id, subject_id, start_date, status) 
            VALUES 
            (:school_id, :staff_id, :assignment_type_id, :academic_year_id, :academic_period_id, :class_id, :stream_id, :subject_id, :start_date, 'active')",
            [
                'school_id'          => $data['school_id'],
                'staff_id'           => $data['staff_id'],
                'assignment_type_id' => $data['assignment_type_id'],
                'academic_year_id'   => $data['academic_year_id'],
                'academic_period_id' => $data['academic_period_id'] ?? null,
                'class_id'           => $data['class_id'],
                'stream_id'          => $data['stream_id'] ?? null,
                'subject_id'         => $data['subject_id'] ?? null,
                'start_date'         => $data['start_date'] ?? date('Y-m-d')
            ]
        );
    }
}