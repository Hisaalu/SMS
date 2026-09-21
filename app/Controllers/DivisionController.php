<?php
// File: /app/Controllers/DivisionController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\DivisionService;

class DivisionController extends Controller
{
    private $divisionService;

    public function __construct()
    {
        parent::__construct();
        $this->divisionService = new DivisionService();
    }

    public function index(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.view')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $divisions = $this->divisionService->getAll($schoolId);

        echo $this->view->renderWithLayout('examinations/grading/divisions/index', 'default', [
            'title'     => 'Divisions',
            'divisions' => $divisions,
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;

        $name         = trim($_POST['name'] ?? '');
        $code         = trim($_POST['code'] ?? '');
        $minAggregate = (int)($_POST['min_aggregate'] ?? 0);
        $maxAggregate = (int)($_POST['max_aggregate'] ?? 0);
        $description  = trim($_POST['description'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $status       = $_POST['status'] ?? 'active';

        if (empty($name) || empty($code)) {
            $_SESSION['flash_error'] = 'Name and Code are required.';
            header('Location: ' . BASE_URL . '/grading/divisions');
            exit;
        }

        if ($minAggregate > $maxAggregate) {
            $_SESSION['flash_error'] = 'Minimum aggregate cannot be greater than maximum.';
            header('Location: ' . BASE_URL . '/grading/divisions');
            exit;
        }

        if ($this->divisionService->hasOverlap($minAggregate, $maxAggregate, $schoolId)) {
            $_SESSION['flash_error'] = "Aggregate range {$minAggregate}-{$maxAggregate} overlaps with an existing division.";
            header('Location: ' . BASE_URL . '/grading/divisions');
            exit;
        }

        $this->divisionService->create([
            'name'          => $name,
            'code'          => $code,
            'min_aggregate' => $minAggregate,
            'max_aggregate' => $maxAggregate,
            'description'   => $description,
            'display_order' => $displayOrder,
            'status'        => $status,
        ], $schoolId);

        $_SESSION['flash_success'] = 'Division created successfully.';
        header('Location: ' . BASE_URL . '/grading/divisions');
        exit;
    }

    public function update($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $id = (int)($params['id'] ?? 0);

        $division = $this->divisionService->find($id, $schoolId);
        if (!$division) {
            $_SESSION['flash_error'] = 'Division not found.';
            header('Location: ' . BASE_URL . '/grading/divisions');
            exit;
        }

        $name         = trim($_POST['name'] ?? '');
        $code         = trim($_POST['code'] ?? '');
        $minAggregate = (int)($_POST['min_aggregate'] ?? 0);
        $maxAggregate = (int)($_POST['max_aggregate'] ?? 0);
        $description  = trim($_POST['description'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $status       = $_POST['status'] ?? 'active';

        if ($minAggregate > $maxAggregate) {
            $_SESSION['flash_error'] = 'Minimum aggregate cannot be greater than maximum.';
            header('Location: ' . BASE_URL . '/grading/divisions');
            exit;
        }

        if ($this->divisionService->hasOverlap($minAggregate, $maxAggregate, $schoolId, $id)) {
            $_SESSION['flash_error'] = "Aggregate range {$minAggregate}-{$maxAggregate} overlaps with an existing division.";
            header('Location: ' . BASE_URL . '/grading/divisions');
            exit;
        }

        $this->divisionService->update($id, [
            'name'          => $name,
            'code'          => $code,
            'min_aggregate' => $minAggregate,
            'max_aggregate' => $maxAggregate,
            'description'   => $description,
            'display_order' => $displayOrder,
            'status'        => $status,
        ], $schoolId);

        $_SESSION['flash_success'] = 'Division updated successfully.';
        header('Location: ' . BASE_URL . '/grading/divisions');
        exit;
    }

    public function delete($params): void
    {
        if (!$this->auth->check() || !$this->auth->getUser()->hasPermission('grading.manage')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $id = (int)($params['id'] ?? 0);

        $division = $this->divisionService->find($id, $schoolId);
        if (!$division) {
            http_response_code(404);
            echo json_encode(['error' => 'Division not found']);
            exit;
        }

        $this->divisionService->delete($id, $schoolId);
        echo json_encode(['success' => true]);
        exit;
    }
}