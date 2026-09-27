<!-- File: /app/Views/examinations/marks/print_form.php -->
<style>
    .marks-print-table,
    .marks-print-table > :not(caption) > * > * {
        border-color: #000000 !important;
    }
    .marks-print-table thead th {
        border-bottom-width: 2px !important;
    }
    @media print {
        .marks-print-table,
        .marks-print-table > :not(caption) > * > * {
            border-color: #000000 !important;
        }
    }
</style>

<?php
    $meta = [
        ['label' => 'Academic Year', 'value' => $academicYear['name']],
        ['label' => 'Term',          'value' => $term['name']],
        ['label' => 'Class',         'value' => $class['name']],
    ];

    if (!empty($classHasStreams) && !empty($stream)) {
        $meta[] = ['label' => 'Stream', 'value' => $stream['name']];
    }

    $meta[] = [
        'label' => 'Subject',
        'value' => $subject['name']
                . (!empty($subject['code']) ? ' (' . $subject['code'] . ')' : ''),
    ];
    $meta[] = ['label' => 'Students', 'value' => count($students)];

    $print = [
        'title'    => 'Marks Entry Form',
        'subtitle' => $examination['name'],
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<table class="table table-bordered align-middle mb-0 marks-print-table" style="font-size: 0.82rem;">
    <thead>
        <tr>
            <th class="text-center" style="width: 40px;">#</th>
            <th style="width: 120px;">Adm No.</th>
            <th>Student Name</th>
            <th class="text-center" style="width: 50px;">Sex</th>
            <th class="text-center" style="width: 110px;">Mark</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($students)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    No students are enrolled in this class for the selected academic year.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($students as $index => $student): ?>
                <?php
                    $rawSex = strtoupper(trim($student['gender'] ?? ''));
                    $sex = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F'
                         : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '—');
                ?>
                <tr style="height: 38px;">
                    <td class="text-center text-muted"><?= $index + 1 ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($student['admission_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-uppercase">
                        <?= htmlspecialchars(
                            trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </td>
                    <td class="text-center"><?= $sex ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="row mt-4 g-3" style="font-size: 0.8rem;">
    <div class="col-6">
        <div class="border-top pt-2">
            <div class="text-muted small mb-1">Prepared by</div>
            <div style="height: 32px;"></div>
            <div class="border-top pt-1 small text-muted">Name, Signature &amp; Date</div>
        </div>
    </div>
    <div class="col-6">
        <div class="border-top pt-2">
            <div class="text-muted small mb-1">Approved by</div>
            <div style="height: 32px;"></div>
            <div class="border-top pt-1 small text-muted">Name, Signature &amp; Date</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
    <span>Printed by: <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span>Generated on <?= date('d M Y, H:i') ?></span>
</div>