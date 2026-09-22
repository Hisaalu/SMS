<?php
// File: /app/Controllers/ExaminationController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class ExaminationController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('examinations.view');

        $schoolId = $this->schoolId();

        $examinations = $this->db->fetchAll(
            "SELECT e.*, at.name AS assessment_type_name,
                    ay.name AS academic_year_name, t.name AS term_name
             FROM examinations e
             LEFT JOIN assessment_types at ON e.assessment_type_id = at.id
             LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
             LEFT JOIN terms t ON e.academic_period_id = t.id
             WHERE e.school_id = :school_id
             ORDER BY e.created_at DESC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/index', 'default', [
            'title'        => 'Examinations',
            'examinations' => $examinations,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('examinations.create');

        $schoolId = $this->schoolId();

        echo $this->view->renderWithLayout('examinations/create', 'default', [
            'title'           => 'Create Examination',
            'academicYears'   => $this->lookup('academic_years', $schoolId),
            'terms'           => $this->lookup('terms', $schoolId),
            'assessmentTypes' => $this->lookup('assessment_types', $schoolId),
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('examinations.create');

        $schoolId = $this->schoolId();
        $data = $this->collectExaminationInput();

        if ($data === null) {
            $this->flashError('Name, Academic Year, Term, and Assessment Type are required.');
            $this->redirect('/examinations/create');
        }

        $data['school_id']  = $schoolId;
        $data['status']     = 'draft';
        $data['is_published'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        try {
            $examId = $this->db->insert('examinations', $data);

            if (!$examId) {
                throw new \Exception('Insert returned no ID.');
            }

            $this->audit('Examination Created', 'examinations', "Created examination: {$data['name']}");
            $this->flashSuccess('Examination created successfully.');
            $this->redirect('/examinations');

        } catch (\Throwable $e) {
            $this->flashError('Failed to create examination: ' . $e->getMessage());
            $this->redirect('/examinations/create');
        }
    }

    public function edit($params): void
    {
        $this->requirePermission('examinations.edit');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $examination = $this->findExamination($id, $schoolId);
        if (!$examination) {
            $this->flashError('Examination not found.');
            $this->redirect('/examinations');
        }

        echo $this->view->renderWithLayout('examinations/edit', 'default', [
            'title'           => 'Edit Examination',
            'examination'     => $examination,
            'academicYears'   => $this->lookup('academic_years', $schoolId),
            'terms'           => $this->lookup('terms', $schoolId),
            'assessmentTypes' => $this->lookup('assessment_types', $schoolId),
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('examinations.edit');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $existing = $this->findExamination($id, $schoolId);
        if (!$existing) {
            $this->flashError('Examination not found.');
            $this->redirect('/examinations');
        }

        $data = $this->collectExaminationInput();
        if ($data === null) {
            $this->flashError('Name, Academic Year, Term, and Assessment Type are required.');
            $this->redirect('/examinations/' . $id . '/edit');
        }

        $data['status']     = $_POST['status'] ?? 'draft';
        $data['updated_at'] = date('Y-m-d H:i:s');

        try {
            $this->db->update('examinations', $data, ['id' => $id, 'school_id' => $schoolId]);

            $this->audit('Examination Updated', 'examinations', "Updated examination: {$data['name']}");
            $this->flashSuccess('Examination updated successfully.');
            $this->redirect('/examinations');

        } catch (\Throwable $e) {
            $this->flashError('Failed to update examination: ' . $e->getMessage());
            $this->redirect('/examinations/' . $id . '/edit');
        }
    }

    public function delete($params): void
    {
        $this->requirePermission('examinations.delete');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $exam = $this->findExamination($id, $schoolId);
        if (!$exam) {
            $this->json(['error' => 'Examination not found'], 404);
        }

        $marks = $this->db->fetch(
            "SELECT COUNT(*) AS count FROM marks WHERE examination_id = :id",
            ['id' => $id]
        );

        if ($marks && (int)$marks['count'] > 0) {
            $this->json(['error' => 'Cannot delete examination with existing marks.'], 400);
        }

        try {
            if ($this->db->delete('examinations', ['id' => $id])) {
                $this->audit('Examination Deleted', 'examinations', "Deleted examination: {$exam['name']}");
                $this->json(['success' => true]);
            }

            $this->json(['error' => 'Failed to delete examination'], 500);

        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to delete examination'], 500);
        }
    }

    private function findExamination(int $id, int $schoolId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM examinations WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        return $row ?: null;
    }

    private function lookup(string $table, int $schoolId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$table} WHERE school_id = :school_id",
            ['school_id' => $schoolId]
        );
    }

    private function collectExaminationInput(): ?array
    {
        $name = trim($_POST['name'] ?? '');
        $academicYearId   = (int)($_POST['academic_year_id'] ?? 0);
        $academicPeriodId = (int)($_POST['academic_period_id'] ?? 0);
        $assessmentTypeId = (int)($_POST['assessment_type_id'] ?? 0);

        if ($name === '' || !$academicYearId || !$academicPeriodId || !$assessmentTypeId) {
            return null;
        }

        return [
            'name'               => $name,
            'code'               => trim($_POST['code'] ?? ''),
            'description'        => trim($_POST['description'] ?? ''),
            'academic_year_id'   => $academicYearId,
            'academic_period_id' => $academicPeriodId,
            'assessment_type_id' => $assessmentTypeId,
            'start_date'         => $_POST['start_date'] ?: null,
            'end_date'           => $_POST['end_date']   ?: null,
        ];
    }
}