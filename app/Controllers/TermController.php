<?php
// File: /app/Controllers/TermController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\Term;
use NexaT\Models\AcademicYear;

class TermController extends Controller
{
    public function index(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $academicYearId = $_GET['year'] ?? null;
        
        if ($academicYearId) {
            $terms = Term::where('academic_year_id', $academicYearId);
            $selectedYear = AcademicYear::find($academicYearId);
        } else {
            $terms = Term::all();
            $selectedYear = null;
        }

        $years = AcademicYear::all([], ['start_date' => 'DESC']);

        $data = [
            'terms' => $terms,
            'years' => $years,
            'selectedYear' => $selectedYear
        ];

        echo $this->view->renderWithLayout('academic/terms/index', 'default', $data);
    }

    public function create(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $years = AcademicYear::all([], ['start_date' => 'DESC']);
        $selectedYearId = $_GET['year'] ?? null;

        $data = [
            'years' => $years,
            'selectedYearId' => $selectedYearId
        ];

        echo $this->view->renderWithLayout('academic/terms/create', 'default', $data);
    }

    public function store(): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $academicYearId = $_POST['academic_year_id'] ?? '';
        $name           = $_POST['name'] ?? '';
        $termNumber     = $_POST['term_number'] ?? 1;
        $startDate      = $_POST['start_date'] ?? '';
        $endDate        = $_POST['end_date'] ?? '';
        $isCurrent      = isset($_POST['is_current']) ? 1 : 0;
        $schoolId       = $this->auth->getUser()->school_id ?? null;

        if (empty($academicYearId) || empty($name) || empty($startDate) || empty($endDate)) {
            $this->view->flash('error', 'All required fields must be filled out.');
            header('Location: ' . BASE_URL . '/academic/terms/create');
            exit;
        }

        $term = new Term([
            'school_id'        => $schoolId,
            'academic_year_id' => $academicYearId,
            'name'             => $name,
            'term_number'      => $termNumber,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'is_current'       => $isCurrent
        ]);

        if ($term->save()) {
            $this->view->flash('success', 'Term created successfully.');
            header('Location: ' . BASE_URL . '/academic/terms?year=' . $academicYearId);
            exit;
        }

        $this->view->flash('error', 'Failed to create term.');
        header('Location: ' . BASE_URL . '/academic/terms/create');
        exit;
    }

    public function edit($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $termId = $params['id'] ?? 0;
        $term = Term::find($termId);

        if (!$term) {
            $this->view->flash('error', 'Term not found.');
            header('Location: ' . BASE_URL . '/academic/terms');
            exit;
        }

        $years = AcademicYear::all([], ['start_date' => 'DESC']);

        $data = [
            'term' => $term,
            'years' => $years
        ];

        echo $this->view->renderWithLayout('academic/terms/edit', 'default', $data);
    }

    public function update($params): void
    {
        if (!$this->auth->check()) {
            require VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $termId = $params['id'] ?? 0;
        $term = Term::find($termId);

        if (!$term) {
            $this->view->flash('error', 'Term not found.');
            header('Location: ' . BASE_URL . '/academic/terms');
            exit;
        }

        $academicYearId = $_POST['academic_year_id'] ?? '';
        $name           = $_POST['name'] ?? '';
        $termNumber     = $_POST['term_number'] ?? 1;
        $startDate      = $_POST['start_date'] ?? '';
        $endDate        = $_POST['end_date'] ?? '';
        $isCurrent      = isset($_POST['is_current']) ? 1 : 0;

        if (empty($academicYearId) || empty($name) || empty($startDate) || empty($endDate)) {
            $this->view->flash('error', 'All required fields must be filled out.');
            header('Location: ' . BASE_URL . '/academic/terms/' . $termId . '/edit');
            exit;
        }

        $term->fill([
            'academic_year_id' => $academicYearId,
            'name'             => $name,
            'term_number'      => $termNumber,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'is_current'       => $isCurrent
        ]);

        if ($term->save()) {
            $this->view->flash('success', 'Term updated successfully.');
            header('Location: ' . BASE_URL . '/academic/terms?year=' . $academicYearId);
            exit;
        }

        $this->view->flash('error', 'Failed to update term.');
        header('Location: ' . BASE_URL . '/academic/terms/' . $termId . '/edit');
        exit;
    }
}