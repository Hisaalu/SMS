<?php

namespace NexaT\Services;

use NexaT\Core\Database;
use Exception;

class StudentAdmissionService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function admitStudent(array $studentData, array $guardianData, array $enrollmentData): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Create Student using explicit query binding (including all contact fields)
            $this->db->execute(
                "INSERT INTO students (
                    school_id, 
                    registration_number, 
                    admission_number, 
                    first_name, 
                    middle_name, 
                    last_name, 
                    preferred_name,
                    gender, 
                    date_of_birth,
                    phone,
                    email,
                    address,
                    photo_path, 
                    current_category_id, 
                    current_status_id, 
                    admission_date
                ) VALUES (
                    :school_id, 
                    :registration_number, 
                    :admission_number, 
                    :first_name, 
                    :middle_name, 
                    :last_name, 
                    :preferred_name,
                    :gender, 
                    :date_of_birth,
                    :phone,
                    :email,
                    :address,
                    :photo_path, 
                    :current_category_id, 
                    :current_status_id, 
                    :admission_date
                )",
                [
                    'school_id'           => $studentData['school_id'],
                    'registration_number' => $studentData['registration_number'],
                    'admission_number'    => $studentData['admission_number'],
                    'first_name'          => $studentData['first_name'],
                    'middle_name'         => $studentData['middle_name'] ?? null,
                    'last_name'           => $studentData['last_name'],
                    'preferred_name'      => $studentData['preferred_name'] ?? null,
                    'gender'              => $studentData['gender'],
                    'date_of_birth'       => $studentData['date_of_birth'] ?? null,
                    'phone'               => $studentData['phone'] ?? null,
                    'email'               => $studentData['email'] ?? null,
                    'address'             => $studentData['address'] ?? null,
                    'photo_path'          => $studentData['photo_path'] ?? null,
                    'current_category_id' => $studentData['current_category_id'],
                    'current_status_id'   => $studentData['current_status_id'],
                    'admission_date'      => $studentData['admission_date']
                ]
            );

            $studentId = (int) $this->db->lastInsertId();

            if (!$studentId) {
                throw new Exception("Failed to insert student core profile.");
            }

            // 2. Create Guardian (including optional Occupation and Address)
            $this->db->execute(
                "INSERT INTO guardians (school_id, full_name, phone, occupation, address) 
                 VALUES (:school_id, :full_name, :phone, :occupation, :address)",
                [
                    'school_id'  => $studentData['school_id'],
                    'full_name'  => $guardianData['full_name'],
                    'phone'      => $guardianData['phone'],
                    'occupation' => $guardianData['occupation'] ?? null,
                    'address'    => $guardianData['address'] ?? null
                ]
            );

            $guardianId = (int) $this->db->lastInsertId();

            // Link Guardian to Student
            $this->db->execute(
                "INSERT INTO student_guardians (student_id, guardian_id, relationship, is_primary, is_emergency) 
                 VALUES (:student_id, :guardian_id, :relationship, 1, 1)",
                [
                    'student_id'   => $studentId,
                    'guardian_id'  => $guardianId,
                    'relationship' => $guardianData['relationship'] ?? 'Parent'
                ]
            );

            // 3. Create Initial Enrollment
            $this->db->execute(
                "INSERT INTO student_enrollments 
                (student_id, academic_year_id, academic_period_id, class_id, stream_id, student_category_id, student_status_id, enrollment_date, status) 
                VALUES 
                (:student_id, :academic_year_id, :academic_period_id, :class_id, :stream_id, :category_id, :status_id, :enrollment_date, 'active')",
                [
                    'student_id'         => $studentId,
                    'academic_year_id'   => $enrollmentData['academic_year_id'],
                    'academic_period_id' => $enrollmentData['academic_period_id'] ?? null,
                    'class_id'           => $enrollmentData['class_id'],
                    'stream_id'          => $enrollmentData['stream_id'] ?? null,
                    'category_id'        => $studentData['current_category_id'],
                    'status_id'          => $studentData['current_status_id'],
                    'enrollment_date'    => $studentData['admission_date']
                ]
            );

            $this->db->commit();
            return $studentId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function enrollStudent(int $studentId, array $enrollmentData, bool $autoDeactivatePrevious = true): int
    {
        // Check if active enrollment exists
        $existingActive = $this->db->fetch(
            "SELECT id FROM student_enrollments WHERE student_id = :student_id AND status = 'active'",
            ['student_id' => $studentId]
        );

        if ($existingActive) {
            if ($autoDeactivatePrevious) {
                // Deactivate prior enrollment to preserve historical tracking
                $this->db->execute(
                    "UPDATE student_enrollments SET status = 'transferred', updated_at = NOW() WHERE id = :id",
                    ['id' => $existingActive['id']]
                );
            } else {
                throw new Exception("Student already has an active enrollment. Please deactivate or promote the student first.");
            }
        }

        // Create new active enrollment
        $this->db->execute(
            "INSERT INTO student_enrollments 
            (student_id, academic_year_id, academic_period_id, class_id, stream_id, student_category_id, student_status_id, enrollment_date, status) 
            VALUES 
            (:student_id, :academic_year_id, :academic_period_id, :class_id, :stream_id, :category_id, :status_id, :enrollment_date, 'active')",
            [
                'student_id'         => $studentId,
                'academic_year_id'   => $enrollmentData['academic_year_id'],
                'academic_period_id' => $enrollmentData['academic_period_id'] ?? null,
                'class_id'           => $enrollmentData['class_id'],
                'stream_id'          => $enrollmentData['stream_id'] ?? null,
                'category_id'        => $enrollmentData['category_id'],
                'status_id'          => $enrollmentData['status_id'],
                'enrollment_date'    => $enrollmentData['enrollment_date'] ?? date('Y-m-d')
            ]
        );

        return (int) $this->db->lastInsertId();
    }
}