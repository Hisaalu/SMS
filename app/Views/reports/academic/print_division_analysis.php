<!-- File: /app/Views/reports/academic/print_division_analysis.php -->
<style>
    .dap-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        font-size: 1rem;
        margin: 0 0 .25rem;
        color: #000;
    }
    .dap-meta {
        text-align: center;
        font-size: .82rem;
        margin-bottom: .75rem;
        color: #000;
    }
    .dap-section {
        text-align: left;
        font-weight: 800;
        font-size: .85rem;
        text-transform: uppercase;
        margin: .85rem 0 .35rem;
        color: #000;
    }
    .dap-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.78rem;
        background: #ffffff;
        color: #000000;
        margin-bottom: 1rem;
    }
    .dap-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        padding: 4px 6px;
        text-align: left;
    }
    .dap-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .dap-table .dap-center { text-align: center; font-weight: 700; }
    .dap-table .dap-bold   { font-weight: 700; }
    .dap-table tfoot td {
        background: #eef2f7;
        font-weight: 800;
    }

    @media print {
        .dap-table { font-size: 10px; }
        .dap-table thead th { font-size: 9px; }
    }
</style>

<?php
    $print = [
        'title'    => 'Division Analysis',
        'subtitle' => ($class['name'] ?? '') . (!empty($stream['name']) ? ' ' . $stream['name'] : ''),
        'meta'     => [
            ['label' => 'Academic Year', 'value' => $academicYear['name'] ?? ''],
            ['label' => 'Term',          'value' => $term['name']         ?? ''],
            ['label' => 'Class',         'value' => $class['name']        ?? ''],
            ['label' => 'Stream',        'value' => $stream['name']       ?? 'N/A'],
            ['label' => 'Students',      'value' => (int)($summary['total'] ?? 0)],
        ],
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($summary['divisions'])): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No division data</h6>
        <p class="small mb-0">Select a year, term and class with graded exams.</p>
    </div>
<?php else: ?>

    <div class="dap-title">END OF TERM REPORT (DIVISION ANALYSIS)</div>
    <div class="dap-meta">
        ACADEMIC YEAR: <strong><?= htmlspecialchars($academicYear['name'] ?? '-') ?></strong>
        &nbsp;&nbsp;&nbsp;
        TERM: <strong><?= htmlspecialchars($term['name'] ?? '-') ?></strong>
        &nbsp;&nbsp;&nbsp;
        CLASS: <strong><?= htmlspecialchars($class['name'] ?? '-') ?><?= !empty($stream['name']) ? ' ' . htmlspecialchars($stream['name']) : '' ?></strong>
    </div>

    <div class="dap-section">DIVISION DISTRIBUTION</div>
    <table class="dap-table">
        <thead>
            <tr>
                <th style="width: 90px;">DIVISION</th>
                <th style="width: 130px;">AGGREGATE RANGE</th>
                <th class="dap-center" style="width: 90px;">STUDENTS</th>
                <th class="dap-center" style="width: 90px;">PERCENT</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['divisions'] as $d): ?>
                <?php $pct = $summary['total'] > 0 ? round(($d['count'] / $summary['total']) * 100, 1) : 0; ?>
                <tr>
                    <td class="dap-bold">DIV <?= htmlspecialchars($d['code']) ?></td>
                    <td>
                        <?= $d['min_agg'] !== null && $d['max_agg'] !== null
                            ? (int)$d['min_agg'] . ' - ' . (int)$d['max_agg']
                            : '-' ?>
                    </td>
                    <td class="dap-center"><?= (int)$d['count'] ?></td>
                    <td class="dap-center"><?= $pct ?> %</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td class="dap-center"><?= (int)$summary['total'] ?></td>
                <td class="dap-center">100 %</td>
            </tr>
        </tfoot>
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