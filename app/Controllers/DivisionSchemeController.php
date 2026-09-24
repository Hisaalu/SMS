<?php
// File: /app/Controllers/DivisionSchemeController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\DivisionSchemeService;
use NexaT\Services\DivisionService;

class DivisionSchemeController extends Controller
{
    private DivisionSchemeService $schemeService;
    private DivisionService $divisionService;

    public function __construct()
    {
        parent::__construct();
        $this->schemeService   = new DivisionSchemeService();
        $this->divisionService = new DivisionService();
    }

    public function index(): void
    {
        $this->requirePermission('grading.view');

        $schoolId = $this->schoolId();
        $schemes  = $this->schemeService->getAll($schoolId);

        echo $this->view->renderWithLayout('examinations/grading/divisions/index', 'default', [
            'title'   => 'Division Schemes',
            'schemes' => $schemes,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('grading.manage');

        echo $this->view->renderWithLayout('examinations/grading/divisions/create', 'default', [
            'title'   => 'New Division Scheme',
            'systems' => $this->systemsForSchool(),
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('grading.manage');

        $schoolId = $this->schoolId();
        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Name and grading system are required.');
            $this->redirect('/grading/divisions/create');
            return;
        }

        $system = $this->db->fetch(
            "SELECT id FROM grading_systems WHERE id = :id AND school_id = :s",
            ['id' => $data['grading_system_id'], 's' => $schoolId]
        );
        if (!$system) {
            $this->flashError('Selected grading system does not belong to your school.');
            $this->redirect('/grading/divisions/create');
            return;
        }

        $schemeId = $this->schemeService->create($data, $schoolId);

        if ($schemeId) {
            $this->audit('Division Scheme Created', 'examinations', "Created scheme: {$data['name']}");
            $this->flashSuccess('Division scheme created. Now add the aggregate ranges.');
            $this->redirect('/grading/divisions/' . $schemeId . '/ranges');
            return;
        }

        $this->flashError('Failed to create division scheme.');
        $this->redirect('/grading/divisions/create');
    }

    public function edit($params): void
    {
        $this->requirePermission('grading.manage');

        $id     = (int)($params['id'] ?? 0);
        $school = $this->schoolId();
        $scheme = $this->schemeService->find($id, $school);

        if (!$scheme) {
            $this->flashError('Division scheme not found.');
            $this->redirect('/grading/divisions');
            return;
        }

        echo $this->view->renderWithLayout('examinations/grading/divisions/edit', 'default', [
            'title'   => 'Edit Division Scheme',
            'scheme'  => $scheme,
            'systems' => $this->systemsForSchool(),
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('grading.manage');

        $id     = (int)($params['id'] ?? 0);
        $school = $this->schoolId();
        $scheme = $this->schemeService->find($id, $school);

        if (!$scheme) {
            $this->flashError('Division scheme not found.');
            $this->redirect('/grading/divisions');
            return;
        }

        $data = $this->collectInput();
        if ($data === null) {
            $this->flashError('Name and grading system are required.');
            $this->redirect('/grading/divisions/' . $id . '/edit');
            return;
        }

        $this->schemeService->update($id, $data, $school);
        $this->flashSuccess('Division scheme updated.');
        $this->redirect('/grading/divisions');
    }

    public function delete($params): void
    {
        $this->requirePermission('grading.manage');

        $id     = (int)($params['id'] ?? 0);
        $school = $this->schoolId();
        $scheme = $this->schemeService->find($id, $school);

        if (!$scheme) {
            $this->json(['error' => 'Scheme not found'], 404);
        }

        $this->schemeService->delete($id, $school);
        $this->json(['success' => true]);
    }

    public function ranges($params): void
    {
        $this->requirePermission('grading.view');

        $id     = (int)($params['id'] ?? 0);
        $school = $this->schoolId();
        $scheme = $this->schemeService->find($id, $school);

        if (!$scheme) {
            $this->flashError('Division scheme not found.');
            $this->redirect('/grading/divisions');
            return;
        }

        $ranges = $this->divisionService->getForScheme($id);

        echo $this->view->renderWithLayout('examinations/grading/divisions/ranges', 'default', [
            'title'  => 'Ranges — ' . $scheme['name'],
            'scheme' => $scheme,
            'ranges' => $ranges,
        ]);
    }

    public function storeRange($params): void
    {
        $this->requirePermission('grading.manage');

        $schemeId = (int)($params['id'] ?? 0);
        $school   = $this->schoolId();
        $scheme   = $this->schemeService->find($schemeId, $school);

        if (!$scheme) {
            $this->flashError('Division scheme not found.');
            $this->redirect('/grading/divisions');
            return;
        }

        $data = $this->collectRangeInput();
        if ($data === null) {
            $this->flashError('Name, code, and range are required.');
            $this->redirect('/grading/divisions/' . $schemeId . '/ranges');
            return;
        }

        if ($data['min_aggregate'] > $data['max_aggregate']) {
            $this->flashError('Minimum aggregate cannot be greater than maximum.');
            $this->redirect('/grading/divisions/' . $schemeId . '/ranges');
            return;
        }

        if ($this->divisionService->hasOverlap($schemeId, $data['min_aggregate'], $data['max_aggregate'])) {
            $this->flashError("Range {$data['min_aggregate']}-{$data['max_aggregate']} overlaps with an existing range in this scheme.");
            $this->redirect('/grading/divisions/' . $schemeId . '/ranges');
            return;
        }

        $data['scheme_id'] = $schemeId;
        $this->divisionService->create($data, $school);

        $this->flashSuccess('Range added.');
        $this->redirect('/grading/divisions/' . $schemeId . '/ranges');
    }

    public function updateRange($params): void
    {
        $this->requirePermission('grading.manage');

        $rangeId = (int)($params['id'] ?? 0);
        $school  = $this->schoolId();

        $range = $this->divisionService->find($rangeId, $school);
        if (!$range) {
            $this->flashError('Range not found.');
            $this->redirect('/grading/divisions');
            return;
        }

        $data = $this->collectRangeInput();
        if ($data === null) {
            $this->flashError('Name, code, and range are required.');
            $this->redirect('/grading/divisions/' . $range['scheme_id'] . '/ranges');
            return;
        }

        if ($data['min_aggregate'] > $data['max_aggregate']) {
            $this->flashError('Minimum aggregate cannot be greater than maximum.');
            $this->redirect('/grading/divisions/' . $range['scheme_id'] . '/ranges');
            return;
        }

        if ($this->divisionService->hasOverlap($range['scheme_id'], $data['min_aggregate'], $data['max_aggregate'], $rangeId)) {
            $this->flashError("Range {$data['min_aggregate']}-{$data['max_aggregate']} overlaps with an existing range in this scheme.");
            $this->redirect('/grading/divisions/' . $range['scheme_id'] . '/ranges');
            return;
        }

        $this->divisionService->update($rangeId, $data, $school);
        $this->flashSuccess('Range updated.');
        $this->redirect('/grading/divisions/' . $range['scheme_id'] . '/ranges');
    }

    public function deleteRange($params): void
    {
        $this->requirePermission('grading.manage');

        $rangeId = (int)($params['id'] ?? 0);
        $school  = $this->schoolId();

        $range = $this->divisionService->find($rangeId, $school);
        if (!$range) {
            $this->json(['error' => 'Range not found'], 404);
        }

        $this->divisionService->delete($rangeId, $school);
        $this->json(['success' => true]);
    }

    private function collectInput(): ?array
    {
        $name   = trim($_POST['name'] ?? '');
        $system = (int)($_POST['grading_system_id'] ?? 0);

        if ($name === '' || $system <= 0) {
            return null;
        }

        return [
            'name'              => $name,
            'grading_system_id' => $system,
            'description'       => trim($_POST['description'] ?? ''),
            'display_order'     => (int)($_POST['display_order'] ?? 0),
            'status'            => $_POST['status'] ?? 'active',
        ];
    }

    private function collectRangeInput(): ?array
    {
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '' || $code === '') {
            return null;
        }

        return [
            'name'          => $name,
            'code'          => $code,
            'min_aggregate' => (int)($_POST['min_aggregate'] ?? 0),
            'max_aggregate' => (int)($_POST['max_aggregate'] ?? 0),
            'description'   => trim($_POST['description'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'status'        => $_POST['status'] ?? 'active',
        ];
    }

    private function systemsForSchool(): array
    {
        return $this->db->fetchAll(
            "SELECT gs.id, gs.name, gs.class_id, c.name AS class_name, gs.is_default
             FROM grading_systems gs
             LEFT JOIN classes c ON gs.class_id = c.id
             WHERE gs.school_id = :s
             ORDER BY gs.is_default DESC, c.name ASC, gs.name ASC",
            ['s' => $this->schoolId()]
        ) ?: [];
    }
}