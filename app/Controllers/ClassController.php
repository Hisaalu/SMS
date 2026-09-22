<?php
// File: /app/Controllers/ClassController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\SchoolClass;

class ClassController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();
        $classes  = SchoolClass::where('school_id', $schoolId);

        echo $this->view->renderWithLayout('academic/classes/index', 'default', [
            'classes' => $classes,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        echo $this->view->renderWithLayout('academic/classes/create', 'default');
    }

    public function store(): void
    {
        $this->requireAuth();

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Class name is required.');
            $this->redirect('/academic/classes/create');
        }

        $class = new SchoolClass([
            'school_id' => $this->schoolId(),
            'name'      => $name,
            'code'      => $code,
        ]);

        if ($class->save()) {
            $this->flashSuccess('Class created successfully.');
            $this->redirect('/academic/classes');
        }

        $this->flashError('Failed to create class.');
        $this->redirect('/academic/classes/create');
    }

    public function edit($params): void
    {
        $this->requireAuth();

        $class = $this->findClassOrRedirect((int)($params['id'] ?? 0));
        if (!$class) {
            return;
        }

        echo $this->view->renderWithLayout('academic/classes/edit', 'default', [
            'class' => $class,
        ]);
    }

    public function update($params): void
    {
        $this->requireAuth();

        $classId = (int)($params['id'] ?? 0);
        $class = $this->findClassOrRedirect($classId);
        if (!$class) {
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Class name is required.');
            $this->redirect('/academic/classes/' . $classId . '/edit');
        }

        $class->fill([
            'name' => $name,
            'code' => $code,
        ]);

        if ($class->save()) {
            $this->flashSuccess('Class updated successfully.');
            $this->redirect('/academic/classes');
        }

        $this->flashError('Failed to update class.');
        $this->redirect('/academic/classes/' . $classId . '/edit');
    }

    private function findClassOrRedirect(int $id): ?SchoolClass
    {
        $class = SchoolClass::find($id);

        if (!$class) {
            $this->flashError('Class not found.');
            $this->redirect('/academic/classes');
            return null;
        }

        return $class;
    }
}