<?php
//File: tests/Stdu

namespace NexaT\Tests;

use PHPUnit\Framework\TestCase;
use NexaT\Services\StudentAdmissionService;
use NexaT\Core\Database;

class StudentAdmissionTest extends TestCase
{
    private Database $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();
    }

    public function testStudentAdmissionTransactionRollbackOnFailure(): void
    {
        $service = new StudentAdmissionService();

        $invalidStudent = [
            'school_id'           => 1,
            'registration_number' => 'REG/TEST/001',
            'admission_number'    => 'ADM/TEST/001',
            'first_name'          => 'Test',
            'last_name'           => 'User',
            'gender'              => 'male',
            'current_category_id' => 1,
            'current_status_id'   => 1,
            'admission_date'      => '2026-08-01'
        ];

        $guardianData = [
            'full_name' => 'Parent Test',
            'phone'     => '0700000000'
        ];

        $invalidEnrollment = [
            'academic_year_id' => 1
        ];

        $this->expectException(\Exception::class);

        try {
            $service->admitStudent($invalidStudent, $guardianData, $invalidEnrollment);
        } finally {
            $check = $this->db->fetch("SELECT * FROM students WHERE registration_number = 'REG/TEST/001'");
            $this->assertFalse($check, "Database rollback failed! Student record exists.");
        }
    }
}