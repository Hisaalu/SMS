<?php
// File: /app/Controllers/StudentCategoryController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Core\Database;
use Exception;

class StudentCategoryController extends Controller
{
    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('student_categories.view')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = (int) $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        $categories = $db->fetchAll(
            "SELECT * FROM student_categories WHERE school_id = :school_id ORDER BY display_order ASC, id DESC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('students/categories/index', 'default', [
            'categories' => $categories
        ]);
    }

    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('student_categories.create')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        echo $this->view->renderWithLayout('students/categories/create', 'default');
    }

    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('student_categories.create')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = (int) $this->auth->getUser()->school_id;
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');

        if (empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Category Name and Code are required.';
            header('Location: ' . BASE_URL . '/student-categories/create');
            exit;
        }

        $db = Database::getInstance();

        try {
            $existing = $db->fetch(
                "SELECT id FROM student_categories WHERE school_id = :school_id AND (code = :code OR name = :name)",
                ['school_id' => $schoolId, 'code' => $code, 'name' => $name]
            );

            if ($existing) {
                $_SESSION['flash_error'] = 'A category with this Name or Code already exists.';
                header('Location: ' . BASE_URL . '/student-categories/create');
                exit;
            }

            $db->execute(
                "INSERT INTO student_categories (school_id, name, code, description, status) 
                 VALUES (:school_id, :name, :code, :description, 'active')",
                [
                    'school_id'   => $schoolId,
                    'name'        => $name,
                    'code'        => $code,
                    'description' => !empty($description) ? $description : null
                ]
            );

            $_SESSION['flash_success'] = 'Student category created successfully.';
            header('Location: ' . BASE_URL . '/student-categories');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Error creating category: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/student-categories/create');
            exit;
        }
    }

    public function edit(array $params = []): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('student_categories.edit')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        $schoolId = (int) $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        $category = $db->fetch(
            "SELECT * FROM student_categories WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!$category) {
            $_SESSION['flash_error'] = 'Category not found.';
            header('Location: ' . BASE_URL . '/student-categories');
            exit;
        }

        echo $this->view->renderWithLayout('students/categories/edit', 'default', [
            'category' => $category
        ]);
    }

    public function update(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('student_categories.edit')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $schoolId = (int) $this->auth->getUser()->school_id;
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($id) || empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Invalid request data.';
            header('Location: ' . BASE_URL . '/student-categories');
            exit;
        }

        $db = Database::getInstance();

        try {
            // Check for code/name collisions excluding current record
            $existing = $db->fetch(
                "SELECT id FROM student_categories 
                 WHERE school_id = :school_id AND (code = :code OR name = :name) AND id != :id",
                ['school_id' => $schoolId, 'code' => $code, 'name' => $name, 'id' => $id]
            );

            if ($existing) {
                $_SESSION['flash_error'] = 'Another category with this Name or Code already exists.';
                header('Location: ' . BASE_URL . '/student-categories/edit?id=' . $id);
                exit;
            }

            $db->update(
                'student_categories',
                [
                    'name'        => $name,
                    'code'        => $code,
                    'description' => !empty($description) ? $description : null,
                    'status'      => $status
                ],
                [
                    'id'        => $id,
                    'school_id' => $schoolId
                ]
            );

            $_SESSION['flash_success'] = 'Student category updated successfully.';
            header('Location: ' . BASE_URL . '/student-categories');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Error updating category: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/student-categories/edit?id=' . $id);
            exit;
        }
    }

    public function delete(): void
    {
        if (!$this->auth->check() || !$this->auth->user()->can('student_categories.delete')) {
            http_response_code(403);
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $schoolId = (int) $this->auth->getUser()->school_id;
        $db = Database::getInstance();

        try {
            // Prevent deletion if students are associated with this category
            $usageCheck = $db->fetch(
                "SELECT COUNT(*) as count FROM students WHERE current_category_id = :id AND school_id = :school_id",
                ['id' => $id, 'school_id' => $schoolId]
            );

            if (!empty($usageCheck['count']) && $usageCheck['count'] > 0) {
                $_SESSION['flash_error'] = 'Cannot delete category: Assigned to ' . $usageCheck['count'] . ' student(s).';
                header('Location: ' . BASE_URL . '/student-categories');
                exit;
            }

            $db->delete('student_categories', ['id' => $id, 'school_id' => $schoolId]);

            $_SESSION['flash_success'] = 'Student category deleted successfully.';
            header('Location: ' . BASE_URL . '/student-categories');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Error deleting category: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/student-categories');
            exit;
        }
    }
}