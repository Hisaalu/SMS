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
        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $safeFetchCount = function (string $sql, array $params = []) use ($db): int {
            try {
                return (int) ($db->fetch($sql, $params)['total'] ?? 0);
            } catch (\PDOException $e) {
                return 0;
            }
        };

        // 1. Core Summary Metrics
        $totalStudents = $safeFetchCount("SELECT COUNT(id) as total FROM students WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $totalTeachers = $safeFetchCount("SELECT COUNT(id) as total FROM staff WHERE school_id = :school_id", ['school_id' => $schoolId]);
        $totalClasses  = $safeFetchCount("SELECT COUNT(id) as total FROM classes WHERE school_id = :school_id", ['school_id' => $schoolId]);

        // 2. Gender Distribution (Male vs Female)
        $genderData = ['male' => 0, 'female' => 0, 'other' => 0];
        try {
            $genders = $db->fetchAll(
                "SELECT LOWER(gender) as gender, COUNT(id) as total 
                 FROM students 
                 WHERE school_id = :school_id AND status = 'active' 
                 GROUP BY LOWER(gender)",
                ['school_id' => $schoolId]
            );
            foreach ($genders as $g) {
                $key = strtolower($g['gender']);
                if (isset($genderData[$key])) {
                    $genderData[$key] = (int)$g['total'];
                } else {
                    $genderData['other'] += (int)$g['total'];
                }
            }
        } catch (\PDOException $e) {}

        // 3. Weekly Attendance Trends (Past 7 Days)
        $attendanceTrend = ['labels' => [], 'data' => []];
        try {
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $dayName = date('D', strtotime($date));
                
                $att = $db->fetch(
                    "SELECT COUNT(id) as total_marked, 
                            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count 
                     FROM attendance 
                     WHERE date = :date AND school_id = :school_id",
                    ['date' => $date, 'school_id' => $schoolId]
                );

                $rate = (!empty($att['total_marked']) && $att['total_marked'] > 0)
                    ? round(($att['present_count'] / $att['total_marked']) * 100)
                    : 0;

                $attendanceTrend['labels'][] = $dayName;
                $attendanceTrend['data'][] = $rate;
            }
        } catch (\PDOException $e) {}

        // Today's attendance calculation
        $todayAttendance = end($attendanceTrend['data']) ?: 0;

        // 4. Distribution: Students per Class
        $classDistribution = ['labels' => [], 'data' => []];
        try {
            $classStats = $db->fetchAll(
                "SELECT c.name, COUNT(s.id) as total 
                 FROM classes c 
                 LEFT JOIN students s ON s.class_id = c.id AND s.status = 'active'
                 WHERE c.school_id = :school_id 
                 GROUP BY c.id, c.name 
                 ORDER BY c.name ASC LIMIT 8",
                ['school_id' => $schoolId]
            );
            foreach ($classStats as $cs) {
                $classDistribution['labels'][] = $cs['name'];
                $classDistribution['data'][] = (int)$cs['total'];
            }
        } catch (\PDOException $e) {}

        // 5. Staff Distribution by Category
        $staffCategories = ['labels' => [], 'data' => []];
        try {
            $staffStats = $db->fetchAll(
                "SELECT sc.name, COUNT(st.id) as total 
                 FROM staff_categories sc 
                 LEFT JOIN staff st ON st.staff_category_id = sc.id 
                 WHERE sc.school_id = :school_id 
                 GROUP BY sc.id, sc.name",
                ['school_id' => $schoolId]
            );
            foreach ($staffStats as $ss) {
                $staffCategories['labels'][] = $ss['name'];
                $staffCategories['data'][] = (int)$ss['total'];
            }
        } catch (\PDOException $e) {}

        // 6. Academic Year & Term Context
        $currentTerm = null;
        try {
            $currentTerm = $db->fetch(
                "SELECT t.name as term_name, ay.name as year_name 
                 FROM terms t 
                 JOIN academic_years ay ON t.academic_year_id = ay.id 
                 WHERE t.is_current = 1 AND t.school_id = :school_id LIMIT 1",
                ['school_id' => $schoolId]
            );
        } catch (\PDOException $e) {}

        // 7. Recent Audit Activities
        $recentActivities = [];
        try {
            $recentActivities = $db->fetchAll(
                "SELECT al.*, u.first_name, u.last_name 
                 FROM audit_logs al 
                 LEFT JOIN users u ON al.user_id = u.id 
                 WHERE al.school_id = :school_id
                 ORDER BY al.created_at DESC LIMIT 5",
                ['school_id' => $schoolId]
            );
        } catch (\PDOException $e) {}

        $data = [
            'totalStudents'     => $totalStudents,
            'totalTeachers'     => $totalTeachers,
            'totalClasses'      => $totalClasses,
            'todayAttendance'   => $todayAttendance,
            'genderData'        => $genderData,
            'attendanceTrend'   => $attendanceTrend,
            'classDistribution' => $classDistribution,
            'staffCategories'   => $staffCategories,
            'currentTerm'       => $currentTerm['term_name'] ?? 'Term 1',
            'currentYear'       => $currentTerm['year_name'] ?? date('Y'),
            'recentActivities'  => $recentActivities ?? [],
            'user'              => $this->auth->getUser(),
        ];

        echo $this->view->renderWithLayout('dashboard/index', 'default', $data);
    }
}