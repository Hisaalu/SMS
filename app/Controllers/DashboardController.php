<?php
// File: /app/Controllers/DashboardController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('login');
            exit;
        }

        $db = Database::getInstance();

        // Safe query helper to handle missing tables during development
        $safeFetchCount = function (string $sql) use ($db): int {
            try {
                return (int) ($db->fetch($sql)['total'] ?? 0);
            } catch (\PDOException $e) {
                return 0;
            }
        };

        // 1. Fetch Dynamic Statistics
        $totalStudents = $safeFetchCount("SELECT COUNT(id) as total FROM students WHERE status = 'active'");
        $totalTeachers = $safeFetchCount("SELECT COUNT(id) as total FROM users WHERE role = 'teacher' AND status = 'active'");
        $totalClasses  = $safeFetchCount("SELECT COUNT(id) as total FROM classes");

        // 2. Calculate Today's Attendance Percentage
        $todayAttendance = 0;
        try {
            $today = date('Y-m-d');
            $attendanceData = $db->fetch(
                "SELECT 
                    COUNT(id) as total_marked, 
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count 
                 FROM attendance 
                 WHERE date = ?",
                [$today]
            );

            if (!empty($attendanceData['total_marked']) && $attendanceData['total_marked'] > 0) {
                $todayAttendance = round(($attendanceData['present_count'] / $attendanceData['total_marked']) * 100);
            }
        } catch (\PDOException $e) {
            $todayAttendance = 0;
        }

        // 3. Fetch Current Academic Year and Term
        $currentTerm = null;
        try {
            $currentTerm = $db->fetch(
                "SELECT t.name as term_name, ay.name as year_name 
                 FROM terms t 
                 JOIN academic_years ay ON t.academic_year_id = ay.id 
                 WHERE t.is_current = 1 LIMIT 1"
            );
        } catch (\PDOException $e) {
            $currentTerm = null;
        }

        // 4. Fetch Recent Audit Logs / System Activities
        $recentActivities = [];
        try {
            $recentActivities = $db->fetchAll(
                "SELECT al.*, u.first_name, u.last_name 
                 FROM audit_logs al 
                 LEFT JOIN users u ON al.user_id = u.id 
                 ORDER BY al.created_at DESC 
                 LIMIT 5"
            );
        } catch (\PDOException $e) {
            $recentActivities = [];
        }

        $data = [
            'totalStudents'    => $totalStudents,
            'totalTeachers'    => $totalTeachers,
            'totalClasses'     => $totalClasses,
            'todayAttendance'  => $todayAttendance,
            'currentTerm'      => $currentTerm['term_name'] ?? 'Term 1',
            'currentYear'      => $currentTerm['year_name'] ?? date('Y'),
            'recentActivities' => $recentActivities ?? [],
            'user'             => $this->auth->getUser(),
        ];

        echo $this->view->renderWithLayout('dashboard/index', 'default', $data);
    }
}