<?php
// File: /app/Controllers/StaffCategoryController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\StaffService;
use Throwable;

class StaffCategoryController extends Controller
{
    private StaffService $staffService;

    public function __construct()
    {
        parent::__construct();
        $this->staffService = new StaffService();
    }

    public function index(): void
    {
        $this->requireAuth();

        $categories = $this->staffService->getAllCategories($this->schoolId());

        echo $this->view->renderWithLayout('staff/categories/index', 'default', [
            'title'      => 'Staff Categories',
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();

        $data = $this->collectInput();

        try {
            $this->staffService->createCategory($data, $this->schoolId());
            $this->flashSuccess('Category created successfully.');
        } catch (Throwable $e) {
            $this->flashError('Failed to create category: ' . $e->getMessage());
        }

        $this->redirect('/staff/categories');
    }

    public function update(): void
    {
        $this->requireAuth();

        $id = (int)($_POST['id'] ?? 0);
        $data = $this->collectInput();

        try {
            $this->staffService->updateCategory($id, $data, $this->schoolId());
            $this->flashSuccess('Category updated successfully.');
        } catch (Throwable $e) {
            $this->flashError('Failed to update category: ' . $e->getMessage());
        }

        $this->redirect('/staff/categories');
    }

    private function collectInput(): array
    {
        return [
            'name'          => trim($_POST['name'] ?? ''),
            'code'          => trim($_POST['code'] ?? ''),
            'description'   => trim($_POST['description'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'status'        => $_POST['status'] ?? 'active',
        ];
    }
}