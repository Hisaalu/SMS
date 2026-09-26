<?php
// File: /app/Controllers/AcademicYearController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Models\AcademicYear;
use Throwable;

class AcademicYearController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('academic_years.view');

        $schoolId = $this->schoolId();

        $years = $this->db->fetchAll(
            "SELECT ay.*,
                    (SELECT COUNT(*) FROM terms t WHERE t.academic_year_id = ay.id) AS terms_count
             FROM academic_years ay
             WHERE ay.school_id = :school_id
             ORDER BY ay.start_date DESC",
            ['school_id' => $schoolId]
        );

        $currentYear = $this->db->fetch(
            "SELECT * FROM academic_years
             WHERE school_id = :school_id AND is_current = 1
             LIMIT 1",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('academic/years/index', 'default', [
            'years'       => $years,
            'currentYear' => $currentYear,
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

        $schoolId = $this->schoolId();
        $data     = $this->collectInput();

        if ($data === null) {
            $this->flashError('Name, start date, and end date are required.');
            $this->redirect('/academic/years/create');
        }

        if ($data['start_date'] > $data['end_date']) {
            $this->flashError('Start date must be before end date.');
            $this->redirect('/academic/years/create');
        }

        try {
            $this->db->beginTransaction();

            if ($data['is_current']) {
                $this->db->execute(
                    "UPDATE academic_years SET is_current = 0
                     WHERE school_id = :school_id AND is_current = 1",
                    ['school_id' => $schoolId]
                );
            }

            $year = new AcademicYear($data);

            if (!$year->save()) {
                throw new \RuntimeException('Failed to save academic year.');
            }

            $this->db->commit();

            $this->audit('Academic Year Created', 'academics', "Created academic year: {$data['name']}");
            $this->flashSuccess('Academic year created successfully.');
            $this->redirect('/academic/years');

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to create academic year: ' . $e->getMessage());
            $this->redirect('/academic/years/create');
        }
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

        $schoolId = $this->schoolId();
        $yearId   = (int)($params['id'] ?? 0);

        $year = $this->findYearOrRedirect($yearId);
        if (!$year) {
            return;
        }

        $data = $this->collectInput();
        if ($data === null) {
            $this->flashError('Name, start date, and end date are required.');
            $this->redirect('/academic/years/' . $yearId . '/edit');
        }

        if ($data['start_date'] > $data['end_date']) {
            $this->flashError('Start date must be before end date.');
            $this->redirect('/academic/years/' . $yearId . '/edit');
        }

        try {
            $this->db->beginTransaction();

            if ($data['is_current']) {
                $this->db->execute(
                    "UPDATE academic_years SET is_current = 0
                     WHERE school_id = :school_id AND id != :id AND is_current = 1",
                    ['school_id' => $schoolId, 'id' => $yearId]
                );
            }

            $year->fill($data);

            if (!$year->save()) {
                throw new \RuntimeException('Failed to update academic year.');
            }

            $stillCurrent = $this->db->fetch(
                "SELECT id FROM academic_years
                 WHERE school_id = :school_id AND is_current = 1 LIMIT 1",
                ['school_id' => $schoolId]
            );

            if (!$stillCurrent) {
                $this->db->execute(
                    "UPDATE academic_years SET is_current = 1 WHERE id = :id",
                    ['id' => $yearId]
                );
            }

            $this->db->commit();

            $this->audit('Academic Year Updated', 'academics', "Updated academic year: {$data['name']}");
            $this->flashSuccess('Academic year updated successfully.');
            $this->redirect('/academic/years');

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to update academic year: ' . $e->getMessage());
            $this->redirect('/academic/years/' . $yearId . '/edit');
        }
    }

    public function setCurrent($params): void
    {
        $this->requirePermission('academic_years.edit');

        $schoolId = $this->schoolId();
        $yearId   = (int)($params['id'] ?? 0);

        $year = AcademicYear::find($yearId);
        if (!$year) {
            $this->flashError('Academic year not found.');
            $this->redirect('/academic/years');
        }

        try {
            $this->db->beginTransaction();

            $this->db->execute(
                "UPDATE academic_years SET is_current = 0
                 WHERE school_id = :school_id AND is_current = 1",
                ['school_id' => $schoolId]
            );

            $this->db->execute(
                "UPDATE academic_years SET is_current = 1 WHERE id = :id AND school_id = :school_id",
                ['id' => $yearId, 'school_id' => $schoolId]
            );

            $this->db->commit();

            $this->audit('Academic Year Set Current', 'academics', "Set current year: {$year->name}");
            $this->flashSuccess('Academic year marked as current.');

        } catch (Throwable $e) {
            $this->db->rollback();
            $this->flashError('Failed to set current year: ' . $e->getMessage());
        }

        $this->redirect('/academic/years');
    }

    public function delete($params): void
    {
        $this->requirePermission('academic_years.delete');

        $yearId = (int)($params['id'] ?? 0);
        $year   = AcademicYear::find($yearId);

        if (!$year) {
            $this->json(['error' => 'Academic year not found'], 404);
        }

        try {
            if (!empty($year->is_current)) {
                $other = $this->db->fetch(
                    "SELECT id FROM academic_years
                     WHERE school_id = :school_id AND id != :id
                     ORDER BY start_date DESC LIMIT 1",
                    ['school_id' => $this->schoolId(), 'id' => $yearId]
                );

                if (!$other) {
                    $this->json(['error' => 'Cannot delete the only academic year.'], 400);
                }
            }

            $name = $year->name;

            if ($year->delete(['id' => $yearId])) {
                $this->audit('Academic Year Deleted', 'academics', "Deleted academic year: {$name}");
                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Failed to delete academic year'], 500);

        } catch (Throwable $e) {
            $this->json(['error' => 'Failed to delete: ' . $e->getMessage()], 500);
        }
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