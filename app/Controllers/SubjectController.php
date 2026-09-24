<?php
// File: /app/Controllers/SubjectController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Subject;
use NexaT\Models\Department;
use NexaT\Services\SubjectTypeService;

class SubjectController extends Controller
{
    private SubjectTypeService $typeService;

    public function __construct()
    {
        parent::__construct();
        $this->typeService = new SubjectTypeService();
    }

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

        $systemList = [];
        foreach ($this->db->fetchAll(
            "SELECT id, name FROM grading_systems WHERE school_id = :s ORDER BY is_default DESC, name ASC",
            ['s' => $schoolId]
        ) as $row) {
            $systemList[(int)$row['id']] = $row['name'];
        }

        $typeList = [];
        foreach ($this->db->fetchAll(
            "SELECT id, name, is_graded FROM grading_subject_types WHERE school_id = :s",
            ['s' => $schoolId]
        ) as $row) {
            $typeList[(int)$row['id']] = $row;
        }

        echo $this->view->renderWithLayout('academic/subjects/index', 'default', [
            'subjects'    => $subjects,
            'deptList'    => $deptList,
            'systemList'  => $systemList,
            'typeList'    => $typeList,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();
        $systems  = $this->systemsForSchool();

        $selectedSystem = (int)($_GET['grading_system_id'] ?? 0);
        if ($selectedSystem === 0 && !empty($systems)) {
            $selectedSystem = (int)$systems[0]['id'];
        }

        $types = $selectedSystem ? $this->typeService->getForSystem($selectedSystem, true) : [];

        echo $this->view->renderWithLayout('academic/subjects/create', 'default', [
            'departments'    => Department::where('school_id', $schoolId),
            'systems'        => $systems,
            'selectedSystem' => $selectedSystem,
            'subjectTypes'   => $types,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();
        $data = $this->collectInput($schoolId);

        if ($data === null) {
            $this->flashError('Subject Name, Code, and Grading System are required.');
            $this->redirect('/academic/subjects/create');
            return;
        }

        $data['school_id'] = $schoolId;

        $subject = new Subject($data);

        if ($subject->save()) {
            $this->flashSuccess('Subject created successfully.');
            $this->redirect('/academic/subjects');
            return;
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

        $schoolId = $this->schoolId();
        $systems  = $this->systemsForSchool();

        $selectedSystem = (int)($subject->grading_system_id ?? 0);
        $types = $selectedSystem ? $this->typeService->getForSystem($selectedSystem, true) : [];

        echo $this->view->renderWithLayout('academic/subjects/edit', 'default', [
            'subject'        => $subject,
            'departments'    => Department::where('school_id', $schoolId),
            'systems'        => $systems,
            'selectedSystem' => $selectedSystem,
            'subjectTypes'   => $types,
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

        $schoolId = $this->schoolId();
        $data = $this->collectInput($schoolId);

        if ($data === null) {
            $this->flashError('Subject Name, Code, and Grading System are required.');
            $this->redirect('/academic/subjects/' . $id . '/edit');
            return;
        }

        $subject->fill($data);

        if ($subject->save()) {
            $this->flashSuccess('Subject updated successfully.');
            $this->redirect('/academic/subjects');
            return;
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

    private function collectInput(int $schoolId): ?array
    {
        $name        = trim($_POST['name'] ?? '');
        $code        = trim($_POST['code'] ?? '');
        $systemId    = (int)($_POST['grading_system_id'] ?? 0);
        $typeId      = (int)($_POST['grading_subject_type_id'] ?? 0);
        $departmentId = (int)($_POST['department_id'] ?? 0);

        if ($name === '' || $code === '' || $systemId <= 0 || $typeId <= 0) {
            return null;
        }

        $system = $this->db->fetch(
            "SELECT id FROM grading_systems WHERE id = :id AND school_id = :s",
            ['id' => $systemId, 's' => $schoolId]
        );
        if (!$system) {
            return null;
        }

        $type = $this->db->fetch(
            "SELECT id FROM grading_subject_types
             WHERE id = :id AND grading_system_id = :sys AND school_id = :s",
            ['id' => $typeId, 'sys' => $systemId, 's' => $schoolId]
        );
        if (!$type) {
            return null;
        }

        return [
            'grading_system_id'        => $systemId,
            'grading_subject_type_id'  => $typeId,
            'department_id'            => $departmentId ?: null,
            'name'                     => $name,
            'code'                     => $code,
        ];
    }

    private function systemsForSchool(): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, class_id, is_default
             FROM grading_systems
             WHERE school_id = :s
             ORDER BY is_default DESC, name ASC",
            ['s' => $this->schoolId()]
        ) ?: [];
    }
}