<?php
// File: /app/Services/AcademicReportService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use NexaT\Models\Subject;
use Throwable;

class AcademicReportService
{
    private Database $db;
    private GradingService $gradingService;
    private ResultCalculationService $resultService;
    private array $marksColumnsCache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gradingService = new GradingService();
        $this->resultService = new ResultCalculationService();
    }

    public function getReportFilters(int $schoolId): array
    {
        return [
            'academic_years' => $this->safeFetchAll(
                "SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC",
                ['s' => $schoolId]
            ),
            'terms' => $this->safeFetchAll(
                "SELECT id, name, academic_year_id FROM terms WHERE school_id = :s ORDER BY term_number ASC",
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
                "SELECT id, name, code, type FROM subjects WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'examinations' => $this->safeFetchAll(
                "SELECT id, name FROM {$this->examinationTable()} WHERE school_id = :s ORDER BY id DESC",
                ['s' => $schoolId]
            ),
        ];
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
            "SELECT COUNT(DISTINCT s.id) AS total
             FROM students s
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE {$whereClause}",
            $params
        );

        $assessed = $this->safeFetch(
            "SELECT COUNT(DISTINCT m.student_id) AS total
             FROM marks m
             INNER JOIN students s ON m.student_id = s.id
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE {$whereClause}",
            $params
        );

        $avg = $this->safeFetch(
            "SELECT AVG(m.marks_obtained) AS average,
                    MAX(m.marks_obtained) AS highest,
                    MIN(m.marks_obtained) AS lowest
             FROM marks m
             INNER JOIN students s ON m.student_id = s.id
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE {$whereClause}",
            $params
        );

        return [
            'total_students'     => (int)($total['total'] ?? 0),
            'assessed_students'  => (int)($assessed['total'] ?? 0),
            'missing_marks'      => max(0, (int)($total['total'] ?? 0) - (int)($assessed['total'] ?? 0)),
            'overall_average'    => round((float)($avg['average'] ?? 0), 2),
            'highest_mark'       => round((float)($avg['highest'] ?? 0), 2),
            'lowest_mark'        => round((float)($avg['lowest'] ?? 0), 2),
            'grade_distribution' => $this->getGradeDistribution($schoolId, $filters),
        ];
    }

    public function getGradeDistribution(int $schoolId, array $filters = []): array
    {
        $system = $this->safeFetch(
            "SELECT * FROM grading_systems WHERE school_id = :s AND is_default = 1 LIMIT 1",
            ['s' => $schoolId]
        ) ?: $this->safeFetch(
            "SELECT * FROM grading_systems WHERE school_id = :s LIMIT 1",
            ['s' => $schoolId]
        );

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

        $where = ["m.school_id = :school_id"];
        $params = ['school_id' => $schoolId];

        $yearCol = $this->marksColumn('academic_year_id');
        if ($yearCol && !empty($filters['academic_year_id'])) {
            $where[] = "m.{$yearCol} = :academic_year_id";
            $params['academic_year_id'] = (int)$filters['academic_year_id'];
        }

        $whereClause = implode(' AND ', $where);
        $distribution = [];

        foreach ($rules as $rule) {
            $count = $this->safeFetch(
                "SELECT COUNT(*) AS c FROM marks m
                 WHERE {$whereClause}
                   AND m.marks_obtained >= :min
                   AND m.marks_obtained <= :max",
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

        $hasSubjectIdData = false;
        try {
            $test = $this->db->fetch(
                "SELECT COUNT(*) AS c FROM marks
                 WHERE subject_id IS NOT NULL AND school_id = :s",
                ['s' => $schoolId]
            );
            $hasSubjectIdData = $test && (int)$test['c'] > 0;
        } catch (Throwable $e) {}

        $params = ['school_id' => $schoolId, 'subject_id' => $subjectId];

        if ($hasSubjectIdData) {
            $where = ["m.school_id = :school_id", "m.subject_id = :subject_id"];
            $join = '';
        } else {
            $where = ["m.school_id = :school_id", "es.subject_id = :subject_id"];
            $join = "INNER JOIN examination_subjects es ON m.examination_subject_id = es.id";
        }

        if (!empty($filters['academic_year_id']) && $this->marksColumn('academic_year_id')) {
            $where[] = "m.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = (int)$filters['academic_year_id'];
        }

        $whereClause = implode(' AND ', $where);

        $stats = $this->safeFetch(
            "SELECT COUNT(*) AS total_marks,
                    AVG(m.marks_obtained) AS avg_mark,
                    MAX(m.marks_obtained) AS highest,
                    MIN(m.marks_obtained) AS lowest
             FROM marks m
             {$join}
             WHERE {$whereClause}",
            $params
        );

        $studentCount = $this->safeFetch(
            "SELECT COUNT(DISTINCT s.id) AS c
             FROM students s
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE s.school_id = :school_id AND se.status = 'active'",
            ['school_id' => $schoolId]
        );

        return [
            'total_students' => (int)($studentCount['c'] ?? 0),
            'assessed'       => (int)($stats['total_marks'] ?? 0),
            'average'        => round((float)($stats['avg_mark'] ?? 0), 2),
            'highest'        => round((float)($stats['highest'] ?? 0), 2),
            'lowest'         => round((float)($stats['lowest'] ?? 0), 2),
            'pass_rate'      => 0,
        ];
    }

    public function getClassResultsMatrix(int $schoolId, array $filters): array
    {
        if (empty($filters['academic_year_id'])) {
            return ['students' => [], 'subjects' => [], 'marks' => []];
        }

        $params = [
            'school_id'        => $schoolId,
            'academic_year_id' => (int)$filters['academic_year_id'],
        ];

        $studentWhere = [
            "se.status = 'active'",
            "se.academic_year_id = :academic_year_id",
            "s.school_id = :school_id",
        ];

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
                    cl.name AS class_name, str.name AS stream_name
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

        $markSubjectColumn = $this->detectMarksSubjectColumn($studentIds);
        if ($markSubjectColumn === null) {
            return ['students' => $students, 'subjects' => [], 'marks' => []];
        }

        if ($markSubjectColumn === 'subject_id') {
            $subjects = $this->safeFetchAll(
                "SELECT DISTINCT s.id, s.name, s.code, s.type
                 FROM subjects s
                 INNER JOIN marks m ON s.id = m.subject_id
                 WHERE m.student_id IN ({$sPlaceholders}) AND s.school_id = ?
                 ORDER BY s.name ASC",
                array_merge($studentIds, [$schoolId])
            );
        } else {
            $subjects = $this->safeFetchAll(
                "SELECT DISTINCT s.id, s.name, s.code, s.type
                 FROM subjects s
                 INNER JOIN examination_subjects es ON s.id = es.subject_id
                 INNER JOIN marks m ON es.id = m.examination_subject_id
                 WHERE m.student_id IN ({$sPlaceholders}) AND s.school_id = ?
                 ORDER BY s.name ASC",
                array_merge($studentIds, [$schoolId])
            );
        }

        if (empty($subjects)) {
            return ['students' => $students, 'subjects' => [], 'marks' => []];
        }

        $subjectIds = array_column($subjects, 'id');
        $subPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));

        $marks = [];

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
        } catch (Throwable $e) {
            $marks = [];
        }

        return [
            'students' => $students,
            'subjects' => $subjects,
            'marks'    => $marks,
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
        $student = $this->safeFetch(
            "SELECT s.* FROM students s WHERE s.id = :id AND s.school_id = :school_id",
            ['id' => $studentId, 'school_id' => $schoolId]
        );

        if (!$student) {
            return [];
        }

        $enrollment = $this->safeFetch(
            "SELECT se.class_id, se.stream_id
             FROM student_enrollments se
             WHERE se.student_id = :student_id AND se.academic_year_id = :year_id
             ORDER BY se.id DESC LIMIT 1",
            ['student_id' => $studentId, 'year_id' => $academicYearId]
        );

        $student['class_id']  = $enrollment['class_id']  ?? null;
        $student['stream_id'] = $enrollment['stream_id'] ?? null;
        $student['class_name']  = $this->lookupName('classes', $student['class_id']);
        $student['stream_name'] = $this->lookupName('streams', $student['stream_id']);

        $gradingSystem = !empty($student['class_id'])
            ? $this->gradingService->getSystemForClass((int)$student['class_id'], $schoolId, $academicYearId)
            : $this->gradingService->getDefaultSystem($schoolId);

        if ($gradingSystem && empty($gradingSystem['rules'])) {
            try {
                $gradingSystem['rules'] = $this->gradingService->getRules((int)$gradingSystem['id']);
            } catch (Throwable $e) {}
        }

        $exams = [];
        if (!empty($examinationIds)) {
            $ph = implode(',', array_fill(0, count($examinationIds), '?'));
            $exams = $this->safeFetchAll(
                "SELECT * FROM examinations WHERE id IN ({$ph}) ORDER BY id ASC",
                $examinationIds
            );
        }

        [$marksByExam, $subjectsSeen] = $this->loadMarksByExam($studentId, $academicYearId, $termId, $exams, $gradingSystem);

        $examTotals      = $this->computeExamTotals($exams, $marksByExam, $gradingSystem);
        $teacherInitials = $this->loadTeacherInitials($schoolId, $student, $subjectsSeen);

        [$subjectAverages, $sourceExamIds, $sourceLabel] = $this->computeSubjectAverages(
            $subjectsSeen, $marksByExam, $gradingSystem, $exams, $options['final_grade_method'] ?? 'average'
        );

        foreach ($teacherInitials as $sid => $initials) {
            if (isset($subjectAverages[$sid])) {
                $subjectAverages[$sid]['initials'] = $initials;
            }
        }

        $bestSubjectsCount = (int)($options['best_subjects_count'] ?? 0);

        $contributingIds = [];
        $otherIds        = [];

        foreach ($subjectAverages as $sid => $sa) {
            if (empty($sa['contributes'])) {
                $otherIds[] = (int)$sid;
            } else {
                $contributingIds[(int)$sid] = (float)$sa['score'];
            }
        }

        if ($bestSubjectsCount > 0 && count($contributingIds) > $bestSubjectsCount) {
            arsort($contributingIds);
            $bestSubjects = array_slice(array_keys($contributingIds), 0, $bestSubjectsCount);

            $contributingIds = array_fill_keys($bestSubjects, true);
            foreach ($subjectAverages as $sid => $sa) {
                if (!empty($sa['contributes']) && !isset($contributingIds[$sid])) {
                    $subjectAverages[$sid]['contributes'] = false;
                    $otherIds[] = (int)$sid;
                }
            }
            $contributingIds = array_keys($contributingIds);
        } else {
            $contributingIds = array_keys($contributingIds);
        }

        $totalScore     = 0;
        $totalAggregate = 0;
        foreach ($contributingIds as $sid) {
            if (isset($subjectAverages[$sid])) {
                $totalScore     += (float)$subjectAverages[$sid]['mark'];
                $totalAggregate += (float)$subjectAverages[$sid]['score'];
            }
        }
        $gradedCount = count($contributingIds);

        $positionRanking = $options['position_ranking'] ?? 'aggregate';
        [$position, $classSize] = $this->computePosition(
            $studentId, $student, $schoolId, $academicYearId, $termId,
            array_column($exams, 'id'), $gradingSystem, $positionRanking,
            $otherIds
        );

        $divisionCode = null;
        try {
            $division = (new DivisionService())->getDivisionForAggregate($totalAggregate, $schoolId);
            if ($division) {
                $divisionCode = preg_replace('/^D/i', '', $division['code']);
            }
        } catch (Throwable $e) {}

        $ctRemark = '';
        if (($options['ct_comment'] ?? 'auto') !== 'no' && $gradedCount > 0) {
            $ctRemark = $this->remarkFromAverage($totalScore / $gradedCount);
        }

        $nextTerm = $this->safeFetch(
            "SELECT name, start_date, end_date FROM terms
             WHERE school_id = :s AND start_date > CURDATE()
             ORDER BY start_date ASC LIMIT 1",
            ['s' => $schoolId]
        );

        return [
            'student'             => $student,
            'exams'               => $exams,
            'subjects'            => $subjectsSeen,
            'marks_by_exam'       => $marksByExam,
            'exam_totals'         => $examTotals,
            'subject_averages'    => $subjectAverages,
            'grading_system'      => $gradingSystem,
            'options'             => $options,
            'total_score'         => $totalScore,
            'total_aggregate'     => $totalAggregate,
            'graded_subjects'     => $gradedCount,
            'non_graded_count'    => count($otherIds),
            'best_subjects'       => $contributingIds,
            'best_subjects_count' => $gradedCount,
            'other_subjects'      => $otherIds,
            'division_code'       => $divisionCode,
            'position'            => $position,
            'class_size'          => $classSize,
            'ct_remark'           => $ctRemark,
            'next_term'           => $nextTerm,
            'final_grade_method'  => $options['final_grade_method'] ?? 'average',
            'final_source_label'  => $sourceLabel,
            'source_exam_ids'     => $sourceExamIds,
            'position_ranking'    => $positionRanking,
        ];
    }

    private function loadMarksByExam(int $studentId, int $yearId, int $termId, array $exams, ?array $gradingSystem): array
    {
        if (empty($exams)) {
            return [[], []];
        }

        $examIds = array_column($exams, 'id');
        $ph = implode(',', array_fill(0, count($examIds), '?'));

        $rows = $this->safeFetchAll(
            "SELECT m.*, sub.name AS subject_name, sub.code AS subject_code, sub.type AS subject_type
             FROM marks m
             LEFT JOIN subjects sub ON m.subject_id = sub.id
             WHERE m.student_id = ? AND m.academic_year_id = ? AND m.term_id = ?
               AND m.examination_id IN ({$ph})
             ORDER BY sub.name ASC",
            array_merge([$studentId, $yearId, $termId], $examIds)
        );

        $marksByExam  = [];
        $subjectsSeen = [];

        foreach ($rows as $row) {
            $markVal  = (float)($row['marks_obtained'] ?? 0);
            $rawType  = $row['subject_type'] ?? null;
            $type     = $rawType !== null && $rawType !== '' ? strtolower((string)$rawType) : 'core';
            $isOther  = in_array($type, Subject::NON_GRADED_TYPES, true);

            if ($gradingSystem && !empty($gradingSystem['id'])) {
                $grade = $this->gradingService->getGradeFromSystem($markVal, (int)$gradingSystem['id']);
                $row['grade']  = $grade['grade'] ?? '-';
                $row['score']  = $grade['score'] ?? 0;
                $row['remark'] = $grade['description'] ?? '-';
            } else {
                $row['grade']  = '-';
                $row['score']  = 0;
                $row['remark'] = '-';
            }

            $row['is_other'] = $isOther;

            $marksByExam[$row['examination_id']][$row['subject_id']] = $row;
            $subjectsSeen[$row['subject_id']] = [
                'id'       => (int)$row['subject_id'],
                'name'     => $row['subject_name'],
                'code'     => $row['subject_code'],
                'type'     => $type,
                'is_other' => $isOther,
            ];
        }

        return [$marksByExam, $subjectsSeen];
    }

    private function computeExamTotals(array $exams, array $marksByExam, ?array $gradingSystem): array
    {
        $totals = [];

        foreach ($exams as $exam) {
            $examId = $exam['id'];
            $total = 0;
            $count = 0;
            $totalScore = 0;

            foreach ($marksByExam[$examId] ?? [] as $mark) {
                if (!empty($mark['is_other'])) {
                    continue;
                }
                $total += (float)$mark['marks_obtained'];
                $totalScore += (float)($mark['score'] ?? 0);
                $count++;
            }

            $avg = $count > 0 ? round($total / $count, 0) : 0;
            $grade = ($gradingSystem && $count > 0 && !empty($gradingSystem['id']))
                ? $this->gradingService->getGradeFromSystem($avg, (int)$gradingSystem['id'])
                : null;

            $totals[$examId] = [
                'total'   => $total,
                'average' => $avg,
                'count'   => $count,
                'score'   => $totalScore,
                'grade'   => $grade['grade'] ?? '-',
            ];
        }

        return $totals;
    }

    private function loadTeacherInitials(int $schoolId, array $student, array $subjectsSeen): array
    {
        $initials = [];

        if (empty($student['class_id'])) {
            return $initials;
        }

        foreach ($subjectsSeen as $subj) {
            $sid = (int)$subj['id'];
            $teacher = null;

            if (!empty($student['stream_id'])) {
                $teacher = $this->safeFetch(
                    "SELECT st.first_name, st.last_name
                     FROM teacher_assignments ta
                     INNER JOIN staff st ON ta.staff_id = st.id
                     WHERE ta.school_id = :school_id
                       AND ta.subject_id = :subject_id
                       AND ta.class_id = :class_id
                       AND ta.stream_id = :stream_id
                       AND ta.status = 'active'
                     LIMIT 1",
                    [
                        'school_id'  => $schoolId,
                        'subject_id' => $sid,
                        'class_id'   => $student['class_id'],
                        'stream_id'  => $student['stream_id'],
                    ]
                );
            }

            if (!$teacher) {
                $teacher = $this->safeFetch(
                    "SELECT st.first_name, st.last_name
                     FROM teacher_assignments ta
                     INNER JOIN staff st ON ta.staff_id = st.id
                     WHERE ta.school_id = :school_id
                       AND ta.subject_id = :subject_id
                       AND ta.class_id = :class_id
                       AND ta.stream_id IS NULL
                       AND ta.status = 'active'
                     LIMIT 1",
                    [
                        'school_id'  => $schoolId,
                        'subject_id' => $sid,
                        'class_id'   => $student['class_id'],
                    ]
                );
            }

            if (!$teacher) {
                $teacher = $this->safeFetch(
                    "SELECT st.first_name, st.last_name
                     FROM teacher_assignments ta
                     INNER JOIN staff st ON ta.staff_id = st.id
                     WHERE ta.school_id = :school_id
                       AND ta.subject_id = :subject_id
                       AND ta.class_id = :class_id
                       AND ta.status = 'active'
                     ORDER BY ta.stream_id DESC LIMIT 1",
                    [
                        'school_id'  => $schoolId,
                        'subject_id' => $sid,
                        'class_id'   => $student['class_id'],
                    ]
                );
            }

            if ($teacher) {
                $f = strtoupper(substr(trim($teacher['first_name']), 0, 1));
                $l = strtoupper(substr(trim($teacher['last_name']),  0, 1));
                $initials[$sid] = $f . '.' . $l;
            } else {
                $initials[$sid] = '';
            }
        }

        return $initials;
    }

    private function computeSubjectAverages(
        array $subjectsSeen,
        array $marksByExam,
        ?array $gradingSystem,
        array $exams,
        string $finalGradeMethod
    ): array {
        $allExamIds = array_column($exams, 'id');

        $studentExamAverages = [];
        foreach ($marksByExam as $exId => $subjectMarks) {
            $sum = 0;
            $count = 0;
            foreach ($subjectMarks as $m) {
                if (!empty($m['is_other'])) {
                    continue;
                }
                $sum += (float)$m['marks_obtained'];
                $count++;
            }
            if ($count > 0) {
                $studentExamAverages[$exId] = $sum / $count;
            }
        }

        $sourceExamIds = $allExamIds;
        $sourceLabel = 'average';

        if ($finalGradeMethod === 'best_set' && !empty($studentExamAverages)) {
            arsort($studentExamAverages);
            $sourceExamIds = [array_key_first($studentExamAverages)];
            $sourceLabel = 'best_set';
        } elseif ($finalGradeMethod === 'worst_set' && !empty($studentExamAverages)) {
            asort($studentExamAverages);
            $sourceExamIds = [array_key_first($studentExamAverages)];
            $sourceLabel = 'worst_set';
        } elseif (strpos($finalGradeMethod, 'exam:') === 0) {
            $sourceExamIds = [(int)substr($finalGradeMethod, 5)];
            $sourceLabel = 'exam';
        }

        $subjectAverages = [];

        foreach ($subjectsSeen as $subj) {
            $sid  = (int)$subj['id'];
            $type = strtolower((string)($subj['type'] ?? 'core'));
            $isOther = in_array($type, Subject::NON_GRADED_TYPES, true);

            $vals = [];
            foreach ($sourceExamIds as $srcId) {
                $row = $marksByExam[$srcId][$sid] ?? null;
                if ($row) {
                    $vals[] = (float)$row['marks_obtained'];
                }
            }

            $finalMark = !empty($vals) ? round(array_sum($vals) / count($vals), 0) : 0;

            $finalScore  = 0;
            $finalRemark = '';
            $finalGrade  = '-';

            if ($finalMark > 0 && $gradingSystem && !empty($gradingSystem['id'])) {
                $g = $this->gradingService->getGradeFromSystem($finalMark, (int)$gradingSystem['id']);
                if (is_array($g) && !empty($g['grade'])) {
                    $finalGrade  = $g['grade'];
                    $finalScore  = $g['score'] ?? 0;
                    $finalRemark = $g['description'] ?? '';
                }
            }

            $subjectAverages[$sid] = [
                'mark'       => $finalMark,
                'grade'      => $finalGrade,
                'score'      => $finalScore,
                'remark'     => $finalRemark,
                'type'       => $type,
                'is_other'   => $isOther,
                'contributes'=> !$isOther,
            ];
        }

        usort($subjectsSeen, fn($a, $b) => strcmp($a['name'], $b['name']));

        return [$subjectAverages, $sourceExamIds, $sourceLabel];
    }

    private function computePosition(
        int $studentId,
        array $student,
        int $schoolId,
        int $academicYearId,
        int $termId,
        array $allExamIds,
        ?array $gradingSystem,
        string $positionRanking,
        array $excludedSubjectIds = []
    ): array {
        if (empty($allExamIds) || empty($student['class_id'])) {
            return [null, 0];
        }

        $classMates = $this->safeFetchAll(
            "SELECT DISTINCT s.id FROM students s
             INNER JOIN student_enrollments se ON s.id = se.student_id
             WHERE se.status = 'active'
               AND se.academic_year_id = :year_id
               AND se.class_id = :class_id
               AND s.school_id = :school_id",
            [
                'year_id'   => $academicYearId,
                'class_id'  => $student['class_id'],
                'school_id' => $schoolId,
            ]
        );

        $classSize = count($classMates);
        if ($classSize === 0) {
            return [null, 0];
        }

        $ids = array_column($classMates, 'id');
        $ph = implode(',', array_fill(0, count($allExamIds), '?'));

        $excludeClause = '';
        $excludeParams = [];
        if (!empty($excludedSubjectIds)) {
            $ep = implode(',', array_fill(0, count($excludedSubjectIds), '?'));
            $excludeClause = " AND m.subject_id NOT IN ({$ep})";
            $excludeParams = array_values($excludedSubjectIds);
        }

        $rankings = [];

        foreach ($ids as $cid) {
            $cmMarks = $this->safeFetchAll(
                "SELECT m.marks_obtained FROM marks m
                 WHERE m.student_id = ? AND m.academic_year_id = ? AND m.term_id = ?
                   AND m.examination_id IN ({$ph})
                   {$excludeClause}",
                array_merge([$cid, $academicYearId, $termId], $allExamIds, $excludeParams)
            );

            $sumMarks = 0;
            $countMark = 0;
            $sumScores = 0;

            foreach ($cmMarks as $m) {
                $sumMarks += (float)$m['marks_obtained'];
                $countMark++;

                if ($gradingSystem && !empty($gradingSystem['id'])) {
                    $g = $this->gradingService->getGradeFromSystem((float)$m['marks_obtained'], (int)$gradingSystem['id']);
                    $sumScores += (float)($g['score'] ?? 0);
                }
            }

            $rankings[$cid] = [
                'aggregate' => $sumScores,
                'total'     => $sumMarks,
                'average'   => $countMark > 0 ? $sumMarks / $countMark : 0,
            ];
        }

        uasort($rankings, function ($a, $b) use ($positionRanking) {
            $cmp = match ($positionRanking) {
                'total'   => $b['total'] <=> $a['total'],
                'average' => $b['average'] <=> $a['average'],
                default   => $a['aggregate'] <=> $b['aggregate'],
            };

            if ($cmp !== 0) return $cmp;
            if (($cmp = $b['total'] <=> $a['total']) !== 0) return $cmp;
            return $b['average'] <=> $a['average'];
        });

        $position = null;
        $rank = 1;
        $prevVals = null;

        foreach (array_keys($rankings) as $index => $cid) {
            $vals = $rankings[$cid];

            if ($prevVals === null) {
                $currentRank = 1;
            } else {
                $same = ((float)$vals['aggregate'] === (float)$prevVals['aggregate'])
                     && ((float)$vals['total']     === (float)$prevVals['total'])
                     && ((float)$vals['average']   === (float)$prevVals['average']);
                $currentRank = $same ? $rank : $index + 1;
            }

            $rank = $currentRank;

            if ((int)$cid === $studentId) {
                $position = $currentRank;
                break;
            }

            $prevVals = $vals;
        }

        return [$position, $classSize];
    }

    private function remarkFromAverage(float $average): string
    {
        if ($average >= 80) return 'Excellent performance. Keep it up!';
        if ($average >= 70) return 'Very good work. Aim higher.';
        if ($average >= 60) return 'Good effort. Put in more work.';
        if ($average >= 50) return 'Fair performance. Improve.';
        if ($average >= 40) return 'Below average. Needs attention.';
        return 'Needs serious improvement.';
    }

    private function examinationTable(): string
    {
        try {
            $this->db->fetch("SELECT 1 FROM examination_sets LIMIT 1");
            return 'examination_sets';
        } catch (Throwable $e) {
            return 'examinations';
        }
    }

    private function marksColumn(string $column): ?string
    {
        if (!isset($this->marksColumnsCache['columns'])) {
            $this->marksColumnsCache['columns'] = [];
            try {
                foreach ($this->db->fetchAll("SHOW COLUMNS FROM marks") as $c) {
                    $this->marksColumnsCache['columns'][$c['Field']] = true;
                }
            } catch (Throwable $e) {}
        }

        return isset($this->marksColumnsCache['columns'][$column]) ? $column : null;
    }

    private function detectMarksSubjectColumn(array $studentIds): ?string
    {
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        foreach (['subject_id', 'examination_subject_id'] as $col) {
            try {
                $test = $this->db->fetch(
                    "SELECT COUNT(*) AS c FROM marks
                     WHERE {$col} IS NOT NULL AND student_id IN ({$placeholders})",
                    $studentIds
                );
                if ($test && (int)$test['c'] > 0) {
                    return $col;
                }
            } catch (Throwable $e) {}
        }

        return null;
    }

    private function lookupName(string $table, ?int $id): ?string
    {
        if (empty($id)) {
            return null;
        }

        $row = $this->safeFetch("SELECT name FROM {$table} WHERE id = :id", ['id' => $id]);
        return $row['name'] ?? null;
    }

    private function safeFetchAll(string $sql, array $params = []): array
    {
        try {
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function safeFetch(string $sql, array $params = []): ?array
    {
        try {
            return $this->db->fetch($sql, $params) ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }
}