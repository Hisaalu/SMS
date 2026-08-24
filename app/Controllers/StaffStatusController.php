<?php
namespace NexaT\Controllers;

use NexaT\Core\Controller;

class StaffStatusController extends Controller
{
    public function index(): void
    {
        if (method_exists($this, 'authorize')) {
            try {
                $this->authorize('staff_statuses.view');
            } catch (\Exception $e) {
                // Fallback if permission isn't seeded yet
            }
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $statuses = $this->db->fetchAll(
            "SELECT s.*, (SELECT COUNT(*) FROM staff WHERE staff_status_id = s.id) AS usage_count 
             FROM staff_statuses s 
             WHERE s.school_id = :school_id 
             ORDER BY s.display_order ASC, s.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('staff/statuses/index', 'default', [
            'title' => 'Staff Statuses',
            'statuses' => $statuses
        ]);
    }

    public function store(): void
    {
        if (method_exists($this, 'authorize')) {
            try {
                $this->authorize('staff_statuses.create');
            } catch (\Exception $e) {}
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active_status']) ? 1 : 0;
        $allowsLogin = isset($_POST['allows_login']) ? 1 : 0;
        $displayOrder = (int)($_POST['display_order'] ?? 0);

        $this->db->execute(
            "INSERT INTO staff_statuses (school_id, name, code, description, is_active_status, allows_login, display_order, status) 
             VALUES (:school_id, :name, :code, :description, :is_active_status, :allows_login, :display_order, 'active')",
            [
                'school_id' => $schoolId,
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'is_active_status' => $isActive,
                'allows_login' => $allowsLogin,
                'display_order' => $displayOrder
            ]
        );

        $_SESSION['flash_success'] = 'Status created successfully.';
        $this->redirect('/staff/statuses');
    }

    public function update(): void
    {
        if (method_exists($this, 'authorize')) {
            try {
                $this->authorize('staff_statuses.edit');
            } catch (\Exception $e) {}
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $id = (int)($_POST['id'] ?? 0);

        $this->db->execute(
            "UPDATE staff_statuses 
             SET name = :name, code = :code, description = :description, 
                 is_active_status = :is_active_status, allows_login = :allows_login, display_order = :display_order 
             WHERE id = :id AND school_id = :school_id",
            [
                'name' => trim($_POST['name'] ?? ''),
                'code' => strtoupper(trim($_POST['code'] ?? '')),
                'description' => trim($_POST['description'] ?? ''),
                'is_active_status' => isset($_POST['is_active_status']) ? 1 : 0,
                'allows_login' => isset($_POST['allows_login']) ? 1 : 0,
                'display_order' => (int)($_POST['display_order'] ?? 0),
                'id' => $id,
                'school_id' => $schoolId
            ]
        );

        $_SESSION['flash_success'] = 'Status updated successfully.';
        $this->redirect('/staff/statuses');
    }

    public function delete(): void
    {
        if (method_exists($this, 'authorize')) {
            try {
                $this->authorize('staff_statuses.delete');
            } catch (\Exception $e) {}
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $id = (int)($_POST['id'] ?? 0);

        // Check usage count before deletion
        $usage = $this->db->fetch(
            "SELECT COUNT(*) as count FROM staff WHERE staff_status_id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!empty($usage['count']) && $usage['count'] > 0) {
            $_SESSION['flash_error'] = "Cannot delete status. It is currently assigned to {$usage['count']} staff member(s).";
            $this->redirect('/staff/statuses');
            return;
        }

        $this->db->execute(
            "DELETE FROM staff_statuses WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        $_SESSION['flash_success'] = 'Status deleted successfully.';
        $this->redirect('/staff/statuses');
    }
}