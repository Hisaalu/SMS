<?php
// File: /app/Services/StudentAdmissionService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Exception;
use Throwable;

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
            $studentId = $this->createStudent($studentData);
            if (!$studentId) {
                throw new Exception('Failed to insert student core profile.');
            }

            $guardianId = $this->createGuardian($studentData['school_id'], $guardianData);
            $this->linkGuardian($studentId, $guardianId, $guardianData['relationship'] ?? 'Parent');
            $this->createInitialEnrollment($studentId, $studentData, $enrollmentData);

            $this->db->commit();
            return $studentId;

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function enrollStudent(int $studentId, array $enrollmentData, bool $autoDeactivatePrevious = true): int
    {
        $existingActive = $this->db->fetch(
            "SELECT id FROM student_enrollments
             WHERE student_id = :student_id AND status = 'active'",
            ['student_id' => $studentId]
        );

        if ($existingActive) {
            if (!$autoDeactivatePrevious) {
                throw new Exception('Student already has an active enrollment. Deactivate or promote first.');
            }

            $this->db->execute(
                "UPDATE student_enrollments SET status = 'transferred', updated_at = NOW() WHERE id = :id",
                ['id' => $existingActive['id']]
            );
        }

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
                'enrollment_date'    => $enrollmentData['enrollment_date'] ?? date('Y-m-d'),
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    private function createStudent(array $data): int
    {
        $this->db->execute(
            "INSERT INTO students (
                school_id, registration_number, admission_number,
                first_name, middle_name, last_name, preferred_name,
                gender, date_of_birth, phone, email, address, photo_path,
                current_category_id, current_status_id, admission_date
            ) VALUES (
                :school_id, :registration_number, :admission_number,
                :first_name, :middle_name, :last_name, :preferred_name,
                :gender, :date_of_birth, :phone, :email, :address, :photo_path,
                :current_category_id, :current_status_id, :admission_date
            )",
            [
                'school_id'           => $data['school_id'],
                'registration_number' => $data['registration_number'],
                'admission_number'    => $data['admission_number'],
                'first_name'          => $data['first_name'],
                'middle_name'         => $data['middle_name'] ?? null,
                'last_name'           => $data['last_name'],
                'preferred_name'      => $data['preferred_name'] ?? null,
                'gender'              => $data['gender'],
                'date_of_birth'       => $data['date_of_birth'] ?? null,
                'phone'               => $data['phone'] ?? null,
                'email'               => $data['email'] ?? null,
                'address'             => $data['address'] ?? null,
                'photo_path'          => $data['photo_path'] ?? null,
                'current_category_id' => $data['current_category_id'],
                'current_status_id'   => $data['current_status_id'],
                'admission_date'      => $data['admission_date'],
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    private function createGuardian(int $schoolId, array $data): int
    {
        $this->db->execute(
            "INSERT INTO guardians (school_id, full_name, phone, occupation, address)
             VALUES (:school_id, :full_name, :phone, :occupation, :address)",
            [
                'school_id'  => $schoolId,
                'full_name'  => $data['full_name'],
                'phone'      => $data['phone'],
                'occupation' => $data['occupation'] ?? null,
                'address'    => $data['address'] ?? null,
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    private function linkGuardian(int $studentId, int $guardianId, string $relationship): void
    {
        $this->db->execute(
            "INSERT INTO student_guardians (student_id, guardian_id, relationship, is_primary, is_emergency)
             VALUES (:student_id, :guardian_id, :relationship, 1, 1)",
            [
                'student_id'   => $studentId,
                'guardian_id'  => $guardianId,
                'relationship' => $relationship,
            ]
        );
    }

    private function createInitialEnrollment(int $studentId, array $studentData, array $enrollmentData): void
    {
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
                'enrollment_date'    => $studentData['admission_date'],
            ]
        );
    }
}