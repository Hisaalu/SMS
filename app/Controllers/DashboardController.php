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

        $currentYear = $this->db->fetch(
            "SELECT id, name, start_date, end_date
             FROM academic_years
             WHERE school_id = :school_id AND is_current = 1
             LIMIT 1",
            ['school_id' => $schoolId]
        );

        $currentTerm = null;
        if ($currentYear) {
            $currentTerm = $this->db->fetch(
                "SELECT id, name, term_number, start_date, end_date
                 FROM terms
                 WHERE academic_year_id = :year_id AND is_current = 1
                 LIMIT 1",
                ['year_id' => $currentYear['id']]
            );
        }

        $filterYearId = (int)($_GET['year_id'] ?? 0);
        $filterTermId = (int)($_GET['term_id'] ?? 0);

        $activeYear = $currentYear;
        if ($filterYearId && $filterYearId !== (int)($currentYear['id'] ?? 0)) {
            $activeYear = $this->db->fetch(
                "SELECT id, name, start_date, end_date
                 FROM academic_years
                 WHERE id = :id AND school_id = :school_id
                 LIMIT 1",
                ['id' => $filterYearId, 'school_id' => $schoolId]
            ) ?: $currentYear;
        }

        $activeTerm = $currentTerm;
        if ($filterTermId && $activeYear) {
            $activeTerm = $this->db->fetch(
                "SELECT id, name, term_number, start_date, end_date
                 FROM terms
                 WHERE id = :id AND academic_year_id = :year_id
                 LIMIT 1",
                ['id' => $filterTermId, 'year_id' => $activeYear['id']]
            ) ?: $currentTerm;
        } elseif ($activeYear && (int)$activeYear['id'] !== (int)($currentYear['id'] ?? 0)) {
            $activeTerm = $this->db->fetch(
                "SELECT id, name, term_number, start_date, end_date
                 FROM terms
                 WHERE academic_year_id = :year_id
                 ORDER BY term_number ASC LIMIT 1",
                ['year_id' => $activeYear['id']]
            ) ?: null;
        }

        $yearId = (int)($activeYear['id'] ?? 0);
        $termId = (int)($activeTerm['id'] ?? 0);

        $totalStudents = $this->scopedStudentCount($schoolId, $yearId);
        $totalTeachers = $this->safeCount(
            "SELECT COUNT(id) AS total FROM staff WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
        $totalClasses  = $this->safeCount(
            "SELECT COUNT(id) AS total FROM classes WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );

        $attendanceTrend = $this->buildAttendanceTrend($schoolId, $yearId, $termId);
        $todayAttendance = !empty($attendanceTrend['data']) ? end($attendanceTrend['data']) : 0;

        $data = [
            'totalStudents'         => $totalStudents,
            'totalTeachers'         => $totalTeachers,
            'totalClasses'          => $totalClasses,
            'todayAttendance'       => $todayAttendance,
            'genderData'            => $this->buildGenderDistribution($schoolId, $yearId),
            'attendanceTrend'       => $attendanceTrend,
            'classDistribution'     => $this->buildClassDistribution($schoolId, $yearId),
            'staffCategories'       => $this->buildStaffDistribution($schoolId),
            'currentYear'           => $activeYear,
            'currentTerm'           => $activeTerm,
            'isCurrentYear'         => $currentYear && $activeYear
                                        && (int)$currentYear['id'] === (int)$activeYear['id'],
            'isCurrentTerm'         => $currentTerm && $activeTerm
                                        && (int)$currentTerm['id'] === (int)$activeTerm['id'],
            'academicTermsList'     => $this->getAcademicTermsList($schoolId),
            'recentActivities'      => $this->recentActivities($schoolId),
            'recentlyAddedStudents' => $this->recentlyAddedStudents($schoolId, $yearId),
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

    private function scopedStudentCount(int $schoolId, int $yearId): int
    {
        $sql = "SELECT COUNT(DISTINCT s.id) AS total
                FROM students s
                INNER JOIN student_enrollments se ON se.student_id = s.id
                WHERE s.school_id = :school_id AND se.status = 'active'";
        $params = ['school_id' => $schoolId];

        if ($yearId) {
            $sql .= " AND se.academic_year_id = :year_id";
            $params['year_id'] = $yearId;
        }

        return $this->safeCount($sql, $params);
    }

    private function buildGenderDistribution(int $schoolId, int $yearId): array
    {
        $gender = ['male' => 0, 'female' => 0, 'other' => 0];

        try {
            $sql = "SELECT LOWER(s.gender) AS gender, COUNT(DISTINCT s.id) AS total
                    FROM students s
                    INNER JOIN student_enrollments se ON se.student_id = s.id
                    WHERE s.school_id = :school_id AND se.status = 'active'";
            $params = ['school_id' => $schoolId];

            if ($yearId) {
                $sql .= " AND se.academic_year_id = :year_id";
                $params['year_id'] = $yearId;
            }

            $sql .= " GROUP BY LOWER(s.gender)";

            foreach ($this->db->fetchAll($sql, $params) as $row) {
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

    private function buildAttendanceTrend(int $schoolId, int $yearId, int $termId): array
    {
        $trend = ['labels' => [], 'data' => []];

        try {
            $startDate = null;
            $endDate   = null;

            if ($termId) {
                $term = $this->db->fetch(
                    "SELECT start_date, end_date FROM terms WHERE id = :id LIMIT 1",
                    ['id' => $termId]
                );
                if ($term) {
                    $startDate = $term['start_date'];
                    $endDate   = $term['end_date'];
                }
            }

            if (!$startDate || !$endDate) {
                $startDate = date('Y-m-d', strtotime('-6 days'));
                $endDate   = date('Y-m-d');
            }

            $today = date('Y-m-d');
            if ($endDate > $today) {
                $endDate = $today;
            }

            $rangeStart = new \DateTime($startDate);
            $rangeEnd   = new \DateTime($endDate);
            $interval   = new \DateInterval('P1D');
            $period     = new \DatePeriod($rangeStart, $interval, $rangeEnd->modify('+1 day'));

            foreach ($period as $date) {
                $day = $date->format('Y-m-d');

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
                    ['date' => $day, 'school_id' => $schoolId]
                );

                $marked  = (int)($row['total_marked']  ?? 0);
                $present = (int)($row['present_count'] ?? 0);
                $rate    = $marked > 0 ? round(($present / $marked) * 100) : 0;

                $trend['labels'][] = $date->format('D');
                $trend['data'][]   = $rate;

                if (count($trend['data']) >= 60) {
                    break;
                }
            }
        } catch (\Throwable $e) {}

        return $trend;
    }

    private function buildClassDistribution(int $schoolId, int $yearId): array
    {
        $distribution = ['labels' => [], 'male' => [], 'female' => []];

        try {
            $sql = "SELECT c.name,
                           SUM(CASE WHEN LOWER(s.gender) = 'male'   THEN 1 ELSE 0 END) AS male_total,
                           SUM(CASE WHEN LOWER(s.gender) = 'female' THEN 1 ELSE 0 END) AS female_total
                    FROM classes c
                    LEFT JOIN student_enrollments se
                           ON se.class_id = c.id
                          AND se.status = 'active'";
            $params = ['school_id' => $schoolId];

            if ($yearId) {
                $sql .= " AND se.academic_year_id = :year_id";
                $params['year_id'] = $yearId;
            }

            $sql .= " LEFT JOIN students s ON se.student_id = s.id
                      WHERE c.school_id = :school_id
                      GROUP BY c.id, c.name
                      ORDER BY c.name ASC
                      LIMIT 8";

            foreach ($this->db->fetchAll($sql, $params) as $row) {
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

    private function getAcademicTermsList(int $schoolId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT t.id AS term_id,
                        t.name AS term_name,
                        t.is_current AS term_is_current,
                        ay.id AS year_id,
                        ay.name AS year_name,
                        ay.is_current AS year_is_current
                 FROM terms t
                 INNER JOIN academic_years ay ON ay.id = t.academic_year_id
                 WHERE t.school_id = :school_id
                 ORDER BY ay.start_date DESC, t.term_number ASC",
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

    private function recentlyAddedStudents(int $schoolId, int $yearId): array
    {
        try {
            $sql = "SELECT s.id, s.first_name, s.last_name,
                           s.admission_number, s.registration_number,
                           cl.name AS class_name,
                           sc.name AS category_name
                    FROM students s
                    LEFT JOIN student_enrollments se
                           ON se.student_id = s.id AND se.status = 'active'";
            $params = ['school_id' => $schoolId];

            if ($yearId) {
                $sql .= " AND se.academic_year_id = :year_id";
                $params['year_id'] = $yearId;
            }

            $sql .= " LEFT JOIN classes cl ON se.class_id = cl.id
                      LEFT JOIN student_categories sc ON s.current_category_id = sc.id
                      WHERE s.school_id = :school_id
                      ORDER BY s.id DESC
                      LIMIT 5";

            return $this->db->fetchAll($sql, $params);
        } catch (\Throwable $e) {
            return [];
        }
    }
}