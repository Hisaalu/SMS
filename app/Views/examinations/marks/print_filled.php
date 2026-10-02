<!-- File: /app/Views/examinations/marks/print_filled.php -->
<style>
    .marks-print-table {
        border: 1px solid #000000 !important;
        border-collapse: collapse !important;
        border-top: 1px solid #000000 !important;
    }
    .marks-print-table > thead > tr > th {
        border-top: 1px solid #000000 !important;
        border-bottom-width: 2px !important;
        background: #f5f5f5;
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-weight: 600;
    }
</style>

<?php
    $meta = [
        ['label' => 'Academic Year', 'value' => $academicYear['name']],
        ['label' => 'Term',          'value' => $term['name']],
        ['label' => 'Class',         'value' => $class['name'] . ($stream ? ' - ' . $stream['name'] : '')],
        ['label' => 'Subject',       'value' => ($subject['code'] ?? $subject['name']) . ' - ' . $subject['name']],
        ['label' => 'Examination',   'value' => $examination['name']],
        ['label' => 'Students',      'value' => count($students)],
    ];

    $print = [
        'title'    => 'Marks Sheet',
        'subtitle' => $examination['name'],
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($students)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No students found</h6>
    </div>
<?php else: ?>
    <table class="table table-bordered align-middle mb-0 text-nowrap marks-print-table">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th style="width: 110px;">ADM NO</th>
                <th style="min-width: 220px;">STUDENT NAME</th>
                <th style="width: 50px;" class="text-center">SEX</th>
                <th style="width: 110px;" class="text-center">MARK</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $i => $st): ?>
                <?php
                    $rawSex = strtoupper(trim($st['gender'] ?? ''));
                    $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F'
                                : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                    $mark = $marks[(int)$st['id']] ?? null;
                ?>
                <tr>
                    <td class="text-center text-muted small"><?= $i + 1 ?></td>
                    <td class="fw-semibold small"><?= htmlspecialchars($st['admission_number']) ?></td>
                    <td class="text-uppercase fw-semibold">
                        <?= htmlspecialchars(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? '')) ?>
                    </td>
                    <td class="text-center small"><?= $sexDisplay ?></td>
                    <td class="text-center fw-bold">
                        <?= $mark !== null ? htmlspecialchars((string)(int)round((float)$mark)) : '-' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="mt-4 pt-3 border-top small">
    <div class="d-flex flex-wrap align-items-end gap-3 mb-2" style="font-size: 0.85rem;">
        <span>
            Approved By: <span style="display:inline-block; min-width: 180px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
        </span>
        <span>
            Signature <span style="display:inline-block; min-width: 180px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
        </span>
        <span>
            Date:
            <span style="display:inline-block; min-width: 22px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
            /
            <span style="display:inline-block; min-width: 22px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
            /
            <span style="display:inline-block; min-width: 30px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
        </span>
    </div>

    <div class="d-flex flex-wrap gap-3" style="font-size: 0.8rem;">
        <span>
            Printed By : <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong>
        </span>
        <span>
            Date : <?= date('Y/m/d, H:i:s') ?>
        </span>
    </div>
</div>