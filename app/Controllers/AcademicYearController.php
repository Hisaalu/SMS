<?php
// File: /app/Controllers/AcademicYearController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\AcademicYear;
use NexaT\Models\Term;
use NexaT\Services\AuditService;

class AcademicYearController extends Controller
{
    protected $audit;

    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('academic_years.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $years = AcademicYear::all([], ['start_date' => 'DESC']);
        $data = ['years' => $years];
        echo $this->view->renderWithLayout('academic/years/index', 'default', $data);
    }

    public function create(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('academic_years.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        echo $this->view->renderWithLayout('academic/years/create', 'default');
    }
    
    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('academic_years.create')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $name = $_POST['name'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;
        
        // Get current logged-in user's school_id
        $schoolId = $this->auth->getUser()->school_id ?? null;

        if (empty($name) || empty($startDate) || empty($endDate)) {
            $this->view->flash('error', 'Name, start date, and end date are required');
            header('Location: ' . BASE_URL . '/academic/years/create');
            exit;
        }

        $year = new AcademicYear([
            'school_id'  => $schoolId, // <-- Attach school_id here
            'name'       => $name,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'is_current' => $isCurrent,
            'status'     => $status
        ]);

        if ($year->save()) {
            if ($isCurrent) {
                $year->setCurrent();
            }

            $this->audit->log(
                $this->auth->id(),
                'Academic Year Created',
                'academics',
                "Created academic year: {$name}"
            );

            $this->view->flash('success', 'Academic year created successfully');
            header('Location: ' . BASE_URL . '/academic/years');
            exit;
        }

        $this->view->flash('error', 'Failed to create academic year');
        header('Location: ' . BASE_URL . '/academic/years/create');
        exit;
    }

    public function edit($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('academic_years.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $yearId = $params['id'] ?? 0;
        $year = AcademicYear::find($yearId);

        if (!$year) {
            $this->view->flash('error', 'Academic year not found');
            header('Location: ' . BASE_URL . '/academic/years');
            exit;
        }

        $data = ['year' => $year];
        echo $this->view->renderWithLayout('academic/years/edit', 'default', $data);
    }

    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('academic_years.edit')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $yearId = $params['id'] ?? 0;
        $year = AcademicYear::find($yearId);

        if (!$year) {
            $this->view->flash('error', 'Academic year not found');
            header('Location: ' . BASE_URL . '/academic/years');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $status = $_POST['status'] ?? 'active';
        $isCurrent = isset($_POST['is_current']) ? 1 : 0;

        if (empty($name) || empty($startDate) || empty($endDate)) {
            $this->view->flash('error', 'Name, start date, and end date are required');
            header('Location: ' . BASE_URL . '/academic/years/' . $yearId . '/edit');
            exit;
        }

        $year->fill([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'is_current' => $isCurrent
        ]);

        if ($year->save()) {
            if ($isCurrent) {
                $year->setCurrent();
            }

            $this->audit->log(
                $this->auth->id(),
                'Academic Year Updated',
                'academics',
                "Updated academic year: {$name}"
            );

            $this->view->flash('success', 'Academic year updated successfully');
            header('Location: ' . BASE_URL . '/academic/years');
            exit;
        }

        $this->view->flash('error', 'Failed to update academic year');
        header('Location: ' . BASE_URL . '/academic/years/' . $yearId . '/edit');
        exit;
    }

    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('academic_years.delete')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $yearId = $params['id'] ?? 0;
        $year = AcademicYear::find($yearId);

        if (!$year) {
            http_response_code(404);
            echo json_encode(['error' => 'Academic year not found']);
            exit;
        }

        $name = $year->name;
        $deleted = $year->delete(['id' => $yearId]);

        if ($deleted) {
            $this->audit->log(
                $this->auth->id(),
                'Academic Year Deleted',
                'academics',
                "Deleted academic year: {$name}"
            );
            echo json_encode(['success' => true]);
            exit;
        }

        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete academic year']);
        exit;
    }
}