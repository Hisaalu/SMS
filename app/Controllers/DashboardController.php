<?php
// File: /app/Controllers/DashboardController.php
namespace NexaT\Controllers;

use NexaT\Core\Controller;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();

        $totalStudents = $this->safeCount(
            "SELECT COUNT(DISTINCT s.id) AS total
             FROM students s
             INNER JOIN student_enrollments se ON se.student_id = s.id
             WHERE s.school_id = :school_id AND se.status = 'active'",
            ['school_id' => $schoolId]
        );

        $totalTeachers = $this->safeCount(
            "SELECT COUNT(id) AS total FROM staff WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );

        $totalClasses = $this->safeCount(
            "SELECT COUNT(id) AS total FROM classes WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );

        $attendanceTrend = $this->buildAttendanceTrend($schoolId);
        $todayAttendance = end($attendanceTrend['data']) ?: 0;
        
        $selectedTerm = $_GET['term'] ?? null;
        $selectedYear = $_GET['year'] ?? null;
        $term = $this->resolvedTerm($schoolId, $selectedTerm, $selectedYear);

        $data = [
            'totalStudents'         => $totalStudents,
            'totalTeachers'         => $totalTeachers,
            'totalClasses'          => $totalClasses,
            'todayAttendance'       => $todayAttendance,
            'genderData'            => $this->buildGenderDistribution($schoolId),
            'attendanceTrend'       => $attendanceTrend,
            'classDistribution'     => $this->buildClassDistribution($schoolId),
            'staffCategories'       => $this->buildStaffDistribution($schoolId),
            'currentTerm'           => $term['term'],
            'currentYear'           => $term['year'],
            'academicTermsList'     => $this->getAcademicTermsList($schoolId),
            'recentActivities'      => $this->recentActivities($schoolId),
            'recentlyAddedStudents' => $this->recentlyAddedStudents($schoolId),
            'user'                  => $this->auth->getUser(),
        ];

        echo $this->view->renderWithLayout('dashboard/index', 'default', $data);
    }

    private function safeCount(string $sql, array $params = []): int
    {
        try {
            return (int)($this->db->fetch($sql, $params)['total'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function buildGenderDistribution(int $schoolId): array
    {
        $gender = ['male' => 0, 'female' => 0, 'other' => 0];

        try {
            $rows = $this->db->fetchAll(
                "SELECT LOWER(s.gender) AS gender, COUNT(DISTINCT s.id) AS total
                 FROM students s
                 INNER JOIN student_enrollments se ON se.student_id = s.id
                 WHERE s.school_id = :school_id AND se.status = 'active'
                 GROUP BY LOWER(s.gender)",
                ['school_id' => $schoolId]
            );

            foreach ($rows as $row) {
                $key = strtolower($row['gender'] ?? 'other');
                if (isset($gender[$key])) {
                    $gender[$key] = (int)$row['total'];
                } else {
                    $gender['other'] += (int)$row['total'];
                }
            }
        } catch (\Throwable $e) {}

        return $gender;
    }

    private function buildAttendanceTrend(int $schoolId): array
    {
        $trend = ['labels' => [], 'data' => []];

        try {
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));

                $row = $this->db->fetch(
                    "SELECT
                        COUNT(ar.id) AS total_marked,
                        SUM(CASE WHEN ast.counts_as_present = 1 THEN 1 ELSE 0 END) AS present_count
                     FROM attendance_records ar
                     INNER JOIN attendance_registers r ON ar.register_id = r.id
                     INNER JOIN attendance_statuses ast ON ar.attendance_status_id = ast.id
                     WHERE r.attendance_date = :date
                       AND r.school_id = :school_id
                       AND r.status = 'submitted'",
                    ['date' => $date, 'school_id' => $schoolId]
                );

                $marked  = (int)($row['total_marked']  ?? 0);
                $present = (int)($row['present_count'] ?? 0);
                $rate    = $marked > 0 ? round(($present / $marked) * 100) : 0;

                $trend['labels'][] = date('D', strtotime($date));
                $trend['data'][]   = $rate;
            }
        } catch (\Throwable $e) {}

        return $trend;
    }

    private function buildClassDistribution(int $schoolId): array
    {
        $distribution = ['labels' => [], 'male' => [], 'female' => []];

        try {
            $rows = $this->db->fetchAll(
                "SELECT c.name, 
                        SUM(CASE WHEN LOWER(s.gender) = 'male' THEN 1 ELSE 0 END) AS male_total,
                        SUM(CASE WHEN LOWER(s.gender) = 'female' THEN 1 ELSE 0 END) AS female_total
                 FROM classes c
                 LEFT JOIN student_enrollments se ON se.class_id = c.id AND se.status = 'active'
                 LEFT JOIN students s ON se.student_id = s.id
                 WHERE c.school_id = :school_id
                 GROUP BY c.id, c.name
                 ORDER BY c.name ASC
                 LIMIT 8",
                ['school_id' => $schoolId]
            );

            foreach ($rows as $row) {
                $distribution['labels'][] = $row['name'];
                $distribution['male'][]   = (int)$row['male_total'];
                $distribution['female'][] = (int)$row['female_total'];
            }
        } catch (\Throwable $e) {}

        return $distribution;
    }

    private function buildStaffDistribution(int $schoolId): array
    {
        $distribution = ['labels' => [], 'data' => []];

        try {
            $rows = $this->db->fetchAll(
                "SELECT sc.name, COUNT(st.id) AS total
                 FROM staff_categories sc
                 LEFT JOIN staff st ON st.staff_category_id = sc.id
                 WHERE sc.school_id = :school_id
                 GROUP BY sc.id, sc.name",
                ['school_id' => $schoolId]
            );

            foreach ($rows as $row) {
                $distribution['labels'][] = $row['name'];
                $distribution['data'][]   = (int)$row['total'];
            }
        } catch (\Throwable $e) {}

        return $distribution;
    }

    private function resolvedTerm(int $schoolId, ?string $term, ?string $year): array
    {
        if ($term && $year) {
            return ['term' => $term, 'year' => $year];
        }

        try {
            $row = $this->db->fetch(
                "SELECT t.name AS term_name, ay.name AS year_name
                 FROM terms t
                 JOIN academic_years ay ON t.academic_year_id = ay.id
                 WHERE t.is_current = 1 AND t.school_id = :school_id
                 LIMIT 1",
                ['school_id' => $schoolId]
            );

            if ($row) {
                return ['term' => $row['term_name'], 'year' => $row['year_name']];
            }
        } catch (\Throwable $e) {}

        try {
            $row = $this->db->fetch(
                "SELECT name FROM terms WHERE school_id = :school_id ORDER BY id DESC LIMIT 1",
                ['school_id' => $schoolId]
            );

            if ($row) {
                return ['term' => $row['name'], 'year' => date('Y')];
            }
        } catch (\Throwable $e) {}

        return ['term' => 'Term 1', 'year' => date('Y')];
    }

    private function getAcademicTermsList(int $schoolId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT t.name AS term_name, ay.name AS year_name, t.is_current
                 FROM terms t
                 JOIN academic_years ay ON t.academic_year_id = ay.id
                 WHERE t.school_id = :school_id
                 ORDER BY ay.name DESC, t.id DESC",
                ['school_id' => $schoolId]
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function recentActivities(int $schoolId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT al.*, u.first_name, u.last_name
                 FROM audit_logs al
                 LEFT JOIN users u ON al.user_id = u.id
                 WHERE al.school_id = :school_id
                 ORDER BY al.created_at DESC
                 LIMIT 5",
                ['school_id' => $schoolId]
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function recentlyAddedStudents(int $schoolId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT s.first_name, s.last_name, s.admission_number, cl.name AS class_name, sc.name AS category_name
                 FROM students s
                 LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
                 LEFT JOIN classes cl ON se.class_id = cl.id
                 LEFT JOIN student_categories sc ON s.current_category_id = sc.id
                 WHERE s.school_id = :school_id
                 ORDER BY s.id DESC
                 LIMIT 5",
                ['school_id' => $schoolId]
            );
        } catch (\Throwable $e) {
            return [];
        }
    }
}