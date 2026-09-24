<?php
// File: /app/Controllers/ResultController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\DivisionSchemeService;
use NexaT\Services\DivisionService;
use NexaT\Services\GradingService;

class ResultController extends Controller
{
    public function examination($params): void
    {
        $this->requirePermission('results.view');

        $examinationId = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $examination = $this->db->fetch(
            "SELECT e.*, at.name AS assessment_type_name
             FROM examinations e
             LEFT JOIN assessment_types at ON e.assessment_type_id = at.id
             WHERE e.id = :id AND e.school_id = :school_id",
            ['id' => $examinationId, 'school_id' => $schoolId]
        );

        if (!$examination) {
            $this->flashError('Examination not found.');
            $this->redirect('/examinations');
        }

        $results = $this->db->fetchAll(
            "SELECT s.id AS student_id, s.admission_number, s.first_name, s.last_name,
                    m.marks_obtained, m.remarks, m.entered_at,
                    cl.name AS class_name, st.name AS stream_name,
                    ss.name AS status_name
             FROM marks m
             INNER JOIN students s ON m.student_id = s.id
             LEFT JOIN student_enrollments se ON s.id = se.student_id AND se.status = 'active'
             LEFT JOIN classes cl ON se.class_id = cl.id
             LEFT JOIN streams st ON se.stream_id = st.id
             LEFT JOIN student_statuses ss ON se.student_status_id = ss.id
             WHERE m.examination_id = :exam_id AND m.school_id = :school_id
             ORDER BY s.last_name ASC",
            ['exam_id' => $examinationId, 'school_id' => $schoolId]
        );

        $stats = $this->summarise($results);

        echo $this->view->renderWithLayout('examinations/results/examination', 'default', [
            'title'         => 'Results - ' . $examination['name'],
            'examination'   => $examination,
            'results'       => $results,
            'totalStudents' => $stats['total'],
            'average'       => $stats['average'],
            'passed'        => $stats['passed'],
            'failed'        => $stats['failed'],
        ]);
    }

    public function student($params): void
    {
        $this->requirePermission('results.view');

        $studentId = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $student = $this->db->fetch(
            "SELECT s.*, sc.name AS category_name, st.name AS status_name
             FROM students s
             LEFT JOIN student_categories sc ON s.current_category_id = sc.id
             LEFT JOIN student_statuses st ON s.current_status_id = st.id
             WHERE s.id = :id AND s.school_id = :school_id",
            ['id' => $studentId, 'school_id' => $schoolId]
        );

        if (!$student) {
            $this->flashError('Student not found.');
            $this->redirect('/students');
        }

        $results = $this->db->fetchAll(
            "SELECT m.*, e.name AS examination_name, e.code AS examination_code,
                    at.name AS assessment_type, ay.name AS academic_year, t.name AS term
             FROM marks m
             INNER JOIN examinations e ON m.examination_id = e.id
             LEFT JOIN assessment_types at ON e.assessment_type_id = at.id
             LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
             LEFT JOIN terms t ON e.academic_period_id = t.id
             WHERE m.student_id = :student_id AND m.school_id = :school_id
             ORDER BY e.id DESC, e.created_at DESC",
            ['student_id' => $studentId, 'school_id' => $schoolId]
        );

        $total = count($results);
        $sum = 0.0;
        foreach ($results as $r) {
            $sum += (float)$r['marks_obtained'];
        }
        $average = $total > 0 ? round($sum / $total, 2) : 0;

        echo $this->view->renderWithLayout('examinations/results/student', 'default', [
            'title'      => 'Student Results',
            'student'    => $student,
            'results'    => $results,
            'totalExams' => $total,
            'average'    => $average,
        ]);
    }

    public function publish($params): void
    {
        $this->requirePermission('results.publish');

        $examinationId = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $exam = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $examinationId, 'school_id' => $schoolId]
        );

        if (!$exam) {
            $this->json(['error' => 'Examination not found'], 404);
        }

        $ok = $this->db->update('examinations', [
            'status'       => 'published',
            'is_published' => 1,
            'updated_at'   => date('Y-m-d H:i:s'),
        ], ['id' => $examinationId]);

        if ($ok !== false) {
            $this->audit('Results Published', 'examinations', "Published results for examination: {$exam['name']}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to publish results'], 500);
    }

    public function classSelector(): void
    {
        $this->requirePermission('results.view');

        $schoolId = $this->schoolId();

        echo $this->view->renderWithLayout('examinations/results/selector', 'default', [
            'title'         => 'Class Results',
            'academicYears' => $this->db->fetchAll("SELECT * FROM academic_years WHERE school_id = :s ORDER BY id DESC", ['s' => $schoolId]),
            'terms'         => $this->db->fetchAll("SELECT * FROM terms WHERE school_id = :s ORDER BY term_number ASC", ['s' => $schoolId]),
            'classes'       => $this->db->fetchAll("SELECT * FROM classes WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'streams'       => $this->db->fetchAll("SELECT * FROM streams WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'examinations'  => $this->db->fetchAll("SELECT * FROM examinations WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
        ]);
    }

    public function classResults($params = []): void
    {
        $this->requirePermission('results.view');

        $examinationId  = (int)($_GET['examination_id'] ?? $params['examinationId'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = (int)($_GET['stream_id'] ?? 0);
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);
        $subjectId      = $_GET['subject_id'] ?? 'all';
        $schoolId       = $this->schoolId();

        $examination   = null;
        $students      = [];
        $subjects      = [];
        $resultsMatrix = [];
        $scoreMatrix   = [];
        $studentTotals = [];
        $divisions     = [];
        $divisionSchemeId = null;

        $gradingSystem = null;

        if ($examinationId > 0 && $classId > 0) {
            $examination = $this->db->fetch(
                "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
                ['id' => $examinationId, 'school_id' => $schoolId]
            );

            if ($examination) {
                $gradingSystem = (new GradingService())->getSystemForClass($classId, $schoolId, $academicYearId ?: null);
                $gradingSystemId = !empty($gradingSystem['id']) ? (int)$gradingSystem['id'] : null;

                $schemeService = new DivisionSchemeService();
                $divisionService = new DivisionService();

                if ($gradingSystemId) {
                    $schemes = $schemeService->getForSystem($gradingSystemId, $schoolId, true);
                    if (!empty($schemes)) {
                        $divisionSchemeId = (int)$schemes[0]['id'];
                    }
                }

                if ($divisionSchemeId === null) {
                    $allSchemes = $schemeService->getAll($schoolId, true);
                    if (!empty($allSchemes)) {
                        $divisionSchemeId = (int)$allSchemes[0]['id'];
                    }
                }

                if ($divisionSchemeId !== null) {
                    $divisions = $divisionService->getForScheme($divisionSchemeId, true);
                }

                $examSubjects = $this->db->fetchAll(
                    "SELECT s.id, s.name, s.code FROM examination_subjects es
                     INNER JOIN subjects s ON es.subject_id = s.id
                     WHERE es.examination_id = :exam_id
                     ORDER BY s.code ASC, s.name ASC",
                    ['exam_id' => $examinationId]
                );

                $allSubjects = $this->db->fetchAll(
                    "SELECT * FROM subjects WHERE school_id = :s AND status = 'active' ORDER BY code ASC, name ASC",
                    ['s' => $schoolId]
                );

                $subjects = !empty($examSubjects) ? $examSubjects : $allSubjects;

                if ($subjectId !== 'all' && (int)$subjectId > 0) {
                    $filtered = array_values(array_filter($subjects, fn($s) => (int)$s['id'] === (int)$subjectId));
                    if (!empty($filtered)) {
                        $subjects = $filtered;
                    }
                }

                $students = $this->loadEnrolledStudents($academicYearId, $classId, $streamId, $schoolId);

                if (!empty($students)) {
                    $studentIds = array_column($students, 'id');
                    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

                    $rawMarks = $this->db->fetchAll(
                        "SELECT m.student_id, m.subject_id, m.marks_obtained
                         FROM marks m
                         WHERE m.academic_year_id = ?
                           AND m.term_id = ?
                           AND m.examination_id = ?
                           AND m.student_id IN ({$placeholders})
                           AND m.school_id = ?",
                        array_merge([$academicYearId, $termId, $examinationId], $studentIds, [$schoolId])
                    );

                    foreach ($rawMarks as $row) {
                        $resultsMatrix[$row['student_id']][$row['subject_id']] = $row['marks_obtained'];
                    }

                    $rules = $gradingSystem['rules'] ?? [];

                    foreach ($resultsMatrix as $sid => $subjectMarks) {
                        foreach ($subjectMarks as $subjId => $mark) {
                            $scoreMatrix[$sid][$subjId] = $this->resolveScore((float)$mark, $rules);
                        }
                    }

                    foreach ($students as $s) {
                        $sid = $s['id'];
                        $total = 0;
                        $ta = 0;
                        $count = 0;

                        foreach ($subjects as $subj) {
                            $raw = $resultsMatrix[$sid][$subj['id']] ?? null;
                            if ($raw !== null) {
                                $total += (float)$raw;
                                $ta += (float)($scoreMatrix[$sid][$subj['id']] ?? 0);
                                $count++;
                            }
                        }

                        $studentTotals[$sid] = [
                            'total' => $total,
                            'avg'   => $count > 0 ? round($total / $count, 2) : 0,
                            'ta'    => $ta,
                            'div'   => $this->resolveDivision($ta, $divisions),
                            'count' => $count,
                        ];
                    }

                    usort($students, function ($a, $b) use ($studentTotals) {
                        $taA = $studentTotals[$a['id']]['ta'] ?? PHP_INT_MAX;
                        $taB = $studentTotals[$b['id']]['ta'] ?? PHP_INT_MAX;
                        if ($taA === $taB) {
                            return ($studentTotals[$b['id']]['total'] ?? 0) <=> ($studentTotals[$a['id']]['total'] ?? 0);
                        }
                        return $taA <=> $taB;
                    });

                    $rank = 1;
                    foreach ($students as &$s) {
                        $studentTotals[$s['id']]['position'] = $rank++;
                    }
                    unset($s);
                }
            }
        }

        echo $this->view->renderWithLayout('examinations/results/selector', 'default', [
            'title'            => 'Class Results',
            'academicYears'    => $this->db->fetchAll("SELECT * FROM academic_years WHERE school_id = :s ORDER BY id DESC", ['s' => $schoolId]),
            'terms'            => $this->db->fetchAll("SELECT * FROM terms WHERE school_id = :s ORDER BY term_number ASC", ['s' => $schoolId]),
            'classes'          => $this->db->fetchAll("SELECT * FROM classes WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'streams'          => $this->db->fetchAll("SELECT * FROM streams WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'examinations'     => $this->db->fetchAll("SELECT * FROM examinations WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'subjects'         => $subjects,
            'examination'      => $examination,
            'students'         => $students,
            'resultsMatrix'    => $resultsMatrix,
            'scoreMatrix'      => $scoreMatrix,
            'studentTotals'    => $studentTotals,
            'divisions'        => $divisions,
            'divisionSchemeId' => $divisionSchemeId,
            'gradingSystem'    => $gradingSystem,
        ]);
    }

    public function getStreamsByClass($params = []): void
    {
        if (!$this->auth->check()) {
            $this->json([]);
        }

        $classId = (int)($_GET['class_id'] ?? 0);
        $schoolId = $this->schoolId();

        if ($classId <= 0) {
            $this->json([]);
        }

        $streams = $this->db->fetchAll(
            "SELECT id, name FROM streams
             WHERE class_id = :class_id AND school_id = :school_id
             ORDER BY name ASC",
            ['class_id' => $classId, 'school_id' => $schoolId]
        );

        $this->json($streams ?: []);
    }

    private function summarise(array $results): array
    {
        $total = count($results);
        $sum = 0.0;
        $passed = 0;
        $failed = 0;

        foreach ($results as $r) {
            $sum += (float)$r['marks_obtained'];
            if ($r['marks_obtained'] >= 50) {
                $passed++;
            } else {
                $failed++;
            }
        }

        return [
            'total'   => $total,
            'average' => $total > 0 ? round($sum / $total, 2) : 0,
            'passed'  => $passed,
            'failed'  => $failed,
        ];
    }

    private function loadEnrolledStudents(int $yearId, int $classId, int $streamId, int $schoolId): array
    {
        $sql = "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                WHERE se.academic_year_id = :year
                  AND se.class_id = :class_id
                  AND se.status = 'active'
                  AND s.school_id = :school_id";

        $params = ['year' => $yearId, 'class_id' => $classId, 'school_id' => $schoolId];

        if ($streamId > 0) {
            $sql .= " AND se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }

        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    private function resolveScore(float $mark, array $rules): float
    {
        foreach ($rules as $rule) {
            if ($mark >= (float)$rule['min_mark'] && $mark <= (float)$rule['max_mark']) {
                return (float)($rule['score'] ?? 0);
            }
        }
        return 0.0;
    }

    private function resolveDivision(float $aggregate, array $divisions): ?array
    {
        foreach ($divisions as $d) {
            if ($aggregate >= (int)$d['min_aggregate'] && $aggregate <= (int)$d['max_aggregate']) {
                return $d;
            }
        }
        return null;
    }
}