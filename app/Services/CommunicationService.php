<?php
// File: /app/Services/CommunicationService.php

namespace NexaT\Services;

use NexaT\Core\Database;
use NexaT\Core\SettingsService;
use Throwable;

class CommunicationService
{
    private Database $db;
    private SettingsService $settings;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->settings = new SettingsService();
    }

    public function getMessageTypes(int $schoolId, string $audience = 'parents'): array
    {
        if ($audience === 'staff') {
            return $this->staffMessageTypes();
        }
        return $this->parentMessageTypes();
    }

    private function parentMessageTypes(): array
    {
        $defaults = [
            'fees' => [
                'label'   => 'Fees Reminder',
                'subject' => 'Outstanding Fees Balance — {{term}}',
                'body'    => "Dear {{guardian_name}},\n\nThis is a reminder that {{student_name}} ({{admission_number}}) in {{class}} {{stream}} has an outstanding balance of {{fees_balance}} for {{term}} {{academic_year}}.\n\nKindly clear the balance by {{fees_due_date}}.\n\nThank you,\n{{school_name}}",
            ],
            'visitation' => [
                'label'   => 'Visitation Day',
                'subject' => 'Visitation Day — {{term}}',
                'body'    => "Dear {{guardian_name}},\n\nThis is to invite you to Visitation Day for {{student_name}} ({{class}} {{stream}}) on {{event_date}}.\n\nWe look forward to seeing you.\n\nRegards,\n{{school_name}}",
            ],
            'results' => [
                'label'   => 'Exam Results',
                'subject' => '{{term}} Exam Results',
                'body'    => "Dear {{guardian_name}},\n\n{{student_name}} ({{admission_number}}) sat the {{term}} examinations. Please log in to view the results or contact the class teacher.\n\nRegards,\n{{school_name}}",
            ],
            'event' => [
                'label'   => 'General Event',
                'subject' => '{{custom_subject}}',
                'body'    => "Dear {{guardian_name}},\n\n{{custom_note}}\n\nRegards,\n{{school_name}}",
            ],
            'custom' => [
                'label'   => 'Custom Message',
                'subject' => '',
                'body'    => '',
            ],
        ];

        foreach ($defaults as $key => $tpl) {
            $s = $this->settings->get("communication.templates.{$key}.subject", null);
            $b = $this->settings->get("communication.templates.{$key}.body", null);
            if ($s !== null) $defaults[$key]['subject'] = (string)$s;
            if ($b !== null) $defaults[$key]['body']    = (string)$b;
        }

        return $defaults;
    }

    private function staffMessageTypes(): array
    {
        $defaults = [
            'staff_announcement' => [
                'label'   => 'Staff Announcement',
                'subject' => 'Staff Announcement — {{term}}',
                'body'    => "Dear {{staff_name}},\n\n{{custom_note}}\n\nRegards,\n{{school_name}}",
            ],
            'staff_meeting' => [
                'label'   => 'Staff Meeting',
                'subject' => 'Staff Meeting — {{event_date}}',
                'body'    => "Dear {{staff_name}},\n\nThere will be a staff meeting on {{event_date}}. Kindly attend.\n\nRegards,\n{{school_name}}",
            ],
            'staff_duty' => [
                'label'   => 'Duty Roster',
                'subject' => 'Duty Roster — {{term}}',
                'body'    => "Dear {{staff_name}},\n\nYou have been assigned duties for {{term}} {{academic_year}}. Please review the roster.\n\nRegards,\n{{school_name}}",
            ],
            'staff_event' => [
                'label'   => 'General Staff Event',
                'subject' => '{{custom_subject}}',
                'body'    => "Dear {{staff_name}},\n\n{{custom_note}}\n\nRegards,\n{{school_name}}",
            ],
            'custom' => [
                'label'   => 'Custom Message',
                'subject' => '',
                'body'    => '',
            ],
        ];

        foreach ($defaults as $key => $tpl) {
            $s = $this->settings->get("communication.staff_templates.{$key}.subject", null);
            $b = $this->settings->get("communication.staff_templates.{$key}.body", null);
            if ($s !== null) $defaults[$key]['subject'] = (string)$s;
            if ($b !== null) $defaults[$key]['body']    = (string)$b;
        }

        return $defaults;
    }

    public function getComposeFilters(int $schoolId): array
    {
        return [
            'academic_years' => $this->safeFetchAll(
                "SELECT id, name FROM academic_years WHERE school_id = :s ORDER BY id DESC",
                ['s' => $schoolId]
            ),
            'terms' => $this->safeFetchAll(
                "SELECT id, name, academic_year_id FROM terms WHERE school_id = :s ORDER BY term_number ASC",
                ['s' => $schoolId]
            ),
            'sections' => $this->safeFetchAll(
                "SELECT id, name FROM student_categories WHERE school_id = :s AND status = 'active' ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'classes' => $this->safeFetchAll(
                "SELECT id, name FROM classes WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'streams' => $this->safeFetchAll(
                "SELECT id, name, class_id FROM streams WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
        ];
    }

    public function getStaffComposeFilters(int $schoolId): array
    {
        return [
            'staff_categories' => $this->safeFetchAll(
                "SELECT id, name FROM staff_categories WHERE school_id = :s AND status = 'active' ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'staff_statuses' => $this->safeFetchAll(
                "SELECT id, name FROM staff_statuses WHERE school_id = :s AND status = 'active' ORDER BY name ASC",
                ['s' => $schoolId]
            ),
            'departments' => $this->safeFetchAll(
                "SELECT id, name FROM departments WHERE school_id = :s ORDER BY name ASC",
                ['s' => $schoolId]
            ),
        ];
    }

    public function resolveRecipients(int $schoolId, array $filters): array
    {
        $audience = $filters['audience'] ?? 'parents';

        if ($audience === 'staff') {
            return $this->resolveStaffRecipients($schoolId, $filters);
        }

        return $this->resolveParentRecipients($schoolId, $filters);
    }

    private function resolveParentRecipients(int $schoolId, array $filters): array
    {
        $where  = ["s.school_id = :school_id", "se.status = 'active'"];
        $params = ['school_id' => $schoolId];

        if (!empty($filters['academic_year_id'])) {
            $where[] = "se.academic_year_id = :academic_year_id";
            $params['academic_year_id'] = (int)$filters['academic_year_id'];
        }
        if (!empty($filters['section_id'])) {
            $where[] = "se.student_category_id = :section_id";
            $params['section_id'] = (int)$filters['section_id'];
        }
        if (!empty($filters['class_id'])) {
            $where[] = "se.class_id = :class_id";
            $params['class_id'] = (int)$filters['class_id'];
        }
        if (!empty($filters['stream_id'])) {
            $where[] = "se.stream_id = :stream_id";
            $params['stream_id'] = (int)$filters['stream_id'];
        }

        $whereClause = implode(' AND ', $where);

        $students = $this->safeFetchAll(
            "SELECT s.id, s.admission_number, s.first_name, s.last_name,
                    cl.name AS class_name, str.name AS stream_name,
                    sc.name AS section_name
             FROM students s
             INNER JOIN student_enrollments se ON se.student_id = s.id
             LEFT JOIN classes cl            ON se.class_id = cl.id
             LEFT JOIN streams str           ON se.stream_id = str.id
             LEFT JOIN student_categories sc ON se.student_category_id = sc.id
             WHERE {$whereClause}
             ORDER BY s.last_name ASC, s.first_name ASC",
            $params
        );

        if (empty($students)) {
            return [];
        }

        $studentIds = array_column($students, 'id');
        $ph = implode(',', array_fill(0, count($studentIds), '?'));

        $guardians = $this->safeFetchAll(
            "SELECT sg.student_id, g.id AS guardian_id, g.full_name, g.phone, g.email,
                    sg.relationship, sg.is_primary
             FROM student_guardians sg
             INNER JOIN guardians g ON sg.guardian_id = g.id
             WHERE sg.student_id IN ({$ph})
             ORDER BY sg.is_primary DESC, sg.id ASC",
            $studentIds
        );

        $byStudent = [];
        foreach ($guardians as $g) {
            $byStudent[(int)$g['student_id']][] = $g;
        }

        $rows = [];
        foreach ($students as $s) {
            $sid = (int)$s['id'];
            $gList = $byStudent[$sid] ?? [];

            if (empty($gList)) {
                $rows[] = ['student' => $s, 'guardian' => null];
            } else {
                foreach ($gList as $g) {
                    $rows[] = ['student' => $s, 'guardian' => $g];
                }
            }
        }

        return $rows;
    }

    private function resolveStaffRecipients(int $schoolId, array $filters): array
    {
        $where  = ["s.school_id = :school_id", "ss.status = 'active'"];
        $params = ['school_id' => $schoolId];

        if (!empty($filters['staff_category_id'])) {
            $where[] = "s.staff_category_id = :staff_category_id";
            $params['staff_category_id'] = (int)$filters['staff_category_id'];
        }
        if (!empty($filters['staff_status_id'])) {
            $where[] = "s.staff_status_id = :staff_status_id";
            $params['staff_status_id'] = (int)$filters['staff_status_id'];
        }
        if (!empty($filters['department_id'])) {
            $where[] = "s.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }

        $whereClause = implode(' AND ', $where);

        $staff = $this->safeFetchAll(
            "SELECT s.id, s.staff_number, s.first_name, s.middle_name, s.last_name,
                    s.phone, s.alt_phone, s.email,
                    sc.name AS category_name,
                    ss.name AS status_name,
                    d.name  AS department_name,
                    u.id    AS user_id, u.username, u.email AS user_email
             FROM staff s
             LEFT JOIN staff_categories sc ON s.staff_category_id = sc.id
             LEFT JOIN staff_statuses   ss ON s.staff_status_id   = ss.id
             LEFT JOIN departments      d  ON s.department_id     = d.id
             LEFT JOIN users            u  ON s.user_id           = u.id
             WHERE {$whereClause}
             ORDER BY s.last_name ASC, s.first_name ASC",
            $params
        );

        if (empty($staff)) {
            return [];
        }

        $rows = [];
        foreach ($staff as $s) {
            $rows[] = ['staff' => $s];
        }

        return $rows;
    }

    public function render(string $text, array $ctx): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($m) use ($ctx) {
            $key = $m[1];
            return array_key_exists($key, $ctx) ? (string)$ctx[$key] : '';
        }, $text);
    }

    public function buildContext(array $row, array $meta, int $schoolId): array
    {
        $schoolName = (string)$this->settings->get('school.name', '');

        if (!empty($row['staff'])) {
            $st = $row['staff'];

            $fullName = strtoupper(trim(
                ($st['first_name'] ?? '') . ' ' .
                ($st['middle_name'] ?? '') . ' ' .
                ($st['last_name'] ?? '')
            ));

            return [
                'staff_name'       => $fullName,
                'staff_first_name' => (string)($st['first_name'] ?? ''),
                'staff_last_name'  => (string)($st['last_name']  ?? ''),
                'staff_number'     => (string)($st['staff_number'] ?? ''),
                'staff_phone'      => (string)($st['phone'] ?? ''),
                'staff_email'      => (string)($st['email'] ?? ''),
                'staff_department' => (string)($st['department_name'] ?? ''),
                'staff_category'   => (string)($st['category_name']   ?? ''),
                'staff_status'     => (string)($st['status_name']     ?? ''),

                'academic_year'    => (string)($meta['academic_year']  ?? ''),
                'term'             => (string)($meta['term']           ?? ''),
                'school_name'      => $schoolName,
                'event_date'       => (string)($meta['event_date']     ?? ''),
                'custom_subject'   => (string)($meta['custom_subject'] ?? ''),
                'custom_note'      => (string)($meta['custom_note']    ?? ''),
                'today'            => date('d M Y'),

                'student_name'     => '',
                'student_first_name' => '',
                'student_last_name'  => '',
                'admission_number' => '',
                'class'            => '',
                'stream'           => '',
                'section'          => '',
                'guardian_name'    => $fullName,
                'guardian_phone'   => (string)($st['phone'] ?? ''),
                'guardian_email'   => (string)($st['email'] ?? ''),
                'guardian_relation'=> '',
                'fees_balance'     => '',
                'fees_due_date'    => '',
            ];
        }

        $student  = $row['student']  ?? [];
        $guardian = $row['guardian'] ?? [];

        $fullName = strtoupper(trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')));

        $feesBalance = '';
        try {
            if (method_exists($this, 'getStudentFeesBalance')) {
                $feesBalance = $this->getStudentFeesBalance((int)($student['id'] ?? 0), $schoolId);
            }
        } catch (Throwable $e) {
            $feesBalance = '';
        }

        return [
            'student_name'       => $fullName,
            'student_first_name' => (string)($student['first_name'] ?? ''),
            'student_last_name'  => (string)($student['last_name'] ?? ''),
            'admission_number'   => (string)($student['admission_number'] ?? ''),
            'class'              => (string)($student['class_name'] ?? ''),
            'stream'             => (string)($student['stream_name'] ?? ''),
            'section'            => (string)($student['section_name'] ?? ''),
            'guardian_name'      => (string)($guardian['full_name'] ?? 'Parent/Guardian'),
            'guardian_phone'     => (string)($guardian['phone'] ?? ''),
            'guardian_email'     => (string)($guardian['email'] ?? ''),
            'guardian_relation'  => (string)($guardian['relationship'] ?? ''),
            'academic_year'      => (string)($meta['academic_year'] ?? ''),
            'term'               => (string)($meta['term'] ?? ''),
            'school_name'        => $schoolName,
            'fees_balance'       => $feesBalance,
            'fees_due_date'      => (string)($meta['fees_due_date'] ?? ''),
            'event_date'         => (string)($meta['event_date'] ?? ''),
            'custom_subject'     => (string)($meta['custom_subject'] ?? ''),
            'custom_note'        => (string)($meta['custom_note'] ?? ''),
            'today'              => date('d M Y'),

            'staff_name'       => '',
            'staff_first_name' => '',
            'staff_last_name'  => '',
            'staff_number'     => '',
            'staff_phone'      => '',
            'staff_email'      => '',
            'staff_department' => '',
            'staff_category'   => '',
            'staff_status'     => '',
        ];
    }

    public function dispatch(int $schoolId, int $userId, array $input): int
    {
        $recipients = $input['__recipients'] ?? [];
        if (empty($recipients)) {
            return 0;
        }

        $now    = date('Y-m-d H:i:s');
        $count  = 0;
        $audience = (string)($input['audience'] ?? 'parents');

        $this->db->beginTransaction();
        try {
            foreach ($recipients as $r) {
                $this->db->insert('communication_messages', [
                    'school_id'             => $schoolId,
                    'created_by'            => $userId,
                    'academic_year_id'      => (int)($input['academic_year_id'] ?? 0) ?: null,
                    'term_id'               => (int)($input['term_id'] ?? 0) ?: null,
                    'section_id'            => !empty($input['section_id']) ? (int)$input['section_id'] : null,
                    'class_id'              => !empty($input['class_id'])   ? (int)$input['class_id']   : null,
                    'stream_id'             => !empty($input['stream_id'])  ? (int)$input['stream_id']  : null,
                    'message_type'          => (string)($input['message_type'] ?? 'custom'),
                    'recipient_type'        => $audience,
                    'subject'               => (string)($r['subject'] ?? ''),
                    'body'                  => (string)($input['body'] ?? ''),
                    'channel'               => (string)($input['channel'] ?? 'sms'),
                    'status'                => 'queued',
                    'recipient_student_id'  => !empty($r['student_id']) ? (int)$r['student_id'] : null,
                    'recipient_guardian_id' => !empty($r['guardian_id']) ? (int)$r['guardian_id'] : null,
                    'recipient_name'        => (string)($r['recipient_name'] ?? ''),
                    'recipient_phone'       => (string)($r['recipient_phone'] ?? ''),
                    'recipient_email'       => (string)($r['recipient_email'] ?? ''),
                    'resolved_body'         => (string)($r['resolved_body'] ?? ''),
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
                $count++;
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return $count;
    }

    public function getHistory(int $schoolId, array $filters = []): array
    {
        $where  = ["cm.school_id = :s"];
        $params = ['s' => $schoolId];

        if (!empty($filters['date_from'])) {
            $where[] = "cm.created_at >= :from";
            $params['from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = "cm.created_at <= :to";
            $params['to'] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['message_type'])) {
            $where[] = "cm.message_type = :type";
            $params['type'] = $filters['message_type'];
        }
        if (!empty($filters['recipient_type'])) {
            $where[] = "cm.recipient_type = :rt";
            $params['rt'] = $filters['recipient_type'];
        }

        $whereClause = implode(' AND ', $where);

        return $this->safeFetchAll(
            "SELECT
                cm.subject,
                cm.message_type,
                cm.recipient_type,
                cm.academic_year_id,
                cm.term_id,
                cm.section_id,
                cm.class_id,
                cm.stream_id,
                COUNT(*) AS recipients,
                SUM(CASE WHEN cm.status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                MIN(cm.created_at) AS first_created_at,
                MAX(cm.created_at) AS last_created_at
             FROM communication_messages cm
             WHERE {$whereClause}
             GROUP BY cm.subject, cm.message_type, cm.recipient_type,
                      cm.academic_year_id, cm.term_id,
                      cm.section_id, cm.class_id, cm.stream_id
             ORDER BY last_created_at DESC
             LIMIT 200",
            $params
        );
    }

    private function safeFetchAll(string $sql, array $params = []): array
    {
        try {
            return $this->db->fetchAll($sql, $params) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }
}