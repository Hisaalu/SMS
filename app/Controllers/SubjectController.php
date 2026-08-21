<?php
// File: /app/Controllers/SubjectController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Subject;
use NexaT\Models\Department;

class SubjectController extends Controller
{
    public function index(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $subjects = Subject::where('school_id', $schoolId);
        $departments = Department::where('school_id', $schoolId);

        $deptList = [];
        foreach ($departments as $dept) {
            $deptList[$dept->id] = $dept->name;
        }

        echo $this->view->renderWithLayout('academic/subjects/index', 'default', [
            'subjects' => $subjects,
            'deptList' => $deptList
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $departments = Department::where('school_id', $schoolId);

        echo $this->view->renderWithLayout('academic/subjects/create', 'default', [
            'departments' => $departments
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId     = $this->auth->getUser()->school_id;
        $departmentId = $_POST['department_id'] ?? null;
        $name         = $_POST['name'] ?? '';
        $code         = $_POST['code'] ?? '';
        $type         = $_POST['type'] ?? 'core';

        if (empty($name) || empty($code)) {
            $this->view->flash('error', 'Subject Name and Code are required.');
            header('Location: ' . BASE_URL . '/academic/subjects/create');
            exit;
        }

        $subject = new Subject([
            'school_id'     => $schoolId,
            'department_id' => !empty($departmentId) ? $departmentId : null,
            'name'          => $name,
            'code'          => $code,
            'type'          => $type
        ]);

        if ($subject->save()) {
            $this->view->flash('success', 'Subject created successfully.');
            header('Location: ' . BASE_URL . '/academic/subjects');
            exit;
        }

        $this->view->flash('error', 'Failed to create subject.');
        header('Location: ' . BASE_URL . '/academic/subjects/create');
        exit;
    }

    public function edit($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = $params['id'] ?? 0;
        $subject = Subject::find($id);

        if (!$subject) {
            $this->view->flash('error', 'Subject not found.');
            header('Location: ' . BASE_URL . '/academic/subjects');
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $departments = Department::where('school_id', $schoolId);

        echo $this->view->renderWithLayout('academic/subjects/edit', 'default', [
            'subject'     => $subject,
            'departments' => $departments
        ]);
    }

    public function update($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = $params['id'] ?? 0;
        $subject = Subject::find($id);

        if (!$subject) {
            $this->view->flash('error', 'Subject not found.');
            header('Location: ' . BASE_URL . '/academic/subjects');
            exit;
        }

        $departmentId = $_POST['department_id'] ?? null;
        $name         = $_POST['name'] ?? '';
        $code         = $_POST['code'] ?? '';
        $type         = $_POST['type'] ?? 'core';

        if (empty($name) || empty($code)) {
            $this->view->flash('error', 'Subject Name and Code are required.');
            header('Location: ' . BASE_URL . '/academic/subjects/' . $id . '/edit');
            exit;
        }

        $subject->fill([
            'department_id' => !empty($departmentId) ? $departmentId : null,
            'name'          => $name,
            'code'          => $code,
            'type'          => $type
        ]);

        if ($subject->save()) {
            $this->view->flash('success', 'Subject updated successfully.');
            header('Location: ' . BASE_URL . '/academic/subjects');
            exit;
        }

        $this->view->flash('error', 'Failed to update subject.');
        header('Location: ' . BASE_URL . '/academic/subjects/' . $id . '/edit');
        exit;
    }
}