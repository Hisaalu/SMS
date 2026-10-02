<!-- File: /app/Views/reports/academic/print_teachers_assessment.php -->
<style>
    .tap-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        font-size: 1rem;
        margin: 0 0 .25rem;
        color: #000;
    }
    .tap-meta {
        text-align: center;
        font-size: .82rem;
        margin-bottom: .75rem;
        color: #000;
    }
    .tap-section {
        text-align: left;
        font-weight: 800;
        font-size: .85rem;
        text-transform: uppercase;
        margin: .75rem 0 .25rem;
        color: #000;
    }
    .tap-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.78rem;
        background: #ffffff;
        color: #000000;
        margin-bottom: 1rem;
    }
    .tap-table thead th {
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
    .tap-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .tap-table .tap-center { text-align: center; font-weight: 700; }
    .tap-table .tap-bold   { font-weight: 700; }

    @media print {
        .tap-table { font-size: 10px; }
        .tap-table thead th { font-size: 9px; }
    }
</style>

<?php
    $print = [
        'title'    => 'Teachers Assessment Report',
        'subtitle' => ($class['name'] ?? '') . (!empty($stream['name']) ? ' ' . $stream['name'] : ''),
        'meta'     => [
            ['label' => 'Academic Year', 'value' => $academicYear['name'] ?? ''],
            ['label' => 'Term',          'value' => $term['name']         ?? ''],
            ['label' => 'Class',         'value' => $class['name']        ?? ''],
            ['label' => 'Stream',        'value' => $stream['name']       ?? 'N/A'],
            ['label' => 'Students',      'value' => (int)($summary['students'] ?? 0)],
        ],
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($summary['teachers'])): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No teacher assessment data</h6>
        <p class="small mb-0">Select a year, term and class with graded subjects.</p>
    </div>
<?php else: ?>

    <div class="tap-title">END OF TERM REPORT (TEACHER'S ASSESSMENT REPORT)</div>
    <div class="tap-meta">
        ACADEMIC YEAR: <strong><?= htmlspecialchars($academicYear['name'] ?? '-') ?></strong>
        &nbsp;&nbsp;&nbsp;
        TERM: <strong><?= htmlspecialchars($term['name'] ?? '-') ?></strong>
        &nbsp;&nbsp;&nbsp;
        CLASS: <strong><?= htmlspecialchars($class['name'] ?? '-') ?><?= !empty($stream['name']) ? ' ' . htmlspecialchars($stream['name']) : '' ?></strong>
    </div>

    <?php if (!empty($summary['department'])): ?>
        <div class="tap-section">DEPARTMENTAL RANKING</div>
        <table class="tap-table">
            <thead>
                <tr>
                    <th>DEPARTMENT HEAD</th>
                    <th>DEPARTMENT</th>
                    <th>PERCENTAGE</th>
                    <th>POSITION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($summary['department'] as $row): ?>
                    <tr>
                        <td></td>
                        <td class="tap-bold"><?= htmlspecialchars($row['department_name']) ?></td>
                        <td class="tap-center"><?= (int)$row['percentage'] ?> %</td>
                        <td class="tap-center"><?= (int)$row['position'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="tap-section">GENERAL TEACHER'S RANKING</div>
    <table class="tap-table">
        <thead>
            <tr>
                <th>CLASS</th>
                <th>TEACHER</th>
                <th>SUBJECT</th>
                <th>PERCENTAGE</th>
                <th>POSITION</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['teachers'] as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($class['name'] ?? '-') ?><?= !empty($stream['name']) ? ' ' . htmlspecialchars($stream['name']) : '' ?></td>
                    <td class="tap-bold"><?= htmlspecialchars($row['teacher_name']) ?></td>
                    <td class="tap-bold"><?= htmlspecialchars(strtoupper($row['subject_code'] ?: $row['subject_name'])) ?></td>
                    <td class="tap-center"><?= (int)$row['percentage'] ?> %</td>
                    <td class="tap-center"><?= (int)$row['position'] ?></td>
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