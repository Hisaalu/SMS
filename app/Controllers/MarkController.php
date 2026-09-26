<?php
// File: /app/Controllers/MarkController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class MarkController extends Controller
{
    public function entrySelector(): void
    {
        $this->requirePermission('results.enter');

        if (isset($_GET['academic_year_id'], $_GET['term_id'], $_GET['class_id'], $_GET['examination_id'])) {
            $this->entry();
            return;
        }

        $schoolId = $this->schoolId();

        echo $this->view->renderWithLayout('examinations/marks/selector', 'default', [
            'title'        => 'Marks Entry',
            'academicYears'=> $this->lookup('academic_years', $schoolId),
            'terms'        => $this->db->fetchAll("SELECT * FROM terms WHERE school_id = :s ORDER BY term_number ASC", ['s' => $schoolId]),
            'classes'      => $this->lookup('classes', $schoolId),
            'streams'      => $this->lookup('streams', $schoolId),
            'subjects'     => $this->db->fetchAll("SELECT * FROM subjects WHERE school_id = :s AND status = 'active' ORDER BY name ASC", ['s' => $schoolId]),
            'examinations' => $this->lookup('examinations', $schoolId),
        ]);
    }

    public function entry(): void
    {
        $this->requirePermission('results.enter');

        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = (int)($_GET['stream_id'] ?? 0);
        $subjectId      = $_GET['subject_id'] ?? 'all';
        $examinationId  = (int)($_GET['examination_id'] ?? 0);
        $schoolId       = $this->schoolId();

        if (!$academicYearId || !$termId || !$classId || !$examinationId) {
            $this->flashError('Please select Academic Year, Term, Class, and Examination.');
            $this->redirect('/marks/entry');
        }

        $academicYear = $this->fetchOne('academic_years', $academicYearId, $schoolId);
        $term         = $this->fetchOne('terms', $termId, $schoolId);
        $class        = $this->fetchOne('classes', $classId, $schoolId);
        $examination  = $this->fetchOne('examinations', $examinationId, $schoolId);
        $stream       = $streamId > 0 ? $this->fetchOne('streams', $streamId, $schoolId) : null;

        if (!$academicYear || !$term || !$class || !$examination) {
            $this->flashError('Invalid selection choices provided.');
            $this->redirect('/marks/entry');
        }

        $allSubjects = $this->db->fetchAll(
            "SELECT * FROM subjects WHERE school_id = :s AND status = 'active' ORDER BY code ASC, name ASC",
            ['s' => $schoolId]
        );

        $selectedSubject = null;
        $subjects = $allSubjects;

        if ($subjectId !== 'all' && (int)$subjectId > 0) {
            $selectedSubject = $this->fetchOne('subjects', (int)$subjectId, $schoolId);
            $subjects = $selectedSubject ? [$selectedSubject] : [];
        }

        if (empty($subjects)) {
            $this->flashError('No active subjects available.');
            $this->redirect('/marks/entry');
        }

        $students = $this->loadEnrolledStudents($academicYearId, $classId, $streamId, $schoolId);
        $existingMarks = $this->loadExistingMarks($students, $academicYearId, $termId, $examinationId);

        echo $this->view->renderWithLayout('examinations/marks/entry', 'default', [
            'title'           => 'Enter Marks - ' . $class['name'] . ($stream ? ' (' . $stream['name'] . ')' : ''),
            'academicYear'    => $academicYear,
            'term'            => $term,
            'class'           => $class,
            'stream'          => $stream,
            'subjects'        => $subjects,
            'allSubjects'     => $allSubjects,
            'selectedSubject' => $selectedSubject,
            'examination'     => $examination,
            'students'        => $students,
            'existingMarks'   => $existingMarks,
        ]);
    }

    public function bulkSave($params): void
    {
        if (ob_get_length()) {
            ob_clean();
        }

        $this->requirePermission('results.enter');

        $schoolId = $this->schoolId();
        $input = json_decode(file_get_contents('php://input'), true) ?: [];

        $marksData      = $input['marks'] ?? [];
        $academicYearId = (int)($input['academic_year_id'] ?? 0);
        $termId         = (int)($input['term_id'] ?? 0);
        $examinationId  = (int)($input['examination_id'] ?? 0);

        if (!$examinationId) {
            $this->json(['error' => 'Examination ID is required.'], 400);
        }

        try {
            $this->db->beginTransaction();

            foreach ($marksData as $studentId => $subjectMarks) {
                $studentId = (int)$studentId;

                $enrollment = $this->db->fetch(
                    "SELECT id FROM student_enrollments
                     WHERE student_id = :student_id AND academic_year_id = :year LIMIT 1",
                    ['student_id' => $studentId, 'year' => $academicYearId]
                );
                $enrollmentId = $enrollment['id'] ?? null;

                foreach ($subjectMarks as $subjectId => $rawMark) {
                    $subjectId = (int)$subjectId;
                    $rawMark = trim((string)$rawMark);

                    if ($rawMark === '') {
                        continue;
                    }

                    $marksObtained = (float)$rawMark;

                    $existing = $this->db->fetch(
                        "SELECT id FROM marks
                         WHERE student_id = :student_id
                           AND subject_id = :subject_id
                           AND academic_year_id = :year
                           AND term_id = :term
                           AND examination_id = :exam",
                        [
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'year'       => $academicYearId,
                            'term'       => $termId,
                            'exam'       => $examinationId,
                        ]
                    );

                    if ($existing) {
                        $this->db->update('marks', [
                            'marks_obtained' => $marksObtained,
                            'updated_by'     => $this->auth->id(),
                            'updated_at'     => date('Y-m-d H:i:s'),
                        ], ['id' => $existing['id']]);
                    } else {
                        $this->db->insert('marks', [
                            'school_id'             => $schoolId,
                            'student_enrollment_id' => $enrollmentId,
                            'academic_year_id'      => $academicYearId,
                            'term_id'               => $termId,
                            'examination_id'        => $examinationId,
                            'subject_id'            => $subjectId,
                            'student_id'            => $studentId,
                            'marks_obtained'        => $marksObtained,
                            'entered_by'            => $this->auth->id(),
                            'entered_at'            => date('Y-m-d H:i:s'),
                            'created_at'            => date('Y-m-d H:i:s'),
                            'updated_at'            => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }

            $this->db->commit();
            $this->json(['success' => true, 'message' => 'Marks saved successfully']);

        } catch (\Throwable $e) {
            $this->db->rollback();
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    private function lookup(string $table, int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$table} WHERE school_id = :s ORDER BY id DESC",
            ['s' => $schoolId]
        );
    }

    private function fetchOne(string $table, int $id, int $schoolId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM {$table} WHERE id = :id AND school_id = :s",
            ['id' => $id, 's' => $schoolId]
        );
        return $row ?: null;
    }

    private function loadEnrolledStudents(int $yearId, int $classId, int $streamId, int $schoolId): array
    {
        $sql = "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender,
                       se.id AS enrollment_id, se.class_id, se.stream_id,
                       cl.name AS class_name, st.name AS stream_name
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                INNER JOIN classes cl ON se.class_id = cl.id
                LEFT JOIN streams st ON se.stream_id = st.id
                WHERE se.academic_year_id = :year
                  AND se.class_id = :class_id
                  AND se.status = 'active'
                  AND s.school_id = :school_id";

        $params = [
            'year'       => $yearId,
            'class_id'   => $classId,
            'school_id'  => $schoolId,
        ];

        if ($streamId > 0) {
            $sql .= " AND se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }

        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    private function loadExistingMarks(array $students, int $yearId, int $termId, int $examId): array
    {
        if (empty($students)) {
            return [];
        }

        $studentIds = array_column($students, 'id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        $marks = $this->db->fetchAll(
            "SELECT * FROM marks
             WHERE academic_year_id = ? AND term_id = ? AND examination_id = ?
               AND student_id IN ({$placeholders})",
            array_merge([$yearId, $termId, $examId], $studentIds)
        );

        $matrix = [];
        foreach ($marks as $mark) {
            $matrix[$mark['student_id']][$mark['subject_id']] = $mark;
        }

        return $matrix;
    }

    public function entryLookup(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();
        $type     = (string)($_GET['type'] ?? '');

        try {
            switch ($type) {

                case 'terms':
                    $yearId = (int)($_GET['year_id'] ?? 0);
                    if (!$yearId) {
                        $this->json([]);
                        return;
                    }

                    $rows = $this->db->fetchAll(
                        "SELECT id, name, term_number, is_current
                        FROM terms
                        WHERE school_id = :school_id
                        AND academic_year_id = :year_id
                        ORDER BY term_number ASC, id ASC",
                        ['school_id' => $schoolId, 'year_id' => $yearId]
                    );

                    $this->json($rows);
                    return;

                case 'examinations':
                    $yearId = (int)($_GET['year_id'] ?? 0);
                    $termId = (int)($_GET['term_id'] ?? 0);

                    if (!$yearId) {
                        $this->json([]);
                        return;
                    }

                    $sql    = "SELECT id, name, code, academic_period_id
                            FROM examinations
                            WHERE school_id = :school_id
                                AND academic_year_id = :year_id";
                    $params = ['school_id' => $schoolId, 'year_id' => $yearId];

                    if ($termId) {
                        $sql .= " AND academic_period_id = :term_id";
                        $params['term_id'] = $termId;
                    }

                    $sql .= " ORDER BY created_at DESC";

                    $this->json($this->db->fetchAll($sql, $params));
                    return;

                case 'streams':
                    $classId = (int)($_GET['class_id'] ?? 0);
                    if (!$classId) {
                        $this->json([]);
                        return;
                    }

                    $rows = $this->db->fetchAll(
                        "SELECT id, name
                        FROM streams
                        WHERE school_id = :school_id AND class_id = :class_id
                        ORDER BY name ASC",
                        ['school_id' => $schoolId, 'class_id' => $classId]
                    );

                    $this->json($rows);
                    return;

                case 'subjects':
                    $classId  = (int)($_GET['class_id']  ?? 0);

                    if ($classId) {
                        $rows = $this->db->fetchAll(
                            "SELECT s.id, s.name, s.code
                            FROM class_subjects cs
                            INNER JOIN subjects s ON cs.subject_id = s.id
                            WHERE cs.class_id = :class_id
                            AND cs.school_id = :school_id
                            ORDER BY s.code ASC, s.name ASC",
                            ['class_id' => $classId, 'school_id' => $schoolId]
                        );

                        if (!empty($rows)) {
                            $this->json($rows);
                            return;
                        }
                    }

                    $rows = $this->db->fetchAll(
                        "SELECT s.id, s.name, s.code
                        FROM subjects s
                        WHERE s.school_id = :school_id
                        AND NOT EXISTS (
                            SELECT 1 FROM class_subjects cs WHERE cs.subject_id = s.id
                        )
                        ORDER BY s.code ASC, s.name ASC",
                        ['school_id' => $schoolId]
                    );

                    $this->json($rows);
                    return;
            }

            $this->json([]);

        } catch (\Throwable $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}