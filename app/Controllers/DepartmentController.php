<?php
// File: /app/Controllers/DepartmentController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Department;

class DepartmentController extends Controller
{
    public function index(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $departments = Department::where('school_id', $schoolId);

        echo $this->view->renderWithLayout('academic/departments/index', 'default', [
            'departments' => $departments
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        echo $this->view->renderWithLayout('academic/departments/create', 'default');
    }

    public function store(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $name     = $_POST['name'] ?? '';
        $code     = $_POST['code'] ?? '';

        if (empty($name)) {
            $this->view->flash('error', 'Department name is required.');
            header('Location: ' . BASE_URL . '/academic/departments/create');
            exit;
        }

        $department = new Department([
            'school_id' => $schoolId,
            'name'      => $name,
            'code'      => $code
        ]);

        if ($department->save()) {
            $this->view->flash('success', 'Department created successfully.');
            header('Location: ' . BASE_URL . '/academic/departments');
            exit;
        }

        $this->view->flash('error', 'Failed to create department.');
        header('Location: ' . BASE_URL . '/academic/departments/create');
        exit;
    }

    public function edit($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = $params['id'] ?? 0;
        $department = Department::find($id);

        if (!$department) {
            $this->view->flash('error', 'Department not found.');
            header('Location: ' . BASE_URL . '/academic/departments');
            exit;
        }

        echo $this->view->renderWithLayout('academic/departments/edit', 'default', [
            'department' => $department
        ]);
    }

    public function update($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = $params['id'] ?? 0;
        $department = Department::find($id);

        if (!$department) {
            $this->view->flash('error', 'Department not found.');
            header('Location: ' . BASE_URL . '/academic/departments');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';

        if (empty($name)) {
            $this->view->flash('error', 'Department name is required.');
            header('Location: ' . BASE_URL . '/academic/departments/' . $id . '/edit');
            exit;
        }

        $department->fill([
            'name' => $name,
            'code' => $code
        ]);

        if ($department->save()) {
            $this->view->flash('success', 'Department updated successfully.');
            header('Location: ' . BASE_URL . '/academic/departments');
            exit;
        }

        $this->view->flash('error', 'Failed to update department.');
        header('Location: ' . BASE_URL . '/academic/departments/' . $id . '/edit');
        exit;
    }
}