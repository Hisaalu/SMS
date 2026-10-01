<!-- File: /app/Views/reports/attendance/print_student_monitor.php -->
<style>
    .smp-section {
        font-weight: 800;
        font-size: .85rem;
        text-transform: uppercase;
        margin: .85rem 0 .35rem;
        color: #000;
    }
    .smp-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.78rem;
        background: #ffffff;
        color: #000000;
        margin-bottom: 1rem;
    }
    .smp-table thead th {
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
    .smp-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .smp-table .smp-center { text-align: center; font-weight: 700; }
    .smp-table .smp-bold   { font-weight: 700; }

    @media print {
        .smp-table { font-size: 10px; }
        .smp-table thead th { font-size: 9px; }
    }
</style>

<?php
    $print = [
        'title'    => 'Student Attendance Monitor',
        'subtitle' => strtoupper(trim(($data['student']['last_name'] ?? '') . ' ' . ($data['student']['first_name'] ?? ''))),
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($data['student'])): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No data available</h6>
    </div>
<?php else: ?>

    <?php $s = $data['summary']; ?>

    <div class="smp-section">Summary</div>
    <table class="smp-table">
        <thead>
            <tr>
                <th>Records</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Average %</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="smp-center"><?= (int)$s['records'] ?></td>
                <td class="smp-center"><?= (int)$s['present'] ?></td>
                <td class="smp-center"><?= (int)$s['absent'] ?></td>
                <td class="smp-center"><?= (int)$s['late'] ?></td>
                <td class="smp-center"><?= (float)$s['avg_percent'] ?> %</td>
            </tr>
        </tbody>
    </table>

    <?php if (!empty($data['by_session'])): ?>
        <div class="smp-section">By Session</div>
        <table class="smp-table">
            <thead>
                <tr>
                    <th>Session</th>
                    <th class="text-center">Records</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">Average %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['by_session'] as $row): ?>
                    <tr>
                        <td class="smp-bold"><?= htmlspecialchars($row['session_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="smp-center"><?= (int)$row['records'] ?></td>
                        <td class="smp-center"><?= (int)$row['present'] ?></td>
                        <td class="smp-center"><?= (int)$row['absent'] ?></td>
                        <td class="smp-center"><?= (int)$row['late'] ?></td>
                        <td class="smp-center"><?= (float)$row['avg_percent'] ?> %</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="smp-section">Attendance Records</div>
    <table class="smp-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 90px;">Date</th>
                <th>Session</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['records'] as $i => $r): ?>
                <tr>
                    <td class="smp-center"><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($r['attendance_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($r['session_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="smp-bold"><?= htmlspecialchars($r['status_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($r['reason'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($r['remarks'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['records'])): ?>
                <tr><td colspan="6" class="smp-center">No attendance records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
    <span>Printed by: <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span>Generated on <?= date('d M Y, H:i') ?></span>
</div>