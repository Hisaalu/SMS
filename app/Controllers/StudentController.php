<?php
// File: /app/Controllers/StudentController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\StudentAdmissionService;
use NexaT\Services\StudentNumberGenerator;
use NexaT\Core\Database;
use Exception;

class StudentController extends Controller
{
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.view')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';

        $params = ['school_id' => $schoolId];
        $searchSql = "";

        if (!empty($search)) {
            $searchSql = " AND (s.first_name LIKE :s OR s.last_name LIKE :s OR s.admission_number LIKE :s)";
            $params['s'] = "%$search%";
        }

        $students = $db->fetchAll(
            "SELECT s.*, 
                    COALESCE(c.name, 'N/A') as category_name, 
                    COALESCE(st.name, 'Active') as status_name 
             FROM students s 
             LEFT JOIN student_categories c ON s.current_category_id = c.id 
             LEFT JOIN student_statuses st ON s.current_status_id = st.id 
             WHERE s.school_id = :school_id $searchSql 
             ORDER BY s.id DESC LIMIT $limit OFFSET $offset",
            $params
        );

        echo $this->view->renderWithLayout('students/index', 'default', [
            'students' => $students,
            'search'   => $search,
            'page'     => $page
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.create')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        // Auto-generate preview numbers
        $generator = new StudentNumberGenerator();
        $generatedAdmissionNo = $generator->generate($schoolId, 'admission');
        $generatedRegNo = $generator->generate($schoolId, 'registration');

        $categories = $db->fetchAll("SELECT id, name FROM student_categories WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $statuses   = $db->fetchAll("SELECT id, name FROM student_statuses WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $years      = $db->fetchAll("SELECT id, name FROM academic_years WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $classes    = $db->fetchAll("SELECT id, name FROM classes WHERE school_id = :school_id", ['school_id' => $schoolId]);

        echo $this->view->renderWithLayout('students/create', 'default', [
            'admission_number'    => $generatedAdmissionNo,
            'registration_number' => $generatedRegNo,
            'categories'          => $categories,
            'statuses'            => $statuses,
            'years'               => $years,
            'classes'             => $classes
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.create')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $generator = new StudentNumberGenerator();

        $regNumber = !empty($_POST['registration_number']) ? trim($_POST['registration_number']) : $generator->generate($schoolId, 'registration');
        $admNumber = !empty($_POST['admission_number']) ? trim($_POST['admission_number']) : $generator->generate($schoolId, 'admission');

        // Convert empty fields to NULL for SQL compatibility
        $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
        $middleName = !empty($_POST['middle_name']) ? trim($_POST['middle_name']) : null;
        $streamId = !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null;

        $photoPath = null;

        $studentData = [
            'school_id'           => (int)$schoolId,
            'registration_number' => $regNumber,
            'admission_number'    => $admNumber,
            'first_name'          => trim($_POST['first_name']),
            'middle_name'         => $middleName,
            'last_name'           => trim($_POST['last_name']),
            'gender'              => $_POST['gender'],
            'date_of_birth'       => $dob,
            'photo_path'          => $photoPath,
            'current_category_id' => (int)$_POST['category_id'],
            'current_status_id'   => (int)$_POST['status_id'],
            'admission_date'      => $_POST['admission_date']
        ];

        $guardianData = [
            'full_name'    => trim($_POST['guardian_name']),
            'phone'        => trim($_POST['guardian_phone']),
            'relationship' => $_POST['guardian_relationship'] ?? 'Parent'
        ];

        $enrollmentData = [
            'academic_year_id' => (int)$_POST['academic_year_id'],
            'class_id'         => (int)$_POST['class_id'],
            'stream_id'        => $streamId
        ];

        try {
            $service = new StudentAdmissionService();
            $service->admitStudent($studentData, $guardianData, $enrollmentData);

            $_SESSION['flash_success'] = 'Student admitted successfully.';
            header('Location: ' . BASE_URL . '/students');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Admission Failed: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/students/create');
            exit;
        }

        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/students/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'student_' . time() . '_' . uniqid() . '.' . $extension;
            $targetFile = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $photoPath = 'uploads/students/' . $filename;
            }
        }

        $studentData['photo_path'] = $photoPath;
    }

    public function show(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.view')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $schoolId = $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        try {
            // 1. Fetch Student Core Details
            $student = $db->fetch(
                "SELECT s.*, 
                        c.name as category_name, 
                        st.name as status_name
                FROM students s
                LEFT JOIN student_categories c ON s.current_category_id = c.id
                LEFT JOIN student_statuses st ON s.current_status_id = st.id
                WHERE s.id = :id AND s.school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );

            if (!$student) {
                $_SESSION['flash_error'] = 'Student record not found.';
                header('Location: ' . BASE_URL . '/students');
                exit;
            }

            // 2. Fetch Enrollment History safely
            $enrollments = $db->fetchAll(
                "SELECT se.*, 
                        ay.name as academic_year_name, 
                        cl.name as class_name, 
                        str.name as stream_name,
                        st.name as status_name
                FROM student_enrollments se
                LEFT JOIN academic_years ay ON se.academic_year_id = ay.id
                LEFT JOIN classes cl ON se.class_id = cl.id
                LEFT JOIN streams str ON se.stream_id = str.id
                LEFT JOIN student_statuses st ON se.student_status_id = st.id
                WHERE se.student_id = :student_id
                ORDER BY se.id DESC",
                ['student_id' => $id]
            );

            // 3. Fetch Guardians safely
            $guardians = $db->fetchAll(
                "SELECT g.*, sg.relationship 
                FROM guardians g
                INNER JOIN student_guardians sg ON g.id = sg.guardian_id
                WHERE sg.student_id = :student_id",
                ['student_id' => $id]
            );

            echo $this->view->renderWithLayout('students/show', 'default', [
                'student'     => $student,
                'enrollments' => $enrollments ?? [],
                'guardians'   => $guardians ?? []
            ]);

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Error loading profile: " . $e->getMessage();
            header('Location: ' . BASE_URL . '/students');
            exit;
        }
    }

    public function edit(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.edit')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $schoolId = $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        $student = $db->fetch(
            "SELECT * FROM students WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$student) {
            $_SESSION['flash_error'] = 'Student not found.';
            header('Location: ' . BASE_URL . '/students');
            exit;
        }

        // Fetch primary guardian record
        $primaryGuardian = $db->fetch(
            "SELECT g.*, sg.relationship, sg.is_primary 
            FROM guardians g
            INNER JOIN student_guardians sg ON g.id = sg.guardian_id
            WHERE sg.student_id = :student_id AND sg.is_primary = 1",
            ['student_id' => $id]
        );

        $categories = $db->fetchAll("SELECT id, name FROM student_categories WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $statuses   = $db->fetchAll("SELECT id, name FROM student_statuses WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);

        echo $this->view->renderWithLayout('students/edit', 'default', [
            'student'         => $student,
            'primaryGuardian' => $primaryGuardian,
            'categories'      => $categories,
            'statuses'        => $statuses
        ]);
    }

    public function update(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.edit')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $schoolId = $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        $existingStudent = $db->fetch(
            "SELECT * FROM students WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$existingStudent) {
            $_SESSION['flash_error'] = 'Student record not found.';
            header('Location: ' . BASE_URL . '/students');
            exit;
        }

        $photoPath = $existingStudent['photo_path'];

        // Process photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/students/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'student_' . time() . '_' . uniqid() . '.' . $extension;
            $targetFile = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                if (!empty($existingStudent['photo_path']) && file_exists(__DIR__ . '/../../public/' . $existingStudent['photo_path'])) {
                    unlink(__DIR__ . '/../../public/' . $existingStudent['photo_path']);
                }
                $photoPath = 'uploads/students/' . $filename;
            }
        }

        try {
            $db->beginTransaction();

            // 1. Update Student Table
            $db->execute(
                "UPDATE students SET
                    first_name          = :first_name,
                    middle_name         = :middle_name,
                    last_name           = :last_name,
                    gender              = :gender,
                    date_of_birth       = :date_of_birth,
                    photo_path          = :photo_path,
                    current_category_id = :category_id,
                    current_status_id   = :status_id,
                    admission_date      = :admission_date
                WHERE id = :id AND school_id = :school_id",
                [
                    'first_name'     => trim($_POST['first_name']),
                    'middle_name'    => !empty($_POST['middle_name']) ? trim($_POST['middle_name']) : null,
                    'last_name'      => trim($_POST['last_name']),
                    'gender'         => $_POST['gender'],
                    'date_of_birth'  => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                    'photo_path'     => $photoPath,
                    'category_id'   => (int)$_POST['category_id'],
                    'status_id'     => (int)$_POST['status_id'],
                    'admission_date' => $_POST['admission_date'],
                    'id'             => $id,
                    'school_id'      => $schoolId
                ]
            );

            // 2. Update or Insert Primary Guardian
            $guardianId = isset($_POST['guardian_id']) ? (int)$_POST['guardian_id'] : 0;
            $guardianName = trim($_POST['guardian_name']);
            $guardianPhone = trim($_POST['guardian_phone']);
            $relationship = trim($_POST['guardian_relationship']);

            if ($guardianId > 0) {
                // Update existing guardian info
                $db->execute(
                    "UPDATE guardians SET full_name = :full_name, phone = :phone WHERE id = :id AND school_id = :school_id",
                    [
                        'full_name' => $guardianName,
                        'phone'     => $guardianPhone,
                        'id'        => $guardianId,
                        'school_id' => $schoolId
                    ]
                );

                // Update relationship link
                $db->execute(
                    "UPDATE student_guardians SET relationship = :relationship WHERE student_id = :student_id AND guardian_id = :guardian_id",
                    [
                        'relationship' => $relationship,
                        'student_id'   => $id,
                        'guardian_id'  => $guardianId
                    ]
                );
            } else if (!empty($guardianName) && !empty($guardianPhone)) {
                // Create new guardian record if missing
                $db->execute(
                    "INSERT INTO guardians (school_id, full_name, phone, created_at) VALUES (:school_id, :full_name, :phone, NOW())",
                    [
                        'school_id' => $schoolId,
                        'full_name' => $guardianName,
                        'phone'     => $guardianPhone
                    ]
                );
                $newGuardianId = $db->lastInsertId();

                $db->execute(
                    "INSERT INTO student_guardians (student_id, guardian_id, relationship, is_primary) VALUES (:student_id, :guardian_id, :relationship, 1)",
                    [
                        'student_id'   => $id,
                        'guardian_id'  => $newGuardianId,
                        'relationship' => $relationship
                    ]
                );
            }

            $db->commit();

            $_SESSION['flash_success'] = 'Student profile updated successfully.';
            header('Location: ' . BASE_URL . '/students/show?id=' . $id);
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Update Failed: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/students/edit?id=' . $id);
            exit;
        }
    }
}