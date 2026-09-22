<?php
// File: /app/Controllers/AttendanceStatusController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class AttendanceStatusController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('attendance.manage');

        $schoolId = $this->schoolId();

        $statuses = $this->db->fetchAll(
            "SELECT * FROM attendance_statuses
             WHERE school_id = :school_id
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('attendance/statuses/index', 'default', [
            'title'    => 'Attendance Statuses',
            'statuses' => $statuses,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('attendance.manage');

        echo $this->view->renderWithLayout('attendance/statuses/create', 'default', [
            'title' => 'Create Attendance Status',
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('attendance.manage');

        $schoolId = $this->schoolId();
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));

        if ($name === '' || $code === '') {
            $this->flashError('Name and code are required.');
            $this->redirect('/attendance/statuses/create');
        }

        $existing = $this->db->fetch(
            "SELECT id FROM attendance_statuses
             WHERE school_id = :school_id AND (name = :name OR code = :code)",
            ['school_id' => $schoolId, 'name' => $name, 'code' => $code]
        );

        if ($existing) {
            $this->flashError('A status with this name or code already exists.');
            $this->redirect('/attendance/statuses/create');
        }

        $result = $this->db->insert('attendance_statuses', [
            'school_id'         => $schoolId,
            'name'              => $name,
            'code'              => $code,
            'description'       => trim($_POST['description'] ?? ''),
            'counts_as_present' => isset($_POST['counts_as_present']) ? 1 : 0,
            'counts_as_absent'  => isset($_POST['counts_as_absent'])  ? 1 : 0,
            'counts_as_late'    => isset($_POST['counts_as_late'])    ? 1 : 0,
            'requires_reason'   => isset($_POST['requires_reason'])   ? 1 : 0,
            'status'            => 'active',
            'display_order'     => (int)($_POST['display_order'] ?? 0),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        if ($result) {
            $this->audit('Attendance Status Created', 'attendance', "Created attendance status: {$name} ({$code})");
            $this->flashSuccess('Attendance status created successfully.');
            $this->redirect('/attendance/statuses');
        }

        $this->flashError('Failed to create attendance status.');
        $this->redirect('/attendance/statuses/create');
    }

    public function edit($params): void
    {
        $this->requirePermission('attendance.manage');

        $id = (int)($params['id'] ?? 0);
        $status = $this->db->fetch(
            "SELECT * FROM attendance_statuses WHERE id = :id",
            ['id' => $id]
        );

        if (!$status) {
            $this->flashError('Status not found.');
            $this->redirect('/attendance/statuses');
        }

        echo $this->view->renderWithLayout('attendance/statuses/edit', 'default', [
            'title'  => 'Edit Attendance Status',
            'status' => (object)$status,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('attendance.manage');

        $id = (int)($params['id'] ?? 0);
        $status = $this->db->fetch(
            "SELECT * FROM attendance_statuses WHERE id = :id",
            ['id' => $id]
        );

        if (!$status) {
            $this->flashError('Status not found.');
            $this->redirect('/attendance/statuses');
        }

        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));

        if ($name === '' || $code === '') {
            $this->flashError('Name and code are required.');
            $this->redirect('/attendance/statuses/' . $id . '/edit');
        }

        $result = $this->db->update('attendance_statuses', [
            'name'              => $name,
            'code'              => $code,
            'description'       => trim($_POST['description'] ?? ''),
            'counts_as_present' => isset($_POST['counts_as_present']) ? 1 : 0,
            'counts_as_absent'  => isset($_POST['counts_as_absent'])  ? 1 : 0,
            'counts_as_late'    => isset($_POST['counts_as_late'])    ? 1 : 0,
            'requires_reason'   => isset($_POST['requires_reason'])   ? 1 : 0,
            'status'            => $_POST['status'] ?? 'active',
            'display_order'     => (int)($_POST['display_order'] ?? 0),
            'updated_at'        => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        if ($result !== false) {
            $this->audit('Attendance Status Updated', 'attendance', "Updated attendance status: {$name} ({$code})");
            $this->flashSuccess('Attendance status updated successfully.');
            $this->redirect('/attendance/statuses');
        }

        $this->flashError('Failed to update attendance status.');
        $this->redirect('/attendance/statuses/' . $id . '/edit');
    }

    public function delete($params): void
    {
        $this->requirePermission('attendance.manage');

        $id = (int)($params['id'] ?? 0);
        $status = $this->db->fetch(
            "SELECT * FROM attendance_statuses WHERE id = :id",
            ['id' => $id]
        );

        if (!$status) {
            $this->json(['error' => 'Status not found'], 404);
        }

        $usage = $this->db->fetch(
            "SELECT COUNT(*) AS count FROM attendance_records WHERE attendance_status_id = :id",
            ['id' => $id]
        );

        if ($usage && (int)$usage['count'] > 0) {
            $this->json(['error' => "Cannot delete status: used in {$usage['count']} attendance records."], 400);
        }

        $name = $status['name'];

        if ($this->db->delete('attendance_statuses', ['id' => $id])) {
            $this->audit('Attendance Status Deleted', 'attendance', "Deleted attendance status: {$name}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete status'], 500);
    }
}