<?php
// File: /app/Views/students/admission_letter.php

use NexaT\Core\SettingsService;

$settings = new SettingsService();

$schoolName  = (string) $settings->get('school.name', 'School Name');
$schoolMotto = trim((string) $settings->get('school.motto', ''));

$headteacherName = trim((string) $settings->get('school.headteacher_name', ''));

$paymentCode     = trim((string) $settings->get('school.payment_code', ''));
$paymentMethods  = trim((string) $settings->get(
    'school.payment_methods',
    'Centenary Bank or by Electronic Fund Transfer (EFT) or by BankDrafts, MTN Mobile money or by Airtel money'
));
$requirementsNote = trim((string) $settings->get(
    'school.requirements_note',
    'school rules and regulations, school fees structures, medical and requirements forms'
));

$student    = is_array($student    ?? null) ? $student    : [];
$guardian   = is_array($guardian   ?? null) ? $guardian   : null;
$enrollment = is_array($enrollment ?? null) ? $enrollment : null;
$nextTerm   = is_array($nextTerm   ?? null) ? $nextTerm   : null;

$fullName = strtoupper(trim(
    ($student['first_name']  ?? '') . ' ' .
    ($student['middle_name'] ?? '') . ' ' .
    ($student['last_name']   ?? '')
));

$admissionDate = !empty($student['admission_date'])
    ? date('d M Y', strtotime($student['admission_date']))
    : date('d M Y');

$className  = $enrollment['class_name']         ?? '—';
$streamName = $enrollment['stream_name']        ?? null;
$yearName   = $enrollment['academic_year_name'] ?? '—';

$guardianName = $guardian['full_name']    ?? '';
$guardianRel  = $guardian['relationship'] ?? 'Parent / Guardian';

$serial = $student['admission_number'] ?? '—';

$reportDate = !empty($nextTerm['start_date'])
    ? date('d M Y', strtotime($nextTerm['start_date']))
    : $admissionDate;

$house = $streamName !== null && trim((string) $streamName) !== ''
    ? strtoupper((string) $streamName)
    : 'GENERAL';

$meta = [
    ['label' => 'Date',           'value' => $admissionDate],
    ['label' => 'Reference No',   'value' => (string) $serial],
];

if ($paymentCode !== '') {
    $meta[] = ['label' => 'Payment Code', 'value' => $paymentCode];
}

$print = [
    'title'    => 'Admission Letter',
    'meta'     => $meta,
    'compact'  => false,
];

include __DIR__ . '/../partials/print_header.php';
?>

<style>
    .admission-letter-body {
        font-size: 19px;
        line-height: 1.6;
        color: #111;
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 260px);
    }

    .admission-letter-body .salutation {
        margin: 0.5rem 0 1rem;
        font-weight: 600;
    }

    .admission-letter-body p {
        margin: 0 0 0.75rem;
        text-align: justify;
    }

    .admission-letter-body .signature-block {
        margin-top: auto;
        padding-top: 2rem;
        max-width: 340px;
    }
    .admission-letter-body .signature-block .handwriting {
        height: 52px;
        margin-bottom: 6px;
    }
    .admission-letter-body .signature-block .handwriting img {
        max-height: 52px;
        max-width: 220px;
        object-fit: contain;
        display: block;
    }
    .admission-letter-body .signature-block .sig-rule {
        border-top: 1px solid #111;
        padding-top: 6px;
        font-weight: 800;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    .admission-letter-body .signature-block .role {
        font-size: 17px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #333;
        margin-top: 4px;
    }

    .admission-letter-body .motto-line {
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px dashed #bbb;
        text-align: center;
        font-style: italic;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        font-size: 20px;
        color: #333;
    }

    .admission-letter-body .letter-footer {
        margin-top: 0.75rem;
        font-size: 11px;
        color: #666;
        text-align: center;
        letter-spacing: 0.3px;
    }
</style>

<div class="admission-letter-body">

    <p class="salutation">Dear <?= htmlspecialchars($fullName ?: 'Sir/Madam') ?>,</p>

    <p>
        I am pleased to inform you that you have been admitted to
        <strong><?= htmlspecialchars((string) $className) ?></strong>,
        House (<strong><?= htmlspecialchars($house) ?></strong>).
        I congratulate you on this achievement and welcome you to
        <strong><?= htmlspecialchars($schoolName) ?></strong>.
    </p>

    <p>
        You are expected to report to school on
        <strong><?= htmlspecialchars($reportDate) ?></strong>
        and accompanied by your Parent/Guardian.
    </p>

    <p>
        Attached please, find the
        <strong><?= htmlspecialchars($requirementsNote) ?></strong>.
        Carefully read and understand them for a better stay at school.
    </p>

    <p>
        Use the school payment code
        <?php if ($paymentCode !== ''): ?>
            <strong><?= htmlspecialchars($paymentCode) ?></strong>
        <?php endif; ?>
        provided to pay fees through <?= htmlspecialchars($paymentMethods) ?>
        before reporting to school.
    </p>

    <p>
        I wish you the best of luck and fruitful time while at
        <strong><?= htmlspecialchars($schoolName) ?></strong>.
    </p>

    <div class="signature-block">
        <?php if (!empty($signatureUrl)): ?>
            <div class="handwriting">
                <img src="<?= htmlspecialchars($signatureUrl) ?>" alt="Signature">
            </div>
        <?php endif; ?>

        <?php if ($headteacherName !== ''): ?>
            <div class="sig-rule"><?= htmlspecialchars($headteacherName) ?></div>
        <?php else: ?>
            <div class="sig-rule">&nbsp;</div>
        <?php endif; ?>
        <div class="role">Headteacher</div>
    </div>

    <?php if ($schoolMotto !== ''): ?>
        <div class="motto-line">&ldquo;<?= htmlspecialchars($schoolMotto) ?>&rdquo;</div>
    <?php endif; ?>

    <div class="letter-footer">
        Ref: <?= htmlspecialchars((string) $serial) ?>
        &nbsp;·&nbsp;
        Generated: <?= htmlspecialchars((string) ($generatedAt ?? date('d M Y'))) ?>
    </div>

</div>