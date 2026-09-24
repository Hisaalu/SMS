<?php
// File: /app/Controllers/DivisionController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\DivisionService;

class DivisionController extends Controller
{
    private DivisionService $divisionService;

    public function __construct()
    {
        parent::__construct();
        $this->divisionService = new DivisionService();
    }

    public function index(): void
    {
        $this->requirePermission('grading.view');

        $schoolId = $this->schoolId();

        $systems = $this->db->fetchAll(
            "SELECT gs.id, gs.name, gs.class_id, c.name AS class_name
             FROM grading_systems gs
             LEFT JOIN classes c ON gs.class_id = c.id
             WHERE gs.school_id = :s
             ORDER BY gs.is_default DESC, c.name ASC, gs.name ASC",
            ['s' => $schoolId]
        );

        $divisions = $this->divisionService->getAll($schoolId, false, 0);

        echo $this->view->renderWithLayout('examinations/grading/divisions/index', 'default', [
            'title'     => 'Divisions',
            'divisions' => $divisions,
            'systems'   => $systems,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('grading.manage');

        $schoolId = $this->schoolId();
        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Name and Code are required.');
            $this->redirect('/grading/divisions');
        }

        if ($data['min_aggregate'] > $data['max_aggregate']) {
            $this->flashError('Minimum aggregate cannot be greater than maximum.');
            $this->redirect('/grading/divisions');
        }

        if ($this->divisionService->hasOverlap(
            $data['min_aggregate'],
            $data['max_aggregate'],
            $schoolId,
            $data['grading_system_id']
        )) {
            $this->flashError("Aggregate range {$data['min_aggregate']}-{$data['max_aggregate']} overlaps with an existing division in the same scope.");
            $this->redirect('/grading/divisions');
        }

        $this->divisionService->create($data, $schoolId);
        $this->flashSuccess('Division created successfully.');
        $this->redirect('/grading/divisions');
    }

    public function update($params): void
    {
        $this->requirePermission('grading.manage');

        $schoolId = $this->schoolId();
        $id = (int)($params['id'] ?? 0);

        $division = $this->divisionService->find($id, $schoolId);
        if (!$division) {
            $this->flashError('Division not found.');
            $this->redirect('/grading/divisions');
        }

        $data = $this->collectInput();
        if ($data === null) {
            $this->flashError('Name and Code are required.');
            $this->redirect('/grading/divisions');
        }

        if ($data['min_aggregate'] > $data['max_aggregate']) {
            $this->flashError('Minimum aggregate cannot be greater than maximum.');
            $this->redirect('/grading/divisions');
        }

        if ($this->divisionService->hasOverlap(
            $data['min_aggregate'],
            $data['max_aggregate'],
            $schoolId,
            $data['grading_system_id'],
            $id
        )) {
            $this->flashError("Aggregate range {$data['min_aggregate']}-{$data['max_aggregate']} overlaps with an existing division in the same scope.");
            $this->redirect('/grading/divisions');
        }

        $this->divisionService->update($id, $data, $schoolId);
        $this->flashSuccess('Division updated successfully.');
        $this->redirect('/grading/divisions');
    }

    public function delete($params): void
    {
        $this->requirePermission('grading.manage');

        $schoolId = $this->schoolId();
        $id = (int)($params['id'] ?? 0);

        $division = $this->divisionService->find($id, $schoolId);
        if (!$division) {
            $this->json(['error' => 'Division not found'], 404);
        }

        $this->divisionService->delete($id, $schoolId);
        $this->json(['success' => true]);
    }

    private function collectInput(): ?array
    {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '' || $code === '') {
            return null;
        }

        return [
            'name'              => $name,
            'code'              => $code,
            'min_aggregate'     => (int)($_POST['min_aggregate'] ?? 0),
            'max_aggregate'     => (int)($_POST['max_aggregate'] ?? 0),
            'description'       => trim($_POST['description'] ?? ''),
            'display_order'     => (int)($_POST['display_order'] ?? 0),
            'status'            => $_POST['status'] ?? 'active',
            'grading_system_id' => !empty($_POST['grading_system_id']) ? (int)$_POST['grading_system_id'] : null,
        ];
    }
}