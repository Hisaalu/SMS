<?php
// File: /app/Controllers/StaffController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\User;
use NexaT\Services\StaffService;
use NexaT\Services\NotificationService;
use Throwable;

class StaffController extends Controller
{
    private StaffService $staffService;
    private NotificationService $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->staffService = new StaffService();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $this->requireStaffPermission('staff.view');

        $schoolId = $this->schoolId();
        $filters  = $this->collectFilters();

        try {
            $staffMembers = $this->staffService->getStaffMembers($schoolId, $filters);
            $categories   = $this->staffService->getCategories($schoolId);
            $statuses     = $this->staffService->getStatuses($schoolId);
            $departments  = $this->staffService->getDepartments($schoolId);
        } catch (Throwable $e) {
            error_log('Staff error: ' . $e->getMessage());
            $staffMembers = $categories = $statuses = $departments = [];
        }

        echo $this->view->renderWithLayout('staff/index', 'default', [
            'title'        => 'Staff Directory',
            'staffMembers' => $staffMembers,
            'categories'   => $categories,
            'statuses'     => $statuses,
            'departments'  => $departments,
            'filters'      => $filters,
        ]);
    }

    public function create(): void
    {
        $this->requireStaffPermission('staff.create');

        $schoolId = $this->schoolId();

        try {
            $categories        = $this->staffService->getCategories($schoolId);
            $statuses          = $this->staffService->getStatuses($schoolId);
            $departments       = $this->staffService->getDepartments($schoolId);
            $suggestedStaffNum = $this->staffService->generateStaffNumber($schoolId);
            $roles             = $this->lookupRoles();
            $classes           = $this->lookupTable('classes', $schoolId);
            $streams           = $this->lookupTable('streams', $schoolId);
            $subjects          = $this->lookupTable('subjects', $schoolId);
            $assignmentTypes   = $this->lookupAssignmentTypes($schoolId);
        } catch (Throwable $e) {
            $categories = $statuses = $departments = $classes = $streams = $subjects = $assignmentTypes = $roles = [];
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
            'assignmentTypes'   => $assignmentTypes,
        ]);
    }

    public function store(): void
    {
        $this->requireStaffPermission('staff.create');

        $schoolId = $this->schoolId();
        $formData = $this->collectStaffForm();
        $_SESSION['staff_form_data'] = $formData;

        $createLogin = !empty($_POST['create_login']);
        $password    = $_POST['password']         ?? '';
        $passwordC   = $_POST['password_confirm'] ?? '';
        $roleId      = (int)($_POST['role_id'] ?? 0);
        $assignments = $_POST['assignments'] ?? [];

        $errors = $this->validateStaff($formData, $createLogin, $password, $passwordC, $roleId, $schoolId);

        if ($errors) {
            $this->flashError(implode(' ', $errors));
            $this->redirect('/staff/create');
        }

        try {
            $this->db->beginTransaction();

            $photoPath = $this->handlePhotoUpload();
            $userId    = $createLogin ? $this->createStaffUser($formData, $password, $roleId, $schoolId) : null;

            $staffId = $this->staffService->createStaff([
                'staff_number'   => $formData['staff_number'],
                'username'       => $formData['username'] ?: null,
                'user_id'        => $userId,
                'first_name'     => $formData['first_name'],
                'middle_name'    => $formData['middle_name'] ?: null,
                'last_name'      => $formData['last_name'],
                'gender'         => $formData['gender'],
                'dob'            => $formData['date_of_birth'] ?: null,
                'marital_status' => $formData['marital_status'],
                'phone'          => $formData['phone'] ?: null,
                'alt_phone'      => $formData['alt_phone'] ?: null,
                'email'          => $formData['email'] ?: null,
                'address'        => $formData['address'] ?: null,
                'photo_path'     => $photoPath,
                'category_id'    => (int)$formData['staff_category_id'],
                'status_id'      => (int)$formData['staff_status_id'],
                'department_id'  => $formData['department_id'] ? (int)$formData['department_id'] : null,
                'position'       => $formData['position'] ?: null,
                'emp_date'       => $formData['employment_date'] ?: null,
                'emp_type'       => $formData['employment_type'],
            ], $schoolId, $this->auth->id());

            $this->saveTeacherAssignments($staffId, $schoolId, $assignments);

            $this->db->commit();
            unset($_SESSION['staff_form_data']);

            $this->notifications->notify(
                (int) $this->auth->id(),
                $schoolId,
                'New Staff Member Added',
                "{$formData['first_name']} {$formData['last_name']} has been added to the staff directory" .
                ($createLogin ? " with a system login account." : "."),
                'success',
                BASE_URL . '/staff/show?id=' . $staffId,
                'fas fa-user-tie'
            );

            if ($createLogin && $userId) {
                $this->notifications->notify(
                    (int) $userId,
                    $schoolId,
                    'Welcome to NexaT!',
                    "Your staff account has been created. You can now log in with your username '{$formData['username']}'.",
                    'info',
                    BASE_URL . '/login',
                    'fas fa-sign-in-alt'
                );
            }

            $this->flashSuccess('Staff member registered successfully.');
            $this->redirect('/staff/show?id=' . $staffId);

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to create staff: ' . $e->getMessage());
            $this->redirect('/staff/create');
        }
    }

    public function show(): void
    {
        $this->requireStaffPermission('staff.view');

        $schoolId = $this->schoolId();
        $id       = (int)($_GET['id'] ?? 0);

        try {
            $staff              = $this->staffService->getStaffMember($id, $schoolId);
            $statusHistory      = $this->staffService->getStatusHistory($id);
            $statuses           = $this->staffService->getStatuses($schoolId);
            $unlinkedUsers      = $this->staffService->getUnlinkedUsers($schoolId);
            $teacherAssignments = $this->staffService->getTeacherAssignments($id, $schoolId);
            $linkedUser         = $this->staffService->getLinkedUser($id, $schoolId);
        } catch (Throwable $e) {
            $this->flashError('Error loading staff: ' . $e->getMessage());
            $this->redirect('/staff');
        }

        if (!$staff) {
            $this->flashError('Staff member not found.');
            $this->redirect('/staff');
        }

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
        $this->requireStaffPermission('staff.change_status');

        $schoolId      = $this->schoolId();
        $staffId       = (int)($_POST['staff_id'] ?? 0);
        $newStatusId   = (int)($_POST['staff_status_id'] ?? 0);
        $reason        = trim($_POST['reason'] ?? '');
        $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');

        try {
            $updated = $this->staffService->updateStatus($staffId, $newStatusId, $schoolId, $this->auth->id(), $reason, $effectiveDate);

            if ($updated) {
                $this->flashSuccess('Staff status updated successfully.');

                $staff = $this->staffService->getStaffMember($staffId, $schoolId);
                $newStatus = $this->db->fetch(
                    "SELECT name FROM staff_statuses WHERE id = :id",
                    ['id' => $newStatusId]
                );

                $statusName = $newStatus['name'] ?? 'Unknown';

                $this->notifications->notify(
                    (int) $this->auth->id(),
                    $schoolId,
                    'Staff Status Changed',
                    "Status for {$staff['first_name']} {$staff['last_name']} has been changed to '{$statusName}' effective {$effectiveDate}.",
                    $statusName === 'Active' ? 'success' : 'warning',
                    BASE_URL . '/staff/show?id=' . $staffId,
                    'fas fa-exchange-alt'
                );

            } else {
                $this->flashError('Status is the same or staff not found.');
            }
        } catch (Throwable $e) {
            $this->flashError('Error updating status: ' . $e->getMessage());
        }

        $this->redirect('/staff/show?id=' . $staffId);
    }

    public function linkAccount(): void
    {
        $this->requireStaffPermission('staff.manage_accounts');

        $staffId = (int)($_POST['staff_id'] ?? 0);
        $userId  = (int)($_POST['user_id'] ?? 0);

        try {
            $this->staffService->linkAccount($staffId, $userId, $this->schoolId());
            $this->flashSuccess('System user account linked successfully.');

            $staff = $this->staffService->getStaffMember($staffId, $this->schoolId());
            $this->notifications->notify(
                (int) $this->auth->id(),
                $this->schoolId(),
                'Staff Account Linked',
                "A system user account has been linked to {$staff['first_name']} {$staff['last_name']}'s staff profile.",
                'info',
                BASE_URL . '/staff/show?id=' . $staffId,
                'fas fa-link'
            );

        } catch (Throwable $e) {
            $this->flashError('Failed to link account: ' . $e->getMessage());
        }

        $this->redirect('/staff/show?id=' . $staffId);
    }

    public function unlinkAccount(): void
    {
        $this->requireStaffPermission('staff.manage_accounts');

        $staffId = (int)($_POST['staff_id'] ?? 0);

        try {
            $staff = $this->staffService->getStaffMember($staffId, $this->schoolId());
            $this->staffService->unlinkAccount($staffId, $this->schoolId());
            $this->flashSuccess('System account unlinked successfully.');

            $this->notifications->notify(
                (int) $this->auth->id(),
                $this->schoolId(),
                'Staff Account Unlinked',
                "The system user account has been unlinked from {$staff['first_name']} {$staff['last_name']}'s staff profile.",
                'warning',
                BASE_URL . '/staff/show?id=' . $staffId,
                'fas fa-unlink'
            );

        } catch (Throwable $e) {
            $this->flashError('Failed to unlink account: ' . $e->getMessage());
        }

        $this->redirect('/staff/show?id=' . $staffId);
    }

    public function edit(): void
    {
        $this->requireStaffPermission('staff.edit');

        $schoolId = $this->schoolId();
        $id       = (int)($_GET['id'] ?? 0);

        $staff = $this->staffService->getStaffMember($id, $schoolId);
        if (!$staff) {
            $this->flashError('Staff member not found.');
            $this->redirect('/staff');
        }

        try {
            $categories         = $this->staffService->getCategories($schoolId);
            $statuses           = $this->staffService->getStatuses($schoolId);
            $departments        = $this->staffService->getDepartments($schoolId);
            $teacherAssignments = $this->staffService->getTeacherAssignments($id, $schoolId);
            $classes            = $this->lookupTable('classes', $schoolId);
            $streams            = $this->lookupTable('streams', $schoolId);
            $subjects           = $this->lookupTable('subjects', $schoolId);
            $assignmentTypes    = $this->lookupAssignmentTypes($schoolId);
        } catch (Throwable $e) {
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
        $this->requireStaffPermission('staff.edit');

        $schoolId = $this->schoolId();
        $id       = (int)($_POST['id'] ?? 0);

        try {
            $this->db->beginTransaction();

            $photoPath = $this->handlePhotoUpload();

            $this->staffService->updateStaff($id, [
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
                'emp_type'       => $_POST['employment_type'] ?? 'full_time',
            ], $schoolId);

            $this->removeTeacherAssignments($id, $schoolId, $_POST['remove_assignments'] ?? []);
            $this->saveTeacherAssignments($id, $schoolId, $_POST['assignments'] ?? []);

            $this->db->commit();
            $this->flashSuccess('Staff member updated successfully.');

            $staff = $this->staffService->getStaffMember($id, $schoolId);
            $this->notifications->notify(
                (int) $this->auth->id(),
                $schoolId,
                'Staff Profile Updated',
                "{$staff['first_name']} {$staff['last_name']}'s profile has been updated.",
                'info',
                BASE_URL . '/staff/show?id=' . $id,
                'fas fa-user-edit'
            );

            $this->redirect('/staff/show?id=' . $id);

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to update staff: ' . $e->getMessage());
            $this->redirect('/staff/edit?id=' . $id);
        }
    }

    private function requireStaffPermission(string $permission): void
    {
        if (!$this->auth->check()) {
            $this->redirect('/login');
        }

        $user = $this->auth->getUser();
        if (!$user->isSuperAdmin() && !$user->hasPermission($permission)) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    private function collectFilters(): array
    {
        $filters = [];

        $search = trim((string)($_GET['search'] ?? ''));
        if ($search !== '') {
            $filters['search'] = $search;
        }

        foreach (['category_id', 'status_id', 'department_id'] as $key) {
            $raw = $_GET[$key] ?? '';
            if ($raw !== '' && ctype_digit((string)$raw) && (int)$raw > 0) {
                $filters[$key] = (int)$raw;
            }
        }

        return $filters;
    }

    private function collectStaffForm(): array
    {
        return [
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
    }

    private function validateStaff(array $form, bool $createLogin, string $password, string $passwordConfirm, int $roleId, int $schoolId): array
    {
        $errors = [];

        if ($form['first_name'] === '')        $errors[] = 'First name is required.';
        if ($form['last_name'] === '')         $errors[] = 'Last name is required.';
        if ($form['staff_category_id'] === '') $errors[] = 'Staff category is required.';
        if ($form['staff_status_id'] === '')   $errors[] = 'Staff status is required.';

        if (!$createLogin) {
            return $errors;
        }

        if ($form['username'] === '')          $errors[] = 'Username is required.';
        if ($form['login_email'] === '')       $errors[] = 'Login email is required.';
        if (strlen($password) < 8)             $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $passwordConfirm)    $errors[] = 'Passwords do not match.';
        if (!$roleId)                          $errors[] = 'Please select a role.';

        if ($form['username'] !== '' && !$this->staffService->checkUsernameAvailability($form['username'], $schoolId)) {
            $errors[] = "Username '{$form['username']}' is already taken.";
        }

        if ($form['login_email'] !== '' && User::firstWhere('email', $form['login_email'])) {
            $errors[] = "A user with email '{$form['login_email']}' already exists.";
        }

        return $errors;
    }

    private function createStaffUser(array $form, string $password, int $roleId, int $schoolId): int
    {
        $userId = $this->db->insert('users', [
            'school_id'  => $schoolId,
            'username'   => $form['username'],
            'email'      => $form['login_email'],
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'first_name' => $form['first_name'],
            'last_name'  => $form['last_name'],
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$userId) {
            throw new \Exception('Failed to create user account.');
        }

        if ($roleId) {
            $this->db->insert('user_roles', [
                'user_id'    => $userId,
                'role_id'    => $roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $userId;
    }

    private function handlePhotoUpload(): ?string
    {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $dir = ROOT_PATH . '/public/uploads/staff/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $ext      = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        $filename = 'staff_' . time() . '_' . uniqid() . '.' . $ext;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $filename)) {
            return null;
        }

        return 'uploads/staff/' . $filename;
    }

    private function saveTeacherAssignments(int $staffId, int $schoolId, array $assignments): void
    {
        if (empty($assignments)) {
            return;
        }

        $defaultType = $this->db->fetch(
            "SELECT id FROM teacher_assignment_types
             WHERE school_id = :s OR school_id IS NULL
             ORDER BY id ASC LIMIT 1",
            ['s' => $schoolId]
        );
        $defaultTypeId = $defaultType['id'] ?? null;

        foreach ($assignments as $a) {
            if (empty($a['class_id']) || empty($a['subject_id'])) {
                continue;
            }

            $typeId = !empty($a['assignment_type_id']) ? (int)$a['assignment_type_id'] : $defaultTypeId;
            if (!$typeId) {
                continue;
            }

            $this->db->insert('teacher_assignments', [
                'school_id'          => $schoolId,
                'staff_id'           => $staffId,
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

    private function removeTeacherAssignments(int $staffId, int $schoolId, array $ids): void
    {
        foreach ($ids as $id) {
            $this->db->execute(
                "DELETE FROM teacher_assignments
                 WHERE id = :id AND staff_id = :sid AND school_id = :sch",
                ['id' => (int)$id, 'sid' => $staffId, 'sch' => $schoolId]
            );
        }
    }

    private function lookupRoles(): array
    {
        return $this->db->fetchAll("SELECT * FROM roles WHERE status = 'active' ORDER BY name ASC");
    }

    private function lookupTable(string $table, int $schoolId): array
    {
        $allowed = ['classes', 'streams', 'subjects'];
        if (!in_array($table, $allowed, true)) {
            return [];
        }
        return $this->db->fetchAll(
            "SELECT id, name FROM {$table} WHERE school_id = :s ORDER BY name",
            ['s' => $schoolId]
        );
    }

    private function lookupAssignmentTypes(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT id, name FROM teacher_assignment_types
             WHERE school_id = :s OR school_id IS NULL
             ORDER BY name",
            ['s' => $schoolId]
        );
    }
}