<?php
// File: /app/Controllers/ClassController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\SchoolClass;
use Throwable;

class ClassController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('classes.view');

        $schoolId = $this->schoolId();

        $classes = $this->db->fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM streams  s WHERE s.class_id = c.id)  AS streams_count,
                    (SELECT COUNT(*) FROM student_enrollments se
                        WHERE se.class_id = c.id AND se.status = 'active')     AS students_count
             FROM classes c
             WHERE c.school_id = :school_id
             ORDER BY c.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('academic/classes/index', 'default', [
            'classes' => $classes,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('classes.create');

        echo $this->view->renderWithLayout('academic/classes/create', 'default');
    }

    public function store(): void
    {
        $this->requirePermission('classes.create');

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Class name is required.');
            $this->redirect('/academic/classes/create');
        }

        if ($this->nameExists($this->schoolId(), $name)) {
            $this->flashError("A class named \"{$name}\" already exists.");
            $this->redirect('/academic/classes/create');
        }

        try {
            $class = new SchoolClass([
                'school_id' => $this->schoolId(),
                'name'      => $name,
                'code'      => $code,
            ]);

            if (!$class->save()) {
                throw new \RuntimeException('Save returned false.');
            }

            $this->audit('Class Created', 'academics', "Created class: {$name}");
            $this->flashSuccess('Class created successfully.');
            $this->redirect('/academic/classes');

        } catch (Throwable $e) {
            $this->flashError('Failed to create class: ' . $e->getMessage());
            $this->redirect('/academic/classes/create');
        }
    }

    public function edit($params): void
    {
        $this->requirePermission('classes.edit');

        $class = $this->findClassOrRedirect((int)($params['id'] ?? 0));
        if (!$class) {
            return;
        }

        echo $this->view->renderWithLayout('academic/classes/edit', 'default', [
            'class' => $class,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('classes.edit');

        $classId = (int)($params['id'] ?? 0);
        $class   = $this->findClassOrRedirect($classId);
        if (!$class) {
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');

        if ($name === '') {
            $this->flashError('Class name is required.');
            $this->redirect('/academic/classes/' . $classId . '/edit');
        }

        if ($this->nameExists($this->schoolId(), $name, $classId)) {
            $this->flashError("Another class named \"{$name}\" already exists.");
            $this->redirect('/academic/classes/' . $classId . '/edit');
        }

        try {
            $class->fill(['name' => $name, 'code' => $code]);

            if (!$class->save()) {
                throw new \RuntimeException('Save returned false.');
            }

            $this->audit('Class Updated', 'academics', "Updated class: {$name}");
            $this->flashSuccess('Class updated successfully.');
            $this->redirect('/academic/classes');

        } catch (Throwable $e) {
            $this->flashError('Failed to update class: ' . $e->getMessage());
            $this->redirect('/academic/classes/' . $classId . '/edit');
        }
    }

    public function delete($params): void
    {
        $this->requirePermission('classes.delete');

        $classId = (int)($params['id'] ?? 0);
        $class   = SchoolClass::find($classId);

        if (!$class) {
            $this->json(['error' => 'Class not found.'], 404);
        }

        $streams = (int)($this->db->fetch(
            "SELECT COUNT(*) AS c FROM streams WHERE class_id = :id",
            ['id' => $classId]
        )['c'] ?? 0);

        if ($streams > 0) {
            $this->json([
                'error' => "Cannot delete this class: {$streams} stream(s) still belong to it. Remove or reassign them first."
            ], 409);
        }

        $enrollments = (int)($this->db->fetch(
            "SELECT COUNT(*) AS c FROM student_enrollments
             WHERE class_id = :id AND status = 'active'",
            ['id' => $classId]
        )['c'] ?? 0);

        if ($enrollments > 0) {
            $this->json([
                'error' => "Cannot delete this class: {$enrollments} active student enrollment(s) reference it."
            ], 409);
        }

        try {
            $name = $class->name;

            if ($class->delete(['id' => $classId])) {
                $this->audit('Class Deleted', 'academics', "Deleted class: {$name}");
                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Failed to delete class.'], 500);

        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to delete class: ' . $e->getMessage()], 500);
        }
    }

    private function findClassOrRedirect(int $id): ?SchoolClass
    {
        $class = SchoolClass::find($id);

        if (!$class) {
            $this->flashError('Class not found.');
            $this->redirect('/academic/classes');
            return null;
        }

        return $class;
    }

    private function nameExists(int $schoolId, string $name, ?int $excludeId = null): bool
    {
        $sql    = "SELECT id FROM classes WHERE school_id = :school_id AND name = :name";
        $params = ['school_id' => $schoolId, 'name' => $name];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        return (bool)$this->db->fetch($sql . " LIMIT 1", $params);
    }
}