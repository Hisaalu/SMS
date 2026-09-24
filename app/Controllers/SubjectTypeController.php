<?php
// File: /app/Controllers/SubjectTypeController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\SubjectTypeService;

class SubjectTypeController extends Controller
{
    private SubjectTypeService $typeService;

    public function __construct()
    {
        parent::__construct();
        $this->typeService = new SubjectTypeService();
    }

    public function index($params): void
    {
        $this->requirePermission('grading.view');

        $systemId = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $system = $this->db->fetch(
            "SELECT gs.*, c.name AS class_name
             FROM grading_systems gs
             LEFT JOIN classes c ON gs.class_id = c.id
             WHERE gs.id = :id AND gs.school_id = :s",
            ['id' => $systemId, 's' => $schoolId]
        );

        if (!$system) {
            $this->flashError('Grading system not found.');
            $this->redirect('/grading/systems');
            return;
        }

        $types = $this->typeService->getForSystem($systemId, false);

        $subjects = $this->db->fetchAll(
            "SELECT s.id, s.name, s.code, s.grading_subject_type_id,
                    t.name AS type_name
             FROM subjects s
             LEFT JOIN grading_subject_types t ON s.grading_subject_type_id = t.id
             WHERE s.school_id = :s AND s.grading_system_id = :sys
             ORDER BY s.name ASC",
            ['s' => $schoolId, 'sys' => $systemId]
        ) ?: [];

        echo $this->view->renderWithLayout('examinations/grading/subject_types', 'default', [
            'title'    => 'Subject Types — ' . $system['name'],
            'system'   => $system,
            'types'    => $types,
            'subjects' => $subjects,
        ]);
    }

    public function store($params): void
    {
        $this->requirePermission('grading.manage');

        $systemId = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id AND school_id = :s",
            ['id' => $systemId, 's' => $schoolId]
        );

        if (!$system) {
            $this->flashError('Grading system not found.');
            $this->redirect('/grading/systems');
            return;
        }

        $data = $this->collectInput($systemId, $schoolId);
        if ($data === null) {
            $this->redirect('/grading/systems/' . $systemId . '/subject-types');
            return;
        }

        $this->typeService->create($data, $schoolId);
        $this->flashSuccess('Subject type added.');
        $this->redirect('/grading/systems/' . $systemId . '/subject-types');
    }

    public function update($params): void
    {
        $this->requirePermission('grading.manage');

        $id       = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $type = $this->typeService->find($id, $schoolId);
        if (!$type) {
            $this->flashError('Subject type not found.');
            $this->redirect('/grading/systems');
            return;
        }

        $data = $this->collectInput((int)$type['grading_system_id'], $schoolId, $id);
        if ($data === null) {
            $this->redirect('/grading/systems/' . $type['grading_system_id'] . '/subject-types');
            return;
        }

        $this->typeService->update($id, $data, $schoolId);
        $this->flashSuccess('Subject type updated.');
        $this->redirect('/grading/systems/' . $type['grading_system_id'] . '/subject-types');
    }

    public function delete($params): void
    {
        $this->requirePermission('grading.manage');

        $id       = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $type = $this->typeService->find($id, $schoolId);
        if (!$type) {
            $this->json(['error' => 'Subject type not found'], 404);
        }

        $inUse = $this->db->fetch(
            "SELECT COUNT(*) AS c FROM subjects WHERE grading_subject_type_id = :id",
            ['id' => $id]
        );

        if (!empty($inUse['c'])) {
            $this->json(['error' => 'Cannot delete a subject type that is in use by subjects.'], 400);
        }

        $this->typeService->delete($id, $schoolId);
        $this->json(['success' => true]);
    }

    public function options($params): void
    {
        if (!$this->auth->check()) {
            $this->json([]);
        }

        $systemId = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $system = $this->db->fetch(
            "SELECT id FROM grading_systems WHERE id = :id AND school_id = :s",
            ['id' => $systemId, 's' => $schoolId]
        );

        if (!$system) {
            $this->json([]);
        }

        $types = $this->typeService->getForSystem($systemId, true);

        $this->json(array_map(static function (array $t): array {
            return [
                'id'            => (int)$t['id'],
                'name'          => $t['name'],
                'code'          => $t['code'],
                'is_graded'     => (bool)$t['is_graded'],
                'is_subsidiary' => (bool)$t['is_subsidiary'],
            ];
        }, $types));
    }

    private function collectInput(int $systemId, int $schoolId, ?int $ignoreId = null): ?array
    {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '' || $code === '') {
            $this->flashError('Name and code are required.');
            return null;
        }

        $sql = "SELECT id FROM grading_subject_types
                WHERE grading_system_id = :sys AND UPPER(code) = :code";
        $params = ['sys' => $systemId, 'code' => strtoupper($code)];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $exists = $this->db->fetch($sql, $params);
        if ($exists) {
            $this->flashError('A subject type with that code already exists in this system.');
            return null;
        }

        $isSubsidiary = isset($_POST['is_subsidiary']) ? 1 : 0;

        return [
            'grading_system_id'    => $systemId,
            'name'                 => $name,
            'code'                 => $code,
            'is_graded'            => isset($_POST['is_graded']) ? 1 : 0,
            'is_subsidiary'        => $isSubsidiary,
            'subsidiary_pass_mark' => $isSubsidiary ? (int)($_POST['subsidiary_pass_mark'] ?? 40) : null,
            'subsidiary_score'     => $isSubsidiary ? (int)($_POST['subsidiary_score']     ?? 1)  : 1,
            'display_order'        => (int)($_POST['display_order'] ?? 0),
            'status'               => $_POST['status'] ?? 'active',
        ];
    }
}