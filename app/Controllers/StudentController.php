<?php
// File: /app/Controllers/StudentController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\StudentAdmissionService;
use NexaT\Services\StudentNumberGenerator;
use Throwable;

class StudentController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('students.view');

        $schoolId = $this->schoolId();

        $search         = trim($_GET['search'] ?? '');
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = (int)($_GET['stream_id'] ?? 0);
        $categoryId     = (int)($_GET['category_id'] ?? 0);
        $statusId       = (int)($_GET['status_id'] ?? 0);

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        $where  = ["s.school_id = :school_id"];
        $params = ['school_id' => $schoolId];

        if ($search !== '') {
            $where[] = "(s.first_name LIKE :s1 OR s.last_name LIKE :s2
                      OR s.admission_number LIKE :s3 OR s.registration_number LIKE :s4)";
            $like = "%{$search}%";
            $params += ['s1' => $like, 's2' => $like, 's3' => $like, 's4' => $like];
        }

        $filters = [
            'academic_year_id' => [$academicYearId, 'se.academic_year_id'],
            'class_id'         => [$classId,        'se.class_id'],
            'stream_id'        => [$streamId,       'se.stream_id'],
            'category_id'      => [$categoryId,     'se.student_category_id'],
            'status_id'        => [$statusId,       'se.student_status_id'],
        ];

        foreach ($filters as $key => [$value, $column]) {
            if ($value > 0) {
                $where[] = "{$column} = :{$key}";
                $params[$key] = $value;
            }
        }

        $whereClause = 'WHERE ' . implode(' AND ', $where);

        $totalStudents = (int)($this->db->fetch(
            "SELECT COUNT(DISTINCT s.id) AS total
             FROM students s
             LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
             {$whereClause}",
            $params
        )['total'] ?? 0);

        $totalPages = max(1, (int)ceil($totalStudents / $limit));

        $students = $this->db->fetchAll(
            "SELECT s.id, s.admission_number, s.registration_number,
                    s.first_name, s.last_name, s.gender,
                    cl.name AS class_name,
                    st.name AS stream_name,
                    sc.name AS category_name,
                    ss.name AS status_name
             FROM students s
             LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
             LEFT JOIN classes cl            ON se.class_id = cl.id
             LEFT JOIN streams st            ON se.stream_id = st.id
             LEFT JOIN student_categories sc ON se.student_category_id = sc.id
             LEFT JOIN student_statuses ss   ON se.student_status_id = ss.id
             {$whereClause}
             ORDER BY s.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        echo $this->view->renderWithLayout('students/index', 'default', [
            'students'       => $students,
            'academicYears'  => $this->db->fetchAll("SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC", ['s' => $schoolId]),
            'classes'        => $this->db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'streams'        => $this->db->fetchAll("SELECT id, name, class_id FROM streams WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'categories'     => $this->db->fetchAll("SELECT id, name FROM student_categories WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]),
            'statuses'       => $this->db->fetchAll("SELECT id, name FROM student_statuses WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]),
            'search'         => $search,
            'academicYearId' => $academicYearId,
            'classId'        => $classId,
            'streamId'       => $streamId,
            'categoryId'     => $categoryId,
            'statusId'       => $statusId,
            'page'           => $page,
            'totalPages'     => $totalPages,
            'totalStudents'  => $totalStudents,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('students.create');

        $schoolId = $this->schoolId();
        $generator = new StudentNumberGenerator();

        echo $this->view->renderWithLayout('students/create', 'default', [
            'admission_number'    => $generator->generate($schoolId, 'admission'),
            'registration_number' => $generator->generate($schoolId, 'registration'),
            'categories'          => $this->activeCategories($schoolId),
            'statuses'            => $this->activeStatuses($schoolId),
            'years'               => $this->db->fetchAll("SELECT id, name FROM academic_years WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]),
            'classes'             => $this->db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s", ['s' => $schoolId]),
            'streams'             => $this->db->fetchAll("SELECT * FROM streams WHERE school_id = :s", ['s' => $schoolId]),
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('students.create');

        $schoolId = $this->schoolId();
        $generator = new StudentNumberGenerator();

        $regNumber = !empty($_POST['registration_number'])
            ? trim($_POST['registration_number'])
            : $generator->generate($schoolId, 'registration');

        $admNumber = !empty($_POST['admission_number'])
            ? trim($_POST['admission_number'])
            : $generator->generate($schoolId, 'admission');

        $photoPath = $this->handlePhotoUpload();

        $studentData = [
            'school_id'           => $schoolId,
            'registration_number' => $regNumber,
            'admission_number'    => $admNumber,
            'first_name'          => trim($_POST['first_name']),
            'middle_name'         => $this->nullablePost('middle_name'),
            'last_name'           => trim($_POST['last_name']),
            'preferred_name'      => $this->nullablePost('preferred_name'),
            'gender'              => $_POST['gender'],
            'date_of_birth'       => $this->nullablePost('date_of_birth'),
            'phone'               => $this->nullablePost('phone'),
            'email'               => $this->nullablePost('email'),
            'address'             => $this->nullablePost('address'),
            'photo_path'          => $photoPath,
            'current_category_id' => (int)$_POST['category_id'],
            'current_status_id'   => (int)$_POST['status_id'],
            'admission_date'      => $_POST['admission_date'],
        ];

        $guardianData = [
            'full_name'    => trim($_POST['guardian_name']),
            'phone'        => trim($_POST['guardian_phone']),
            'occupation'   => $this->nullablePost('guardian_occupation'),
            'address'      => $this->nullablePost('guardian_address'),
            'relationship' => $_POST['guardian_relationship'] ?? 'Parent',
        ];

        $enrollmentData = [
            'academic_year_id' => (int)$_POST['academic_year_id'],
            'class_id'         => (int)$_POST['class_id'],
            'stream_id'        => !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null,
        ];

        try {
            $studentId = (new StudentAdmissionService())->admitStudent($studentData, $guardianData, $enrollmentData);
            $this->flashSuccess('Student admitted successfully. You can now print the admission letter.');

            // Send the admin straight to the letter so they can print right away
            $this->redirect('/students/admission-letter?id=' . $studentId);

        } catch (Throwable $e) {
            $this->flashError('Admission Failed: ' . $e->getMessage());
            $this->redirect('/students/create');
        }
    }

    public function show(): void
    {
        $this->requirePermission('students.view');

        $id = (int)($_GET['id'] ?? 0);
        $schoolId = $this->schoolId();

        $student = $this->db->fetch(
            "SELECT s.*, c.name AS category_name, st.name AS status_name
             FROM students s
             LEFT JOIN student_categories c ON s.current_category_id = c.id
             LEFT JOIN student_statuses st  ON s.current_status_id = st.id
             WHERE s.id = :id AND s.school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$student) {
            $this->flashError('Student record not found.');
            $this->redirect('/students');
        }

        $enrollments = $this->db->fetchAll(
            "SELECT se.*, ay.name AS academic_year_name,
                    cl.name AS class_name, str.name AS stream_name, st.name AS status_name
             FROM student_enrollments se
             LEFT JOIN academic_years ay  ON se.academic_year_id = ay.id
             LEFT JOIN classes cl         ON se.class_id = cl.id
             LEFT JOIN streams str        ON se.stream_id = str.id
             LEFT JOIN student_statuses st ON se.student_status_id = st.id
             WHERE se.student_id = :student_id
             ORDER BY se.id DESC",
            ['student_id' => $id]
        );

        $guardians = $this->db->fetchAll(
            "SELECT g.*, sg.relationship
             FROM guardians g
             INNER JOIN student_guardians sg ON g.id = sg.guardian_id
             WHERE sg.student_id = :student_id",
            ['student_id' => $id]
        );

        echo $this->view->renderWithLayout('students/show', 'default', [
            'student'     => $student,
            'enrollments' => $enrollments,
            'guardians'   => $guardians,
        ]);
    }

    /**
     * Render a printable admission letter for a student.
     * Output is standalone — no app layout — so the browser can print cleanly.
     */
    public function admissionLetter(): void
    {
        $this->requirePermission('students.view');

        $schoolId  = $this->schoolId();
        $studentId = (int)($_GET['id'] ?? 0);

        if ($studentId <= 0) {
            $this->flashError('Student not specified.');
            $this->redirect('/students');
        }

        $student = $this->db->fetch(
            "SELECT s.*, sc.name AS category_name, ss.name AS status_name
             FROM students s
             LEFT JOIN student_categories sc ON s.current_category_id = sc.id
             LEFT JOIN student_statuses ss   ON s.current_status_id = ss.id
             WHERE s.id = :id AND s.school_id = :school_id",
            ['id' => $studentId, 'school_id' => $schoolId]
        );

        if (!$student) {
            $this->flashError('Student record not found.');
            $this->redirect('/students');
        }

        // Primary guardian — falls back to any linked guardian
        $guardian = $this->db->fetch(
            "SELECT g.*, sg.relationship, sg.is_primary
             FROM guardians g
             INNER JOIN student_guardians sg ON g.id = sg.guardian_id
             WHERE sg.student_id = :student_id
             ORDER BY sg.is_primary DESC, sg.id ASC
             LIMIT 1",
            ['student_id' => $studentId]
        );

        // Current active enrollment
        $enrollment = $this->db->fetch(
            "SELECT se.*,
                    ay.name AS academic_year_name,
                    cl.name AS class_name,
                    st.name AS stream_name
             FROM student_enrollments se
             LEFT JOIN academic_years ay ON se.academic_year_id = ay.id
             LEFT JOIN classes cl        ON se.class_id = cl.id
             LEFT JOIN streams st        ON se.stream_id = st.id
             WHERE se.student_id = :student_id AND se.status = 'active'
             ORDER BY se.id DESC LIMIT 1",
            ['student_id' => $studentId]
        );

        // Next term (used for the reporting date block)
        $nextTerm = $this->db->fetch(
            "SELECT name, start_date, end_date FROM terms
             WHERE school_id = :s AND start_date > CURDATE()
             ORDER BY start_date ASC LIMIT 1",
            ['s' => $schoolId]
        );

        // Initials for signature block (current user)
        $user = $this->auth->getUser();
        $headInitials = '';
        if ($user) {
            $f = strtoupper(substr(trim($user->first_name ?? ''), 0, 1));
            $l = strtoupper(substr(trim($user->last_name  ?? ''), 0, 1));
            $headInitials = trim($f . '.' . $l, '.');
        }

        $this->audit(
            'Admission Letter Printed',
            'students',
            "Printed admission letter for student #{$studentId} ({$student['admission_number']})"
        );

        echo $this->view->render('students/admission_letter', [
            'student'        => $student,
            'guardian'       => $guardian,
            'enrollment'     => $enrollment,
            'nextTerm'       => $nextTerm,
            'headInitials'   => $headInitials,
            'generatedAt'    => date('d M Y'),
        ]);
    }

    public function edit(): void
    {
        $this->requirePermission('students.edit');

        $id = (int)($_GET['id'] ?? 0);
        $schoolId = $this->schoolId();

        $student = $this->db->fetch(
            "SELECT id, school_id, registration_number, admission_number, admission_date,
                    first_name, middle_name, last_name, preferred_name,
                    gender, date_of_birth, phone, email, address, photo_path,
                    current_category_id, current_status_id, created_at
             FROM students
             WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$student) {
            $this->flashError('Student not found.');
            $this->redirect('/students');
        }

        $primaryGuardian = $this->db->fetch(
            "SELECT g.*, sg.relationship, sg.is_primary
             FROM guardians g
             INNER JOIN student_guardians sg ON g.id = sg.guardian_id
             WHERE sg.student_id = :student_id AND sg.is_primary = 1",
            ['student_id' => $id]
        );

        $currentEnrollment = $this->db->fetch(
            "SELECT * FROM student_enrollments
             WHERE student_id = :student_id AND status = 'active'
             ORDER BY id DESC LIMIT 1",
            ['student_id' => $id]
        );

        echo $this->view->renderWithLayout('students/edit', 'default', [
            'student'           => $student,
            'primaryGuardian'   => $primaryGuardian,
            'currentEnrollment' => $currentEnrollment,
            'categories'        => $this->activeCategories($schoolId),
            'statuses'          => $this->activeStatuses($schoolId),
            'classes'           => $this->db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s", ['s' => $schoolId]),
            'streams'           => $this->db->fetchAll("SELECT id, name FROM streams WHERE school_id = :s", ['s' => $schoolId]),
        ]);
    }

    public function update(): void
    {
        $this->requirePermission('students.edit');

        $id = (int)($_POST['id'] ?? 0);
        $schoolId = $this->schoolId();

        $existing = $this->db->fetch(
            "SELECT * FROM students WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$existing) {
            $this->flashError('Student record not found.');
            $this->redirect('/students');
        }

        $oldStatusId   = (int)$existing['current_status_id'];
        $newStatusId   = (int)$_POST['status_id'];
        $statusChanged = $oldStatusId !== $newStatusId;

        $photoPath = $this->handlePhotoUpload($existing['photo_path'] ?? null);

        try {
            $this->db->beginTransaction();

            $this->updateStudentRecord($id, $schoolId, $photoPath, $newStatusId);
            $this->updateEnrollmentOnClassChange($id, $newStatusId);
            $this->recordStatusChange($id, $oldStatusId, $newStatusId, $statusChanged);
            $this->upsertGuardian($id, $schoolId);

            $this->db->commit();
            $this->flashSuccess('Student profile updated successfully.');
            $this->redirect('/students/show?id=' . $id);

        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->flashError('Update Failed: ' . $e->getMessage());
            $this->redirect('/students/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->requirePermission('students.delete');

        $id = (int)($_GET['id'] ?? 0);
        $schoolId = $this->schoolId();

        try {
            $this->db->execute(
                "DELETE FROM students WHERE id = :id AND school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );
            $this->flashSuccess('Student record deleted successfully.');

        } catch (Throwable $e) {
            $this->flashError('Failed to delete student: ' . $e->getMessage());
        }

        $this->redirect('/students');
    }

    public function promoteOrTransfer(): void
    {
        $this->requirePermission('students.edit');

        $studentId = (int)$_POST['student_id'];

        try {
            $this->db->beginTransaction();

            $this->db->execute(
                "UPDATE student_enrollments SET status = 'promoted'
                 WHERE student_id = :student_id AND status = 'active'",
                ['student_id' => $studentId]
            );

            $this->db->execute(
                "INSERT INTO student_enrollments
                (student_id, academic_year_id, class_id, stream_id, student_category_id, student_status_id, enrollment_date, status)
                VALUES
                (:student_id, :academic_year_id, :class_id, :stream_id, :category_id, :status_id, :enrollment_date, 'active')",
                [
                    'student_id'       => $studentId,
                    'academic_year_id' => (int)$_POST['academic_year_id'],
                    'class_id'         => (int)$_POST['class_id'],
                    'stream_id'        => !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null,
                    'category_id'      => (int)$_POST['category_id'],
                    'status_id'        => (int)$_POST['status_id'],
                    'enrollment_date'  => date('Y-m-d'),
                ]
            );

            $this->db->commit();
            $this->flashSuccess('Student enrollment updated successfully.');

        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->flashError('Enrollment update failed: ' . $e->getMessage());
        }

        $this->redirect('/students/show?id=' . $studentId);
    }

    public function enrollments(): void
    {
        $this->requirePermission('students.edit');

        $schoolId = $this->schoolId();

        $selectedYear  = (int)($_GET['academic_year_id'] ?? 0);
        $selectedClass = (int)($_GET['class_id'] ?? 0);
        $selectedTerm  = (int)($_GET['term_id'] ?? 0);

        $selectedTermName = '-';
        if ($selectedTerm > 0) {
            $row = $this->db->fetch(
                "SELECT name FROM terms WHERE id = :id AND school_id = :s",
                ['id' => $selectedTerm, 's' => $schoolId]
            );
            if ($row) {
                $selectedTermName = $row['name'];
            }
        }

        $where  = ["s.school_id = :s", "se.status = 'active'"];
        $params = ['s' => $schoolId];

        if ($selectedYear > 0) {
            $where[] = "se.academic_year_id = :year_id";
            $params['year_id'] = $selectedYear;
        }

        if ($selectedClass > 0) {
            $where[] = "se.class_id = :class_id";
            $params['class_id'] = $selectedClass;
        }

        $candidateStudents = $this->db->fetchAll(
            "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender,
                    cl.name AS class_name, str.name AS stream_name,
                    sc.name AS section_name, st.name AS status_name,
                    ay.name AS academic_year_name
             FROM student_enrollments se
             INNER JOIN students s            ON se.student_id = s.id
             LEFT JOIN classes cl             ON se.class_id = cl.id
             LEFT JOIN streams str            ON se.stream_id = str.id
             LEFT JOIN student_categories sc  ON se.student_category_id = sc.id
             LEFT JOIN student_statuses st    ON se.student_status_id = st.id
             LEFT JOIN academic_years ay      ON se.academic_year_id = ay.id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY s.last_name ASC, s.first_name ASC",
            $params
        );

        foreach ($candidateStudents as &$s) {
            $s['term_name'] = $selectedTermName;
        }

        echo $this->view->renderWithLayout('students/enrollment', 'default', [
            'academicYears'     => $this->db->fetchAll("SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC", ['s' => $schoolId]),
            'classes'           => $this->db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'terms'             => $this->db->fetchAll("SELECT id, name FROM terms WHERE school_id = :s ORDER BY term_number ASC", ['s' => $schoolId]),
            'streams'           => $this->db->fetchAll("SELECT id, name FROM streams WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]),
            'categories'        => $this->activeCategories($schoolId),
            'statuses'          => $this->activeStatuses($schoolId),
            'candidateStudents' => $candidateStudents,
            'selectedYear'      => $selectedYear,
            'selectedClass'     => $selectedClass,
            'selectedTerm'      => $selectedTerm,
        ]);
    }

    public function storeEnrollment(): void
    {
        $this->requirePermission('students.edit');

        $studentIds     = $_POST['student_ids'] ?? [];
        $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
        $classId        = (int)($_POST['class_id'] ?? 0);
        $enrollmentDate = $_POST['enrollment_date'] ?? date('Y-m-d');

        if (empty($studentIds) || !$academicYearId || !$classId) {
            $this->flashError('Please select an Academic Year, Class, and at least one student.');
            $this->redirect('/students/enrollments?academic_year_id=' . $academicYearId . '&class_id=' . $classId);
        }

        try {
            $this->db->beginTransaction();

            $enrolledCount = 0;
            foreach ($studentIds as $studentId) {
                $studentId = (int)$studentId;

                $lastEnrollment = $this->db->fetch(
                    "SELECT stream_id, student_category_id, student_status_id
                     FROM student_enrollments
                     WHERE student_id = :student_id
                     ORDER BY id DESC LIMIT 1",
                    ['student_id' => $studentId]
                );

                $streamId   = !empty($_POST['stream_id'])   ? (int)$_POST['stream_id']   : ($lastEnrollment['stream_id']           ?? null);
                $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : ($lastEnrollment['student_category_id'] ?? null);
                $statusId   = !empty($_POST['status_id'])   ? (int)$_POST['status_id']   : ($lastEnrollment['student_status_id']   ?? null);

                $this->db->execute(
                    "UPDATE student_enrollments SET status = 'completed'
                     WHERE student_id = :student_id AND status = 'active'",
                    ['student_id' => $studentId]
                );

                $this->db->execute(
                    "INSERT INTO student_enrollments
                    (student_id, academic_year_id, class_id, stream_id, student_category_id, student_status_id, enrollment_date, status)
                    VALUES
                    (:student_id, :academic_year_id, :class_id, :stream_id, :category_id, :status_id, :enrollment_date, 'active')",
                    [
                        'student_id'       => $studentId,
                        'academic_year_id' => $academicYearId,
                        'class_id'         => $classId,
                        'stream_id'        => $streamId,
                        'category_id'      => $categoryId,
                        'status_id'        => $statusId,
                        'enrollment_date'  => $enrollmentDate,
                    ]
                );
                $enrolledCount++;
            }

            $this->db->commit();
            $this->flashSuccess("Successfully enrolled {$enrolledCount} student(s).");

        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->flashError('Bulk enrollment failed: ' . $e->getMessage());
        }

        $this->redirect('/students/enrollments?academic_year_id=' . $academicYearId . '&class_id=' . $classId);
    }

    private function activeCategories(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT id, name FROM student_categories WHERE school_id = :s AND status = 'active'",
            ['s' => $schoolId]
        );
    }

    private function activeStatuses(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT id, name FROM student_statuses WHERE school_id = :s AND status = 'active'",
            ['s' => $schoolId]
        );
    }

    private function nullablePost(string $key): ?string
    {
        $value = trim($_POST[$key] ?? '');
        return $value === '' ? null : $value;
    }

    private function handlePhotoUpload(?string $existingPath = null): ?string
    {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return $existingPath;
        }

        $uploadDir = ROOT_PATH . '/public/uploads/students/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'student_' . time() . '_' . uniqid() . '.' . $ext;
        $target = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            return $existingPath;
        }

        if ($existingPath && file_exists(ROOT_PATH . '/public/' . $existingPath)) {
            @unlink(ROOT_PATH . '/public/' . $existingPath);
        }

        return 'uploads/students/' . $filename;
    }

    private function updateStudentRecord(int $id, int $schoolId, ?string $photoPath, int $newStatusId): void
    {
        $this->db->execute(
            "UPDATE students SET
                first_name          = :first_name,
                middle_name         = :middle_name,
                last_name           = :last_name,
                preferred_name      = :preferred_name,
                gender              = :gender,
                date_of_birth       = :date_of_birth,
                phone               = :phone,
                email               = :email,
                address             = :address,
                photo_path          = :photo_path,
                current_category_id = :category_id,
                current_status_id   = :status_id,
                admission_date      = :admission_date
             WHERE id = :id AND school_id = :school_id",
            [
                'first_name'     => trim($_POST['first_name']),
                'middle_name'    => $this->nullablePost('middle_name'),
                'last_name'      => trim($_POST['last_name']),
                'preferred_name' => $this->nullablePost('preferred_name'),
                'gender'         => $_POST['gender'],
                'date_of_birth'  => $this->nullablePost('date_of_birth'),
                'phone'          => $this->nullablePost('phone'),
                'email'          => $this->nullablePost('email'),
                'address'        => $this->nullablePost('address'),
                'photo_path'     => $photoPath,
                'category_id'    => (int)$_POST['category_id'],
                'status_id'      => $newStatusId,
                'admission_date' => $_POST['admission_date'],
                'id'             => $id,
                'school_id'      => $schoolId,
            ]
        );
    }

    private function updateEnrollmentOnClassChange(int $id, int $newStatusId): void
    {
        if (empty($_POST['class_id'])) {
            return;
        }

        $classId  = (int)$_POST['class_id'];
        $streamId = !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null;

        $active = $this->db->fetch(
            "SELECT * FROM student_enrollments
             WHERE student_id = :student_id AND status = 'active'
             ORDER BY id DESC LIMIT 1",
            ['student_id' => $id]
        );

        if (!$active) {
            return;
        }

        $classChanged  = (int)$active['class_id'] !== $classId;
        $streamChanged = (int)$active['stream_id'] !== $streamId;

        if (!$classChanged && !$streamChanged) {
            return;
        }

        $this->db->execute(
            "UPDATE student_enrollments SET status = 'transferred' WHERE id = :id",
            ['id' => $active['id']]
        );

        $this->db->execute(
            "INSERT INTO student_enrollments
            (student_id, academic_year_id, class_id, stream_id, student_category_id, student_status_id, enrollment_date, status)
            VALUES
            (:student_id, :academic_year_id, :class_id, :stream_id, :category_id, :status_id, :enrollment_date, 'active')",
            [
                'student_id'       => $id,
                'academic_year_id' => $active['academic_year_id'],
                'class_id'         => $classId,
                'stream_id'        => $streamId,
                'category_id'      => (int)$_POST['category_id'],
                'status_id'        => $newStatusId,
                'enrollment_date'  => date('Y-m-d'),
            ]
        );
    }

    private function recordStatusChange(int $studentId, int $oldStatusId, int $newStatusId, bool $statusChanged): void
    {
        if (!$statusChanged) {
            return;
        }

        $this->db->execute(
            "INSERT INTO student_status_history
            (student_id, old_status_id, new_status_id, changed_by, reason, created_at)
            VALUES
            (:student_id, :old_status, :new_status, :changed_by, :reason, NOW())",
            [
                'student_id' => $studentId,
                'old_status' => $oldStatusId,
                'new_status' => $newStatusId,
                'changed_by' => $this->auth->id(),
                'reason'     => 'Status updated via student edit form',
            ]
        );
    }

    private function upsertGuardian(int $studentId, int $schoolId): void
    {
        $guardianId   = (int)($_POST['guardian_id'] ?? 0);
        $name         = trim($_POST['guardian_name'] ?? '');
        $phone        = trim($_POST['guardian_phone'] ?? '');
        $occupation   = $this->nullablePost('guardian_occupation');
        $address      = $this->nullablePost('guardian_address');
        $relationship = trim($_POST['guardian_relationship'] ?? 'Parent');

        if ($guardianId > 0) {
            $this->db->execute(
                "UPDATE guardians SET full_name = :name, phone = :phone,
                        occupation = :occupation, address = :address
                 WHERE id = :id AND school_id = :school_id",
                [
                    'name'       => $name,
                    'phone'      => $phone,
                    'occupation' => $occupation,
                    'address'    => $address,
                    'id'         => $guardianId,
                    'school_id'  => $schoolId,
                ]
            );

            $this->db->execute(
                "UPDATE student_guardians SET relationship = :relationship
                 WHERE student_id = :student_id AND guardian_id = :guardian_id",
                [
                    'relationship' => $relationship,
                    'student_id'   => $studentId,
                    'guardian_id'  => $guardianId,
                ]
            );
            return;
        }

        if ($name === '' || $phone === '') {
            return;
        }

        $this->db->execute(
            "INSERT INTO guardians (school_id, full_name, phone, occupation, address, created_at)
             VALUES (:school_id, :name, :phone, :occupation, :address, NOW())",
            [
                'school_id'  => $schoolId,
                'name'       => $name,
                'phone'      => $phone,
                'occupation' => $occupation,
                'address'    => $address,
            ]
        );

        $newGuardianId = (int)$this->db->lastInsertId();

        $this->db->execute(
            "INSERT INTO student_guardians (student_id, guardian_id, relationship, is_primary)
             VALUES (:student_id, :guardian_id, :relationship, 1)",
            [
                'student_id'   => $studentId,
                'guardian_id'  => $newGuardianId,
                'relationship' => $relationship,
            ]
        );
    }
}