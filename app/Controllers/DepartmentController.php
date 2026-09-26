<?php
// File: /app/Controllers/DepartmentController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Department;
use Throwable;

class DepartmentController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();

        $departments = $this->db->fetchAll(
            "SELECT d.*,
                    (SELECT COUNT(*) FROM subjects s
                        WHERE s.department_id = d.id) AS subjects_count
             FROM departments d
             WHERE d.school_id = :school_id
             ORDER BY d.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('academic/departments/index', 'default', [
            'departments' => $departments,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        echo $this->view->renderWithLayout('academic/departments/create', 'default');
    }

    public function store(): void
    {
        $this->requireAuth();

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Department name is required.');
            $this->redirect('/academic/departments/create');
            return;
        }

        if ($this->nameExists($this->schoolId(), $name)) {
            $this->flashError("A department named \"{$name}\" already exists.");
            $this->redirect('/academic/departments/create');
            return;
        }

        try {
            $department = new Department([
                'school_id' => $this->schoolId(),
                'name'      => $name,
                'code'      => $code,
            ]);

            if (!$department->save()) {
                throw new \RuntimeException('Save returned false.');
            }

            $this->audit('Department Created', 'academics', "Created department: {$name}");
            $this->flashSuccess('Department created successfully.');
            $this->redirect('/academic/departments');

        } catch (Throwable $e) {
            $this->flashError('Failed to create department: ' . $e->getMessage());
            $this->redirect('/academic/departments/create');
        }
    }

    public function edit($params): void
    {
        $this->requireAuth();

        $department = $this->findOrRedirect((int)($params['id'] ?? 0));
        if (!$department) {
            return;
        }

        echo $this->view->renderWithLayout('academic/departments/edit', 'default', [
            'department' => $department,
        ]);
    }

    public function update($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);
        $department = $this->findOrRedirect($id);
        if (!$department) {
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Department name is required.');
            $this->redirect('/academic/departments/' . $id . '/edit');
            return;
        }

        if ($this->nameExists($this->schoolId(), $name, $id)) {
            $this->flashError("Another department named \"{$name}\" already exists.");
            $this->redirect('/academic/departments/' . $id . '/edit');
            return;
        }

        try {
            $department->fill([
                'name' => $name,
                'code' => $code,
            ]);

            if (!$department->save()) {
                throw new \RuntimeException('Save returned false.');
            }

            $this->audit('Department Updated', 'academics', "Updated department: {$name}");
            $this->flashSuccess('Department updated successfully.');
            $this->redirect('/academic/departments');

        } catch (Throwable $e) {
            $this->flashError('Failed to update department: ' . $e->getMessage());
            $this->redirect('/academic/departments/' . $id . '/edit');
        }
    }

    public function delete($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);
        $department = Department::find($id);

        if (!$department) {
            $this->json(['error' => 'Department not found.'], 404);
        }
        
        $subjects = (int)($this->db->fetch(
            "SELECT COUNT(*) AS c FROM subjects WHERE department_id = :id",
            ['id' => $id]
        )['c'] ?? 0);

        if ($subjects > 0) {
            $this->json([
                'error' => "Cannot delete this department: {$subjects} subject(s) are assigned to it. Reassign them first."
            ], 409);
        }

        try {
            $name = $department->name;

            if ($department->delete(['id' => $id])) {
                $this->audit('Department Deleted', 'academics', "Deleted department: {$name}");
                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Failed to delete department.'], 500);

        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to delete department: ' . $e->getMessage()], 500);
        }
    }

    private function findOrRedirect(int $id): ?Department
    {
        $department = Department::find($id);

        if (!$department) {
            $this->flashError('Department not found.');
            $this->redirect('/academic/departments');
            return null;
        }

        return $department;
    }

    private function nameExists(int $schoolId, string $name, ?int $excludeId = null): bool
    {
        $sql    = "SELECT id FROM departments WHERE school_id = :school_id AND name = :name";
        $params = ['school_id' => $schoolId, 'name' => $name];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        return (bool)$this->db->fetch($sql . " LIMIT 1", $params);
    }
}