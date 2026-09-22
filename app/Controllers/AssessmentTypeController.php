<?php
// File: /app/Controllers/AssessmentTypeController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class AssessmentTypeController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('assessment.types.view');

        $schoolId = $this->schoolId();

        $types = $this->db->fetchAll(
            "SELECT * FROM assessment_types
             WHERE school_id = :school_id
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/assessment/types', 'default', [
            'title' => 'Assessment Types',
            'types' => $types,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('assessment.types.create');

        echo $this->view->renderWithLayout('examinations/assessment/create', 'default', [
            'title' => 'Create Assessment Type',
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('assessment.types.create');

        $schoolId = $this->schoolId();
        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Name and code are required.');
            $this->redirect('/assessment/types/create');
        }

        $existing = $this->db->fetch(
            "SELECT id FROM assessment_types
             WHERE school_id = :school_id AND (name = :name OR code = :code)",
            ['school_id' => $schoolId, 'name' => $data['name'], 'code' => $data['code']]
        );

        if ($existing) {
            $this->flashError('An assessment type with this name or code already exists.');
            $this->redirect('/assessment/types/create');
        }

        $data['school_id']  = $schoolId;
        $data['status']     = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $typeId = $this->db->insert('assessment_types', $data);

        if ($typeId) {
            $this->audit('Assessment Type Created', 'examinations', "Created assessment type: {$data['name']} ({$data['code']})");
            $this->flashSuccess('Assessment type created successfully.');
            $this->redirect('/assessment/types');
        }

        $this->flashError('Failed to create assessment type.');
        $this->redirect('/assessment/types/create');
    }

    public function edit($params): void
    {
        $this->requirePermission('assessment.types.edit');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $type = $this->findType($id, $schoolId);
        if (!$type) {
            $this->flashError('Assessment type not found.');
            $this->redirect('/assessment/types');
        }

        echo $this->view->renderWithLayout('examinations/assessment/edit', 'default', [
            'title' => 'Edit Assessment Type',
            'type'  => $type,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('assessment.types.edit');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $existing = $this->findType($id, $schoolId);
        if (!$existing) {
            $this->flashError('Assessment type not found.');
            $this->redirect('/assessment/types');
        }

        $data = $this->collectInput();
        if ($data === null) {
            $this->flashError('Name and code are required.');
            $this->redirect('/assessment/types/' . $id . '/edit');
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $ok = $this->db->update('assessment_types', $data, ['id' => $id, 'school_id' => $schoolId]);

        if ($ok !== false) {
            $this->audit('Assessment Type Updated', 'examinations', "Updated assessment type: {$data['name']} ({$data['code']})");
            $this->flashSuccess('Assessment type updated successfully.');
            $this->redirect('/assessment/types');
        }

        $this->flashError('Failed to update assessment type.');
        $this->redirect('/assessment/types/' . $id . '/edit');
    }

    public function delete($params): void
    {
        $this->requirePermission('assessment.types.delete');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $type = $this->findType($id, $schoolId);
        if (!$type) {
            $this->json(['error' => 'Assessment type not found'], 404);
        }

        $usage = $this->db->fetch(
            "SELECT COUNT(*) AS count FROM examinations WHERE assessment_type_id = :id",
            ['id' => $id]
        );

        if ($usage && (int)$usage['count'] > 0) {
            $this->json(['error' => "Cannot delete assessment type: used in {$usage['count']} examinations."], 400);
        }

        if ($this->db->delete('assessment_types', ['id' => $id])) {
            $this->audit('Assessment Type Deleted', 'examinations', "Deleted assessment type: {$type['name']}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete assessment type'], 500);
    }

    private function findType(int $id, int $schoolId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM assessment_types WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        return $row ?: null;
    }

    private function collectInput(): ?array
    {
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));

        if ($name === '' || $code === '') {
            return null;
        }

        return [
            'name'          => $name,
            'code'          => $code,
            'description'   => trim($_POST['description'] ?? ''),
            'max_marks'     => (float)($_POST['max_marks'] ?? 100),
            'weight'        => (float)($_POST['weight'] ?? 0),
            'display_order' => (int)($_POST['display_order'] ?? 0),
        ];
    }
}