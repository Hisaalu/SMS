<!-- File: /app/Views/reports/attendance/student_monitor_view.php -->
<?php
    $data = is_array($data ?? null) ? $data : [];

    $student = is_array($data['student'] ?? null) ? $data['student'] : [];
    $summary = is_array($data['summary'] ?? null)
        ? $data['summary']
        : ['records' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'avg_percent' => 0];

    $records   = is_array($data['records'] ?? null)    ? $data['records']    : [];
    $bySession = is_array($data['by_session'] ?? null) ? $data['by_session'] : [];

    $backUrl = $backUrl ?? (BASE_URL . '/reports/attendance/student-monitor');
?>

<style>
    .sm-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }
    .sm-stat {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: .5rem;
        padding: .75rem 1rem;
    }
    .sm-stat .label {
        color: var(--text-muted);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .sm-stat .value {
        color: var(--text-color);
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .sm-stat .value.accent { color: var(--accent-color); }

    .sm-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .sm-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        padding: 6px 8px;
        text-align: left;
    }
    .sm-table td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .sm-table .sm-center { text-align: center; font-weight: 700; }
    .sm-table .sm-bold   { font-weight: 700; }
    .sm-table tbody tr:hover td { background: rgba(var(--accent-rgb), 0.10); }

    .sm-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .sm-badge.present { background: #16A34A; color: #fff; }
    .sm-badge.absent  { background: #DC2626; color: #fff; }
    .sm-badge.late    { background: #EAB308; color: #422006; }
    .sm-badge.other   { background: #64748B; color: #fff; }

    [data-theme="dark"] .sm-table { background: var(--surface-color); color: var(--text-color); }
    [data-theme="dark"] .sm-table thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .sm-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Student Attendance Monitor</h4>
            <small class="text-muted">Attendance detail for the selected student</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($backUrl) ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="smPrintBtn"
                    <?= empty($student) ? 'disabled' : '' ?>>
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <?php if (empty($student)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-user-slash fa-3x d-block mb-3 opacity-25"></i>
            <h6>Student not found</h6>
        </div></div>
    <?php else: ?>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="<?= BASE_URL ?>/reports/attendance/student-monitor/view" class="row g-2">
                    <input type="hidden" name="student_id" value="<?= (int)($student['id'] ?? 0) ?>">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Session</label>
                        <select name="session_id" class="form-select form-select-sm">
                            <option value="">All Sessions</option>
                            <?php foreach (($filters['sessions'] ?? []) as $sess): ?>
                                <option value="<?= (int)$sess['id'] ?>"
                                        <?= ($selectedFilters['session_id'] ?? 0) == $sess['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sess['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-dark btn-sm flex-fill">
                            <i class="fas fa-filter me-1"></i> Apply
                        </button>
                        <a href="<?= BASE_URL ?>/reports/attendance/student-monitor/view?student_id=<?= (int)($student['id'] ?? 0) ?>"
                           class="btn btn-outline-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-1 fw-bold">
                    <?= htmlspecialchars(strtoupper(trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')))) ?>
                </h6>
                <small class="text-muted">
                    Adm No: <strong><?= htmlspecialchars($student['admission_number'] ?? '-') ?></strong>
                    <?php if (!empty($student['class_name'])): ?>
                        &nbsp;|&nbsp; Class: <strong><?= htmlspecialchars($student['class_name']) ?></strong>
                    <?php endif; ?>
                    <?php if (!empty($student['stream_name'])): ?>
                        &nbsp;|&nbsp; Stream: <strong><?= htmlspecialchars($student['stream_name']) ?></strong>
                    <?php endif; ?>
                </small>
            </div>
        </div>

        <div class="sm-stats">
            <div class="sm-stat"><div class="label">Records</div><div class="value"><?= (int)$summary['records'] ?></div></div>
            <div class="sm-stat"><div class="label">Present</div><div class="value accent"><?= (int)$summary['present'] ?></div></div>
            <div class="sm-stat"><div class="label">Absent</div><div class="value"><?= (int)$summary['absent'] ?></div></div>
            <div class="sm-stat"><div class="label">Late</div><div class="value"><?= (int)$summary['late'] ?></div></div>
            <div class="sm-stat"><div class="label">Average</div><div class="value accent"><?= (float)$summary['avg_percent'] ?> %</div></div>
        </div>

        <?php if (!empty($bySession)): ?>
            <div class="card mb-3">
                <div class="card-header fw-bold"><i class="fas fa-clock me-2 text-secondary"></i>By Session</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table sm-table mb-0">
                            <thead>
                                <tr>
                                    <th>Session</th>
                                    <th class="text-center">Records</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center">Late</th>
                                    <th class="text-center">Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bySession as $row): ?>
                                    <tr>
                                        <td class="sm-bold"><?= htmlspecialchars($row['session_name'] ?? '-') ?></td>
                                        <td class="sm-center"><?= (int)($row['records'] ?? 0) ?></td>
                                        <td class="sm-center"><?= (int)($row['present'] ?? 0) ?></td>
                                        <td class="sm-center"><?= (int)($row['absent'] ?? 0) ?></td>
                                        <td class="sm-center"><?= (int)($row['late'] ?? 0) ?></td>
                                        <td class="sm-center"><?= (float)($row['avg_percent'] ?? 0) ?> %</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header fw-bold"><i class="fas fa-list-check me-2 text-secondary"></i>Attendance Records</div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table sm-table mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 110px;">Date</th>
                                <th>Session</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $i => $r): ?>
                                <?php
                                    $cls = 'other';
                                    if (!empty($r['counts_as_present'])) $cls = 'present';
                                    elseif (!empty($r['counts_as_absent'])) $cls = 'absent';
                                    elseif (!empty($r['counts_as_late']))   $cls = 'late';
                                ?>
                                <tr>
                                    <td class="sm-center"><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($r['attendance_date'] ?? 'now'))) ?></td>
                                    <td><?= htmlspecialchars($r['session_name'] ?? '-') ?></td>
                                    <td><span class="sm-badge <?= $cls ?>"><?= htmlspecialchars($r['status_name'] ?? '-') ?></span></td>
                                    <td><?= htmlspecialchars($r['reason'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['remarks'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($records)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No attendance records found for the selected period.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
(function () {
    const printBtn = document.getElementById('smPrintBtn');
    if (printBtn && !printBtn.disabled) {
        printBtn.addEventListener('click', function () {
            const qs = window.location.search.replace(/^\?/, '');
            window.open('<?= BASE_URL ?>/reports/attendance/student-monitor/print?' + qs, '_blank');
        });
    }
})();
</script>