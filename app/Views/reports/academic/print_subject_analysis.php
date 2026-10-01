<!-- File: /app/Views/reports/academic/print_subject_analysis.php -->
<style>
    .sa-print-table {
        border: 1px solid #000 !important;
        border-collapse: collapse !important;
        border-top: 1px solid #000 !important;
        font-size: 0.78rem;
    }
    .sa-print-table > thead > tr > th {
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
    .sa-print-table td {
        border: 1px solid #222;
        padding: 4px 6px;
        vertical-align: middle;
        color: #000;
    }
    .sa-print-table .sa-num    { text-align: center; color: #555; }
    .sa-print-table .sa-stu    { font-weight: 700; text-transform: uppercase; }
    .sa-print-table .sa-adm    { font-weight: 700; }
    .sa-print-table .sa-center { text-align: center; }
    .sa-print-table .sa-mark   { text-align: center; font-weight: 800; color: #c62828; }
    .sa-print-table .sa-grade  { text-align: center; font-weight: 800; color: #000; }
    .sa-print-table .sa-remark { font-size: 0.72rem; text-transform: uppercase; color: #c62828; font-weight: 700; }

    .sa-print-summary {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem 1.6rem;
        padding: 6px 10px;
        border: 1px solid #222;
        border-radius: 4px;
        background: #f4f6f9;
        margin-bottom: 10px;
        font-size: 11px;
        color: #000;
    }
    .sa-print-summary .item { display: inline-flex; gap: .3rem; align-items: baseline; }
    .sa-print-summary .label {
        color: #546e7a;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 9.5px;
        letter-spacing: .3px;
    }
    .sa-print-summary .value { font-weight: 800; color: #1a237e; }

    @media print {
        .sa-print-table { font-size: 10px; }
        .sa-print-table > thead > tr > th { font-size: 9px; }
        .sa-print-summary {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<?php
    $print = [
        'title'    => 'Subject Performance Analysis',
        'subtitle' => !empty($matrix['subject'])
            ? ($matrix['subject']['code'] ?: $matrix['subject']['name'])
            : '',
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($matrix['students']) || empty($matrix['subject'])): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No data available</h6>
        <p class="small mb-0">Select an Academic Year, Class, and Subject to generate the analysis.</p>
    </div>
<?php else: ?>

    <?php $sum = $matrix['summary'] ?? []; ?>

    <div class="sa-print-summary">
        <span class="item">
            <span class="label">Subject:</span>
            <span class="value"><?= htmlspecialchars($matrix['subject']['code'] ?: $matrix['subject']['name']) ?></span>
        </span>
        <span class="item">
            <span class="label">Students:</span>
            <span class="value"><?= count($matrix['students']) ?></span>
        </span>
        <span class="item">
            <span class="label">Entered:</span>
            <span class="value"><?= (int)($sum['entered'] ?? 0) ?></span>
        </span>
        <span class="item">
            <span class="label">Missing:</span>
            <span class="value"><?= (int)($sum['missing'] ?? 0) ?></span>
        </span>
        <span class="item">
            <span class="label">Avg Mark:</span>
            <span class="value"><?= htmlspecialchars((string)($sum['avg_mark'] ?? 0)) ?></span>
        </span>
        <span class="item">
            <span class="label">Highest:</span>
            <span class="value"><?= htmlspecialchars((string)($sum['highest'] ?? '-')) ?></span>
        </span>
        <span class="item">
            <span class="label">Lowest:</span>
            <span class="value"><?= htmlspecialchars((string)($sum['lowest'] ?? '-')) ?></span>
        </span>
    </div>

    <table class="table sa-print-table mb-0">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 100px;">ADM NO</th>
                <th style="min-width: 180px;">STUDENT</th>
                <th style="width: 40px;" class="text-center">SEX</th>
                <th class="text-center">MARK</th>
                <th class="text-center">GRADE</th>
                <th class="text-center">SCORE</th>
                <th>REMARK</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matrix['students'] as $i => $st): ?>
                <?php
                    $sid = (int)$st['id'];
                    $m   = $matrix['marks'][$sid] ?? [];
                    $rawSex = strtoupper(trim($st['gender'] ?? ''));
                    $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true) ? 'F'
                            : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');
                ?>
                <tr>
                    <td class="sa-num"><?= $i + 1 ?></td>
                    <td class="sa-adm"><?= htmlspecialchars($st['admission_number'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="sa-stu"><?= htmlspecialchars(trim(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="sa-center"><?= $sex ?></td>
                    <td class="sa-mark"><?= $m['mark'] !== null ? (int)$m['mark'] : '-' ?></td>
                    <td class="sa-grade"><?= htmlspecialchars((string)($m['grade'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="sa-center"><?= (int)($m['score'] ?? 0) ?></td>
                    <td class="sa-remark"><?= htmlspecialchars((string)($m['remark'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
    <span>Printed by: <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span>Generated on <?= date('d M Y, H:i') ?></span>
</div>