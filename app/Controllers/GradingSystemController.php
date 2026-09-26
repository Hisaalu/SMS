<?php
// File: /app/Controllers/GradingSystemController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use Throwable;

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
                    (SELECT COUNT(*) FROM grading_rules         WHERE system_id = gs.id)        AS rule_count,
                    (SELECT COUNT(*) FROM grading_subject_types WHERE grading_system_id = gs.id) AS type_count
             FROM grading_systems gs
             LEFT JOIN classes c         ON gs.class_id = c.id
             LEFT JOIN academic_years ay ON gs.academic_year_id = ay.id
             WHERE gs.school_id = :school_id OR gs.school_id IS NULL
             ORDER BY gs.is_default DESC, gs.name ASC",
            ['school_id' => $schoolId]
        );

        foreach ($systems as &$system) {
            $system['classes'] = $this->classesForSystem((int)$system['id'], $schoolId);
        }
        unset($system);

        echo $this->view->renderWithLayout('examinations/grading/index', 'default', [
            'title'   => 'Grading Systems',
            'systems' => $systems,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('grading.manage');

        echo $this->view->renderWithLayout('examinations/grading/create', 'default', [
            'title'           => 'Create Grading System',
            'classes'         => $this->classesForSchool(),
            'years'           => $this->academicYearsForSchool(),
            'selectedClasses' => [],
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

        $classIds = $this->collectClassIds($schoolId);

        try {
            $this->db->beginTransaction();

            if ($data['is_default']) {
                $this->clearDefaultFlag($schoolId);
            }

            $data['class_id'] = $classIds[0] ?? null;

            $systemId = $this->db->insert('grading_systems', array_merge($data, [
                'school_id'  => $schoolId,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]));

            if (!$systemId) {
                throw new \RuntimeException('Failed to insert grading system.');
            }

            $this->syncSystemClasses((int)$systemId, $schoolId, $classIds);

            $this->db->commit();

            $this->audit('Grading System Created', 'examinations', "Created grading system: {$data['name']}");
            $this->flashSuccess('Grading system created. Now configure its subject types and grade rules.');
            $this->redirect('/grading/systems/' . $systemId . '/subject-types');

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to create grading system: ' . $e->getMessage());
            $this->redirect('/grading/systems/create');
        }
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
            'title'           => 'Edit Grading System',
            'system'          => $system,
            'classes'         => $this->classesForSchool(),
            'years'           => $this->academicYearsForSchool(),
            'selectedClasses' => $this->classIdsForSystem($id, $this->schoolId()),
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

        $classIds = $this->collectClassIds($schoolId);

        try {
            $this->db->beginTransaction();

            if ($data['is_default']) {
                $this->clearDefaultFlag($schoolId, $id);
            }

            $data['class_id'] = $classIds[0] ?? null;

            $ok = $this->db->update('grading_systems', array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s'),
            ]), ['id' => $id]);

            if ($ok === false) {
                throw new \RuntimeException('Update returned false.');
            }

            $this->syncSystemClasses($id, $schoolId, $classIds);

            $this->db->commit();

            $this->audit('Grading System Updated', 'examinations', "Updated grading system: {$data['name']}");
            $this->flashSuccess('Grading system updated successfully.');
            $this->redirect('/grading/systems');

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to update grading system: ' . $e->getMessage());
            $this->redirect('/grading/systems/' . $id . '/edit');
        }
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

        try {
            $this->db->beginTransaction();

            $this->db->execute(
                "DELETE FROM grading_system_classes WHERE grading_system_id = :id",
                ['id' => $id]
            );

            if (!$this->db->delete('grading_systems', ['id' => $id])) {
                throw new \RuntimeException('Delete returned false.');
            }

            $this->db->commit();

            $this->audit('Grading System Deleted', 'examinations', "Deleted grading system: {$system['name']}");
            $this->json(['success' => true]);

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->json(['error' => 'Failed to delete grading system: ' . $e->getMessage()], 500);
        }
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
            'academic_year_id' => !empty($_POST['academic_year_id']) ? (int)$_POST['academic_year_id'] : null,
            'is_default'       => isset($_POST['is_default']) ? 1 : 0,
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

        return array_map(fn($r) => (int)$r['id'], $rows);
    }

    private function syncSystemClasses(int $systemId, int $schoolId, array $classIds): void
    {
        $this->db->execute(
            "DELETE FROM grading_system_classes WHERE grading_system_id = :id",
            ['id' => $systemId]
        );

        $now = date('Y-m-d H:i:s');

        foreach ($classIds as $classId) {
            $this->db->insert('grading_system_classes', [
                'grading_system_id' => $systemId,
                'class_id'          => (int)$classId,
                'created_at'        => $now,
            ]);
        }
    }

    private function classIdsForSystem(int $systemId, int $schoolId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT class_id FROM grading_system_classes
                 WHERE grading_system_id = :id",
                ['id' => $systemId]
            );
            return array_map(fn($r) => (int)$r['class_id'], $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function classesForSystem(int $systemId, int $schoolId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT c.id, c.name
                 FROM grading_system_classes gsc
                 INNER JOIN classes c ON c.id = gsc.class_id
                 WHERE gsc.grading_system_id = :id
                 ORDER BY c.name ASC",
                ['id' => $systemId]
            ) ?: [];
        } catch (Throwable $e) {
            return [];
        }
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