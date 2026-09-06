<?php
// File: /app/Controllers/MarkController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\AuditService;

class MarkController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function entrySelector(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.enter')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        // If required query parameters are present, render entry view
        if (isset($_GET['academic_year_id'], $_GET['term_id'], $_GET['class_id'], $_GET['subject_id'], $_GET['examination_id'])) {
            $this->entry();
            return;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $academicYears = $this->db->fetchAll(
            "SELECT * FROM academic_years WHERE school_id = :school_id ORDER BY id DESC",
            ['school_id' => $schoolId]
        );
        
        $terms = $this->db->fetchAll(
            "SELECT * FROM terms WHERE school_id = :school_id ORDER BY term_number ASC",
            ['school_id' => $schoolId]
        );

        $classes = $this->db->fetchAll(
            "SELECT * FROM classes WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        $streams = $this->db->fetchAll(
            "SELECT * FROM streams WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $schoolId]
        );
        
        $subjects = $this->db->fetchAll(
            "SELECT * FROM subjects WHERE school_id = :school_id AND status = 'active' ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        $examinations = $this->db->fetchAll(
            "SELECT * FROM examinations WHERE school_id = :school_id ORDER BY id DESC",
            ['school_id' => $schoolId]
        );
        
        echo $this->view->renderWithLayout('examinations/marks/selector', 'default', [
            'title'        => 'Marks Entry',
            'academicYears'=> $academicYears,
            'terms'        => $terms,
            'classes'      => $classes,
            'streams'      => $streams,
            'subjects'     => $subjects,
            'examinations' => $examinations
        ]);
    }
    
    public function entry(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.enter')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $termId         = (int)($_GET['term_id'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = (int)($_GET['stream_id'] ?? 0);
        $subjectId      = (int)($_GET['subject_id'] ?? 0);
        $examinationId  = (int)($_GET['examination_id'] ?? 0);
        $schoolId       = $this->auth->getUser()->school_id ?? 1;
        $user           = $this->auth->getUser();
        
        if (!$academicYearId || !$termId || !$classId || !$subjectId || !$examinationId) {
            $_SESSION['flash_error'] = 'Please select Academic Year, Term, Class, Examination, and Subject.';
            header('Location: ' . BASE_URL . '/marks/entry');
            exit;
        }
        
        $academicYear = $this->db->fetch(
            "SELECT * FROM academic_years WHERE id = :id AND school_id = :school_id",
            ['id' => $academicYearId, 'school_id' => $schoolId]
        );
        
        $term = $this->db->fetch(
            "SELECT * FROM terms WHERE id = :id AND school_id = :school_id",
            ['id' => $termId, 'school_id' => $schoolId]
        );

        $class = $this->db->fetch(
            "SELECT * FROM classes WHERE id = :id AND school_id = :school_id",
            ['id' => $classId, 'school_id' => $schoolId]
        );

        $examination = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $examinationId, 'school_id' => $schoolId]
        );

        $stream = null;
        if ($streamId > 0) {
            $stream = $this->db->fetch(
                "SELECT * FROM streams WHERE id = :id AND school_id = :school_id",
                ['id' => $streamId, 'school_id' => $schoolId]
            );
        }
        
        $subject = $this->db->fetch(
            "SELECT * FROM subjects WHERE id = :id AND school_id = :school_id AND status = 'active'",
            ['id' => $subjectId, 'school_id' => $schoolId]
        );
        
        if (!$academicYear || !$term || !$class || !$subject || !$examination) {
            $_SESSION['flash_error'] = 'Invalid selection choices provided.';
            header('Location: ' . BASE_URL . '/marks/entry');
            exit;
        }
        
        // Check teacher permission for this subject
        if (!$user->isSuperAdmin() && !$user->hasPermission('marks.view_all')) {
            $hasAccess = $this->db->fetch(
                "SELECT id FROM teacher_assignments 
                 WHERE teacher_id = :teacher_id AND subject_id = :subject_id",
                ['teacher_id' => $user->id, 'subject_id' => $subjectId]
            );
            if (!$hasAccess) {
                require VIEWS_PATH . '/errors/403.php';
                exit;
            }
        }
        
        $sql = "SELECT s.id, s.admission_number, s.first_name, s.last_name,
                       se.id as enrollment_id, se.class_id, se.stream_id,
                       cl.name as class_name, st.name as stream_name
                FROM students s
                INNER JOIN student_enrollments se ON s.id = se.student_id
                INNER JOIN classes cl ON se.class_id = cl.id
                LEFT JOIN streams st ON se.stream_id = st.id
                WHERE se.academic_year_id = :academic_year_id
                  AND se.class_id = :class_id
                  AND se.status = 'active'
                  AND s.school_id = :school_id";

        $params = [
            'academic_year_id' => $academicYearId,
            'class_id'         => $classId,
            'school_id'        => $schoolId
        ];

        if ($streamId > 0) {
            $sql .= " AND se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }

        $sql .= " ORDER BY s.last_name ASC, s.first_name ASC";
        $students = $this->db->fetchAll($sql, $params);
        
        // Fetch matching examination_subject_id explicitly for selected examination
        $examSubject = $this->db->fetch(
            "SELECT id FROM examination_subjects 
             WHERE examination_id = :examination_id AND subject_id = :subject_id 
             LIMIT 1",
            [
                'examination_id' => $examinationId,
                'subject_id'     => $subjectId
            ]
        );
        $examinationSubjectId = $examSubject['id'] ?? null;

        // Fetch existing marks tied to this subject and exam
        $existingMarks = [];
        if (!empty($students)) {
            $studentIds = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
            
            $marks = $this->db->fetchAll(
                "SELECT m.* FROM marks m
                 WHERE m.academic_year_id = ? 
                   AND m.term_id = ?
                   AND m.subject_id = ?
                   AND (m.examination_subject_id = ? OR ? IS NULL)
                   AND m.student_id IN ({$placeholders})",
                array_merge([$academicYearId, $termId, $subjectId, $examinationSubjectId, $examinationSubjectId], $studentIds)
            );
            
            foreach ($marks as $mark) {
                $existingMarks[$mark['student_id']] = $mark;
            }
        }
        
        echo $this->view->renderWithLayout('examinations/marks/entry', 'default', [
            'title'                => 'Enter Marks - ' . $class['name'] . ($stream ? ' (' . $stream['name'] . ')' : ''),
            'academicYear'         => $academicYear,
            'term'                 => $term,
            'class'                => $class,
            'stream'               => $stream,
            'subject'              => $subject,
            'examination'          => $examination,
            'students'             => $students,
            'existingMarks'        => $existingMarks,
            'examinationSubjectId' => $examinationSubjectId
        ]);
    }

    public function bulkSave($params): void
    {
        if (ob_get_length()) ob_clean();

        header('Content-Type: application/json');

        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('results.enter')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized access']);
            exit;
        }
        
        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $input    = json_decode(file_get_contents('php://input'), true);
        
        $marksData            = $input['marks'] ?? [];
        $examinationSubjectId = (int)($input['examination_subject_id'] ?? 0);
        $academicYearId       = (int)($input['academic_year_id'] ?? 0);
        $termId               = (int)($input['term_id'] ?? 0);
        $subjectId            = (int)($input['subject_id'] ?? 0);
        
        if (empty($marksData)) {
            http_response_code(400);
            echo json_encode(['error' => 'No marks submitted']);
            exit;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($marksData as $studentId => $data) {
                $studentId = (int)$studentId;
                $rawMark   = trim($data['marks_obtained'] ?? '');
                $remarks   = trim($data['remarks'] ?? '');
                
                if ($rawMark === '') continue;

                $marksObtained = (float)$rawMark;

                // Fetch student_enrollment_id for this student and academic year
                $enrollment = $this->db->fetch(
                    "SELECT id FROM student_enrollments 
                    WHERE student_id = :student_id 
                    AND academic_year_id = :academic_year_id 
                    LIMIT 1",
                    [
                        'student_id'       => $studentId,
                        'academic_year_id' => $academicYearId
                    ]
                );

                $studentEnrollmentId = $enrollment['id'] ?? null;

                if (!$studentEnrollmentId) {
                    $latestEnrollment = $this->db->fetch(
                        "SELECT id FROM student_enrollments 
                        WHERE student_id = :student_id 
                        ORDER BY id DESC LIMIT 1",
                        ['student_id' => $studentId]
                    );
                    $studentEnrollmentId = $latestEnrollment['id'] ?? null;
                }

                // Fix: Match exact examination_subject_id and term_id when checking for existing marks
                $existing = $this->db->fetch(
                    "SELECT id FROM marks 
                    WHERE student_id = :student_id 
                    AND subject_id = :subject_id 
                    AND academic_year_id = :academic_year_id
                    AND term_id = :term_id
                    AND (examination_subject_id = :examination_subject_id OR (:examination_subject_id_null IS NULL AND examination_subject_id IS NULL))",
                    [
                        'student_id'             => $studentId,
                        'subject_id'             => $subjectId,
                        'academic_year_id'       => $academicYearId,
                        'term_id'                => $termId,
                        'examination_subject_id' => $examinationSubjectId > 0 ? $examinationSubjectId : null,
                        'examination_subject_id_null' => $examinationSubjectId > 0 ? $examinationSubjectId : null
                    ]
                );
                
                if ($existing) {
                    $this->db->update('marks', [
                        'marks_obtained'         => $marksObtained,
                        'remarks'                => $remarks,
                        'examination_subject_id' => $examinationSubjectId > 0 ? $examinationSubjectId : null,
                        'updated_by'             => $this->auth->id(),
                        'updated_at'             => date('Y-m-d H:i:s')
                    ], ['id' => $existing['id']]);
                } else {
                    $this->db->insert('marks', [
                        'school_id'              => $schoolId,
                        'student_enrollment_id'  => $studentEnrollmentId,
                        'examination_subject_id' => $examinationSubjectId > 0 ? $examinationSubjectId : null,
                        'academic_year_id'       => $academicYearId > 0 ? $academicYearId : null,
                        'term_id'                => $termId > 0 ? $termId : null,
                        'subject_id'             => $subjectId > 0 ? $subjectId : null,
                        'student_id'             => $studentId,
                        'marks_obtained'         => $marksObtained,
                        'remarks'                => $remarks,
                        'entered_by'             => $this->auth->id(),
                        'entered_at'             => date('Y-m-d H:i:s'),
                        'created_at'             => date('Y-m-d H:i:s'),
                        'updated_at'             => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Marks saved successfully']);
            exit;
            
        } catch (\Exception $e) {
            $this->db->rollback();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}