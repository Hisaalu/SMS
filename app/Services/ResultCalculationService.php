<?php
// File: /app/Services/ResultCalculationService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use Exception;

class ResultCalculationService
{
    private Database $db;
    private GradingService $gradingService;
    private array $config = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gradingService = new GradingService();
    }

    public function calculateResults(int $examinationSetId, int $schoolId): array
    {
        $examSet = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $examinationSetId, 'school_id' => $schoolId]
        );

        if (!$examSet) {
            throw new Exception('Examination not found');
        }

        $this->loadConfiguration($schoolId);

        $students = $this->getStudents($schoolId);
        $subjects = $this->getSubjects($examinationSetId);
        $marks    = $this->getMarks($examinationSetId, array_column($students, 'id'));

        $results = [];

        foreach ($students as $student) {
            $studentMarks = $this->getStudentMarks($student['id'], $marks);
            $result = $this->calculateStudentResult($student, $studentMarks, $subjects);
            $results[] = $result;

            $this->saveStudentResult($examSet, $student, $result);
        }

        $this->calculateRankings($examinationSetId, $results);

        return $results;
    }

    private function loadConfiguration(int $schoolId): void
    {
        $config = $this->db->fetch(
            "SELECT * FROM result_configurations
             WHERE school_id = :school_id AND status = 'active' LIMIT 1",
            ['school_id' => $schoolId]
        );

        $this->config = $config ?: [
            'school_id'               => $schoolId,
            'calculation_type'        => 'average',
            'best_subjects_count'     => 0,
            'use_grade_points'        => false,
            'calculate_rank'          => true,
            'rank_type'               => 'competition',
            'include_compulsory_only' => false,
        ];
    }

    private function getStudents(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT s.id, s.admission_number, s.first_name, s.last_name,
                    se.class_id, se.stream_id, cl.name AS class_name, st.name AS stream_name
             FROM students s
             INNER JOIN student_enrollments se ON s.id = se.student_id
             INNER JOIN classes cl ON se.class_id = cl.id
             LEFT JOIN streams st ON se.stream_id = st.id
             WHERE se.status = 'active' AND s.school_id = :school_id
             ORDER BY s.last_name ASC, s.first_name ASC",
            ['school_id' => $schoolId]
        );
    }

    private function getSubjects(int $examinationSetId): array
    {
        return $this->db->fetchAll(
            "SELECT es.*, s.name AS subject_name, s.code AS subject_code
             FROM examination_subjects es
             INNER JOIN subjects s ON es.subject_id = s.id
             WHERE es.examination_id = :exam_id
             ORDER BY es.display_order ASC",
            ['exam_id' => $examinationSetId]
        );
    }

    private function getMarks(int $examinationSetId, array $studentIds): array
    {
        if (empty($studentIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        return $this->db->fetchAll(
            "SELECT m.*
             FROM marks m
             WHERE m.examination_id = ?
               AND m.student_id IN ({$placeholders})",
            array_merge([$examinationSetId], $studentIds)
        );
    }

    private function getStudentMarks(int $studentId, array $marks): array
    {
        return array_values(array_filter(
            $marks,
            fn($mark) => (int)$mark['student_id'] === $studentId
        ));
    }

    private function calculateStudentResult(array $student, array $marks, array $subjects): array
    {
        $subjectResults = [];
        $totalMarks = 0.0;
        $totalScore = 0.0;
        $subjectCount = 0;
        $passed = 0;
        $failed = 0;

        foreach ($subjects as $subject) {
            $mark = null;
            foreach ($marks as $m) {
                if ((int)$m['subject_id'] === (int)$subject['subject_id']) {
                    $mark = $m;
                    break;
                }
            }

            $result = [
                'subject_id'     => $subject['subject_id'],
                'subject_name'   => $subject['subject_name'],
                'max_marks'      => $subject['max_marks'],
                'marks_obtained' => $mark['marks_obtained'] ?? null,
                'grade'          => null,
                'score'          => null,
                'pass'           => null,
                'remarks'        => $mark['remarks'] ?? null,
            ];

            if ($mark !== null) {
                $grade = $this->gradingService->getGrade((float)$mark['marks_obtained']);

                if ($grade) {
                    $result['grade'] = $grade['grade'];
                    $result['score'] = $grade['score'];
                    $result['pass']  = (bool)$grade['pass'];

                    if ($grade['pass']) {
                        $passed++;
                    } else {
                        $failed++;
                    }

                    $totalScore += (float)$grade['score'];
                }

                $totalMarks += (float)$mark['marks_obtained'];
                $subjectCount++;
            }

            $subjectResults[] = $result;
        }

        $average = $subjectCount > 0 ? round($totalMarks / $subjectCount, 2) : 0;
        $overallPass = $failed === 0 && $subjectCount > 0;
        $overallGrade = $this->gradingService->getGrade($average);

        $division = (new DivisionService())->getDivisionForAggregate($totalScore, (int)$this->config['school_id']);

        return [
            'student'         => $student,
            'subjects'        => $subjectResults,
            'total_marks'     => $totalMarks,
            'total_score'     => $totalScore,
            'average'         => $average,
            'subjects_passed' => $passed,
            'subjects_failed' => $failed,
            'overall_pass'    => $overallPass,
            'overall_grade'   => $overallGrade['grade'] ?? null,
            'score'           => $overallGrade['score'] ?? null,
            'division'        => $division,
            'position'        => null,
        ];
    }

    private function saveStudentResult(array $examSet, array $student, array $result): void
    {
        $existing = $this->db->fetch(
            "SELECT id FROM student_results
             WHERE examination_set_id = :exam_set_id AND student_id = :student_id",
            ['exam_set_id' => $examSet['id'], 'student_id' => $student['id']]
        );

        $data = [
            'examination_set_id' => $examSet['id'],
            'student_id'         => $student['id'],
            'total_marks'        => $result['total_marks'],
            'average_marks'      => $result['average'],
            'grade'              => $result['overall_grade'],
            'score'              => $result['score'],
            'result_status'      => $result['overall_pass'] ? 'passed' : 'failed',
            'calculated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->db->update('student_results', $data, ['id' => $existing['id']]);
        } else {
            $data['school_id']  = $examSet['school_id'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('student_results', $data);
        }
    }

    private function calculateRankings(int $examinationSetId, array $results): void
    {
        if (empty($this->config['calculate_rank'])) {
            return;
        }

        usort($results, fn($a, $b) => $b['average'] <=> $a['average']);

        $rank = 1;
        $previousAverage = null;

        foreach ($results as $index => $result) {
            if ($previousAverage !== null && $result['average'] < $previousAverage) {
                $rank = $this->config['rank_type'] === 'competition' ? $index + 1 : $rank + 1;
            }

            $this->db->update('student_results',
                ['position' => $rank],
                ['examination_set_id' => $examinationSetId, 'student_id' => $result['student']['id']]
            );

            $previousAverage = $result['average'];
        }
    }
}