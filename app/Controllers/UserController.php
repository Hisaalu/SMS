<?php
// File: /app/Controllers/UserController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Core\SettingsService;
use NexaT\Models\User;
use NexaT\Models\School;
use NexaT\Models\Role;

class UserController extends Controller
{
    private function logAudit(int $userId, string $action, string $module, string $description): void
    {
        if ($this->audit && method_exists($this->audit, 'log')) {
            $this->audit->log($userId, $action, $module, $description);
        }
    }

    public function index(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $users = $userModel->all();

        // School name comes from settings (no School model needed)
        $schoolName = $this->settings->get('school.name',
                    $this->settings->get('branding.school_name', 'NexaT School'));

        $data = [
            'users'         => $users,
            'schoolName'    => $schoolName,
            'currentUserId' => $_SESSION['user_id'] ?? null
        ];

        echo $this->view->renderWithLayout('users/index', 'default', $data);
    }
    
    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('users.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $db = \NexaT\Core\Database::getInstance();
        $roles = $db->fetchAll("SELECT * FROM roles WHERE status = 'active' ORDER BY name ASC");
        $data = ['roles' => $roles];
        echo $this->view->renderWithLayout('users/create', 'default', $data);
    }

    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('users.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $roleIds   = $_POST['roles'] ?? [];
        
        if (empty($username) || empty($email) || empty($password)) {
            $this->view->flash('error', 'Username, email, and password are required');
            header('Location: ' . BASE_URL . '/users/create');
            exit;
        }
        
        // Check if user exists in this school
        $existing = User::firstWhere('email', $email);
        if ($existing) {
            $this->view->flash('error', 'User with this email already exists in your school');
            header('Location: ' . BASE_URL . '/users/create');
            exit;
        }
        
        $db = \NexaT\Core\Database::getInstance();
        $schoolId = $_SESSION['school_id'] ?? 1;
        
        try {
            $db->beginTransaction();
            
            // 1. Create the user
            $userId = $db->insert('users', [
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
                throw new \Exception("Failed to create user account.");
            }
            
            // 2. Assign roles
            if (!empty($roleIds)) {
                foreach ($roleIds as $roleId) {
                    $db->insert('user_roles', [
                        'user_id'    => $userId,
                        'role_id'    => (int)$roleId,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
            
            // 3. Find or create default staff category & status
            // Try to find "Teaching Staff" or any active category
            $category = $db->fetch(
                "SELECT id FROM staff_categories WHERE school_id = :s AND status = 'active' ORDER BY id ASC LIMIT 1",
                ['s' => $schoolId]
            );
            if (!$category) {
                // Auto-create a default category
                $categoryId = $db->insert('staff_categories', [
                    'school_id'     => $schoolId,
                    'name'          => 'General Staff',
                    'code'          => 'GENERAL',
                    'description'   => 'Default category for system users',
                    'display_order' => 99,
                    'status'        => 'active',
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            } else {
                $categoryId = $category['id'];
            }
            
            // Try to find an "Active" staff status
            $status = $db->fetch(
                "SELECT id FROM staff_statuses WHERE school_id = :s AND status = 'active' ORDER BY id ASC LIMIT 1",
                ['s' => $schoolId]
            );
            if (!$status) {
                $statusId = $db->insert('staff_statuses', [
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
            } else {
                $statusId = $status['id'];
            }
            
            // 4. Generate staff number
            $lastStaff = $db->fetch(
                "SELECT id FROM staff WHERE school_id = :s ORDER BY id DESC LIMIT 1",
                ['s' => $schoolId]
            );
            $nextNum = ($lastStaff['id'] ?? 0) + 1;
            $staffNumber = 'STF-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            
            // 5. Create staff record linked to the user
            $staffService = new \NexaT\Services\StaffService();
            $staffId = $staffService->createStaff([
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
                throw new \Exception("Failed to create staff record.");
            }
            
            $db->commit();
            
            $this->logAudit(
                $this->auth->id(),
                'User Created',
                'users',
                "Created user: {$username} ({$email}) with staff record #{$staffNumber}"
            );
            
            $this->view->flash('success', 'User and staff record created successfully');
            header('Location: ' . BASE_URL . '/users');
            exit;
            
        } catch (\Exception $e) {
            $db->rollback();
            $this->view->flash('error', 'Failed to create user: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/users/create');
            exit;
        }
    }
    
    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('users.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $userId = $params['id'] ?? 0;
        $user = User::find($userId);
        
        if (!$user) {
            $this->view->flash('error', 'User not found');
            header('Location: ' . BASE_URL . '/users');
            exit;
        }
        
        $db = \NexaT\Core\Database::getInstance();
        $roles = $db->fetchAll("SELECT * FROM roles WHERE status = 'active' ORDER BY name ASC");
        $userRoles = $user->roles();
        $userRoleIds = array_column($userRoles, 'id');
        
        $data = [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoleIds
        ];
        
        echo $this->view->renderWithLayout('users/edit', 'default', $data);
    }
    
    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('users.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $userId = $params['id'] ?? 0;
        $user = User::find($userId);
        
        if (!$user) {
            $this->view->flash('error', 'User not found');
            header('Location: ' . BASE_URL . '/users');
            exit;
        }
        
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $status    = $_POST['status'] ?? 'active';
        $roleIds   = $_POST['roles'] ?? [];
        $password  = $_POST['password'] ?? '';
        
        // Validate required fields
        $errors = [];
        if (empty($firstName)) $errors[] = 'First name is required.';
        if (empty($lastName))  $errors[] = 'Last name is required.';
        if (empty($username))  $errors[] = 'Username is required.';
        if (empty($email))     $errors[] = 'Email is required.';
        
        // Check username uniqueness (excluding this user)
        $db = \NexaT\Core\Database::getInstance();
        if (!empty($username)) {
            $existing = $db->fetch(
                "SELECT id FROM users WHERE username = :u AND id != :id AND school_id = :s",
                ['u' => $username, 'id' => $userId, 's' => $user->school_id]
            );
            if ($existing) {
                $errors[] = "Username '{$username}' is already taken.";
            }
        }
        
        // Check email uniqueness (excluding this user)
        if (!empty($email)) {
            $existing = $db->fetch(
                "SELECT id FROM users WHERE email = :e AND id != :id AND school_id = :s",
                ['e' => $email, 'id' => $userId, 's' => $user->school_id]
            );
            if ($existing) {
                $errors[] = "Email '{$email}' is already taken.";
            }
        }
        
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            header('Location: ' . BASE_URL . '/users/' . $userId . '/edit');
            exit;
        }
        
        // Build update payload
        $updateData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'username'   => $username,
            'email'      => $email,
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        if (!empty($password)) {
            $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
        }
        
        try {
            $db->update('users', $updateData, ['id' => $userId]);
            
            // Sync roles
            $db->delete('user_roles', ['user_id' => $userId]);
            foreach ($roleIds as $roleId) {
                $db->insert('user_roles', [
                    'user_id'    => $userId,
                    'role_id'    => (int)$roleId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            
            // Keep staff record in sync if it exists
            $staff = $db->fetch("SELECT id FROM staff WHERE user_id = :uid", ['uid' => $userId]);
            if ($staff) {
                $db->update('staff', [
                    'username'   => $username,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                ], ['id' => $staff['id']]);
            }
            
            $this->logAudit(
                $this->auth->id(),
                'User Updated',
                'users',
                "Updated user: {$username} ({$email})"
            );
            
            $this->view->flash('success', 'User updated successfully');
            header('Location: ' . BASE_URL . '/users');
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update user: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/users/' . $userId . '/edit');
            exit;
        }
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('users.delete')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $userId = $params['id'] ?? 0;
        $user = User::find($userId);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }
        
        // Prevent deleting yourself
        if ($user->id == $this->auth->id()) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete your own account']);
            exit;
        }
        
        $username = $user->username;
        $deleted = $user->delete(['id' => $userId]);
        
        if ($deleted) {
            $this->logAudit(
                $this->auth->id(),
                'User Deleted',
                'users',
                "Deleted user: {$username}"
            );
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete user']);
        exit;
    }
}