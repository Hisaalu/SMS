<?php
// File: /app/Services/ResultCalculationService.php

namespace NexaT\Services;

use NexaT\Core\Database;

class ResultCalculationService
{
    private $db;
    private $gradingService;
    private $config;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gradingService = new GradingService();
    }
    
    /**
     * Calculate results for an examination set
     */
    public function calculateResults(int $examinationSetId, int $schoolId): array
    {
        // Get examination set
        $examSet = $this->db->fetch(
            "SELECT * FROM examination_sets WHERE id = :id AND school_id = :school_id",
            ['id' => $examinationSetId, 'school_id' => $schoolId]
        );
        
        if (!$examSet) {
            throw new \Exception("Examination set not found");
        }
        
        // Get result configuration
        $this->loadConfiguration($schoolId);
        
        // Get students for this examination
        $students = $this->getStudents($examinationSetId, $schoolId);
        
        // Get subjects for this examination
        $subjects = $this->getSubjects($examinationSetId);
        
        // Get marks
        $marks = $this->getMarks($examinationSetId, array_column($students, 'id'));
        
        // Calculate results for each student
        $results = [];
        foreach ($students as $student) {
            $studentMarks = $this->getStudentMarks($student['id'], $marks);
            $result = $this->calculateStudentResult($student, $studentMarks, $subjects);
            $results[] = $result;
            
            // Save student result
            $this->saveStudentResult($examSet, $student, $result);
        }
        
        // Calculate rankings
        $this->calculateRankings($examinationSetId, $results);
        
        return $results;
    }
    
    /**
     * Load result configuration
     */
    private function loadConfiguration(int $schoolId): void
    {
        $config = $this->db->fetch(
            "SELECT * FROM result_configurations WHERE school_id = :school_id AND status = 'active' LIMIT 1",
            ['school_id' => $schoolId]
        );
        
        if (!$config) {
            // Default configuration
            $config = [
                'school_id' => $schoolId,
                'calculation_type' => 'average',
                'best_subjects_count' => 0,
                'use_grade_points' => false,
                'calculate_rank' => true,
                'rank_type' => 'competition',
                'include_compulsory_only' => false
            ];
        }
        
        $this->config = $config;
    }
    
    /**
     * Get students for examination
     */
    private function getStudents(int $examinationSetId, int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT s.id, s.admission_number, s.first_name, s.last_name,
                    se.class_id, se.stream_id, cl.name as class_name, st.name as stream_name
             FROM students s
             INNER JOIN student_enrollments se ON s.id = se.student_id
             INNER JOIN classes cl ON se.class_id = cl.id
             LEFT JOIN streams st ON se.stream_id = st.id
             WHERE se.status = 'active'
             AND s.school_id = :school_id
             ORDER BY s.last_name ASC, s.first_name ASC",
            ['school_id' => $schoolId]
        );
    }
    
    /**
     * Get subjects for examination
     */
    private function getSubjects(int $examinationSetId): array
    {
        return $this->db->fetchAll(
            "SELECT es.*, s.name as subject_name, s.code as subject_code
             FROM examination_subjects es
             INNER JOIN subjects s ON es.subject_id = s.id
             WHERE es.examination_set_id = :examination_set_id
             ORDER BY es.display_order ASC",
            ['examination_set_id' => $examinationSetId]
        );
    }
    
    /**
     * Get marks for examination
     */
    private function getMarks(int $examinationSetId, array $studentIds): array
    {
        if (empty($studentIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        
        return $this->db->fetchAll(
            "SELECT m.*, es.subject_id, es.class_id, es.stream_id
             FROM marks m
             INNER JOIN examination_subjects es ON m.examination_subject_id = es.id
             WHERE es.examination_set_id = ?
             AND m.student_id IN ({$placeholders})",
            array_merge([$examinationSetId], $studentIds)
        );
    }
    
    /**
     * Get marks for a specific student
     */
    private function getStudentMarks(int $studentId, array $marks): array
    {
        $studentMarks = [];
        foreach ($marks as $mark) {
            if ($mark['student_id'] == $studentId) {
                $studentMarks[] = $mark;
            }
        }
        return $studentMarks;
    }
    
    private function calculateStudentResult(array $student, array $marks, array $subjects): array
    {
        $subjectResults  = [];
        $totalMarks      = 0;
        $totalScore      = 0;   // Aggregate of scores
        $subjectCount    = 0;
        $passedSubjects  = 0;
        $failedSubjects  = 0;

        foreach ($subjects as $subject) {
            $mark = null;
            foreach ($marks as $m) {
                if ($m['subject_id'] == $subject['subject_id']) {
                    $mark = $m;
                    break;
                }
            }

            $subjectResult = [
                'subject_id'     => $subject['subject_id'],
                'subject_name'   => $subject['subject_name'],
                'max_marks'      => $subject['max_marks'],
                'marks_obtained' => $mark ? $mark['marks_obtained'] : null,
                'grade'          => null,
                'score'          => null,   // renamed from grade_points
                'pass'           => null,
                'remarks'        => $mark ? $mark['remarks'] : null
            ];

            if ($mark !== null) {
                $grade = $this->gradingService->getGrade($mark['marks_obtained']);
                if ($grade) {
                    $subjectResult['grade'] = $grade['grade'];
                    $subjectResult['score'] = $grade['score'];   // renamed
                    $subjectResult['pass']  = $grade['pass'];

                    if ($grade['pass']) {
                        $passedSubjects++;
                    } else {
                        $failedSubjects++;
                    }
                    $totalScore += (float)$grade['score'];
                }

                $totalMarks += $mark['marks_obtained'];
                $subjectCount++;
            }

            $subjectResults[] = $subjectResult;
        }

        $average = $subjectCount > 0 ? round($totalMarks / $subjectCount, 2) : 0;

        $overallPass  = $failedSubjects === 0 && $subjectCount > 0;
        $overallGrade = $this->gradingService->getGrade($average);

        // Look up division
        $divisionService = new \NexaT\Services\DivisionService();
        $division = $divisionService->getDivisionForAggregate($totalScore, $this->getSchoolId());

        return [
            'student'         => $student,
            'subjects'        => $subjectResults,
            'total_marks'     => $totalMarks,
            'total_score'     => $totalScore,
            'average'         => $average,
            'subjects_passed' => $passedSubjects,
            'subjects_failed' => $failedSubjects,
            'overall_pass'    => $overallPass,
            'overall_grade'   => $overallGrade ? $overallGrade['grade'] : null,
            'score'           => $overallGrade ? $overallGrade['score'] : null,   // renamed
            'division'        => $division,
            'position'        => null
        ];
    }

    private function getSchoolId(): int
    {
        if (!$this->config) return 1;
        return (int)($this->config['school_id'] ?? 1);
    }
    
    /**
     * Save student result
     */
    private function saveStudentResult(array $examSet, array $student, array $result): void
    {
        $existing = $this->db->fetch(
            "SELECT * FROM student_results WHERE examination_set_id = :exam_set_id AND student_id = :student_id",
            [
                'exam_set_id' => $examSet['id'],
                'student_id' => $student['id']
            ]
        );
        
        $data = [
            'examination_set_id' => $examSet['id'],
            'student_id' => $student['id'],
            'total_marks' => $result['total_marks'],
            'average_marks' => $result['average'],
            'grade' => $result['overall_grade'],
            'score'              => $result['score'],
            'result_status' => $result['overall_pass'] ? 'passed' : 'failed',
            'calculated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $this->db->update('student_results', $data, ['id' => $existing['id']]);
        } else {
            $data['school_id'] = $examSet['school_id'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('student_results', $data);
        }
    }
    
    /**
     * Calculate rankings
     */
    private function calculateRankings(int $examinationSetId, array $results): void
    {
        if (!$this->config['calculate_rank']) {
            return;
        }
        
        // Sort by average descending
        usort($results, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });
        
        $rank = 1;
        $previousAverage = null;
        $skipRank = 0;
        
        foreach ($results as $index => $result) {
            if ($this->config['rank_type'] === 'competition') {
                if ($previousAverage !== null && $result['average'] < $previousAverage) {
                    $rank = $index + 1;
                }
            } else {
                // Dense ranking
                if ($previousAverage !== null && $result['average'] < $previousAverage) {
                    $rank++;
                }
            }
            
            $this->db->update('student_results', 
                ['position' => $rank],
                ['examination_set_id' => $examinationSetId, 'student_id' => $result['student']['id']]
            );
            
            $previousAverage = $result['average'];
        }
    }
}