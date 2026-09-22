<?php
// File: /app/Controllers/AttendanceSessionController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class AttendanceSessionController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();

        $sessions = $this->db->fetchAll(
            "SELECT * FROM attendance_sessions
             WHERE school_id = :school_id OR school_id IS NULL
             ORDER BY display_order ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('attendance/sessions/index', 'default', [
            'title'    => 'Attendance Sessions',
            'sessions' => $sessions,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        echo $this->view->renderWithLayout('attendance/sessions/create', 'default', [
            'title' => 'Create Attendance Session',
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $schoolId = $this->schoolId();
        $name = trim($_POST['name'] ?? '');
        $code = strtoupper(trim($_POST['code'] ?? ''));

        if ($name === '' || $code === '') {
            $this->flashError('Name and code are required.');
            $this->redirect('/attendance/sessions/create');
        }

        $this->db->insert('attendance_sessions', [
            'school_id'     => $schoolId,
            'name'          => $name,
            'code'          => $code,
            'description'   => trim($_POST['description'] ?? ''),
            'start_time'    => $_POST['start_time'] ?: null,
            'end_time'      => $_POST['end_time']   ?: null,
            'status'        => 'active',
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->flashSuccess('Attendance session created successfully.');
        $this->redirect('/attendance/sessions');
    }

    public function edit($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);
        $session = $this->db->fetch(
            "SELECT * FROM attendance_sessions WHERE id = :id",
            ['id' => $id]
        );

        if (!$session) {
            $this->flashError('Session not found.');
            $this->redirect('/attendance/sessions');
        }

        echo $this->view->renderWithLayout('attendance/sessions/edit', 'default', [
            'title'   => 'Edit Attendance Session',
            'session' => (object)$session,
        ]);
    }

    public function update($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);

        $this->db->update('attendance_sessions', [
            'name'          => trim($_POST['name'] ?? ''),
            'code'          => strtoupper(trim($_POST['code'] ?? '')),
            'description'   => trim($_POST['description'] ?? ''),
            'start_time'    => $_POST['start_time'] ?: null,
            'end_time'      => $_POST['end_time']   ?: null,
            'status'        => $_POST['status'] ?? 'active',
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'updated_at'    => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        $this->flashSuccess('Attendance session updated successfully.');
        $this->redirect('/attendance/sessions');
    }

    public function delete($params): void
    {
        $this->requireAuth();

        $id = (int)($params['id'] ?? 0);

        if ($this->db->delete('attendance_sessions', ['id' => $id])) {
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete session'], 500);
    }
}