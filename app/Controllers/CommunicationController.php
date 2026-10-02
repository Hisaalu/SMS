<?php
// File: /app/Controllers/CommunicationController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Services\CommunicationService;
use Throwable;

class CommunicationController extends Controller
{
    private CommunicationService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CommunicationService();
    }

    public function index(): void
    {
        $this->requirePermission('communication.view');
        $this->renderHistory('parents');
    }

    public function staffIndex(): void
    {
        $this->requirePermission('communication.view');
        $this->renderHistory('staff');
    }

    private function renderHistory(string $audience): void
    {
        $schoolId = $this->schoolId();

        $filters = array_filter([
            'date_from'      => $_GET['date_from']      ?? null,
            'date_to'        => $_GET['date_to']        ?? null,
            'message_type'   => $_GET['message_type']   ?? null,
            'recipient_type' => $audience,
        ]);

        echo $this->view->renderWithLayout('communication/index', 'default', [
            'title'           => $audience === 'staff' ? 'Staff Communication' : 'Communication',
            'audience'        => $audience,
            'history'         => $this->service->getHistory($schoolId, $filters),
            'messageTypes'    => $this->service->getMessageTypes($schoolId, $audience),
            'selectedFilters' => $filters,
        ]);
    }

    public function compose(): void
    {
        $this->requirePermission('communication.send');
        $this->renderCompose('parents');
    }

    public function staffCompose(): void
    {
        $this->requirePermission('communication.send');
        $this->renderCompose('staff');
    }

    private function renderCompose(string $audience): void
    {
        $schoolId = $this->schoolId();

        echo $this->view->renderWithLayout('communication/compose', 'default', [
            'title'        => $audience === 'staff' ? 'Staff Communication' : 'Compose Message',
            'audience'     => $audience,
            'filters'      => $this->service->getComposeFilters($schoolId),
            'staffFilters' => $this->service->getStaffComposeFilters($schoolId),
            'messageTypes' => $this->service->getMessageTypes($schoolId, $audience),
        ]);
    }

    public function preview(): void
    {
        $this->requirePermission('communication.send');

        $schoolId = $this->schoolId();
        $input    = $this->collectInput();

        $filters = [
            'audience'         => $input['audience'],
            'academic_year_id' => $input['academic_year_id'],
            'term_id'          => $input['term_id'],
            'section_id'       => $input['section_id'],
            'class_id'         => $input['class_id'],
            'stream_id'        => $input['stream_id'],
            'staff_category_id'=> $input['staff_category_id'],
            'staff_status_id'  => $input['staff_status_id'],
            'department_id'    => $input['department_id'],
        ];

        $recipients = $this->service->resolveRecipients($schoolId, $filters);

        $meta = [
            'academic_year'  => $this->lookupName('academic_years', $input['academic_year_id']),
            'term'           => $this->lookupName('terms', $input['term_id']),
            'fees_due_date'  => $input['fees_due_date'],
            'event_date'     => $input['event_date'],
            'custom_subject' => $input['custom_subject'],
            'custom_note'    => $input['custom_note'],
        ];

        $rendered = [];
        foreach ($recipients as $r) {
            $ctx = $this->service->buildContext($r, $meta, $schoolId);
            if (!empty($r['staff'])) {
                $name  = strtoupper(trim(($r['staff']['first_name'] ?? '') . ' ' . ($r['staff']['last_name'] ?? '')));
                $phone = (string)($r['staff']['phone'] ?? '');
                $email = (string)($r['staff']['email'] ?? '');
                $sid   = null;
                $gid   = null;
            } else {
                $name  = (string)($r['guardian']['full_name'] ?? (($r['student']['first_name'] ?? '') . ' ' . ($r['student']['last_name'] ?? '')));
                $phone = (string)($r['guardian']['phone'] ?? '');
                $email = (string)($r['guardian']['email'] ?? '');
                $sid   = (int)($r['student']['id'] ?? 0);
                $gid   = !empty($r['guardian']['guardian_id']) ? (int)$r['guardian']['guardian_id'] : null;
            }

            $rendered[] = [
                'student_id'      => $sid,
                'guardian_id'     => $gid,
                'recipient_name'  => $name,
                'recipient_phone' => $phone,
                'recipient_email' => $email,
                'subject'         => $this->service->render($input['subject'], $ctx),
                'resolved_body'   => $this->service->render($input['body'], $ctx),
            ];
        }

        echo $this->view->renderWithLayout('communication/preview', 'default', [
            'title'      => 'Preview Message',
            'audience'   => $input['audience'],
            'input'      => $input,
            'meta'       => $meta,
            'recipients' => $rendered,
            'total'      => count($rendered),
        ]);
    }

    public function send(): void
    {
        $this->requirePermission('communication.send');

        $schoolId = $this->schoolId();
        $input    = $this->collectInput();

        $filters = [
            'audience'         => $input['audience'],
            'academic_year_id' => $input['academic_year_id'],
            'term_id'          => $input['term_id'],
            'section_id'       => $input['section_id'],
            'class_id'         => $input['class_id'],
            'stream_id'        => $input['stream_id'],
            'staff_category_id'=> $input['staff_category_id'],
            'staff_status_id'  => $input['staff_status_id'],
            'department_id'    => $input['department_id'],
        ];

        $recipients = $this->service->resolveRecipients($schoolId, $filters);

        $meta = [
            'academic_year'  => $this->lookupName('academic_years', $input['academic_year_id']),
            'term'           => $this->lookupName('terms', $input['term_id']),
            'fees_due_date'  => $input['fees_due_date'],
            'event_date'     => $input['event_date'],
            'custom_subject' => $input['custom_subject'],
            'custom_note'    => $input['custom_note'],
        ];

        $rows = [];
        foreach ($recipients as $r) {
            $ctx = $this->service->buildContext($r, $meta, $schoolId);

            if (!empty($r['staff'])) {
                $name  = strtoupper(trim(($r['staff']['first_name'] ?? '') . ' ' . ($r['staff']['last_name'] ?? '')));
                $phone = (string)($r['staff']['phone'] ?? '');
                $email = (string)($r['staff']['email'] ?? '');
                $sid   = null;
                $gid   = null;
            } else {
                $name  = (string)($r['guardian']['full_name'] ?? (($r['student']['first_name'] ?? '') . ' ' . ($r['student']['last_name'] ?? '')));
                $phone = (string)($r['guardian']['phone'] ?? '');
                $email = (string)($r['guardian']['email'] ?? '');
                $sid   = (int)($r['student']['id'] ?? 0);
                $gid   = !empty($r['guardian']['guardian_id']) ? (int)$r['guardian']['guardian_id'] : null;
            }

            $rows[] = [
                'student_id'      => $sid,
                'guardian_id'     => $gid,
                'recipient_name'  => $name,
                'recipient_phone' => $phone,
                'recipient_email' => $email,
                'subject'         => $this->service->render($input['subject'], $ctx),
                'resolved_body'   => $this->service->render($input['body'], $ctx),
            ];
        }

        try {
            $count = $this->service->dispatch(
                $schoolId,
                (int)$this->auth->id(),
                array_merge($input, ['__recipients' => $rows])
            );

            $this->audit('Communication Sent', 'communication', "Sent {$count} {$input['audience']} message(s): {$input['subject']}");
            $this->flashSuccess("Message queued for {$count} recipient(s).");

            $redirect = $input['audience'] === 'staff' ? '/staff/communication' : '/communication';
            $this->redirect($redirect);

        } catch (Throwable $e) {
            $this->flashError('Failed to send: ' . $e->getMessage());
            $back = $input['audience'] === 'staff' ? '/staff/communication/compose' : '/communication/compose';
            $this->redirect($back);
        }
    }

    private function collectInput(): array
    {
        return [
            'audience'          => (string)($_POST['audience'] ?? 'parents'),
            'academic_year_id'  => (int)($_POST['academic_year_id'] ?? 0),
            'term_id'           => (int)($_POST['term_id'] ?? 0),
            'section_id'        => (int)($_POST['section_id'] ?? 0),
            'class_id'          => (int)($_POST['class_id'] ?? 0),
            'stream_id'         => (int)($_POST['stream_id'] ?? 0),
            'staff_category_id' => (int)($_POST['staff_category_id'] ?? 0),
            'staff_status_id'   => (int)($_POST['staff_status_id'] ?? 0),
            'department_id'     => (int)($_POST['department_id'] ?? 0),
            'message_type'      => (string)($_POST['message_type'] ?? 'custom'),
            'channel'           => (string)($_POST['channel'] ?? 'sms'),
            'subject'           => (string)($_POST['subject'] ?? ''),
            'body'              => (string)($_POST['body'] ?? ''),
            'fees_due_date'     => (string)($_POST['fees_due_date'] ?? ''),
            'event_date'        => (string)($_POST['event_date'] ?? ''),
            'custom_subject'    => (string)($_POST['custom_subject'] ?? ''),
            'custom_note'       => (string)($_POST['custom_note'] ?? ''),
        ];
    }

    private function lookupName(string $table, int $id): string
    {
        if ($id <= 0) return '';
        try {
            $row = $this->db->fetch("SELECT name FROM {$table} WHERE id = :id", ['id' => $id]);
            return $row['name'] ?? '';
        } catch (Throwable $e) {
            return '';
        }
    }
}