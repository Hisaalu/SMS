<?php
// File: /app/Controllers/RoleController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Role;
use NexaT\Models\Permission;

class RoleController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('roles.view');

        echo $this->view->renderWithLayout('roles/index', 'default', [
            'roles'       => Role::all([], ['name' => 'ASC']),
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('roles.manage');

        echo $this->view->renderWithLayout('roles/create', 'default', [
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('roles.manage');

        $name        = trim($_POST['name'] ?? '');
        $slug        = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        if ($name === '' || $slug === '') {
            $this->flashError('Role name and slug are required.');
            $this->redirect('/roles');
        }

        if (Role::firstWhere('slug', $slug)) {
            $this->flashError('Role with this slug already exists.');
            $this->redirect('/roles');
        }

        $role = new Role([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'status'      => 'active',
        ]);

        if (!$role->save()) {
            $this->flashError('Failed to create role.');
            $this->redirect('/roles/create');
        }

        foreach ($permissions as $permissionId) {
            $role->assignPermission((int)$permissionId);
        }

        $this->audit('Role Created', 'roles', "Created role: {$name} with slug: {$slug}");
        $this->flashSuccess('Role created successfully.');
        $this->redirect('/roles');
    }

    public function edit($params): void
    {
        $this->requirePermission('roles.manage');

        $role = $this->findRoleOrRedirect((int)($params['id'] ?? 0));
        if (!$role) {
            return;
        }

        echo $this->view->renderWithLayout('roles/edit', 'default', [
            'role'            => $role,
            'permissions'     => $this->groupedPermissions(),
            'rolePermissions' => array_column($role->permissions(), 'id'),
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('roles.manage');

        $roleId = (int)($params['id'] ?? 0);
        $role = $this->findRoleOrRedirect($roleId);
        if (!$role) {
            return;
        }

        if ($role->slug === 'super_admin') {
            $this->flashError('Cannot modify Super Administrator role.');
            $this->redirect('/roles');
        }

        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status      = $_POST['status'] ?? 'active';
        $permissions = $_POST['permissions'] ?? [];

        if ($name === '') {
            $this->flashError('Role name is required.');
            $this->redirect('/roles/' . $roleId . '/edit');
        }

        $role->fill([
            'name'        => $name,
            'description' => $description,
            'status'      => $status,
        ]);

        if (!$role->save()) {
            $this->flashError('Failed to update role.');
            $this->redirect('/roles/' . $roleId . '/edit');
        }

        $role->syncPermissions(array_map('intval', $permissions));

        $this->audit('Role Updated', 'roles', "Updated role: {$name} (ID: {$roleId})");
        $this->flashSuccess('Role updated successfully.');
        $this->redirect('/roles');
    }

    public function delete($params): void
    {
        $this->requirePermission('roles.manage');

        $roleId = (int)($params['id'] ?? 0);
        $role = Role::find($roleId);

        if (!$role) {
            $this->json(['error' => 'Role not found'], 404);
        }

        if ($role->slug === 'super_admin') {
            $this->json(['error' => 'Cannot delete Super Administrator role'], 400);
        }

        if (count($role->users()) > 0) {
            $this->json(['error' => 'Cannot delete role with assigned users'], 400);
        }

        $roleName = $role->name;

        if ($role->delete(['id' => $roleId])) {
            $this->audit('Role Deleted', 'roles', "Deleted role: {$roleName}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete role'], 500);
    }

    private function findRoleOrRedirect(int $id): ?Role
    {
        $role = Role::find($id);

        if (!$role) {
            $this->flashError('Role not found.');
            $this->redirect('/roles');
            return null;
        }

        return $role;
    }

    private function groupedPermissions(): array
    {
        $grouped = [];

        foreach (Permission::all([], ['module' => 'ASC', 'name' => 'ASC']) as $permission) {
            $module = $permission->module ?? 'general';
            $grouped[$module][] = $permission;
        }

        return $grouped;
    }
}