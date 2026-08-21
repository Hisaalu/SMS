<?php
// File: /app/Controllers/RoleController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Role;
use NexaT\Models\Permission;
use NexaT\Services\AuditService;

class RoleController extends Controller
{
    private $audit;
    
    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }
    
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('roles.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $roles = Role::all([], ['name' => 'ASC']);
        $permissions = Permission::all([], ['module' => 'ASC', 'name' => 'ASC']);
        
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $module = $permission->module ?? 'general';
            if (!isset($groupedPermissions[$module])) {
                $groupedPermissions[$module] = [];
            }
            $groupedPermissions[$module][] = $permission;
        }
        
        $data = [
            'roles' => $roles,
            'permissions' => $groupedPermissions
        ];
        
        echo $this->view->renderWithLayout('roles/index', 'default', $data);
    }
    
    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('roles.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $permissions = Permission::all([], ['module' => 'ASC', 'name' => 'ASC']);
        
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $module = $permission->module ?? 'general';
            if (!isset($groupedPermissions[$module])) {
                $groupedPermissions[$module] = [];
            }
            $groupedPermissions[$module][] = $permission;
        }
        
        $data = ['permissions' => $groupedPermissions];
        echo $this->view->renderWithLayout('roles/create', 'default', $data);
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('roles.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $name = $_POST['name'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? '';
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($name) || empty($slug)) {
            $this->view->flash('error', 'Role name and slug are required');
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Check if slug already exists
        $existing = Role::firstWhere('slug', $slug);
        if ($existing) {
            $this->view->flash('error', 'Role with this slug already exists');
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $role = new Role([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => 'active'
        ]);
        
        if ($role->save()) {
            // Assign permissions
            foreach ($permissions as $permissionId) {
                $role->assignPermission($permissionId);
            }
            
            // Log the action
            $this->audit->log(
                $this->auth->id(),
                'Role Created',
                'roles',
                "Created role: {$name} with slug: {$slug}"
            );
            
            $this->view->flash('success', 'Role created successfully');
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $this->view->flash('error', 'Failed to create role');
        header('Location: ' . BASE_URL . '/roles/create');
        exit;
    }
    
    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('roles.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $roleId = $params['id'] ?? 0;
        $role = Role::find($roleId);
        
        if (!$role) {
            $this->view->flash('error', 'Role not found');
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $permissions = Permission::all([], ['module' => 'ASC', 'name' => 'ASC']);
        $rolePermissions = $role->permissions();
        $rolePermissionIds = array_column($rolePermissions, 'id');
        
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $module = $permission->module ?? 'general';
            if (!isset($groupedPermissions[$module])) {
                $groupedPermissions[$module] = [];
            }
            $groupedPermissions[$module][] = $permission;
        }
        
        $data = [
            'role' => $role,
            'permissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissionIds
        ];
        
        echo $this->view->renderWithLayout('roles/edit', 'default', $data);
    }
    
    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('roles.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }
        
        $roleId = $params['id'] ?? 0;
        $role = Role::find($roleId);
        
        if (!$role) {
            $this->view->flash('error', 'Role not found');
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        // Prevent modifying super_admin role
        if ($role->slug === 'super_admin') {
            $this->view->flash('error', 'Cannot modify Super Administrator role');
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($name)) {
            $this->view->flash('error', 'Role name is required');
            header('Location: ' . BASE_URL . '/roles/' . $roleId . '/edit');
            exit;
        }
        
        $role->fill([
            'name' => $name,
            'description' => $description,
            'status' => $status
        ]);
        
        if ($role->save()) {
            // Sync permissions
            $role->syncPermissions($permissions);
            
            $this->audit->log(
                $this->auth->id(),
                'Role Updated',
                'roles',
                "Updated role: {$name} (ID: {$roleId})"
            );
            
            $this->view->flash('success', 'Role updated successfully');
            header('Location: ' . BASE_URL . '/roles');
            exit;
        }
        
        $this->view->flash('error', 'Failed to update role');
        header('Location: ' . BASE_URL . '/roles/' . $roleId . '/edit');
        exit;
    }
    
    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('roles.manage')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $roleId = $params['id'] ?? 0;
        $role = Role::find($roleId);
        
        if (!$role) {
            http_response_code(404);
            echo json_encode(['error' => 'Role not found']);
            exit;
        }
        
        // Prevent deleting super_admin role
        if ($role->slug === 'super_admin') {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete Super Administrator role']);
            exit;
        }
        
        // Check if role has users assigned
        $users = $role->users();
        if (count($users) > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot delete role with assigned users']);
            exit;
        }
        
        $roleName = $role->name;
        $deleted = $role->delete(['id' => $roleId]);
        
        if ($deleted) {
            $this->audit->log(
                $this->auth->id(),
                'Role Deleted',
                'roles',
                "Deleted role: {$roleName}"
            );
            echo json_encode(['success' => true]);
            exit;
        }
        
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete role']);
        exit;
    }
}