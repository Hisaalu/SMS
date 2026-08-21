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

        // 1. Try to fetch school name from session or School model
        $schoolName = null;
        if (isset($_SESSION['school_id'])) {
            $schoolModel = new School();
            $school = $schoolModel->find($_SESSION['school_id']);
            $schoolName = $school->name ?? null;
        }

        // 2. Fallback to System Settings branding/school name if not set in session
        if (!$schoolName) {
            $schoolName = $this->settings->get('school.name', $this->settings->get('branding.school_name', 'NexaT School'));
        }

        $data = [
            'users' => $users,
            'schoolName' => $schoolName,
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
        
        $roles = Role::all(['status' => 'active'], ['name' => 'ASC']);
        $data = ['roles' => $roles];
        echo $this->view->renderWithLayout('users/create', 'default', $data);
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('users.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $roleIds = $_POST['roles'] ?? [];
        
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
        
        // Create user - school_id will be auto-assigned from session
        $user = new User([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => 'active'
        ]);
        
        if ($user->save()) {
            // Assign roles
            foreach ($roleIds as $roleId) {
                $user->assignRole($roleId);
            }
            
            $this->logAudit(
                $this->auth->id(),
                'User Created',
                'users',
                "Created user: {$username} ({$email})"
            );
            
            $this->view->flash('success', 'User created successfully');
            header('Location: ' . BASE_URL . '/users');
            exit;
        }
        
        $this->view->flash('error', 'Failed to create user');
        header('Location: ' . BASE_URL . '/users/create');
        exit;
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
        
        $roles = Role::all(['status' => 'active'], ['name' => 'ASC']);
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
        
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $roleIds = $_POST['roles'] ?? [];
        $password = $_POST['password'] ?? '';
        
        $user->fill([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'status' => $status
        ]);
        
        if (!empty($password)) {
            $user->password = password_hash($password, PASSWORD_BCRYPT);
        }
        
        if ($user->save()) {
            // Sync roles
            $user->syncRoles($roleIds);
            
            $this->logAudit(
                $this->auth->id(),
                'User Updated',
                'users',
                "Updated user: {$user->username} ({$user->email})"
            );
            
            $this->view->flash('success', 'User updated successfully');
            header('Location: ' . BASE_URL . '/users');
            exit;
        }
        
        $this->view->flash('error', 'Failed to update user');
        header('Location: ' . BASE_URL . '/users/' . $userId . '/edit');
        exit;
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