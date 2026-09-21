<?php
// File: /app/Controllers/StaffController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\StaffService;

class StaffController extends Controller
{
    private $staffService;
    
    public function __construct()
    {
        parent::__construct();
        $this->staffService = new StaffService();
    }
    
    public function index(): void
    {
        // Check if user is logged in
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;
        
        // Get filters from request - ensure they're properly set
        $filters = [
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'category_id' => isset($_GET['category_id']) ? trim($_GET['category_id']) : '',
            'status_id' => isset($_GET['status_id']) ? trim($_GET['status_id']) : '',
            'department_id' => isset($_GET['department_id']) ? trim($_GET['department_id']) : ''
        ];
        
        // Remove empty filters but keep search even if empty
        $filters = array_filter($filters, function($value) {
            return $value !== '';
        });

        try {
            $staffMembers = $this->staffService->getStaffMembers($schoolId, $filters);
            $categories = $this->staffService->getCategories($schoolId);
            $statuses = $this->staffService->getStatuses($schoolId);
            $departments = $this->staffService->getDepartments($schoolId);
        } catch (\Exception $e) {
            error_log("Staff error: " . $e->getMessage());
            $staffMembers = [];
            $categories = [];
            $statuses = [];
            $departments = [];
        }

        echo $this->view->renderWithLayout('staff/index', 'default', [
            'title' => 'Staff Directory',
            'staffMembers' => $staffMembers,
            'categories' => $categories,
            'statuses' => $statuses,
            'departments' => $departments
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;

        try {
            $categories         = $this->staffService->getCategories($schoolId);
            $statuses           = $this->staffService->getStatuses($schoolId);
            $departments        = $this->staffService->getDepartments($schoolId);
            $suggestedStaffNum  = $this->staffService->generateStaffNumber($schoolId);
            
            // Fetch roles and other related data
            $roles              = $this->db->fetchAll("SELECT * FROM roles WHERE status = 'active' ORDER BY name ASC");
            $classes            = $this->db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s ORDER BY name", ['s' => $schoolId]);
            $streams            = $this->db->fetchAll("SELECT id, name FROM streams WHERE school_id = :s ORDER BY name", ['s' => $schoolId]);
            $subjects           = $this->db->fetchAll("SELECT id, name FROM subjects WHERE school_id = :s ORDER BY name", ['s' => $schoolId]);
            
            // NEW: Fetch assignment types so we can pass them to the form
            $assignmentTypes    = $this->db->fetchAll("SELECT id, name FROM teacher_assignment_types WHERE school_id = :s OR school_id IS NULL ORDER BY name", ['s' => $schoolId]);

        } catch (\Exception $e) {
            $categories = $statuses = $departments = $classes = $streams = $subjects = $assignmentTypes = [];
            $roles = [];
            $suggestedStaffNum = 'STF-0001';
        }

        echo $this->view->renderWithLayout('staff/create', 'default', [
            'title'             => 'Register New Staff',
            'categories'        => $categories,
            'statuses'          => $statuses,
            'departments'       => $departments,
            'suggestedStaffNum' => $suggestedStaffNum,
            'roles'             => $roles,
            'classes'           => $classes,
            'streams'           => $streams,
            'subjects'          => $subjects,
            'assignmentTypes'   => $assignmentTypes, // Pass to view
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;

        // ---- Collect form data ----
        $formData = [
            'staff_number'      => trim($_POST['staff_number'] ?? ''),
            'first_name'        => trim($_POST['first_name'] ?? ''),
            'middle_name'       => trim($_POST['middle_name'] ?? ''),
            'last_name'         => trim($_POST['last_name'] ?? ''),
            'gender'            => $_POST['gender'] ?? 'male',
            'date_of_birth'     => $_POST['date_of_birth'] ?? '',
            'marital_status'    => $_POST['marital_status'] ?? 'single',
            'phone'             => trim($_POST['phone'] ?? ''),
            'alt_phone'         => trim($_POST['alt_phone'] ?? ''),
            'email'             => trim($_POST['email'] ?? ''),
            'address'           => trim($_POST['address'] ?? ''),
            'staff_category_id' => $_POST['staff_category_id'] ?? '',
            'staff_status_id'   => $_POST['staff_status_id'] ?? '',
            'department_id'     => $_POST['department_id'] ?? '',
            'position'          => trim($_POST['position'] ?? ''),
            'employment_date'   => $_POST['employment_date'] ?? '',
            'employment_type'   => $_POST['employment_type'] ?? 'full_time',
            'username'          => trim($_POST['username'] ?? ''),
            'login_email'       => trim($_POST['login_email'] ?? ''),
        ];

        $createLogin = !empty($_POST['create_login']);
        $password    = $_POST['password']         ?? '';
        $passwordC   = $_POST['password_confirm'] ?? '';
        $roleId      = (int)($_POST['role_id'] ?? 0);
        $assignments = $_POST['assignments'] ?? [];

        $_SESSION['staff_form_data'] = $formData;

        // ---- Validate ----
        $errors = [];
        if (empty($formData['first_name']))          $errors[] = 'First name is required.';
        if (empty($formData['last_name']))           $errors[] = 'Last name is required.';
        if (empty($formData['staff_category_id']))   $errors[] = 'Staff category is required.';
        if (empty($formData['staff_status_id']))     $errors[] = 'Staff status is required.';

        if ($createLogin) {
            if (empty($formData['username']))          $errors[] = 'Username is required.';
            if (empty($formData['login_email']))       $errors[] = 'Login email is required.';
            if (strlen($password) < 8)                 $errors[] = 'Password must be at least 8 characters.';
            if ($password !== $passwordC)              $errors[] = 'Passwords do not match.';
            if (!$roleId)                              $errors[] = 'Please select a role.';

            if (!empty($formData['username'])
                && !$this->staffService->checkUsernameAvailability($formData['username'], $schoolId)) {
                $errors[] = "Username '{$formData['username']}' is already taken.";
            }

            if (!empty($formData['login_email'])
                && \NexaT\Models\User::firstWhere('email', $formData['login_email'])) {
                $errors[] = "A user with email '{$formData['login_email']}' already exists.";
            }
        }

        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: ' . BASE_URL . '/staff/create');
            exit;
        }

        // ---- Save in transaction ----
        $db = \NexaT\Core\Database::getInstance();
        try {
            $db->beginTransaction();

            // Photo upload
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $dir = ROOT_PATH . '/public/uploads/staff/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $ext      = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'staff_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $filename)) {
                    $photoPath = 'uploads/staff/' . $filename;
                }
            }

            // Create user account
            $userId = null;
            if ($createLogin) {
                $userId = $db->insert('users', [
                    'school_id'  => $schoolId,
                    'username'   => $formData['username'],
                    'email'      => $formData['login_email'],
                    'password'   => password_hash($password, PASSWORD_BCRYPT),
                    'first_name' => $formData['first_name'],
                    'last_name'  => $formData['last_name'],
                    'status'     => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                if (!$userId) {
                    throw new \Exception("Failed to create user account.");
                }
                if ($roleId) {
                    $db->insert('user_roles', [
                        'user_id'    => $userId,
                        'role_id'    => $roleId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Create staff record
            $staffId = $this->staffService->createStaff([
                'staff_number'    => $formData['staff_number'],
                'username'        => $formData['username'] ?: null,
                'user_id'         => $userId,
                'first_name'      => $formData['first_name'],
                'middle_name'     => $formData['middle_name'] ?: null,
                'last_name'       => $formData['last_name'],
                'gender'          => $formData['gender'],
                'dob'             => $formData['date_of_birth'] ?: null,
                'marital_status'  => $formData['marital_status'],
                'phone'           => $formData['phone'] ?: null,
                'alt_phone'       => $formData['alt_phone'] ?: null,
                'email'           => $formData['email'] ?: null,
                'address'         => $formData['address'] ?: null,
                'photo_path'      => $photoPath,
                'category_id'     => (int)$formData['staff_category_id'],
                'status_id'       => (int)$formData['staff_status_id'],
                'department_id'   => $formData['department_id'] ? (int)$formData['department_id'] : null,
                'position'        => $formData['position'] ?: null,
                'emp_date'        => $formData['employment_date'] ?: null,
                'emp_type'        => $formData['employment_type'],
            ], $schoolId, $this->auth->id());

            if (!empty($assignments) && is_array($assignments)) {
                // Fallback assignment type if none provided
                $defaultType = $db->fetch(
                    "SELECT id FROM teacher_assignment_types WHERE school_id = :s OR school_id IS NULL ORDER BY id ASC LIMIT 1",
                    ['s' => $schoolId]
                );
                $defaultTypeId = $defaultType['id'] ?? null;
                
                foreach ($assignments as $a) {
                    if (empty($a['class_id']) || empty($a['subject_id'])) continue;
                    
                    $typeId = !empty($a['assignment_type_id']) 
                        ? (int)$a['assignment_type_id'] 
                        : $defaultTypeId;
                    
                    if (!$typeId) continue; // Skip if no type available at all
                    
                    $db->insert('teacher_assignments', [
                        'school_id'            => $schoolId,
                        'staff_id'             => $staffId,
                        'class_id'             => (int)$a['class_id'],
                        'stream_id'            => !empty($a['stream_id']) ? (int)$a['stream_id'] : null,
                        'subject_id'           => (int)$a['subject_id'],
                        'assignment_type_id'   => $typeId,
                        'status'               => 'active',
                        'created_at'           => date('Y-m-d H:i:s'),
                        'updated_at'           => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $db->commit();
            unset($_SESSION['staff_form_data']);
            $_SESSION['flash_success'] = 'Staff member registered successfully.';
            header('Location: ' . BASE_URL . '/staff/show?id=' . $staffId);
            exit;

        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['flash_error'] = 'Failed to create staff: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/staff/create');
            exit;
        }
    }

    public function show(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;
        $id = (int)($_GET['id'] ?? 0);

        try {
            $staff              = $this->staffService->getStaffMember($id, $schoolId);
            $statusHistory      = $this->staffService->getStatusHistory($id);
            $statuses           = $this->staffService->getStatuses($schoolId);
            $unlinkedUsers      = $this->staffService->getUnlinkedUsers($schoolId);
            $teacherAssignments = $this->staffService->getTeacherAssignments($id, $schoolId);
            $linkedUser         = $this->staffService->getLinkedUser($id, $schoolId);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error loading staff: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/staff');
            exit;
        }

        if (!$staff) {
            $_SESSION['flash_error'] = 'Staff member not found.';
            header('Location: ' . BASE_URL . '/staff');
            exit;
        }

        // Enrich staff array with linked user info
        if ($linkedUser) {
            $staff['user_id']            = $linkedUser['id'];
            $staff['user_username']      = $linkedUser['username'];
            $staff['user_account_email'] = $linkedUser['email'];
            $staff['user_role_names']    = $linkedUser['role_names'] ?? '-';
            $staff['user_status']        = $linkedUser['status'] ?? 'active';
        }

        echo $this->view->renderWithLayout('staff/show', 'default', [
            'title'              => $staff['first_name'] . ' ' . $staff['last_name'],
            'staff'              => $staff,
            'statusHistory'      => $statusHistory,
            'statuses'           => $statuses,
            'unlinkedUsers'      => $unlinkedUsers,
            'teacherAssignments' => $teacherAssignments,
        ]);
    }

    public function changeStatus(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.change_status')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;
        $staffId = (int)($_POST['staff_id'] ?? 0);
        $newStatusId = (int)($_POST['staff_status_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');

        try {
            $updated = $this->staffService->updateStatus($staffId, $newStatusId, $schoolId, $this->auth->id(), $reason, $effectiveDate);
            if ($updated) {
                $_SESSION['flash_success'] = 'Staff status updated successfully.';
            } else {
                $_SESSION['flash_error'] = 'Status is the same or staff not found.';
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error updating status: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/staff/show?id=' . $staffId);
        exit;
    }

    public function linkAccount(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.manage_accounts')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;
        $staffId = (int)($_POST['staff_id'] ?? 0);
        $userId = (int)($_POST['user_id'] ?? 0);

        try {
            $this->staffService->linkAccount($staffId, $userId, $schoolId);
            $_SESSION['flash_success'] = 'System user account linked successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to link account: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/staff/show?id=' . $staffId);
        exit;
    }

    public function unlinkAccount(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.manage_accounts')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;
        $staffId = (int)($_POST['staff_id'] ?? 0);

        try {
            $this->staffService->unlinkAccount($staffId, $schoolId);
            $_SESSION['flash_success'] = 'System account unlinked successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to unlink account: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/staff/show?id=' . $staffId);
        exit;
    }

    public function edit(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;
        $id = (int)($_GET['id'] ?? 0);

        $staff = $this->staffService->getStaffMember($id, $schoolId);
        if (!$staff) {
            $_SESSION['flash_error'] = 'Staff member not found.';
            header('Location: ' . BASE_URL . '/staff');
            exit;
        }

        try {
            $categories         = $this->staffService->getCategories($schoolId);
            $statuses           = $this->staffService->getStatuses($schoolId);
            $departments        = $this->staffService->getDepartments($schoolId);
            $teacherAssignments = $this->staffService->getTeacherAssignments($id, $schoolId);
            $classes            = $this->db->fetchAll("SELECT id, name FROM classes WHERE school_id = :s ORDER BY name", ['s' => $schoolId]);
            $streams            = $this->db->fetchAll("SELECT id, name FROM streams WHERE school_id = :s ORDER BY name", ['s' => $schoolId]);
            $subjects           = $this->db->fetchAll("SELECT id, name FROM subjects WHERE school_id = :s ORDER BY name", ['s' => $schoolId]);
            $assignmentTypes    = $this->db->fetchAll("SELECT id, name FROM teacher_assignment_types WHERE school_id = :s OR school_id IS NULL ORDER BY name", ['s' => $schoolId]);
        } catch (\Exception $e) {
            $categories = $statuses = $departments = $teacherAssignments = $classes = $streams = $subjects = $assignmentTypes = [];
        }

        echo $this->view->renderWithLayout('staff/edit', 'default', [
            'title'              => 'Edit Staff Member - ' . $staff['first_name'] . ' ' . $staff['last_name'],
            'staff'              => $staff,
            'categories'         => $categories,
            'statuses'           => $statuses,
            'departments'        => $departments,
            'teacherAssignments' => $teacherAssignments,
            'classes'            => $classes,
            'streams'            => $streams,
            'subjects'           => $subjects,
            'assignmentTypes'    => $assignmentTypes,
        ]);
    }

    public function update(): void
    {
        if (!$this->auth->check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = $this->auth->getUser();
        if (!$user->isSuperAdmin() && !$user->hasPermission('staff.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $user->school_id ?? 1;
        $id = (int)($_POST['id'] ?? 0);

        $db = \NexaT\Core\Database::getInstance();

        try {
            $db->beginTransaction();

            // Photo upload
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $dir = ROOT_PATH . '/public/uploads/staff/';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = 'staff_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $filename)) {
                    $photoPath = 'uploads/staff/' . $filename;
                }
            }

            // Update staff record
            $data = [
                'first_name'     => trim($_POST['first_name'] ?? ''),
                'middle_name'    => trim($_POST['middle_name'] ?? ''),
                'last_name'      => trim($_POST['last_name'] ?? ''),
                'gender'         => $_POST['gender'] ?? 'male',
                'dob'            => $_POST['date_of_birth'] ?? null,
                'marital_status' => $_POST['marital_status'] ?? 'single',
                'phone'          => trim($_POST['phone'] ?? ''),
                'alt_phone'      => trim($_POST['alt_phone'] ?? ''),
                'email'          => trim($_POST['email'] ?? ''),
                'address'        => trim($_POST['address'] ?? ''),
                'photo_path'     => $photoPath,
                'category_id'    => (int)($_POST['staff_category_id'] ?? 0),
                'status_id'      => (int)($_POST['staff_status_id'] ?? 0),
                'department_id'  => (int)($_POST['department_id'] ?? 0),
                'position'       => trim($_POST['position'] ?? ''),
                'emp_date'       => $_POST['employment_date'] ?? null,
                'emp_type'       => $_POST['employment_type'] ?? 'full_time'
            ];
            $this->staffService->updateStaff($id, $data, $schoolId);

            // Remove marked assignments
            $removeIds = $_POST['remove_assignments'] ?? [];
            if (!empty($removeIds) && is_array($removeIds)) {
                foreach ($removeIds as $rid) {
                    $db->execute(
                        "DELETE FROM teacher_assignments WHERE id = :id AND staff_id = :sid AND school_id = :sch",
                        ['id' => (int)$rid, 'sid' => $id, 'sch' => $schoolId]
                    );
                }
            }

            // Add new assignments
            $assignments = $_POST['assignments'] ?? [];
            if (!empty($assignments) && is_array($assignments)) {
                // Get default assignment type as fallback
                $defaultType = $db->fetch(
                    "SELECT id FROM teacher_assignment_types WHERE school_id = :s OR school_id IS NULL ORDER BY id ASC LIMIT 1",
                    ['s' => $schoolId]
                );
                $defaultTypeId = $defaultType['id'] ?? null;

                foreach ($assignments as $a) {
                    if (empty($a['class_id']) || empty($a['subject_id'])) continue;
                    $typeId = !empty($a['assignment_type_id']) ? (int)$a['assignment_type_id'] : $defaultTypeId;
                    if (!$typeId) continue;

                    $db->insert('teacher_assignments', [
                        'school_id'          => $schoolId,
                        'staff_id'           => $id,
                        'class_id'           => (int)$a['class_id'],
                        'stream_id'          => !empty($a['stream_id']) ? (int)$a['stream_id'] : null,
                        'subject_id'         => (int)$a['subject_id'],
                        'assignment_type_id' => $typeId,
                        'status'             => 'active',
                        'created_at'         => date('Y-m-d H:i:s'),
                        'updated_at'         => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $db->commit();
            $_SESSION['flash_success'] = 'Staff member updated successfully.';
            header('Location: ' . BASE_URL . '/staff/show?id=' . $id);
            exit;

        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['flash_error'] = 'Failed to update staff: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/staff/edit?id=' . $id);
            exit;
        }
    }
}