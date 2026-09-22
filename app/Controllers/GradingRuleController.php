<?php
// File: /app/Controllers/GradingRuleController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;

class GradingRuleController extends Controller
{
    public function index($params): void
    {
        $this->requirePermission('grading.view');

        $systemId = (int)($params['id'] ?? 0);
        $system = $this->findSystemOrRedirect($systemId);
        if (!$system) {
            return;
        }

        $rules = $this->db->fetchAll(
            "SELECT * FROM grading_rules WHERE system_id = :system_id ORDER BY min_mark DESC",
            ['system_id' => $systemId]
        );

        echo $this->view->renderWithLayout('examinations/grading/rules', 'default', [
            'title'  => 'Grading Rules - ' . $system['name'],
            'system' => $system,
            'rules'  => $rules,
        ]);
    }

    public function store($params): void
    {
        $this->requirePermission('grading.manage');

        $systemId = (int)($params['id'] ?? 0);
        $system = $this->findSystemOrRedirect($systemId);
        if (!$system) {
            return;
        }

        $data = $this->validateRuleInput();
        if ($data === null) {
            $this->redirect('/grading/systems/' . $systemId . '/rules');
            return;
        }

        if ($this->rangeOverlaps($systemId, $data['min_mark'], $data['max_mark'])) {
            $this->flashError('Grade range overlaps with an existing rule.');
            $this->redirect('/grading/systems/' . $systemId . '/rules');
            return;
        }

        $ruleId = $this->db->insert('grading_rules', array_merge($data, [
            'system_id'  => $systemId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]));

        if ($ruleId) {
            $this->audit('Grading Rule Created', 'examinations',
                "Created grading rule: {$data['grade']} ({$data['min_mark']}-{$data['max_mark']})");
            $this->flashSuccess('Grading rule created successfully.');
        } else {
            $this->flashError('Failed to create grading rule.');
        }

        $this->redirect('/grading/systems/' . $systemId . '/rules');
    }

    public function update($params): void
    {
        $this->requirePermission('grading.manage');

        $ruleId = (int)($params['id'] ?? 0);
        $rule = $this->db->fetch(
            "SELECT * FROM grading_rules WHERE id = :id",
            ['id' => $ruleId]
        );

        if (!$rule) {
            $this->flashError('Grading rule not found.');
            $this->redirect('/grading/systems');
            return;
        }

        $data = $this->validateRuleInput();
        if ($data === null) {
            $this->redirect('/grading/systems/' . $rule['system_id'] . '/rules');
            return;
        }

        $ok = $this->db->update('grading_rules', array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]), ['id' => $ruleId]);

        if ($ok !== false) {
            $this->audit('Grading Rule Updated', 'examinations', "Updated grading rule: {$data['grade']}");
            $this->flashSuccess('Grading rule updated successfully.');
        } else {
            $this->flashError('Failed to update grading rule.');
        }

        $this->redirect('/grading/systems/' . $rule['system_id'] . '/rules');
    }

    public function delete($params): void
    {
        $this->requirePermission('grading.manage');

        $ruleId = (int)($params['id'] ?? 0);
        $rule = $this->db->fetch(
            "SELECT * FROM grading_rules WHERE id = :id",
            ['id' => $ruleId]
        );

        if (!$rule) {
            $this->json(['error' => 'Grading rule not found'], 404);
        }

        if ($this->db->delete('grading_rules', ['id' => $ruleId])) {
            $this->audit('Grading Rule Deleted', 'examinations', "Deleted grading rule: {$rule['grade']}");
            $this->json(['success' => true]);
        }

        $this->json(['error' => 'Failed to delete grading rule'], 500);
    }

    private function findSystemOrRedirect(int $systemId): ?array
    {
        $system = $this->db->fetch(
            "SELECT * FROM grading_systems WHERE id = :id",
            ['id' => $systemId]
        );

        if (!$system) {
            $this->flashError('Grading system not found.');
            $this->redirect('/grading/systems');
            return null;
        }

        return $system;
    }

    private function validateRuleInput(): ?array
    {
        $grade   = trim($_POST['grade'] ?? '');
        $minMark = (float)($_POST['min_mark'] ?? 0);
        $maxMark = (float)($_POST['max_mark'] ?? 0);

        if ($grade === '' || $minMark > $maxMark) {
            $this->flashError('Grade is required and min mark must not exceed max mark.');
            return null;
        }

        return [
            'grade'       => $grade,
            'min_mark'    => $minMark,
            'max_mark'    => $maxMark,
            'score'       => (float)($_POST['score'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'pass'        => isset($_POST['pass']) ? 1 : 0,
        ];
    }

    private function rangeOverlaps(int $systemId, float $minMark, float $maxMark): bool
    {
        $overlap = $this->db->fetch(
            "SELECT id FROM grading_rules
             WHERE system_id = :system_id
               AND min_mark <= :max_mark
               AND max_mark >= :min_mark",
            ['system_id' => $systemId, 'min_mark' => $minMark, 'max_mark' => $maxMark]
        );

        return (bool)$overlap;
    }
}