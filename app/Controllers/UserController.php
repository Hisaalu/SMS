<?php
// File: /app/Controllers/UserController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\User;
use NexaT\Services\StaffService;

class UserController extends Controller
{
    private StaffService $staffService;

    public function __construct()
    {
        parent::__construct();
        $this->staffService = new StaffService();
    }

    public function index(): void
    {
        $this->requireAuth();

        $users = User::all();

        $schoolName = $this->settings->get(
            'school.name',
            $this->settings->get('branding.school_name', 'NexaT School')
        );

        echo $this->view->renderWithLayout('users/index', 'default', [
            'users'         => $users,
            'schoolName'    => $schoolName,
            'currentUserId' => $_SESSION['user_id'] ?? null,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('users.create');

        $roles = $this->db->fetchAll(
            "SELECT * FROM roles WHERE status = 'active' ORDER BY name ASC"
        );

        echo $this->view->renderWithLayout('users/create', 'default', [
            'roles' => $roles,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('users.create');

        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $roleIds   = $_POST['roles'] ?? [];

        if ($username === '' || $email === '' || $password === '') {
            $this->flashError('Username, email, and password are required.');
            $this->redirect('/users/create');
        }

        if (User::firstWhere('email', $email)) {
            $this->flashError('User with this email already exists in your school.');
            $this->redirect('/users/create');
        }

        $schoolId = $this->schoolId();

        try {
            $this->db->beginTransaction();

            $userId = $this->db->insert('users', [
                'school_id'  => $schoolId,
                'username'   => $username,
                'email'      => $email,
                'password'   => password_hash($password, PASSWORD_BCRYPT),
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if (!$userId) {
                throw new \Exception('Failed to create user account.');
            }

            $this->syncUserRoles($userId, $roleIds);
            $this->ensureStaffRecord($userId, $schoolId, $username, $firstName, $lastName);

            $this->db->commit();

            $this->audit('User Created', 'users', "Created user: {$username} ({$email})");
            $this->flashSuccess('User created successfully.');
            $this->redirect('/users');

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->flashError('Failed to create user: ' . $e->getMessage());
            $this->redirect('/users/create');
        }
    }

    public function edit($params): void
    {
        $this->requirePermission('users.edit');

        $userId = (int)($params['id'] ?? 0);
        $user = User::find($userId);

        if (!$user) {
            $this->flashError('User not found.');
            $this->redirect('/users');
        }

        $roles = $this->db->fetchAll(
            "SELECT * FROM roles WHERE status = 'active' ORDER BY name ASC"
        );

        $userRoleIds = array_column($user->roles(), 'id');

        echo $this->view->renderWithLayout('users/edit', 'default', [
            'user'      => $user,
            'roles'     => $roles,
            'userRoles' => $userRoleIds,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('users.edit');

        $userId = (int)($params['id'] ?? 0);
        $user = User::find($userId);

        if (!$user) {
            $this->flashError('User not found.');
            $this->redirect('/users');
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $status    = $_POST['status'] ?? 'active';
        $roleIds   = $_POST['roles'] ?? [];
        $password  = $_POST['password'] ?? '';

        $errors = $this->validateUserUpdate($userId, $user->school_id, $firstName, $lastName, $username, $email);

        if ($errors) {
            $this->flashError(implode(' ', $errors));
            $this->redirect('/users/' . $userId . '/edit');
        }

        $updateData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'username'   => $username,
            'email'      => $email,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($password !== '') {
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        try {
            $this->db->beginTransaction();

            $this->db->update('users', $updateData, ['id' => $userId]);
            $this->syncUserRoles($userId, $roleIds);
            $this->syncStaffRecord($userId, $username, $firstName, $lastName);

            $this->db->commit();

            $this->audit('User Updated', 'users', "Updated user: {$username} ({$email})");
            $this->flashSuccess('User updated successfully.');
            $this->redirect('/users');

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->flashError('Failed to update user: ' . $e->getMessage());
            $this->redirect('/users/' . $userId . '/edit');
        }
    }

    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('users.delete')) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        $userId = (int)($params['id'] ?? 0);
        $user = User::find($userId);

        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
        }

        if ($user->id == $this->auth->id()) {
            $this->json(['error' => 'Cannot delete your own account'], 400);
        }

        $username = $user->username;

        if ($user->delete(['id' => $userId])) {
            $this->audit('User Deleted', 'users', "Deleted user: {$username}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete user'], 500);
    }

    private function syncUserRoles(int $userId, array $roleIds): void
    {
        $this->db->delete('user_roles', ['user_id' => $userId]);

        foreach ($roleIds as $roleId) {
            $this->db->insert('user_roles', [
                'user_id'    => $userId,
                'role_id'    => (int)$roleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function ensureStaffRecord(
        int $userId,
        int $schoolId,
        string $username,
        string $firstName,
        string $lastName
    ): void {
        $categoryId = $this->ensureDefaultCategory($schoolId);
        $statusId   = $this->ensureDefaultStatus($schoolId);

        $staffNumber = $this->staffService->generateStaffNumber($schoolId);

        $staffId = $this->staffService->createStaff([
            'staff_number'   => $staffNumber,
            'username'       => $username,
            'user_id'        => $userId,
            'first_name'     => $firstName,
            'middle_name'    => null,
            'last_name'      => $lastName,
            'gender'         => 'male',
            'dob'            => null,
            'marital_status' => 'single',
            'phone'          => null,
            'alt_phone'      => null,
            'email'          => null,
            'address'        => null,
            'photo_path'     => null,
            'category_id'    => $categoryId,
            'status_id'      => $statusId,
            'department_id'  => null,
            'position'       => null,
            'emp_date'       => date('Y-m-d'),
            'emp_type'       => 'full_time',
        ], $schoolId, $this->auth->id());

        if (!$staffId) {
            throw new \Exception('Failed to create staff record.');
        }
    }

    private function ensureDefaultCategory(int $schoolId): int
    {
        $category = $this->db->fetch(
            "SELECT id FROM staff_categories
             WHERE school_id = :s AND status = 'active'
             ORDER BY id ASC LIMIT 1",
            ['s' => $schoolId]
        );

        if ($category) {
            return (int)$category['id'];
        }

        $id = $this->db->insert('staff_categories', [
            'school_id'     => $schoolId,
            'name'          => 'General Staff',
            'code'          => 'GENERAL',
            'description'   => 'Default category for system users',
            'display_order' => 99,
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        if (!$id) {
            throw new \Exception('Failed to create default staff category.');
        }

        return (int)$id;
    }

    private function ensureDefaultStatus(int $schoolId): int
    {
        $status = $this->db->fetch(
            "SELECT id FROM staff_statuses
             WHERE school_id = :s AND status = 'active'
             ORDER BY id ASC LIMIT 1",
            ['s' => $schoolId]
        );

        if ($status) {
            return (int)$status['id'];
        }

        $id = $this->db->insert('staff_statuses', [
            'school_id'        => $schoolId,
            'name'             => 'Active',
            'code'             => 'ACTIVE',
            'description'      => 'Currently employed',
            'is_active_status' => 1,
            'allows_login'     => 1,
            'display_order'    => 1,
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        if (!$id) {
            throw new \Exception('Failed to create default staff status.');
        }

        return (int)$id;
    }

    private function syncStaffRecord(int $userId, string $username, string $firstName, string $lastName): void
    {
        $staff = $this->db->fetch(
            "SELECT id FROM staff WHERE user_id = :uid",
            ['uid' => $userId]
        );

        if (!$staff) {
            return;
        }

        $this->db->update('staff', [
            'username'   => $username,
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ], ['id' => $staff['id']]);
    }

    private function validateUserUpdate(
        int $userId,
        int $schoolId,
        string $firstName,
        string $lastName,
        string $username,
        string $email
    ): array {
        $errors = [];

        if ($firstName === '') $errors[] = 'First name is required.';
        if ($lastName === '')  $errors[] = 'Last name is required.';
        if ($username === '')  $errors[] = 'Username is required.';
        if ($email === '')     $errors[] = 'Email is required.';

        if ($username !== '') {
            $existing = $this->db->fetch(
                "SELECT id FROM users
                 WHERE username = :u AND id != :id AND school_id = :s",
                ['u' => $username, 'id' => $userId, 's' => $schoolId]
            );
            if ($existing) {
                $errors[] = "Username '{$username}' is already taken.";
            }
        }

        if ($email !== '') {
            $existing = $this->db->fetch(
                "SELECT id FROM users
                 WHERE email = :e AND id != :id AND school_id = :s",
                ['e' => $email, 'id' => $userId, 's' => $schoolId]
            );
            if ($existing) {
                $errors[] = "Email '{$email}' is already taken.";
            }
        }

        return $errors;
    }
}