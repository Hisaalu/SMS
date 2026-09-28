<?php
// File: /app/Controllers/StaffCategoryController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use Throwable;

class StaffCategoryController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('staff.view');

        $schoolId = $this->schoolId();

        $categories = $this->db->fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM staff s WHERE s.staff_category_id = c.id AND s.school_id = c.school_id) AS usage_count
             FROM staff_categories c
             WHERE c.school_id = :school_id
             ORDER BY c.display_order ASC, c.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('staff/categories/index', 'default', [
            'title'      => 'Staff Categories',
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('staff.create');

        $schoolId = $this->schoolId();

        $name        = trim($_POST['name'] ?? '');
        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $order       = (int)($_POST['display_order'] ?? 0);
        $status      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        if ($name === '' || $code === '') {
            $this->flashError('Category name and code are required.');
            $this->redirect('/staff/categories');
            return;
        }

        try {
            $existing = $this->db->fetch(
                "SELECT id FROM staff_categories
                 WHERE school_id = :school_id AND (code = :code OR name = :name)",
                ['school_id' => $schoolId, 'code' => $code, 'name' => $name]
            );

            if ($existing) {
                $this->flashError('A category with this name or code already exists.');
                $this->redirect('/staff/categories');
                return;
            }

            $this->db->execute(
                "INSERT INTO staff_categories (school_id, name, code, description, display_order, status)
                 VALUES (:school_id, :name, :code, :description, :display_order, :status)",
                [
                    'school_id'     => $schoolId,
                    'name'          => $name,
                    'code'          => $code,
                    'description'   => $description !== '' ? $description : null,
                    'display_order' => $order,
                    'status'        => $status,
                ]
            );

            $this->flashSuccess('Staff category created successfully.');

        } catch (Throwable $e) {
            $this->flashError('Error creating category: ' . $e->getMessage());
        }

        $this->redirect('/staff/categories');
    }

    public function update(): void
    {
        $this->requirePermission('staff.edit');

        $schoolId = $this->schoolId();
        $id       = (int)($_POST['id'] ?? 0);

        $name        = trim($_POST['name'] ?? '');
        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $order       = (int)($_POST['display_order'] ?? 0);
        $status      = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';

        if ($id === 0 || $name === '' || $code === '') {
            $this->flashError('Invalid request data.');
            $this->redirect('/staff/categories');
            return;
        }

        try {
            $existing = $this->db->fetch(
                "SELECT id FROM staff_categories
                 WHERE school_id = :school_id AND (code = :code OR name = :name) AND id != :id",
                ['school_id' => $schoolId, 'code' => $code, 'name' => $name, 'id' => $id]
            );

            if ($existing) {
                $this->flashError('Another category with this name or code already exists.');
                $this->redirect('/staff/categories');
                return;
            }

            $this->db->execute(
                "UPDATE staff_categories
                 SET name = :name, code = :code, description = :description,
                     display_order = :display_order, status = :status
                 WHERE id = :id AND school_id = :school_id",
                [
                    'name'          => $name,
                    'code'          => $code,
                    'description'   => $description !== '' ? $description : null,
                    'display_order' => $order,
                    'status'        => $status,
                    'id'            => $id,
                    'school_id'     => $schoolId,
                ]
            );

            $this->flashSuccess('Staff category updated successfully.');

        } catch (Throwable $e) {
            $this->flashError('Error updating category: ' . $e->getMessage());
        }

        $this->redirect('/staff/categories');
    }

    public function delete(): void
    {
        $this->requirePermission('staff.edit');

        $schoolId = $this->schoolId();
        $id       = (int)($_POST['id'] ?? 0);

        $usage = $this->db->fetch(
            "SELECT COUNT(*) AS count FROM staff
             WHERE staff_category_id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!empty($usage['count']) && (int)$usage['count'] > 0) {
            $this->flashError("Cannot delete category: assigned to {$usage['count']} staff member(s).");
            $this->redirect('/staff/categories');
            return;
        }

        $this->db->execute(
            "DELETE FROM staff_categories WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        $this->flashSuccess('Staff category deleted successfully.');
        $this->redirect('/staff/categories');
    }
}