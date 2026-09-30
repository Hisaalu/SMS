<!-- File: /app/Views/reports/academic/print_class_analysis.php -->
<style>
    .ca-print-table {
        border: 1px solid #000 !important;
        border-collapse: collapse !important;
        border-top: 1px solid #000 !important;
        font-size: 0.78rem;
    }
    .ca-print-table > thead > tr > th {
        border-top: 1px solid #000 !important;
        border-bottom-width: 2px !important;
        background: #eef2f7;
        font-size: 0.68rem;
        letter-spacing: .3px;
        text-transform: uppercase;
        font-weight: 800;
        color: #1a237e;
        vertical-align: middle;
        padding: 4px 4px;
    }
    .ca-print-table td {
        border: 1px solid #222;
        padding: 4px 6px;
        vertical-align: middle;
        color: #000;
    }
    .ca-print-table .ca-num   { text-align: center; color: #555; }
    .ca-print-table .ca-stu   { font-weight: 700; text-transform: uppercase; }
    .ca-print-table .ca-adm   { font-weight: 700; }
    .ca-print-table .ca-marks { text-align: center; }
    .ca-print-table .ca-total,
    .ca-print-table .ca-avg,
    .ca-print-table .ca-agg,
    .ca-print-table .ca-div,
    .ca-print-table .ca-pos   { font-weight: 800; text-align: center; }
    .ca-print-table .ca-total,
    .ca-print-table .ca-avg,
    .ca-print-table .ca-agg   { color: #c62828; }
    .ca-print-table .ca-div   { color: #000; }

    .ca-print-table thead th.ca-noncontrib,
    .ca-print-table td.ca-noncontrib {
        background: #f5f5f5;
        color: #555;
        font-style: italic;
    }

    @media print {
        .ca-print-table { font-size: 10px; }
        .ca-print-table > thead > tr > th { font-size: 9px; }
    }
</style>

<?php
    $print = [
        'title'    => 'Class Performance Analysis',
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($matrix['students']) || empty($matrix['subjects'])): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No data available</h6>
        <p class="small mb-0">Select an Academic Year and a Class to generate the marks matrix.</p>
    </div>
<?php else: ?>

    <table class="table ca-print-table mb-0">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 100px;">ADM NO</th>
                <th style="min-width: 180px;">STUDENT</th>
                <th style="width: 40px;" class="text-center">SEX</th>
                <?php foreach ($matrix['subjects'] as $subj): ?>
                    <?php $isContrib = !isset($subj['contributes']) || !empty($subj['contributes']); ?>
                    <th class="text-center <?= $isContrib ? '' : 'ca-noncontrib' ?>"
                        title="<?= htmlspecialchars($subj['name']) ?><?= $isContrib ? '' : ' (non-contributing)' ?>">
                        <?= htmlspecialchars(strtoupper($subj['code'] ?: $subj['name'])) ?>
                        <?= $isContrib ? '' : '*' ?>
                    </th>
                <?php endforeach; ?>
                <th class="text-center">TOTAL</th>
                <th class="text-center">AVG</th>
                <th class="text-center">AGG</th>
                <th class="text-center">DIV</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matrix['students'] as $i => $student): ?>
                <?php
                    $sid    = (int)$student['id'];
                    $totals = $matrix['totals'][$sid] ?? null;
                    $rawSex = strtoupper(trim($student['gender'] ?? ''));
                    $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true) ? 'F'
                            : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');
                ?>
                <tr>
                    <td class="ca-num"><?= $i + 1 ?></td>
                    <td class="ca-adm"><?= htmlspecialchars($student['admission_number'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="ca-stu">
                        <?= htmlspecialchars(trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td class="ca-num"><?= $sex ?></td>
                    <?php foreach ($matrix['subjects'] as $subj): ?>
                        <?php
                            $subjId    = (int)$subj['id'];
                            $mark      = $matrix['marks'][$sid][$subjId] ?? null;
                            $isContrib = !isset($subj['contributes']) || !empty($subj['contributes']);
                        ?>
                        <td class="ca-marks <?= $isContrib ? '' : 'ca-noncontrib' ?>">
                            <?= $mark !== null ? (int)$mark : '-' ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="ca-total"><?= $totals ? (int)$totals['total'] : '-' ?></td>
                    <td class="ca-avg"><?= $totals ? htmlspecialchars((string)$totals['average']) : '-' ?></td>
                    <td class="ca-agg">
                        <?php if (!$totals): ?>
                            -
                        <?php elseif (!empty($totals['has_missing'])): ?>
                            X
                        <?php else: ?>
                            <?= (int)$totals['aggregate'] ?>
                        <?php endif; ?>
                    </td>
                    <td class="ca-div">
                        <?php if (!$totals): ?>
                            -
                        <?php elseif (!empty($totals['has_missing'])): ?>
                            U
                        <?php elseif (!empty($totals['division_code'])): ?>
                            <?= htmlspecialchars(strtoupper((string)$totals['division_code'])) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="small text-muted mt-2" style="font-style: italic;">
        <span style="text-transform:none;">*</span> Non-contributing subject — shown for reference, not counted in TOTAL, AVG, AGG or DIV.
    </div>

<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
    <span>Printed by: <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span>Generated on <?= date('d M Y, H:i') ?></span>
</div>