<!-- File: /app/Views/reports/attendance/print_reports.php -->
<style>
    .arpr-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        font-size: 1rem;
        margin: 0 0 .25rem;
        color: #000;
    }
    .arpr-section {
        text-align: left;
        font-weight: 800;
        font-size: .85rem;
        text-transform: uppercase;
        margin: .85rem 0 .35rem;
        color: #000;
    }
    .arpr-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.78rem;
        background: #ffffff;
        color: #000000;
        margin-bottom: 1rem;
    }
    .arpr-table thead th {
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
    .arpr-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .arpr-table .arpr-center { text-align: center; font-weight: 700; }
    .arpr-table .arpr-bold   { font-weight: 700; }
    .arpr-table tfoot td {
        background: #eef2f7;
        font-weight: 800;
    }
    @media print {
        .arpr-table { font-size: 10px; }
        .arpr-table thead th { font-size: 9px; }
    }
</style>

<?php
    $print = [
        'title'    => 'Attendance Reports',
        'subtitle' => 'Summary of attendance across the selected period',
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php $t = $summary['totals']; ?>

<div class="arpr-title">Attendance Report</div>

<div class="arpr-section">Overall Totals</div>
<table class="arpr-table">
    <thead>
        <tr>
            <th>Registers</th>
            <th>Records</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
            <th>Average %</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="arpr-center"><?= (int)$t['registers'] ?></td>
            <td class="arpr-center"><?= (int)$t['records'] ?></td>
            <td class="arpr-center"><?= (int)$t['present'] ?></td>
            <td class="arpr-center"><?= (int)$t['absent'] ?></td>
            <td class="arpr-center"><?= (int)$t['late'] ?></td>
            <td class="arpr-center"><?= (float)$t['avg_percent'] ?> %</td>
        </tr>
    </tbody>
</table>

<?php if (!empty($summary['by_class'])): ?>
    <div class="arpr-section">By Class</div>
    <table class="arpr-table">
        <thead>
            <tr>
                <th>Class</th>
                <th class="text-center">Registers</th>
                <th class="text-center">Records</th>
                <th class="text-center">Present</th>
                <th class="text-center">Absent</th>
                <th class="text-center">Late</th>
                <th class="text-center">Average %</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['by_class'] as $c): ?>
                <tr>
                    <td class="arpr-bold"><?= htmlspecialchars($c['class_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="arpr-center"><?= (int)$c['registers'] ?></td>
                    <td class="arpr-center"><?= (int)$c['records'] ?></td>
                    <td class="arpr-center"><?= (int)$c['present_count'] ?></td>
                    <td class="arpr-center"><?= (int)$c['absent_count'] ?></td>
                    <td class="arpr-center"><?= (int)$c['late_count'] ?></td>
                    <td class="arpr-center"><?= $c['avg_percent'] ?> %</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if (!empty($summary['by_student'])): ?>
    <div class="arpr-section">By Student (Top 100)</div>
    <table class="arpr-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 90px;">Adm No</th>
                <th>Student</th>
                <th>Class</th>
                <th class="text-center">Records</th>
                <th class="text-center">Present</th>
                <th class="text-center">Absent</th>
                <th class="text-center">Late</th>
                <th class="text-center">Average %</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($summary['by_student'] as $i => $s): ?>
                <tr>
                    <td class="arpr-center"><?= $i + 1 ?></td>
                    <td class="arpr-bold"><?= htmlspecialchars($s['admission_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="arpr-bold"><?= htmlspecialchars(strtoupper(trim(($s['last_name'] ?? '') . ' ' . ($s['first_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($s['class_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="arpr-center"><?= (int)$s['records'] ?></td>
                    <td class="arpr-center"><?= (int)$s['present_count'] ?></td>
                    <td class="arpr-center"><?= (int)$s['absent_count'] ?></td>
                    <td class="arpr-center"><?= (int)$s['late_count'] ?></td>
                    <td class="arpr-center"><?= $s['avg_percent'] ?> %</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
    <span>Printed by: <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span>Generated on <?= date('d M Y, H:i') ?></span>
</div>