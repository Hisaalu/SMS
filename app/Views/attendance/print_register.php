<!-- File: /app/Views/attendance/print_register.php -->
<style>
    .attendance-print-table {
        border: 1px solid #000000 !important;
        border-collapse: collapse !important;
        border-top: 1px solid #000000 !important;
    }
    .attendance-print-table > thead > tr > th {
        border-top: 1px solid #000000 !important;
        border-bottom-width: 2px !important;
        background: #f5f5f5;
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-weight: 600;
    }
    .attendance-print-table tbody td {
        vertical-align: middle;
    }
</style>

<?php
    $print = [
        'title'    => 'Attendance Register',
        'subtitle' => ($register['class_name'] ?? '') .
                      (!empty($register['stream_name']) ? ' - ' . $register['stream_name'] : ''),
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../partials/print_header.php';
?>

<?php if (empty($records)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No attendance records</h6>
        <p class="small mb-0">This register has no records saved.</p>
    </div>
<?php else: ?>

    <table class="table table-bordered align-middle mb-0 text-nowrap attendance-print-table">
        <thead class="sticky-top">
            <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th style="width: 100px;">ADM NO</th>
                <th style="min-width: 220px;">STUDENT NAME</th>
                <th style="width: 80px;" class="text-center">CODE</th>
                <th style="width: 110px;">STATUS</th>
                <th>REASON</th>
                <th>REMARKS</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $index => $r): ?>
                <tr>
                    <td class="text-center text-muted small"><?= $index + 1 ?></td>
                    <td class="fw-semibold small"><?= htmlspecialchars($r['admission_number'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-uppercase fw-semibold">
                        <?= htmlspecialchars(
                            trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </td>
                    <td class="text-center"><code><?= htmlspecialchars($r['status_code'] ?? '-', ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td>
                        <?php if (!empty($r['counts_as_present'])): ?>
                            <span class="badge bg-success"><?= htmlspecialchars($r['status_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php elseif (!empty($r['counts_as_absent'])): ?>
                            <span class="badge bg-danger"><?= htmlspecialchars($r['status_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php elseif (!empty($r['counts_as_late'])): ?>
                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($r['status_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($r['status_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['reason']  ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($r['remarks'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top small text-muted" style="font-size: 0.72rem;">
    <span>Printed by: <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong></span>
    <span>Generated on <?= date('d M Y, H:i') ?></span>
</div>