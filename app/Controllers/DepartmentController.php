<?php
// File: /app/Controllers/DepartmentController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Department;

class DepartmentController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $departments = Department::where('school_id', $this->schoolId());

        echo $this->view->renderWithLayout('academic/departments/index', 'default', [
            'departments' => $departments,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        echo $this->view->renderWithLayout('academic/departments/create', 'default');
    }

    public function store(): void
    {
        $this->requireAuth();

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Department name is required.');
            $this->redirect('/academic/departments/create');
        }

        $department = new Department([
            'school_id' => $this->schoolId(),
            'name'      => $name,
            'code'      => $code,
        ]);

        if ($department->save()) {
            $this->flashSuccess('Department created successfully.');
            $this->redirect('/academic/departments');
        }

        $this->flashError('Failed to create department.');
        $this->redirect('/academic/departments/create');
    }

    public function edit($params): void
    {
        $this->requireAuth();

        $department = $this->findOrRedirect((int)($params['id'] ?? 0));
        if (!$department) {
            return;
        }

        echo $this->view->renderWithLayout('academic/departments/edit', 'default', [
            'department' => $department,
        ]);
    }

    public function update($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);
        $department = $this->findOrRedirect($id);
        if (!$department) {
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Department name is required.');
            $this->redirect('/academic/departments/' . $id . '/edit');
        }

        $department->fill([
            'name' => $name,
            'code' => $code,
        ]);

        if ($department->save()) {
            $this->flashSuccess('Department updated successfully.');
            $this->redirect('/academic/departments');
        }

        $this->flashError('Failed to update department.');
        $this->redirect('/academic/departments/' . $id . '/edit');
    }

    private function findOrRedirect(int $id): ?Department
    {
        $department = Department::find($id);

        if (!$department) {
            $this->flashError('Department not found.');
            $this->redirect('/academic/departments');
            return null;
        }

        return $department;
    }
}