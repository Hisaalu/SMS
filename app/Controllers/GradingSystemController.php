<?php
// File: /app/Controllers/GradingSystemController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class GradingSystemController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('grading.view');

        $schoolId = $this->schoolId();

        $systems = $this->db->fetchAll(
            "SELECT gs.*,
                    c.name  AS class_name,
                    ay.name AS academic_year_name,
                    (SELECT COUNT(*) FROM grading_rules WHERE system_id = gs.id) AS rule_count
             FROM grading_systems gs
             LEFT JOIN classes c         ON gs.class_id = c.id
             LEFT JOIN academic_years ay ON gs.academic_year_id = ay.id
             WHERE gs.school_id = :school_id OR gs.school_id IS NULL
             ORDER BY gs.is_default DESC, c.name ASC, gs.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/grading/index', 'default', [
            'title'   => 'Grading Systems',
            'systems' => $systems,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('grading.manage');

        echo $this->view->renderWithLayout('examinations/grading/create', 'default', [
            'title'   => 'Create Grading System',
            'classes' => $this->classesForSchool(),
            'years'   => $this->academicYearsForSchool(),
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('grading.manage');

        $schoolId = $this->schoolId();
        $data = $this->validateSystemInput();

        if ($data === null) {
            $this->redirect('/grading/systems/create');
            return;
        }

        if ($data['is_default']) {
            $this->clearDefaultFlag($schoolId);
        }

        $systemId = $this->db->insert('grading_systems', array_merge($data, [
            'school_id'  => $schoolId,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));

        if ($systemId) {
            $this->audit('Grading System Created', 'examinations', "Created grading system: {$data['name']}");
            $this->flashSuccess('Grading system created successfully. Now add its grade rules.');
            $this->redirect('/grading/systems/' . $systemId . '/rules');
            return;
        }

        $this->flashError('Failed to create grading system.');
        $this->redirect('/grading/systems/create');
    }

    public function edit($params): void
    {
        $this->requirePermission('grading.manage');

        $id = (int)($params['id'] ?? 0);
        $system = $this->findSystemOrRedirect($id);
        if (!$system) {
            return;
        }

        echo $this->view->renderWithLayout('examinations/grading/edit', 'default', [
            'title'   => 'Edit Grading System',
            'system'  => $system,
            'classes' => $this->classesForSchool(),
            'years'   => $this->academicYearsForSchool(),
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('grading.manage');

        $id = (int)($params['id'] ?? 0);
        $system = $this->findSystemOrRedirect($id);
        if (!$system) {
            return;
        }

        $schoolId = $this->schoolId();
        $data = $this->validateSystemInput();

        if ($data === null) {
            $this->redirect('/grading/systems/' . $id . '/edit');
            return;
        }

        $data['status'] = $_POST['status'] ?? 'active';

        if ($data['is_default']) {
            $this->clearDefaultFlag($schoolId, $id);
        }

        $ok = $this->db->update('grading_systems', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]), ['id' => $id]);

        if ($ok !== false) {
            $this->audit('Grading System Updated', 'examinations', "Updated grading system: {$data['name']}");
            $this->flashSuccess('Grading system updated successfully.');
            $this->redirect('/grading/systems');
            return;
        }

        $this->flashError('Failed to update grading system.');
        $this->redirect('/grading/systems/' . $id . '/edit');
    }

    public function delete($params): void
    {
        $this->requirePermission('grading.manage');

        $id = (int)($params['id'] ?? 0);
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $id]
        );

        if (!$system) {
            $this->json(['error' => 'Grading system not found'], 404);
        }

        $ruleCount = $this->db->fetch(
            "SELECT COUNT(*) AS count FROM grading_rules WHERE system_id = :id",
            ['id' => $id]
        );

        if ($ruleCount && (int)$ruleCount['count'] > 0) {
            $this->json(['error' => 'Cannot delete grading system with existing rules.'], 400);
        }

        if ($this->db->delete('grading_systems', ['id' => $id])) {
            $this->audit('Grading System Deleted', 'examinations', "Deleted grading system: {$system['name']}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete grading system'], 500);
    }

    private function findSystemOrRedirect(int $id): ?array
    {
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $id]
        );

        if (!$system) {
            $this->flashError('Grading system not found.');
            $this->redirect('/grading/systems');
            return null;
        }

        return $system;
    }

    private function validateSystemInput(): ?array
    {
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $this->flashError('Name is required.');
            return null;
        }

        return [
            'name'             => $name,
            'description'      => trim($_POST['description'] ?? ''),
            'class_id'         => !empty($_POST['class_id'])         ? (int)$_POST['class_id']         : null,
            'academic_year_id' => !empty($_POST['academic_year_id']) ? (int)$_POST['academic_year_id'] : null,
            'is_default'       => isset($_POST['is_default']) ? 1 : 0,
        ];
    }

    private function clearDefaultFlag(int $schoolId, ?int $exceptId = null): void
    {
        if ($exceptId === null) {
            $this->db->execute(
                "UPDATE grading_systems SET is_default = 0 WHERE school_id = :s",
                ['s' => $schoolId]
            );
        } else {
            $this->db->execute(
                "UPDATE grading_systems SET is_default = 0 WHERE school_id = :s AND id != :id",
                ['s' => $schoolId, 'id' => $exceptId]
            );
        }
    }

    private function classesForSchool(): array
    {
        return $this->db->fetchAll(
            "SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC",
            ['s' => $this->schoolId()]
        );
    }

    private function academicYearsForSchool(): array
    {
        return $this->db->fetchAll(
            "SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC",
            ['s' => $this->schoolId()]
        );
    }
}