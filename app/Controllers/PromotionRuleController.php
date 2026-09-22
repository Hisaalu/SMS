<?php
// File: /app/Controllers/PromotionRuleController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class PromotionRuleController extends Controller
{
    public function index(): void
    {
        $this->requirePermission('promotion.view');

        $schoolId = $this->schoolId();

        $rules = $this->db->fetchAll(
            "SELECT * FROM promotion_rules WHERE school_id = :school_id ORDER BY name ASC",
            ['school_id' => $schoolId]
        );

        echo $this->view->renderWithLayout('examinations/promotion/index', 'default', [
            'title' => 'Promotion Rules',
            'rules' => $rules,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('promotion.manage');

        echo $this->view->renderWithLayout('examinations/promotion/create', 'default', [
            'title' => 'Create Promotion Rule',
        ]);
    }

    public function store(): void
    {
        $this->requirePermission('promotion.manage');

        $schoolId = $this->schoolId();
        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Rule name is required.');
            $this->redirect('/promotion/rules/create');
        }

        $data['school_id']  = $schoolId;
        $data['status']     = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $ruleId = $this->db->insert('promotion_rules', $data);

        if ($ruleId) {
            $this->audit('Promotion Rule Created', 'examinations', "Created promotion rule: {$data['name']}");
            $this->flashSuccess('Promotion rule created successfully.');
            $this->redirect('/promotion/rules');
        }

        $this->flashError('Failed to create promotion rule.');
        $this->redirect('/promotion/rules/create');
    }

    public function edit($params): void
    {
        $this->requirePermission('promotion.manage');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $rule = $this->findRule($id, $schoolId);
        if (!$rule) {
            $this->flashError('Promotion rule not found.');
            $this->redirect('/promotion/rules');
        }

        echo $this->view->renderWithLayout('examinations/promotion/edit', 'default', [
            'title' => 'Edit Promotion Rule',
            'rule'  => $rule,
        ]);
    }

    public function update($params): void
    {
        $this->requirePermission('promotion.manage');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $existing = $this->findRule($id, $schoolId);
        if (!$existing) {
            $this->flashError('Promotion rule not found.');
            $this->redirect('/promotion/rules');
        }

        $data = $this->collectInput();

        if ($data === null) {
            $this->flashError('Rule name is required.');
            $this->redirect('/promotion/rules/' . $id . '/edit');
        }

        $data['status']     = $_POST['status'] ?? 'active';
        $data['updated_at'] = date('Y-m-d H:i:s');

        $ok = $this->db->update('promotion_rules', $data, ['id' => $id]);

        if ($ok !== false) {
            $this->audit('Promotion Rule Updated', 'examinations', "Updated promotion rule: {$data['name']}");
            $this->flashSuccess('Promotion rule updated successfully.');
            $this->redirect('/promotion/rules');
        }

        $this->flashError('Failed to update promotion rule.');
        $this->redirect('/promotion/rules/' . $id . '/edit');
    }

    public function delete($params): void
    {
        $this->requirePermission('promotion.manage');

        $id = (int)($params['id'] ?? 0);
        $schoolId = $this->schoolId();

        $rule = $this->findRule($id, $schoolId);
        if (!$rule) {
            $this->json(['error' => 'Promotion rule not found'], 404);
        }

        if ($this->db->delete('promotion_rules', ['id' => $id])) {
            $this->audit('Promotion Rule Deleted', 'examinations', "Deleted promotion rule: {$rule['name']}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete promotion rule'], 500);
    }

    private function findRule(int $id, int $schoolId): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM promotion_rules WHERE id = :id AND school_id = :school_id",
            ['id' => $id, 'school_id' => $schoolId]
        );
        return $row ?: null;
    }

    private function collectInput(): ?array
    {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            return null;
        }

        return [
            'name'                 => $name,
            'description'          => trim($_POST['description'] ?? ''),
            'pass_mark'            => (float)($_POST['pass_mark'] ?? 50),
            'min_subjects_passed'  => (int)($_POST['min_subjects_passed'] ?? 0),
            'require_all_subjects' => isset($_POST['require_all_subjects']) ? 1 : 0,
        ];
    }
}