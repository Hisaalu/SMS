<?php
// File: /app/Controllers/ClassController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\SchoolClass;

class ClassController extends Controller
{
    public function index(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id;
        $classes = SchoolClass::where('school_id', $schoolId);

        echo $this->view->renderWithLayout('academic/classes/index', 'default', ['classes' => $classes]);
    }

    public function create(): void
    {
        echo $this->view->renderWithLayout('academic/classes/create', 'default');
    }

    public function store(): void
    {
        $schoolId = $this->auth->getUser()->school_id;
        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';

        $class = new SchoolClass([
            'school_id' => $schoolId,
            'name'      => $name,
            'code'      => $code
        ]);

        if ($class->save()) {
            $this->view->flash('success', 'Class created successfully');
            header('Location: ' . BASE_URL . '/academic/classes');
            exit;
        }

        $this->view->flash('error', 'Failed to create class');
        header('Location: ' . BASE_URL . '/academic/classes/create');
        exit;
    }

    public function edit($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $classId = $params['id'] ?? 0;
        $class = SchoolClass::find($classId);

        if (!$class) {
            $this->view->flash('error', 'Class not found.');
            header('Location: ' . BASE_URL . '/academic/classes');
            exit;
        }

        echo $this->view->renderWithLayout('academic/classes/edit', 'default', ['class' => $class]);
    }

    public function update($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $classId = $params['id'] ?? 0;
        $class = SchoolClass::find($classId);

        if (!$class) {
            $this->view->flash('error', 'Class not found.');
            header('Location: ' . BASE_URL . '/academic/classes');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';

        if (empty($name)) {
            $this->view->flash('error', 'Class name is required.');
            header('Location: ' . BASE_URL . '/academic/classes/' . $classId . '/edit');
            exit;
        }

        $class->fill([
            'name' => $name,
            'code' => $code
        ]);

        if ($class->save()) {
            $this->view->flash('success', 'Class updated successfully.');
            header('Location: ' . BASE_URL . '/academic/classes');
            exit;
        }

        $this->view->flash('error', 'Failed to update class.');
        header('Location: ' . BASE_URL . '/academic/classes/' . $classId . '/edit');
        exit;
    }
}