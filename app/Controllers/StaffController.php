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
            $categories = $this->staffService->getCategories($schoolId);
            $statuses = $this->staffService->getStatuses($schoolId);
            $departments = $this->staffService->getDepartments($schoolId);
            $suggestedStaffNum = $this->staffService->generateStaffNumber($schoolId);
        } catch (\Exception $e) {
            error_log("Staff create error: " . $e->getMessage());
            $categories = [];
            $statuses = [];
            $departments = [];
            $suggestedStaffNum = 'STF-0001';
        }

        echo $this->view->renderWithLayout('staff/create', 'default', [
            'title' => 'Register New Staff',
            'categories' => $categories,
            'statuses' => $statuses,
            'departments' => $departments,
            'suggestedStaffNum' => $suggestedStaffNum
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
        
        // Get all form data FIRST
        $formData = [
            'staff_number' => trim($_POST['staff_number'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'gender' => $_POST['gender'] ?? 'male',
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'marital_status' => $_POST['marital_status'] ?? 'single',
            'phone' => trim($_POST['phone'] ?? ''),
            'alt_phone' => trim($_POST['alt_phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'staff_category_id' => $_POST['staff_category_id'] ?? '',
            'staff_status_id' => $_POST['staff_status_id'] ?? '',
            'department_id' => $_POST['department_id'] ?? '',
            'position' => trim($_POST['position'] ?? ''),
            'employment_date' => $_POST['employment_date'] ?? '',
            'employment_type' => $_POST['employment_type'] ?? 'full_time'
        ];

        // Store form data in session for repopulation on error
        $_SESSION['staff_form_data'] = $formData;

        // Validate required fields
        $errors = [];
        
        if (empty($formData['first_name'])) {
            $errors[] = 'First name is required.';
        }
        if (empty($formData['last_name'])) {
            $errors[] = 'Last name is required.';
        }
        if (empty($formData['username'])) {
            $errors[] = 'Username is required.';
        }
        if (empty($formData['staff_category_id'])) {
            $errors[] = 'Staff category is required.';
        }
        if (empty($formData['staff_status_id'])) {
            $errors[] = 'Staff status is required.';
        }

        // Check username availability (after form data is defined)
        if (!empty($formData['username'])) {
            $usernameAvailable = $this->staffService->checkUsernameAvailability($formData['username'], $schoolId);
            if (!$usernameAvailable) {
                $errors[] = 'Username "' . htmlspecialchars($formData['username']) . '" is already taken. Please choose another.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: ' . BASE_URL . '/staff/create');
            exit;
        }

        // Handle photo upload
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

        $data = [
            'staff_number' => $formData['staff_number'],
            'username' => $formData['username'],
            'first_name' => $formData['first_name'],
            'middle_name' => !empty($formData['middle_name']) ? $formData['middle_name'] : null,
            'last_name' => $formData['last_name'],
            'gender' => $formData['gender'],
            'dob' => !empty($formData['date_of_birth']) ? $formData['date_of_birth'] : null,
            'marital_status' => $formData['marital_status'],
            'phone' => !empty($formData['phone']) ? $formData['phone'] : null,
            'alt_phone' => !empty($formData['alt_phone']) ? $formData['alt_phone'] : null,
            'email' => !empty($formData['email']) ? $formData['email'] : null,
            'address' => !empty($formData['address']) ? $formData['address'] : null,
            'photo_path' => $photoPath,
            'category_id' => (int)$formData['staff_category_id'],
            'status_id' => (int)$formData['staff_status_id'],
            'department_id' => !empty($formData['department_id']) ? (int)$formData['department_id'] : null,
            'position' => !empty($formData['position']) ? $formData['position'] : null,
            'emp_date' => !empty($formData['employment_date']) ? $formData['employment_date'] : null,
            'emp_type' => $formData['employment_type']
        ];

        try {
            $staffId = $this->staffService->createStaff($data, $schoolId, $this->auth->id());
            // Clear form data on success
            unset($_SESSION['staff_form_data']);
            $_SESSION['flash_success'] = 'Staff member registered successfully. Username: ' . htmlspecialchars($formData['username']);
            header('Location: ' . BASE_URL . '/staff/show?id=' . $staffId);
            exit;
        } catch (\Exception $e) {
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
            $staff = $this->staffService->getStaffMember($id, $schoolId);
            $statusHistory = $this->staffService->getStatusHistory($id);
            $statuses = $this->staffService->getStatuses($schoolId);
            $unlinkedUsers = $this->staffService->getUnlinkedUsers($schoolId);
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

        echo $this->view->renderWithLayout('staff/show', 'default', [
            'title' => $staff['first_name'] . ' ' . $staff['last_name'],
            'staff' => $staff,
            'statusHistory' => $statusHistory,
            'statuses' => $statuses,
            'unlinkedUsers' => $unlinkedUsers
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

        echo $this->view->renderWithLayout('staff/edit', 'default', [
            'title' => 'Edit Staff Member - ' . $staff['first_name'] . ' ' . $staff['last_name'],
            'staff' => $staff,
            'categories' => $this->staffService->getCategories($schoolId),
            'statuses' => $this->staffService->getStatuses($schoolId),
            'departments' => $this->staffService->getDepartments($schoolId)
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

        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'middle_name' => trim($_POST['middle_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'gender' => $_POST['gender'] ?? 'male',
            'dob' => $_POST['date_of_birth'] ?? null,
            'marital_status' => $_POST['marital_status'] ?? 'single',
            'phone' => trim($_POST['phone'] ?? ''),
            'alt_phone' => trim($_POST['alt_phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'photo_path' => $photoPath,
            'category_id' => (int)($_POST['staff_category_id'] ?? 0),
            'status_id' => (int)($_POST['staff_status_id'] ?? 0),
            'department_id' => (int)($_POST['department_id'] ?? 0),
            'position' => trim($_POST['position'] ?? ''),
            'emp_date' => $_POST['employment_date'] ?? null,
            'emp_type' => $_POST['employment_type'] ?? 'full_time'
        ];

        try {
            $this->staffService->updateStaff($id, $data, $schoolId);
            $_SESSION['flash_success'] = 'Staff member updated successfully.';
            header('Location: ' . BASE_URL . '/staff/show?id=' . $id);
            exit;
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update staff: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/staff/edit?id=' . $id);
            exit;
        }
    }
}