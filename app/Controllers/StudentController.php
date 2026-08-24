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

        // 1. Capture Filters & Pagination Inputs
        $search         = trim($_GET['search'] ?? '');
        $academicYearId = (int)($_GET['academic_year_id'] ?? 0);
        $classId        = (int)($_GET['class_id'] ?? 0);
        $streamId       = (int)($_GET['stream_id'] ?? 0);
        $categoryId     = (int)($_GET['category_id'] ?? 0);
        $statusId       = (int)($_GET['status_id'] ?? 0);

        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        // 2. Build Dynamic Query Conditions
        $whereConditions = ["s.school_id = :school_id"];
        $params = ['school_id' => $schoolId];

        if (!empty($search)) {
            // Use distinct parameter keys for each column check to prevent PDO HY093 parameter mismatch
            $whereConditions[] = "(s.first_name LIKE :search1 OR s.last_name LIKE :search2 OR s.admission_number LIKE :search3 OR s.registration_number LIKE :search4)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
        }

        if ($academicYearId > 0) {
            $whereConditions[] = "se.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = $academicYearId;
        }

        if ($classId > 0) {
            $whereConditions[] = "se.class_id = :class_id";
            $params['class_id'] = $classId;
        }

        if ($streamId > 0) {
            $whereConditions[] = "se.stream_id = :stream_id";
            $params['stream_id'] = $streamId;
        }

        if ($categoryId > 0) {
            $whereConditions[] = "se.student_category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if ($statusId > 0) {
            $whereConditions[] = "se.student_status_id = :status_id";
            $params['status_id'] = $statusId;
        }

        $whereClause = "WHERE " . implode(" AND ", $whereConditions);

        // 3. Count Total Records for Pagination
        $countSql = "SELECT COUNT(DISTINCT s.id) as total
                     FROM students s
                     LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
                     {$whereClause}";
        $totalResult = $db->fetch($countSql, $params);
        $totalStudents = $totalResult['total'] ?? 0;
        $totalPages = max(1, ceil($totalStudents / $limit));

        // 4. Fetch Filtered Students with Current Enrollment Details
        $studentsSql = "SELECT 
                            s.id,
                            s.admission_number,
                            s.registration_number,
                            s.first_name,
                            s.last_name,
                            s.gender,
                            cl.name as class_name,
                            st.name as stream_name,
                            sc.name as category_name,
                            ss.name as status_name
                        FROM students s
                        LEFT JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
                        LEFT JOIN classes cl ON se.class_id = cl.id
                        LEFT JOIN streams st ON se.stream_id = st.id
                        LEFT JOIN student_categories sc ON se.student_category_id = sc.id
                        LEFT JOIN student_statuses ss ON se.student_status_id = ss.id
                        {$whereClause}
                        ORDER BY s.id DESC
                        LIMIT {$limit} OFFSET {$offset}";

        $students = $db->fetchAll($studentsSql, $params);

        // 5. Load Dropdown Metadata
        $academicYears = $db->fetchAll("SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC", ['s' => $schoolId]);
        $classes       = $db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]);
        $streams       = $db->fetchAll("SELECT id, name, class_id FROM streams WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]);
        $categories    = $db->fetchAll("SELECT id, name FROM student_categories WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]);
        $statuses      = $db->fetchAll("SELECT id, name FROM student_statuses WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]);

        echo $this->view->renderWithLayout('students/index', 'default', [
            'students'       => $students,
            'academicYears'  => $academicYears,
            'classes'        => $classes,
            'streams'        => $streams,
            'categories'     => $categories,
            'statuses'       => $statuses,
            'search'         => $search,
            'academicYearId' => $academicYearId,
            'classId'        => $classId,
            'streamId'       => $streamId,
            'categoryId'     => $categoryId,
            'statusId'       => $statusId,
            'page'           => $page,
            'totalPages'     => $totalPages,
            'totalStudents'  => $totalStudents
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

        $generator = new StudentNumberGenerator();
        $generatedAdmissionNo = $generator->generate($schoolId, 'admission');
        $generatedRegNo = $generator->generate($schoolId, 'registration');

        $categories = $db->fetchAll("SELECT id, name FROM student_categories WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $statuses   = $db->fetchAll("SELECT id, name FROM student_statuses WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $years      = $db->fetchAll("SELECT id, name FROM academic_years WHERE school_id = :school_id AND status = 'active'", ['school_id' => $schoolId]);
        $classes    = $db->fetchAll("SELECT id, name FROM classes WHERE school_id = :school_id", ['school_id' => $schoolId]);
        $streams    = $db->fetchAll("SELECT * FROM streams WHERE school_id = :s", ['s' => $schoolId]);

        echo $this->view->renderWithLayout('students/create', 'default', [
            'admission_number'    => $generatedAdmissionNo,
            'registration_number' => $generatedRegNo,
            'categories'          => $categories,
            'statuses'            => $statuses,
            'years'               => $years,
            'classes'             => $classes,
            'streams'             => $streams
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

        $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
        $streamId = !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null;

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

        $studentData = [
            'school_id'           => (int)$schoolId,
            'registration_number' => $regNumber,
            'admission_number'    => $admNumber,
            'first_name'          => trim($_POST['first_name']),
            'middle_name'         => !empty($_POST['middle_name']) ? trim($_POST['middle_name']) : null,
            'last_name'           => trim($_POST['last_name']),
            'preferred_name'      => !empty($_POST['preferred_name']) ? trim($_POST['preferred_name']) : null,
            'gender'              => $_POST['gender'],
            'date_of_birth'       => $dob,
            'phone'               => !empty($_POST['phone']) ? trim($_POST['phone']) : null,
            'email'               => !empty($_POST['email']) ? trim($_POST['email']) : null,
            'address'             => !empty($_POST['address']) ? trim($_POST['address']) : null,
            'photo_path'          => $photoPath,
            'current_category_id' => (int)$_POST['category_id'],
            'current_status_id'   => (int)$_POST['status_id'],
            'admission_date'      => $_POST['admission_date']
        ];

        $guardianData = [
            'full_name'    => trim($_POST['guardian_name']),
            'phone'        => trim($_POST['guardian_phone']),
            'occupation'   => !empty($_POST['guardian_occupation']) ? trim($_POST['guardian_occupation']) : null,
            'address'      => !empty($_POST['guardian_address']) ? trim($_POST['guardian_address']) : null,
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
            "SELECT 
                id, school_id, registration_number, admission_number, admission_date,
                first_name, middle_name, last_name, preferred_name,
                gender, date_of_birth, phone, email, address, photo_path,
                current_category_id, current_status_id, created_at
            FROM students 
            WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$student) {
            $_SESSION['flash_error'] = 'Student not found.';
            header('Location: ' . BASE_URL . '/students');
            exit;
        }

        $primaryGuardian = $db->fetch(
            "SELECT g.*, sg.relationship, sg.is_primary 
            FROM guardians g
            INNER JOIN student_guardians sg ON g.id = sg.guardian_id
            WHERE sg.student_id = :student_id AND sg.is_primary = 1",
            ['student_id' => $id]
        );

        $currentEnrollment = $db->fetch(
            "SELECT * FROM student_enrollments 
            WHERE student_id = :student_id AND status = 'active' 
            ORDER BY id DESC LIMIT 1",
            ['student_id' => $id]
        );

        $categories = $db->fetchAll("SELECT id, name FROM student_categories WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]);
        $statuses   = $db->fetchAll("SELECT id, name FROM student_statuses WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]);
        $classes    = $db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s", ['s' => $schoolId]);
        $streams    = $db->fetchAll("SELECT id, name FROM streams WHERE school_id = :s", ['s' => $schoolId]);

        echo $this->view->renderWithLayout('students/edit', 'default', [
            'student'           => $student,
            'primaryGuardian'   => $primaryGuardian,
            'currentEnrollment' => $currentEnrollment,
            'categories'        => $categories,
            'statuses'          => $statuses,
            'classes'           => $classes,
            'streams'           => $streams
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

        // 1. Fetch Existing Record First (Fixes "Undefined variable $existingStudent")
        $existingStudent = $db->fetch(
            "SELECT * FROM students WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$existingStudent) {
            $_SESSION['flash_error'] = 'Student record not found.';
            header('Location: ' . BASE_URL . '/students');
            exit;
        }

        // 2. Track Status Changes for Audit Logging (Issue 8.9)
        $oldStatusId = (int)$existingStudent['current_status_id'];
        $newStatusId = (int)$_POST['status_id'];
        $statusChanged = ($oldStatusId !== $newStatusId);

        // 3. Handle Photo Upload
        $photoPath = $existingStudent['photo_path'];

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

            // 4. Update Student Details
            $db->execute(
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
                    'middle_name'    => !empty($_POST['middle_name']) ? trim($_POST['middle_name']) : null,
                    'last_name'      => trim($_POST['last_name']),
                    'preferred_name' => !empty($_POST['preferred_name']) ? trim($_POST['preferred_name']) : null,
                    'gender'         => $_POST['gender'],
                    'date_of_birth'  => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
                    'phone'          => !empty($_POST['phone']) ? trim($_POST['phone']) : null,
                    'email'          => !empty($_POST['email']) ? trim($_POST['email']) : null,
                    'address'        => !empty($_POST['address']) ? trim($_POST['address']) : null,
                    'photo_path'     => $photoPath,
                    'category_id'   => (int)$_POST['category_id'],
                    'status_id'     => $newStatusId,
                    'admission_date' => $_POST['admission_date'],
                    'id'             => $id,
                    'school_id'      => $schoolId
                ]
            );

            // 5. Preserving Enrollment History on Class/Stream Change (Issue 8.8)
            if (!empty($_POST['class_id'])) {
                $classId = (int)$_POST['class_id'];
                $streamId = !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null;

                $activeEnrollment = $db->fetch(
                    "SELECT * FROM student_enrollments WHERE student_id = :student_id AND status = 'active' ORDER BY id DESC LIMIT 1",
                    ['student_id' => $id]
                );

                if ($activeEnrollment) {
                    if ((int)$activeEnrollment['class_id'] !== $classId || (int)$activeEnrollment['stream_id'] !== $streamId) {
                        // Mark current enrollment inactive (transferred)
                        $db->execute(
                            "UPDATE student_enrollments SET status = 'transferred' WHERE id = :id",
                            ['id' => $activeEnrollment['id']]
                        );

                        // Insert new active enrollment record
                        $db->execute(
                            "INSERT INTO student_enrollments 
                            (student_id, academic_year_id, class_id, stream_id, student_category_id, student_status_id, enrollment_date, status) 
                            VALUES 
                            (:student_id, :academic_year_id, :class_id, :stream_id, :category_id, :status_id, :enrollment_date, 'active')",
                            [
                                'student_id'       => $id,
                                'academic_year_id' => $activeEnrollment['academic_year_id'],
                                'class_id'         => $classId,
                                'stream_id'        => $streamId,
                                'category_id'      => (int)$_POST['category_id'],
                                'status_id'        => $newStatusId,
                                'enrollment_date'  => date('Y-m-d')
                            ]
                        );
                    }
                }
            }

            // 6. Audit Log Status Change (Issue 8.9)
            if ($statusChanged) {
                $db->execute(
                    "INSERT INTO student_status_history 
                    (student_id, old_status_id, new_status_id, changed_by, reason, created_at) 
                    VALUES 
                    (:student_id, :old_status, :new_status, :changed_by, :reason, NOW())",
                    [
                        'student_id' => $id,
                        'old_status' => $oldStatusId,
                        'new_status' => $newStatusId,
                        'changed_by' => $this->auth->getUser()->id,
                        'reason'     => 'Status updated via student edit form'
                    ]
                );
            }

            // 7. Update Primary Guardian
            $guardianId = isset($_POST['guardian_id']) ? (int)$_POST['guardian_id'] : 0;
            $guardianName = trim($_POST['guardian_name']);
            $guardianPhone = trim($_POST['guardian_phone']);
            $guardianOccupation = !empty($_POST['guardian_occupation']) ? trim($_POST['guardian_occupation']) : null;
            $guardianAddress = !empty($_POST['guardian_address']) ? trim($_POST['guardian_address']) : null;
            $relationship = trim($_POST['guardian_relationship']);

            if ($guardianId > 0) {
                $db->execute(
                    "UPDATE guardians SET full_name = :full_name, phone = :phone, occupation = :occupation, address = :address WHERE id = :id AND school_id = :school_id",
                    [
                        'full_name'  => $guardianName,
                        'phone'      => $guardianPhone,
                        'occupation' => $guardianOccupation,
                        'address'    => $guardianAddress,
                        'id'         => $guardianId,
                        'school_id'  => $schoolId
                    ]
                );

                $db->execute(
                    "UPDATE student_guardians SET relationship = :relationship WHERE student_id = :student_id AND guardian_id = :guardian_id",
                    [
                        'relationship' => $relationship,
                        'student_id'   => $id,
                        'guardian_id'  => $guardianId
                    ]
                );
            } else if (!empty($guardianName) && !empty($guardianPhone)) {
                $db->execute(
                    "INSERT INTO guardians (school_id, full_name, phone, occupation, address, created_at) VALUES (:school_id, :full_name, :phone, :occupation, :address, NOW())",
                    [
                        'school_id'  => $schoolId,
                        'full_name'  => $guardianName,
                        'phone'      => $guardianPhone,
                        'occupation' => $guardianOccupation,
                        'address'    => $guardianAddress
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

    public function delete(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.delete')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $schoolId = $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        try {
            $db->execute(
                "DELETE FROM students WHERE id = :id AND school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );

            $_SESSION['flash_success'] = 'Student record deleted successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to delete student: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/students');
        exit;
    }

    public function promoteOrTransfer(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.edit')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $studentId = (int)$_POST['student_id'];
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            // 1. Mark previous active enrollment as completed or promoted
            $db->execute(
                "UPDATE student_enrollments 
                SET status = 'promoted' 
                WHERE student_id = :student_id AND status = 'active'",
                ['student_id' => $studentId]
            );

            // 2. Insert new active enrollment record
            $db->execute(
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
                    'enrollment_date'  => date('Y-m-d')
                ]
            );

            $db->commit();
            $_SESSION['flash_success'] = 'Student enrollment updated successfully.';
        } catch (\PDOException $e) {
            $db->rollBack();
            if ($e->getCode() == 23000) {
                $_SESSION['flash_error'] = 'Duplicate enrollment error: An active enrollment already exists for this student.';
            } else {
                $_SESSION['flash_error'] = 'Enrollment update failed: ' . $e->getMessage();
            }
        }

        header('Location: ' . BASE_URL . '/students/show?id=' . $studentId);
        exit;
    }

    public function enrollments(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.edit')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        // Selected filters for student lookup
        $selectedYear   = isset($_GET['academic_year_id']) ? (int)$_GET['academic_year_id'] : 0;
        $selectedClass  = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
        $filterStatus   = $_GET['filter'] ?? 'unenrolled'; // 'unenrolled' or 'previous_class'
        $previousClassId = isset($_GET['previous_class_id']) ? (int)$_GET['previous_class_id'] : 0;

        // Metadata dropdowns
        $academicYears = $db->fetchAll("SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC", ['s' => $schoolId]);
        $terms = $db->fetchAll("SELECT id, name FROM terms WHERE school_id = :s ORDER BY id ASC", ['s' => $schoolId]);
        $classes       = $db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC", ['s' => $schoolId]);
        $streams       = $db->fetchAll("SELECT id, name, class_id FROM streams WHERE school_id = :s", ['s' => $schoolId]);
        $categories    = $db->fetchAll("SELECT id, name FROM student_categories WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]);
        $statuses      = $db->fetchAll("SELECT id, name FROM student_statuses WHERE school_id = :s AND status = 'active'", ['s' => $schoolId]);

        // Load candidates for batch enrollment
        $candidateStudents = [];
        if ($filterStatus === 'previous_class' && $previousClassId > 0) {
            // Get students currently enrolled in the previous selected class
            $candidateStudents = $db->fetchAll(
                "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender, cl.name as current_class
                 FROM students s
                 INNER JOIN student_enrollments se ON se.student_id = s.id AND se.status = 'active'
                 INNER JOIN classes cl ON se.class_id = cl.id
                 WHERE s.school_id = :s AND se.class_id = :prev_class_id
                 ORDER BY s.first_name ASC",
                ['s' => $schoolId, 'prev_class_id' => $previousClassId]
            );
        } else {
            // Get all active students without an active enrollment in the selected academic year
            $candidateStudents = $db->fetchAll(
                "SELECT s.id, s.admission_number, s.first_name, s.last_name, s.gender, 'Unassigned' as current_class
                 FROM students s
                 WHERE s.school_id = :s 
                 AND s.id NOT IN (
                     SELECT student_id FROM student_enrollments WHERE academic_year_id = :year_id AND status = 'active'
                 )
                 ORDER BY s.first_name ASC",
                ['s' => $schoolId, 'year_id' => $selectedYear]
            );
        }

        echo $this->view->renderWithLayout('students/enrollment', 'default', [
            'academicYears'     => $academicYears,
            'terms'             => $terms,
            'classes'           => $classes,
            'streams'           => $streams,
            'categories'        => $categories,
            'statuses'          => $statuses,
            'candidateStudents' => $candidateStudents,
            'selectedYear'      => $selectedYear,
            'selectedClass'     => $selectedClass,
            'previousClassId'   => $previousClassId,
            'filterStatus'      => $filterStatus
        ]);
    }

    public function storeEnrollment(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('students.edit')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $studentIds     = $_POST['student_ids'] ?? [];
        $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
        $classId        = (int)($_POST['class_id'] ?? 0);
        $streamId       = !empty($_POST['stream_id']) ? (int)$_POST['stream_id'] : null;
        $categoryId     = (int)($_POST['category_id'] ?? 0);
        $statusId       = (int)($_POST['status_id'] ?? 0);
        $enrollmentDate = $_POST['enrollment_date'] ?? date('Y-m-d');

        if (empty($studentIds) || !$academicYearId || !$classId) {
            $_SESSION['flash_error'] = 'Please select an Academic Year, Class, and at least one student.';
            header('Location: ' . BASE_URL . '/students/enrollments');
            exit;
        }
        $db = Database::getInstance();

        try {
            $db->beginTransaction();

            $enrolledCount = 0;
            foreach ($studentIds as $studentId) {
                $studentId = (int)$studentId;

                // Deactivate previous active enrollment
                $db->execute(
                    "UPDATE student_enrollments SET status = 'completed' WHERE student_id = :student_id AND status = 'active'",
                    ['student_id' => $studentId]
                );

                // Insert new enrollment (without term_id)
                $db->execute(
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
                        'enrollment_date'  => $enrollmentDate
                    ]
                );
                $enrolledCount++;
            }

            $db->commit();
            $_SESSION['flash_success'] = "Successfully enrolled {$enrolledCount} student(s).";
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Bulk enrollment failed: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/students/enrollments');
        exit;
    }
}