<?php
// File: /app/Services/AcademicReportService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class AcademicReportService
{
    private $db;
    private $gradingService;
    private $resultService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gradingService = new GradingService();
        $this->resultService = new ResultCalculationService();
    }

    /**
     * Detect whether the system uses examination_sets or examinations
     */
    private function getExaminationTable(): string
    {
        try {
            $this->db->fetch("SELECT 1 FROM examination_sets LIMIT 1");
            return 'examination_sets';
        } catch (\Exception $e) {
            return 'examinations';
        }
    }

    /**
     * Detect the subject-relationship column name on the marks table
     */
    private function getMarksSubjectColumn(): string
    {
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM marks");
            foreach ($cols as $c) {
                if ($c['Field'] === 'examination_subject_id') return 'examination_subject_id';
                if ($c['Field'] === 'subject_id') return 'subject_id';
            }
        } catch (\Exception $e) {}
        return 'subject_id';
    }

    /**
     * Detect the academic year column name on the marks table
     */
    private function getMarksYearColumn(): string
    {
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM marks");
            foreach ($cols as $c) {
                if ($c['Field'] === 'academic_year_id') return 'academic_year_id';
            }
        } catch (\Exception $e) {}
        return null;
    }

    public function getReportFilters(int $schoolId): array
    {
        return [
            'academic_years' => $this->safeFetchAll(
                "SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC",
                ['s' => $schoolId]
            ),
            'terms' => $this->safeFetchAll(
                "SELECT t.id, t.name, t.academic_year_id FROM terms t WHERE t.school_id = :s ORDER BY t.term_number ASC",
                ['s' => $schoolId]
            ),
            'classes' => $this->safeFetchAll(
                "SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'streams' => $this->safeFetchAll(
                "SELECT id, name, class_id FROM streams WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'subjects' => $this->safeFetchAll(
                "SELECT id, name, code FROM subjects WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'examinations' => $this->safeFetchAll(
                "SELECT id, name FROM {$this->getExaminationTable()} WHERE school_id = :s ORDER BY id DESC",
                ['s' => $schoolId]
            ),
        ];
    }

    private function safeFetchAll(string $sql, array $params = []): array
    {
        try {
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function safeFetch(string $sql, array $params = []): ?array
    {
        try {
            return $this->db->fetch($sql, $params) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getDashboardStats(int $schoolId, array $filters = []): array
    {
        $where = ["s.school_id = :school_id", "se.status = 'active'"];
        $params = ['school_id' => $schoolId];

        if (!empty($filters['academic_year_id'])) {
            $where[] = "se.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = (int)$filters['academic_year_id'];
        }
        if (!empty($filters['class_id'])) {
            $where[] = "se.class_id = :class_id";
            $params['class_id'] = (int)$filters['class_id'];
        }

        $whereClause = implode(' AND ', $where);

        $total = $this->safeFetch(
            "SELECT COUNT(DISTINCT s.id) as total FROM students s
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE {$whereClause}",
            $params
        );

        // Use marks table filtered by student_id only (safe across schemas)
        $assessed = $this->safeFetch(
            "SELECT COUNT(DISTINCT m.student_id) as total 
             FROM marks m
             INNER JOIN students s ON m.student_id = s.id
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE {$whereClause}",
            $params
        );

        $avg = $this->safeFetch(
            "SELECT AVG(m.marks_obtained) as average, 
                    MAX(m.marks_obtained) as highest, 
                    MIN(m.marks_obtained) as lowest
             FROM marks m
             INNER JOIN students s ON m.student_id = s.id
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE {$whereClause}",
            $params
        );

        $gradeDist = $this->getGradeDistribution($schoolId, $filters);

        return [
            'total_students' => (int)($total['total'] ?? 0),
            'assessed_students' => (int)($assessed['total'] ?? 0),
            'missing_marks' => max(0, (int)($total['total'] ?? 0) - (int)($assessed['total'] ?? 0)),
            'overall_average' => round((float)($avg['average'] ?? 0), 2),
            'highest_mark' => round((float)($avg['highest'] ?? 0), 2),
            'lowest_mark' => round((float)($avg['lowest'] ?? 0), 2),
            'grade_distribution' => $gradeDist,
        ];
    }

    public function getGradeDistribution(int $schoolId, array $filters = []): array
    {
        // Try default system first
        $system = $this->safeFetch(
            "SELECT * FROM grading_systems WHERE school_id = :s AND is_default = 1 LIMIT 1",
            ['s' => $schoolId]
        );
        if (!$system) {
            $system = $this->safeFetch(
                "SELECT * FROM grading_systems WHERE school_id = :s LIMIT 1",
                ['s' => $schoolId]
            );
        }
        if (!$system) {
            return [];
        }

        $rules = $this->safeFetchAll(
            "SELECT * FROM grading_rules WHERE system_id = :id ORDER BY min_mark DESC",
            ['id' => $system['id']]
        );

        if (empty($rules)) {
            return [];
        }

        // Build mark filter - only simple filters that work on any schema
        $where = ["m.school_id = :school_id"];
        $params = ['school_id' => $schoolId];

        if (!empty($filters['academic_year_id'])) {
            $yearCol = $this->getMarksYearColumn();
            if ($yearCol) {
                $where[] = "m.{$yearCol} = :academic_year_id";
                $params['academic_year_id'] = (int)$filters['academic_year_id'];
            }
        }

        $whereClause = implode(' AND ', $where);

        $distribution = [];
        foreach ($rules as $rule) {
            $count = $this->safeFetch(
                "SELECT COUNT(*) as c FROM marks m
                 WHERE {$whereClause}
                 AND m.marks_obtained >= :min AND m.marks_obtained <= :max",
                array_merge($params, ['min' => $rule['min_mark'], 'max' => $rule['max_mark']])
            );
            $distribution[] = [
                'grade' => $rule['grade'],
                'range' => $rule['min_mark'] . ' - ' . $rule['max_mark'],
                'count' => (int)($count['c'] ?? 0),
            ];
        }
        return $distribution;
    }

    public function getSubjectPerformance(int $schoolId, array $filters): array
    {
        if (empty($filters['subject_id'])) {
            return [];
        }

        $subjectId = (int)$filters['subject_id'];

        // Determine which column has data
        $hasSubjectIdData = false;
        try {
            $test = $this->db->fetch(
                "SELECT COUNT(*) as c FROM marks WHERE subject_id IS NOT NULL AND school_id = :s",
                ['s' => $schoolId]
            );
            $hasSubjectIdData = ($test && $test['c'] > 0);
        } catch (\Exception $e) {}

        $params = ['school_id' => $schoolId, 'subject_id' => $subjectId];

        if ($hasSubjectIdData) {
            $where = ["m.school_id = :school_id", "m.subject_id = :subject_id"];
            $join = "";
        } else {
            $where = ["m.school_id = :school_id", "es.subject_id = :subject_id"];
            $join = "INNER JOIN examination_subjects es ON m.examination_subject_id = es.id";
        }

        if (!empty($filters['academic_year_id'])) {
            $where[] = "m.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = (int)$filters['academic_year_id'];
        }

        $whereClause = implode(' AND ', $where);

        $stats = $this->safeFetch(
            "SELECT 
                COUNT(*) as total_marks,
                AVG(m.marks_obtained) as avg_mark,
                MAX(m.marks_obtained) as highest,
                MIN(m.marks_obtained) as lowest
            FROM marks m
            {$join}
            WHERE {$whereClause}",
            $params
        );

        $studentCount = $this->safeFetch(
            "SELECT COUNT(DISTINCT s.id) as c FROM students s
            INNER JOIN student_enrollments se ON s.id = se.student_id
            WHERE s.school_id = :school_id AND se.status = 'active'",
            ['school_id' => $schoolId]
        );

        return [
            'total_students' => (int)($studentCount['c'] ?? 0),
            'assessed' => (int)($stats['total_marks'] ?? 0),
            'average' => round((float)($stats['avg_mark'] ?? 0), 2),
            'highest' => round((float)($stats['highest'] ?? 0), 2),
            'lowest' => round((float)($stats['lowest'] ?? 0), 2),
            'pass_rate' => 0,
        ];
    }

    public function getClassResultsMatrix(int $schoolId, array $filters): array
    {
        if (empty($filters['academic_year_id'])) {
            return ['students' => [], 'subjects' => [], 'marks' => []];
        }

        $params = [
            'school_id' => $schoolId,
            'academic_year_id' => (int)$filters['academic_year_id'],
        ];

        // ------------------------------------------------------------
        // STEP 1: Get students in the class
        // ------------------------------------------------------------
        $studentWhere = ["se.status = 'active'", "se.academic_year_id = :academic_year_id", "s.school_id = :school_id"];
        if (!empty($filters['class_id'])) {
            $studentWhere[] = "se.class_id = :class_id";
            $params['class_id'] = (int)$filters['class_id'];
        }
        if (!empty($filters['stream_id'])) {
            $studentWhere[] = "se.stream_id = :stream_id";
            $params['stream_id'] = (int)$filters['stream_id'];
        }

        $students = $this->safeFetchAll(
            "SELECT DISTINCT s.id, s.admission_number, s.first_name, s.last_name,
                    cl.name as class_name, str.name as stream_name
            FROM students s
            INNER JOIN student_enrollments se ON s.id = se.student_id
            LEFT JOIN classes cl ON se.class_id = cl.id
            LEFT JOIN streams str ON se.stream_id = str.id
            WHERE " . implode(' AND ', $studentWhere) . "
            ORDER BY s.last_name ASC, s.first_name ASC",
            $params
        );

        if (empty($students)) {
            return ['students' => [], 'subjects' => [], 'marks' => []];
        }

        $studentIds = array_column($students, 'id');
        $sPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));

        // ------------------------------------------------------------
        // STEP 2: Determine which schema path to use for marks
        // Priority: use whichever column has actual data
        // ------------------------------------------------------------
        
        // Check if marks has actual data via subject_id
        $hasSubjectIdData = false;
        try {
            $test = $this->db->fetch(
                "SELECT COUNT(*) as c FROM marks 
                WHERE subject_id IS NOT NULL 
                AND student_id IN ({$sPlaceholders})",
                $studentIds
            );
            $hasSubjectIdData = ($test && $test['c'] > 0);
        } catch (\Exception $e) {}

        // Check if marks has actual data via examination_subject_id
        $hasExamSubjectData = false;
        try {
            $test = $this->db->fetch(
                "SELECT COUNT(*) as c FROM marks 
                WHERE examination_subject_id IS NOT NULL 
                AND student_id IN ({$sPlaceholders})",
                $studentIds
            );
            $hasExamSubjectData = ($test && $test['c'] > 0);
        } catch (\Exception $e) {}

        // Decide which column to use
        if ($hasSubjectIdData) {
            $markSubjectColumn = 'subject_id';
        } elseif ($hasExamSubjectData) {
            $markSubjectColumn = 'examination_subject_id';
        } else {
            // No marks at all
            return ['students' => $students, 'subjects' => [], 'marks' => []];
        }

        // ------------------------------------------------------------
        // STEP 3: Get applicable subjects
        // ------------------------------------------------------------
        $subjects = [];

        if ($markSubjectColumn === 'subject_id') {
            // Marks link directly to subjects
            $subjects = $this->safeFetchAll(
                "SELECT DISTINCT s.id, s.name, s.code
                FROM subjects s
                INNER JOIN marks m ON s.id = m.subject_id
                WHERE m.student_id IN ({$sPlaceholders})
                AND s.school_id = ?
                ORDER BY s.name ASC",
                array_merge($studentIds, [$schoolId])
            );
        } else {
            // Marks link through examination_subjects
            $subjects = $this->safeFetchAll(
                "SELECT DISTINCT s.id, s.name, s.code
                FROM subjects s
                INNER JOIN examination_subjects es ON s.id = es.subject_id
                INNER JOIN marks m ON es.id = m.examination_subject_id
                WHERE m.student_id IN ({$sPlaceholders})
                AND s.school_id = ?
                ORDER BY s.name ASC",
                array_merge($studentIds, [$schoolId])
            );
        }

        if (empty($subjects)) {
            return ['students' => $students, 'subjects' => [], 'marks' => []];
        }

        // ------------------------------------------------------------
        // STEP 4: Get marks matrix
        // ------------------------------------------------------------
        $marks = [];
        $subjectIds = array_column($subjects, 'id');
        $subPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));

        try {
            if ($markSubjectColumn === 'subject_id') {
                $markRows = $this->db->fetchAll(
                    "SELECT m.student_id, m.subject_id, m.marks_obtained
                    FROM marks m
                    WHERE m.student_id IN ({$sPlaceholders})
                    AND m.subject_id IN ({$subPlaceholders})",
                    array_merge($studentIds, $subjectIds)
                );
            } else {
                $markRows = $this->db->fetchAll(
                    "SELECT m.student_id, es.subject_id, m.marks_obtained
                    FROM marks m
                    INNER JOIN examination_subjects es ON m.examination_subject_id = es.id
                    WHERE m.student_id IN ({$sPlaceholders})
                    AND es.subject_id IN ({$subPlaceholders})",
                    array_merge($studentIds, $subjectIds)
                );
            }

            foreach ($markRows as $row) {
                $marks[$row['student_id']][$row['subject_id']] = $row['marks_obtained'];
            }
        } catch (\Exception $e) {
            $marks = [];
        }

        return [
            'students' => $students,
            'subjects' => $subjects,
            'marks' => $marks,
        ];
    }

    public function getStudentReportCard(int $studentId, int $academicYearId, int $termId, int $schoolId): array
    {
        // ============================================================
        // STEP 1: Get student basic info
        // ============================================================
        $student = $this->safeFetch(
            "SELECT s.* FROM students s WHERE s.id = :id AND s.school_id = :school_id",
            ['id' => $studentId, 'school_id' => $schoolId]
        );

        if (!$student) {
            return [];
        }

        // ============================================================
        // STEP 2: Find enrollment (year-specific first, then fallback)
        // ============================================================
        $enrollment = $this->safeFetch(
            "SELECT se.class_id, se.stream_id, se.academic_year_id
            FROM student_enrollments se
            WHERE se.student_id = :student_id
            AND se.academic_year_id = :year_id
            ORDER BY se.id DESC
            LIMIT 1",
            ['student_id' => $studentId, 'year_id' => $academicYearId]
        );

        if (!$enrollment) {
            $enrollment = $this->safeFetch(
                "SELECT se.class_id, se.stream_id, se.academic_year_id
                FROM student_enrollments se
                WHERE se.student_id = :student_id
                ORDER BY se.id DESC
                LIMIT 1",
                ['student_id' => $studentId]
            );
        }

        $student['class_id']  = $enrollment['class_id']  ?? null;
        $student['stream_id'] = $enrollment['stream_id'] ?? null;

        // ============================================================
        // STEP 3: Resolve class name separately
        // ============================================================
        $student['class_name'] = null;
        if (!empty($student['class_id'])) {
            $classRow = $this->safeFetch(
                "SELECT name FROM classes WHERE id = :id",
                ['id' => $student['class_id']]
            );
            $student['class_name'] = $classRow['name'] ?? null;
        }

        // ============================================================
        // STEP 4: Resolve stream name separately
        // ============================================================
        $student['stream_name'] = null;
        if (!empty($student['stream_id'])) {
            $streamRow = $this->safeFetch(
                "SELECT name FROM streams WHERE id = :id",
                ['id' => $student['stream_id']]
            );
            $student['stream_name'] = $streamRow['name'] ?? null;
        }

        // ============================================================
        // STEP 5: If no stream in enrollment, find ANY enrollment with a stream
        // ============================================================
        if (empty($student['stream_id'])) {
            $anyStream = $this->safeFetch(
                "SELECT se.stream_id, str.name as stream_name
                FROM student_enrollments se
                INNER JOIN streams str ON se.stream_id = str.id
                WHERE se.student_id = :student_id
                AND se.stream_id IS NOT NULL
                ORDER BY se.id DESC
                LIMIT 1",
                ['student_id' => $studentId]
            );
            if ($anyStream) {
                $student['stream_id']   = $anyStream['stream_id'];
                $student['stream_name'] = $anyStream['stream_name'];
            }
        }

        $classId = (int)($student['class_id'] ?? 0);

        // ============================================================
        // STEP 6: Load the correct grading system for THIS CLASS
        // ============================================================
        $gradingSystem = null;
        if ($classId > 0) {
            $gradingSystem = $this->gradingService->getSystemForClass(
                $classId,
                $schoolId,
                $academicYearId
            );
        } else {
            $gradingSystem = $this->gradingService->getDefaultSystem($schoolId);
        }

        // ============================================================
        // STEP 7: Detect available mark columns
        // ============================================================
        $yearCol = $this->getMarksYearColumn();
        $hasTermCol = false;
        $hasSubjectCol = false;
        try {
            $cols = $this->db->fetchAll("SHOW COLUMNS FROM marks");
            foreach ($cols as $c) {
                if ($c['Field'] === 'term_id') $hasTermCol = true;
                if ($c['Field'] === 'subject_id') $hasSubjectCol = true;
            }
        } catch (\Exception $e) {}

        // ============================================================
        // STEP 8: Build the marks query
        // ============================================================
        $where = ["m.student_id = :student_id", "m.school_id = :school_id"];
        $params = [
            'student_id' => $studentId,
            'school_id' => $schoolId,
        ];

        if ($yearCol) {
            $where[] = "m.{$yearCol} = :academic_year_id";
            $params['academic_year_id'] = $academicYearId;
        }
        if ($hasTermCol) {
            $where[] = "m.term_id = :term_id";
            $params['term_id'] = $termId;
        }

        $whereClause = implode(' AND ', $where);

        // ============================================================
        // STEP 9: Fetch the marks
        // ============================================================
        $marks = [];
        if ($hasSubjectCol) {
            $marks = $this->safeFetchAll(
                "SELECT m.*, s.name as subject_name, s.code as subject_code
                FROM marks m
                LEFT JOIN subjects s ON m.subject_id = s.id
                WHERE {$whereClause}
                ORDER BY s.name ASC",
                $params
            );
        } else {
            $marks = $this->safeFetchAll(
                "SELECT m.* FROM marks m WHERE {$whereClause}",
                $params
            );
        }

        if (!is_array($marks)) {
            $marks = [];
        }

        // ============================================================
        // STEP 10: Grade each mark using the CLASS-SPECIFIC grading system
        // ============================================================
        $total = 0;
        $count = 0;

        foreach ($marks as &$mark) {
            $marksObtained = (float)($mark['marks_obtained'] ?? 0);

            if ($gradingSystem) {
                $grade = $this->gradingService->getGradeFromSystem(
                    $marksObtained,
                    (int)$gradingSystem['id']
                );
                $mark['grade'] = $grade['grade'] ?? '-';
                $mark['grade_points'] = $grade['points'] ?? 0;
                $mark['remark'] = $grade['description'] ?? '-';
            } else {
                $mark['grade'] = '-';
                $mark['grade_points'] = 0;
                $mark['remark'] = '-';
            }

            if (empty($mark['subject_name'])) {
                $mark['subject_name'] = '-';
            }

            $total += $marksObtained;
            $count++;
        }
        unset($mark);

        $average = $count > 0 ? round($total / $count, 2) : 0;

        return [
            'student' => $student,
            'marks' => $marks,
            'total' => $total,
            'average' => $average,
            'grading_system' => $gradingSystem,
        ];
    }

    public function getMultiExamReportCard(
        int $studentId,
        int $academicYearId,
        int $termId,
        int $schoolId,
        array $examinationIds = [],
        array $options = []
    ): array {
        // ============================================================
        // STEP 1: Load student
        // ============================================================
        $student = $this->safeFetch(
            "SELECT s.* FROM students s WHERE s.id = :id AND s.school_id = :school_id",
            ['id' => $studentId, 'school_id' => $schoolId]
        );
        if (!$student) return [];

        // ============================================================
        // STEP 2: Enrollment — pick the one for the requested year
        // ============================================================
        $enrollment = $this->safeFetch(
            "SELECT se.class_id, se.stream_id
            FROM student_enrollments se
            WHERE se.student_id = :student_id
            AND se.academic_year_id = :year_id
            ORDER BY se.id DESC LIMIT 1",
            ['student_id' => $studentId, 'year_id' => $academicYearId]
        );
        $student['class_id']  = $enrollment['class_id']  ?? null;
        $student['stream_id'] = $enrollment['stream_id'] ?? null;

        // ============================================================
        // STEP 3: Class / Stream names
        // ============================================================
        $student['class_name'] = null;
        if (!empty($student['class_id'])) {
            $r = $this->safeFetch("SELECT name FROM classes WHERE id = :id", ['id' => $student['class_id']]);
            $student['class_name'] = $r['name'] ?? null;
        }
        $student['stream_name'] = null;
        if (!empty($student['stream_id'])) {
            $r = $this->safeFetch("SELECT name FROM streams WHERE id = :id", ['id' => $student['stream_id']]);
            $student['stream_name'] = $r['name'] ?? null;
        }

        // ============================================================
        // STEP 4: Load grading system for this class
        // ============================================================
        $gradingSystem = null;
        if (!empty($student['class_id'])) {
            $gradingSystem = $this->gradingService->getSystemForClass(
                (int)$student['class_id'], $schoolId, $academicYearId
            );
        } else {
            $gradingSystem = $this->gradingService->getDefaultSystem($schoolId);
        }

        // ============================================================
        // STEP 5: Load the exams to include
        // ============================================================
        $exams = [];
        if (!empty($examinationIds)) {
            $placeholders = implode(',', array_fill(0, count($examinationIds), '?'));
            $exams = $this->safeFetchAll(
                "SELECT * FROM examinations 
                WHERE id IN ({$placeholders})
                ORDER BY id ASC",
                $examinationIds
            );
        }

        // ============================================================
        // STEP 6: Fetch marks for the student for all selected exams
        // ============================================================
        $marksByExam   = [];  // [exam_id][subject_id] => mark row
        $subjectsSeen  = [];  // [subject_id] => subject info

        if (!empty($exams)) {
            $examIds = array_column($exams, 'id');
            $placeholders = implode(',', array_fill(0, count($examIds), '?'));

            $rows = $this->safeFetchAll(
                "SELECT m.*, sub.name AS subject_name, sub.code AS subject_code
                FROM marks m
                LEFT JOIN subjects sub ON m.subject_id = sub.id
                WHERE m.student_id = ?
                AND m.academic_year_id = ?
                AND m.term_id = ?
                AND m.examination_id IN ({$placeholders})
                ORDER BY sub.name ASC",
                array_merge([$studentId, $academicYearId, $termId], $examIds)
            );

            foreach ($rows as $row) {
                $marksByExam[$row['examination_id']][$row['subject_id']] = $row;
                $subjectsSeen[$row['subject_id']] = [
                    'id'   => $row['subject_id'],
                    'name' => $row['subject_name'],
                    'code' => $row['subject_code'],
                ];
            }
        }

        // ============================================================
        // STEP 7: Grade every mark (per-exam)
        // ============================================================
        foreach ($marksByExam as $examId => &$subjectMarks) {
            foreach ($subjectMarks as $subjectId => &$mark) {
                $markVal = (float)($mark['marks_obtained'] ?? 0);
                if ($gradingSystem && !empty($gradingSystem['id'])) {
                    $grade = $this->gradingService->getGradeFromSystem($markVal, (int)$gradingSystem['id']);
                    $mark['grade']        = $grade['grade'] ?? '-';
                    $mark['grade_points'] = $grade['points'] ?? 0;
                    $mark['remark']       = $grade['description'] ?? '-';
                } else {
                    $mark['grade']        = '-';
                    $mark['grade_points'] = 0;
                    $mark['remark']       = '-';
                }
            }
            unset($mark);
        }
        unset($subjectMarks);

        // ============================================================
        // STEP 8: Compute per-exam totals
        // ============================================================
        $examTotals = [];
        foreach ($exams as $exam) {
            $examId = $exam['id'];
            $total = 0; $count = 0;

            foreach ($marksByExam[$examId] ?? [] as $mark) {
                $total += (float)$mark['marks_obtained'];
                $count++;
            }

            $avg = $count > 0 ? round($total / $count, 2) : 0;

            $overallGrade = null;
            if ($gradingSystem && $count > 0 && !empty($gradingSystem['id'])) {
                $overallGrade = $this->gradingService->getGradeFromSystem($avg, (int)$gradingSystem['id']);
            }

            $examTotals[$examId] = [
                'total'   => $total,
                'average' => $avg,
                'count'   => $count,
                'grade'   => $overallGrade['grade'] ?? '-',
                'points'  => $overallGrade['points'] ?? 0,
            ];
        }

        // ============================================================
        // STEP 9: Compute per-subject AVERAGES and GRADES
        //         (this is what the views read)
        // ============================================================
        $subjectAverages = []; // [subject_id] => ['mark'=>, 'grade'=>, 'points'=>]

        foreach ($subjectsSeen as $subj) {
            $sid  = $subj['id'];
            $vals = [];

            foreach ($exams as $exam) {
                $row = $marksByExam[$exam['id']][$sid] ?? null;
                if ($row) {
                    $vals[] = (float)$row['marks_obtained'];
                }
            }

            $avg       = count($vals) ? round(array_sum($vals) / count($vals), 1) : 0;
            $avgGrade  = '-';
            $avgPoints = 0;

            if ($avg > 0 && $gradingSystem && !empty($gradingSystem['id'])) {
                $g = $this->gradingService->getGradeFromSystem($avg, (int)$gradingSystem['id']);
                if (is_array($g) && !empty($g['grade'])) {
                    $avgGrade  = $g['grade'];
                    $avgPoints = $g['points'] ?? 0;
                }
            }

            $subjectAverages[$sid] = [
                'mark'   => $avg,
                'grade'  => $avgGrade,
                'points' => $avgPoints,
            ];
        }

        // ============================================================
        // STEP 10: Sort subjects by name
        // ============================================================
        usort($subjectsSeen, fn($a, $b) => strcmp($a['name'], $b['name']));

        // ============================================================
        // STEP 11: Return everything
        // ============================================================
        return [
            'student'          => $student,
            'exams'            => $exams,
            'subjects'         => $subjectsSeen,
            'marks_by_exam'    => $marksByExam,
            'exam_totals'      => $examTotals,
            'subject_averages' => $subjectAverages,   // ← NOW DEFINED
            'grading_system'   => $gradingSystem,
            'options'          => $options,
        ];
    }
}