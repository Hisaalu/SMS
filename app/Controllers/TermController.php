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
        $this->requirePermission('academic.terms.view');

        $academicYearId = (int)($_GET['year'] ?? 0);

        if ($academicYearId) {
            $terms = Term::where('academic_year_id', $academicYearId);
            $selectedYear = AcademicYear::find($academicYearId);
        } else {
            $terms = Term::all();
            $selectedYear = null;
        }

        $years = AcademicYear::all([], ['start_date' => 'DESC']);

        echo $this->view->renderWithLayout('academic/terms/index', 'default', [
            'terms'        => $terms,
            'years'        => $years,
            'selectedYear' => $selectedYear,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('academic.terms.create');

        $years = AcademicYear::all([], ['start_date' => 'DESC']);
        $selectedYearId = $_GET['year'] ?? null;

        echo $this->view->renderWithLayout('academic/terms/create', 'default', [
            'years'          => $years,
            'selectedYearId' => $selectedYearId,
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('academic.terms.create');

        $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
        $name           = trim($_POST['name'] ?? '');
        $termNumber     = (int)($_POST['term_number'] ?? 1);
        $startDate      = $_POST['start_date'] ?? '';
        $endDate        = $_POST['end_date'] ?? '';
        $isCurrent      = isset($_POST['is_current']) ? 1 : 0;
        $schoolId       = $this->schoolId();

        if ($academicYearId === 0 || $name === '' || $startDate === '' || $endDate === '') {
            $this->flashError('All required fields must be filled out.');
            $this->redirect('/academic/terms/create');
        }

        if ($startDate > $endDate) {
            $this->flashError('Start date must be before end date.');
            $this->redirect('/academic/terms/create');
        }

        $error = $this->validateTermUniqueness($academicYearId, $termNumber, null);
        if ($error) {
            $this->flashError($error);
            $this->redirect('/academic/terms/create');
        }

        $term = new Term([
            'school_id'        => $schoolId,
            'academic_year_id' => $academicYearId,
            'name'             => $name,
            'term_number'      => $termNumber,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'is_current'       => $isCurrent,
        ]);

        try {
            if ($term->save()) {
                $this->audit('Term Created', 'academic', "Created term: {$name} (Year #{$academicYearId})");
                $this->flashSuccess('Term created successfully.');
                $this->redirect('/academic/terms?year=' . $academicYearId);
            }

            $this->flashError('Failed to create term.');
            $this->redirect('/academic/terms/create');

        } catch (\Throwable $e) {
            $this->flashError($this->friendlyTermError($e));
            $this->redirect('/academic/terms/create');
        }
    }

    public function edit($params): void
    {
        $this->requirePermission('academic.terms.edit');

        $termId = (int)($params['id'] ?? 0);
        $term = Term::find($termId);

        if (!$term) {
            $this->flashError('Term not found.');
            $this->redirect('/academic/terms');
        }

        $years = AcademicYear::all([], ['start_date' => 'DESC']);

        echo $this->view->renderWithLayout('academic/terms/edit', 'default', [
            'term'  => $term,
            'years' => $years,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('academic.terms.edit');

        $termId = (int)($params['id'] ?? 0);
        $term = Term::find($termId);

        if (!$term) {
            $this->flashError('Term not found.');
            $this->redirect('/academic/terms');
        }

        $academicYearId = (int)($_POST['academic_year_id'] ?? 0);
        $name           = trim($_POST['name'] ?? '');
        $termNumber     = (int)($_POST['term_number'] ?? 1);
        $startDate      = $_POST['start_date'] ?? '';
        $endDate        = $_POST['end_date'] ?? '';
        $isCurrent      = isset($_POST['is_current']) ? 1 : 0;

        if ($academicYearId === 0 || $name === '' || $startDate === '' || $endDate === '') {
            $this->flashError('All required fields must be filled out.');
            $this->redirect('/academic/terms/' . $termId . '/edit');
        }

        if ($startDate > $endDate) {
            $this->flashError('Start date must be before end date.');
            $this->redirect('/academic/terms/' . $termId . '/edit');
        }

        $error = $this->validateTermUniqueness($academicYearId, $termNumber, $termId);
        if ($error) {
            $this->flashError($error);
            $this->redirect('/academic/terms/' . $termId . '/edit');
        }

        $term->fill([
            'academic_year_id' => $academicYearId,
            'name'             => $name,
            'term_number'      => $termNumber,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'is_current'       => $isCurrent,
        ]);

        try {
            if ($term->save()) {
                $this->audit('Term Updated', 'academic', "Updated term: {$name} (ID: {$termId})");
                $this->flashSuccess('Term updated successfully.');
                $this->redirect('/academic/terms?year=' . $academicYearId);
            }

            $this->flashError('Failed to update term.');
            $this->redirect('/academic/terms/' . $termId . '/edit');

        } catch (\Throwable $e) {
            $this->flashError($this->friendlyTermError($e));
            $this->redirect('/academic/terms/' . $termId . '/edit');
        }
    }

    public function delete($params): void
    {
        $this->requirePermission('academic.terms.delete');

        $termId = (int)($params['id'] ?? 0);
        $term = Term::find($termId);

        if (!$term) {
            $this->json(['error' => 'Term not found'], 404);
        }

        try {
            if ($term->delete(['id' => $termId])) {
                $this->audit('Term Deleted', 'academic', "Deleted term ID: {$termId}");
                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Failed to delete term'], 500);

        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to delete term'], 500);
        }
    }

    private function validateTermUniqueness(int $academicYearId, int $termNumber, ?int $excludeTermId): ?string
    {
        $sql = "SELECT id, name FROM terms
                WHERE academic_year_id = :year
                  AND term_number = :number";

        $params = [
            'year'   => $academicYearId,
            'number' => $termNumber,
        ];

        if ($excludeTermId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeTermId;
        }

        $sql .= " LIMIT 1";

        $existing = $this->db->fetch($sql, $params);

        if ($existing) {
            return "Term number {$termNumber} already exists for this academic year (\"{$existing['name']}\").";
        }

        return null;
    }

    private function friendlyTermError(\Throwable $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, '1062') && str_contains($message, 'uk_terms_year_number')) {
            return 'That term number is already used for this academic year. Please pick a different term number.';
        }

        if (str_contains($message, '1452') || str_contains($message, 'foreign key')) {
            return 'The selected academic year does not exist. Please refresh and try again.';
        }

        if (str_contains($message, '1451') || str_contains($message, 'Cannot delete')) {
            return 'This term cannot be deleted because it is referenced by other records.';
        }

        return 'Something went wrong. Please try again or contact support.';
    }
}