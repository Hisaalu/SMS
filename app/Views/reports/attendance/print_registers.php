<!-- File: /app/Views/reports/attendance/print_registers.php -->
<style>
    .arp-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.78rem;
        background: #ffffff;
        color: #000000;
    }
    .arp-table thead th {
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
    .arp-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .arp-table .arp-center { text-align: center; font-weight: 700; }
    .arp-table .arp-bold   { font-weight: 700; }
    @media print {
        .arp-table { font-size: 10px; }
        .arp-table thead th { font-size: 9px; }
    }
</style>

<?php
    $print = [
        'title'    => 'Attendance Registers',
        'subtitle' => 'Recorded attendance registers',
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($registers)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No registers found</h6>
        <p class="small mb-0">Adjust the filters and try again.</p>
    </div>
<?php else: ?>

    <table class="arp-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 90px;">Date</th>
                <th>Class</th>
                <th>Stream</th>
                <th>Session</th>
                <th class="text-center">Status</th>
                <th class="text-center">Students</th>
                <th class="text-center">Present</th>
                <th class="text-center">Absent</th>
                <th class="text-center">Late</th>
                <th class="text-center">%</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registers as $i => $r): ?>
                <?php
                    $total   = (int)$r['total_students'];
                    $present = (int)$r['present_count'];
                    $pct     = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                ?>
                <tr>
                    <td class="arp-center"><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars(date('d M Y', strtotime($r['attendance_date'])), ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="arp-bold"><?= htmlspecialchars($r['class_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?= !empty($r['stream_name'])
                            ? htmlspecialchars($r['stream_name'], ENT_QUOTES, 'UTF-8')
                            : '<em>All Streams</em>' ?>
                    </td>
                    <td><?= htmlspecialchars($r['session_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="arp-center"><?= ucfirst((string)($r['status'] ?? 'draft')) ?></td>
                    <td class="arp-center"><?= $total ?></td>
                    <td class="arp-center"><?= $present ?></td>
                    <td class="arp-center"><?= (int)$r['absent_count'] ?></td>
                    <td class="arp-center"><?= (int)$r['late_count'] ?></td>
                    <td class="arp-center"><?= $pct ?> %</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
    <span>Printed by: <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span>Generated on <?= date('d M Y, H:i') ?></span>
</div>