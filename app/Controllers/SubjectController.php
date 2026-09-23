<?php
// File: /app/Controllers/SubjectController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Subject;
use NexaT\Models\Department;

class SubjectController extends Controller
{
    private const ALLOWED_TYPES = ['core', 'elective', 'optional', 'other'];

    public function index(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();

        $subjects    = Subject::where('school_id', $schoolId);
        $departments = Department::where('school_id', $schoolId);

        $deptList = [];
        foreach ($departments as $dept) {
            $deptList[$dept->id] = $dept->name;
        }

        echo $this->view->renderWithLayout('academic/subjects/index', 'default', [
            'subjects' => $subjects,
            'deptList' => $deptList,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        echo $this->view->renderWithLayout('academic/subjects/create', 'default', [
            'departments' => Department::where('school_id', $this->schoolId()),
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Subject Name and Code are required.');
            $this->redirect('/academic/subjects/create');
        }

        $data['school_id'] = $this->schoolId();

        $subject = new Subject($data);

        if ($subject->save()) {
            $this->flashSuccess('Subject created successfully.');
            $this->redirect('/academic/subjects');
        }

        $this->flashError('Failed to create subject.');
        $this->redirect('/academic/subjects/create');
    }

    public function edit($params): void
    {
        $this->requireAuth();

        $subject = $this->findSubjectOrRedirect((int)($params['id'] ?? 0));
        if (!$subject) {
            return;
        }

        echo $this->view->renderWithLayout('academic/subjects/edit', 'default', [
            'subject'     => $subject,
            'departments' => Department::where('school_id', $this->schoolId()),
        ]);
    }

    public function update($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);
        $subject = $this->findSubjectOrRedirect($id);
        if (!$subject) {
            return;
        }

        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Subject Name and Code are required.');
            $this->redirect('/academic/subjects/' . $id . '/edit');
        }

        $subject->fill($data);

        if ($subject->save()) {
            $this->flashSuccess('Subject updated successfully.');
            $this->redirect('/academic/subjects');
        }

        $this->flashError('Failed to update subject.');
        $this->redirect('/academic/subjects/' . $id . '/edit');
    }

    private function findSubjectOrRedirect(int $id): ?Subject
    {
        $subject = Subject::find($id);

        if (!$subject) {
            $this->flashError('Subject not found.');
            $this->redirect('/academic/subjects');
            return null;
        }

        return $subject;
    }

    private function collectInput(): ?array
    {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '' || $code === '') {
            return null;
        }

        $departmentId = (int)($_POST['department_id'] ?? 0);
        $type = strtolower(trim($_POST['type'] ?? 'core'));

        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            $type = 'core';
        }

        return [
            'department_id' => $departmentId ?: null,
            'name'          => $name,
            'code'          => $code,
            'type'          => $type,
        ];
    }
}