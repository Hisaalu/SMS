<?php
// File: /app/Controllers/StudentCategoryController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use Throwable;

class StudentCategoryController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('student_categories.view');

        $schoolId = $this->schoolId();

        $categories = $this->db->fetchAll(
            "SELECT * FROM student_categories
             WHERE school_id = :school_id
             ORDER BY display_order ASC, id DESC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('students/categories/index', 'default', [
            'categories' => $categories,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('student_categories.create');

        echo $this->view->renderWithLayout('students/categories/create', 'default');
    }

    public function store(): void
    {
        $this->requirePermission('student_categories.create');

        $schoolId = $this->schoolId();
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $code === '') {
            $this->flashError('Category Name and Code are required.');
            $this->redirect('/student-categories/create');
        }

        try {
            $existing = $this->db->fetch(
                "SELECT id FROM student_categories
                 WHERE school_id = :school_id AND (code = :code OR name = :name)",
                ['school_id' => $schoolId, 'code' => $code, 'name' => $name]
            );

            if ($existing) {
                $this->flashError('A category with this Name or Code already exists.');
                $this->redirect('/student-categories/create');
            }

            $this->db->execute(
                "INSERT INTO student_categories (school_id, name, code, description, status)
                 VALUES (:school_id, :name, :code, :description, 'active')",
                [
                    'school_id'   => $schoolId,
                    'name'        => $name,
                    'code'        => $code,
                    'description' => $description !== '' ? $description : null,
                ]
            );

            $this->flashSuccess('Student category created successfully.');
            $this->redirect('/student-categories');

        } catch (Throwable $e) {
            $this->flashError('Error creating category: ' . $e->getMessage());
            $this->redirect('/student-categories/create');
        }
    }

    public function edit(array $params = []): void
    {
        $this->requirePermission('student_categories.edit');

        $id = (int)($params['id'] ?? $_GET['id'] ?? 0);
        $schoolId = $this->schoolId();

        $category = $this->db->fetch(
            "SELECT * FROM student_categories WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$category) {
            $this->flashError('Category not found.');
            $this->redirect('/student-categories');
        }

        echo $this->view->renderWithLayout('students/categories/edit', 'default', [
            'category' => $category,
        ]);
    }

    public function update(): void
    {
        $this->requirePermission('student_categories.edit');

        $id = (int)($_POST['id'] ?? 0);
        $schoolId = $this->schoolId();
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if ($id === 0 || $name === '' || $code === '') {
            $this->flashError('Invalid request data.');
            $this->redirect('/student-categories');
        }

        try {
            $existing = $this->db->fetch(
                "SELECT id FROM student_categories
                 WHERE school_id = :school_id AND (code = :code OR name = :name) AND id != :id",
                ['school_id' => $schoolId, 'code' => $code, 'name' => $name, 'id' => $id]
            );

            if ($existing) {
                $this->flashError('Another category with this Name or Code already exists.');
                $this->redirect('/student-categories/edit?id=' . $id);
            }

            $this->db->update('student_categories', [
                'name'        => $name,
                'code'        => $code,
                'description' => $description !== '' ? $description : null,
                'status'      => $status,
            ], ['id' => $id, 'school_id' => $schoolId]);

            $this->flashSuccess('Student category updated successfully.');
            $this->redirect('/student-categories');

        } catch (Throwable $e) {
            $this->flashError('Error updating category: ' . $e->getMessage());
            $this->redirect('/student-categories/edit?id=' . $id);
        }
    }

    public function delete(): void
    {
        $this->requirePermission('student_categories.delete');

        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $schoolId = $this->schoolId();

        try {
            $usage = $this->db->fetch(
                "SELECT COUNT(*) AS count FROM students
                 WHERE current_category_id = :id AND school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );

            if (!empty($usage['count']) && (int)$usage['count'] > 0) {
                $this->flashError("Cannot delete category: assigned to {$usage['count']} student(s).");
                $this->redirect('/student-categories');
            }

            $this->db->delete('student_categories', ['id' => $id, 'school_id' => $schoolId]);
            $this->flashSuccess('Student category deleted successfully.');

        } catch (Throwable $e) {
            $this->flashError('Error deleting category: ' . $e->getMessage());
        }

        $this->redirect('/student-categories');
    }
}