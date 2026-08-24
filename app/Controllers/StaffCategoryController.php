<?php
namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\StaffService;

class StaffCategoryController extends Controller
{
    private $staffService;

    public function __construct()
    {
        parent::__construct();
        $this->staffService = new StaffService();
    }

    public function index(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('/login');
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $categories = $this->staffService->getAllCategories($schoolId);

        echo $this->view->renderWithLayout('staff/categories/index', 'default', [
            'title' => 'Staff Categories',
            'categories' => $categories
        ]);
    }

    public function store(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('/login');
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'code' => trim($_POST['code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'status' => $_POST['status'] ?? 'active'
        ];

        try {
            $this->staffService->createCategory($data, $schoolId);
            $_SESSION['flash_success'] = 'Category created successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create category: ' . $e->getMessage();
        }

        $this->redirect('/staff/categories');
    }

    public function update(): void
    {
        if (!$this->auth->check()) {
            $this->redirect('/login');
        }

        $schoolId = $this->auth->getUser()->school_id ?? 1;
        $id = (int)($_POST['id'] ?? 0);

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'code' => trim($_POST['code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'status' => $_POST['status'] ?? 'active'
        ];

        try {
            $this->staffService->updateCategory($id, $data, $schoolId);
            $_SESSION['flash_success'] = 'Category updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update category: ' . $e->getMessage();
        }

        $this->redirect('/staff/categories');
    }
}