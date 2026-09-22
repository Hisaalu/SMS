<?php
// File: /app/Controllers/StaffStatusController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class StaffStatusController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('staff_statuses.view');

        $schoolId = $this->schoolId();

        $statuses = $this->db->fetchAll(
            "SELECT s.*,
                    (SELECT COUNT(*) FROM staff WHERE staff_status_id = s.id) AS usage_count
             FROM staff_statuses s
             WHERE s.school_id = :school_id
             ORDER BY s.display_order ASC, s.name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('staff/statuses/index', 'default', [
            'title'    => 'Staff Statuses',
            'statuses' => $statuses,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('staff_statuses.create');

        $this->db->execute(
            "INSERT INTO staff_statuses
                (school_id, name, code, description, is_active_status, allows_login, display_order, status)
             VALUES
                (:school_id, :name, :code, :description, :is_active_status, :allows_login, :display_order, 'active')",
            $this->collectInput()
        );

        $this->flashSuccess('Status created successfully.');
        $this->redirect('/staff/statuses');
    }

    public function update(): void
    {
        $this->requirePermission('staff_statuses.edit');

        $schoolId = $this->schoolId();
        $id = (int)($_POST['id'] ?? 0);

        $data = $this->collectInput();
        $data['id'] = $id;

        $this->db->execute(
            "UPDATE staff_statuses
             SET name = :name, code = :code, description = :description,
                 is_active_status = :is_active_status, allows_login = :allows_login,
                 display_order = :display_order
             WHERE id = :id AND school_id = :school_id",
            $data
        );

        $this->flashSuccess('Status updated successfully.');
        $this->redirect('/staff/statuses');
    }

    public function delete(): void
    {
        $this->requirePermission('staff_statuses.delete');

        $schoolId = $this->schoolId();
        $id = (int)($_POST['id'] ?? 0);

        $usage = $this->db->fetch(
            "SELECT COUNT(*) AS count FROM staff
             WHERE staff_status_id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        if (!empty($usage['count']) && (int)$usage['count'] > 0) {
            $this->flashError("Cannot delete status. It is currently assigned to {$usage['count']} staff member(s).");
            $this->redirect('/staff/statuses');
            return;
        }

        $this->db->execute(
            "DELETE FROM staff_statuses WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );

        $this->flashSuccess('Status deleted successfully.');
        $this->redirect('/staff/statuses');
    }

    private function collectInput(): array
    {
        return [
            'school_id'        => $this->schoolId(),
            'name'             => trim($_POST['name'] ?? ''),
            'code'             => strtoupper(trim($_POST['code'] ?? '')),
            'description'      => trim($_POST['description'] ?? ''),
            'is_active_status' => isset($_POST['is_active_status']) ? 1 : 0,
            'allows_login'     => isset($_POST['allows_login']) ? 1 : 0,
            'display_order'    => (int)($_POST['display_order'] ?? 0),
        ];
    }
}