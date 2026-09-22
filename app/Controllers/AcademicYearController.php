<?php
// File: /app/Controllers/AcademicYearController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\AcademicYear;

class AcademicYearController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('academic_years.view');

        $years = AcademicYear::all([], ['start_date' => 'DESC']);

        echo $this->view->renderWithLayout('academic/years/index', 'default', [
            'years' => $years,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('academic_years.create');

        echo $this->view->renderWithLayout('academic/years/create', 'default');
    }

    public function store(): void
    {
        $this->requirePermission('academic_years.create');

        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Name, start date, and end date are required.');
            $this->redirect('/academic/years/create');
        }

        $year = new AcademicYear($data);

        if (!$year->save()) {
            $this->flashError('Failed to create academic year.');
            $this->redirect('/academic/years/create');
        }

        if ($data['is_current']) {
            $year->setCurrent();
        }

        $this->audit('Academic Year Created', 'academics', "Created academic year: {$data['name']}");
        $this->flashSuccess('Academic year created successfully.');
        $this->redirect('/academic/years');
    }

    public function edit($params): void
    {
        $this->requirePermission('academic_years.edit');

        $year = $this->findYearOrRedirect((int)($params['id'] ?? 0));
        if (!$year) {
            return;
        }

        echo $this->view->renderWithLayout('academic/years/edit', 'default', [
            'year' => $year,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('academic_years.edit');

        $yearId = (int)($params['id'] ?? 0);
        $year = $this->findYearOrRedirect($yearId);
        if (!$year) {
            return;
        }

        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Name, start date, and end date are required.');
            $this->redirect('/academic/years/' . $yearId . '/edit');
        }

        $year->fill($data);

        if (!$year->save()) {
            $this->flashError('Failed to update academic year.');
            $this->redirect('/academic/years/' . $yearId . '/edit');
        }

        if ($data['is_current']) {
            $year->setCurrent();
        }

        $this->audit('Academic Year Updated', 'academics', "Updated academic year: {$data['name']}");
        $this->flashSuccess('Academic year updated successfully.');
        $this->redirect('/academic/years');
    }

    public function delete($params): void
    {
        $this->requirePermission('academic_years.delete');

        $yearId = (int)($params['id'] ?? 0);
        $year = AcademicYear::find($yearId);

        if (!$year) {
            $this->json(['error' => 'Academic year not found'], 404);
        }

        $name = $year->name;

        if ($year->delete(['id' => $yearId])) {
            $this->audit('Academic Year Deleted', 'academics', "Deleted academic year: {$name}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete academic year'], 500);
    }

    private function findYearOrRedirect(int $id): ?AcademicYear
    {
        $year = AcademicYear::find($id);

        if (!$year) {
            $this->flashError('Academic year not found.');
            $this->redirect('/academic/years');
            return null;
        }

        return $year;
    }

    private function collectInput(): ?array
    {
        $name      = trim($_POST['name'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate   = $_POST['end_date']   ?? '';

        if ($name === '' || $startDate === '' || $endDate === '') {
            return null;
        }

        return [
            'school_id'  => $this->schoolId(),
            'name'       => $name,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'is_current' => isset($_POST['is_current']) ? 1 : 0,
            'status'     => $_POST['status'] ?? 'active',
        ];
    }
}