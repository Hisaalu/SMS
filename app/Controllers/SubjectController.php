<?php
// File: /app/Controllers/SubjectController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Subject;
use NexaT\Models\Department;
use NexaT\Services\SubjectTypeService;
use Throwable;

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

        $classList = [];
        foreach ($this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC",
            ['s' => $schoolId]
        ) as $row) {
            $classList[(int)$row['id']] = $row['name'];
        }

        $subjectClassMap = [];
        foreach ($this->db->fetchAll(
            "SELECT cs.subject_id, cs.class_id
             FROM class_subjects cs
             WHERE cs.school_id = :s",
            ['s' => $schoolId]
        ) as $row) {
            $subjectClassMap[(int)$row['subject_id']][] = (int)$row['class_id'];
        }

        echo $this->view->renderWithLayout('academic/subjects/index', 'default', [
            'subjects'         => $subjects,
            'deptList'         => $deptList,
            'systemList'       => $systemList,
            'typeList'         => $typeList,
            'classList'        => $classList,
            'subjectClassMap'  => $subjectClassMap,
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
            'classes'        => $this->classesForSchool($schoolId),
            'selectedClasses' => [],
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

        $classIds = $this->collectClassIds($schoolId);

        try {
            $this->db->beginTransaction();

            $data['school_id'] = $schoolId;
            $subject = new Subject($data);

            if (!$subject->save()) {
                throw new \RuntimeException('Failed to save subject.');
            }

            $this->syncClassSubjects((int)$subject->id, $schoolId, $classIds);

            $this->db->commit();

            $this->audit('Subject Created', 'academics', "Created subject: {$data['name']}");
            $this->flashSuccess('Subject created successfully.');
            $this->redirect('/academic/subjects');

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to create subject: ' . $e->getMessage());
            $this->redirect('/academic/subjects/create');
        }
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
            'subject'         => $subject,
            'departments'     => Department::where('school_id', $schoolId),
            'systems'         => $systems,
            'selectedSystem'  => $selectedSystem,
            'subjectTypes'    => $types,
            'classes'         => $this->classesForSchool($schoolId),
            'selectedClasses' => $this->classIdsForSubject((int)$subject->id, $schoolId),
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

        $classIds = $this->collectClassIds($schoolId);

        try {
            $this->db->beginTransaction();

            $subject->fill($data);

            if (!$subject->save()) {
                throw new \RuntimeException('Failed to save subject.');
            }

            $this->syncClassSubjects($id, $schoolId, $classIds);

            $this->db->commit();

            $this->audit('Subject Updated', 'academics', "Updated subject: {$data['name']}");
            $this->flashSuccess('Subject updated successfully.');
            $this->redirect('/academic/subjects');

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to update subject: ' . $e->getMessage());
            $this->redirect('/academic/subjects/' . $id . '/edit');
        }
    }

    public function delete($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);
        $subject = Subject::find($id);

        if (!$subject) {
            $this->json(['error' => 'Subject not found.'], 404);
        }

        $guards = [
            'marks'                => 'grade record(s)',
            'teacher_assignments'  => 'teaching assignment(s)',
            'examination_subjects' => 'examination assignment(s)',
        ];

        foreach ($guards as $table => $label) {
            try {
                $row = $this->db->fetch(
                    "SELECT COUNT(*) AS c FROM {$table} WHERE subject_id = :id",
                    ['id' => $id]
                );
                $count = (int)($row['c'] ?? 0);
                if ($count > 0) {
                    $this->json([
                        'error' => "Cannot delete this subject: {$count} {$label} reference it. Remove or reassign them first."
                    ], 409);
                }
            } catch (Throwable $e) {
            }
        }

        try {
            $this->db->beginTransaction();

            $this->db->execute(
                "DELETE FROM class_subjects WHERE subject_id = :id AND school_id = :s",
                ['id' => $id, 's' => $this->schoolId()]
            );

            $name = $subject->name;

            if (!$subject->delete(['id' => $id])) {
                throw new \RuntimeException('Delete returned false.');
            }

            $this->db->commit();

            $this->audit('Subject Deleted', 'academics', "Deleted subject: {$name}");
            $this->json(['success' => true]);

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->json(['error' => 'Failed to delete subject: ' . $e->getMessage()], 500);
        }
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
        $name         = trim($_POST['name'] ?? '');
        $code         = trim($_POST['code'] ?? '');
        $systemId     = (int)($_POST['grading_system_id'] ?? 0);
        $typeId       = (int)($_POST['grading_subject_type_id'] ?? 0);
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

    private function collectClassIds(int $schoolId): array
    {
        $raw = $_POST['class_ids'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $rows = $this->db->fetchAll(
            "SELECT id FROM classes WHERE school_id = ? AND id IN ({$placeholders})",
            array_merge([$schoolId], $ids)
        );

        $valid = array_map(fn($r) => (int)$r['id'], $rows);

        return $valid;
    }

    private function syncClassSubjects(int $subjectId, int $schoolId, array $classIds): void
    {
        $this->db->execute(
            "DELETE FROM class_subjects WHERE subject_id = :sid AND school_id = :s",
            ['sid' => $subjectId, 's' => $schoolId]
        );

        $now = date('Y-m-d H:i:s');

        foreach ($classIds as $classId) {
            $this->db->insert('class_subjects', [
                'school_id'  => $schoolId,
                'class_id'   => (int)$classId,
                'subject_id' => $subjectId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function classIdsForSubject(int $subjectId, int $schoolId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT class_id FROM class_subjects
             WHERE subject_id = :sid AND school_id = :s",
            ['sid' => $subjectId, 's' => $schoolId]
        );

        return array_map(fn($r) => (int)$r['class_id'], $rows);
    }

    private function classesForSchool(int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, code FROM classes
             WHERE school_id = :s
             ORDER BY name ASC",
            ['s' => $schoolId]
        ) ?: [];
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