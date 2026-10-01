<!-- File: /app/Views/reports/attendance/reports.php -->
<style>
    .rp-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }
    .rp-stat {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: .5rem;
        padding: .75rem 1rem;
    }
    .rp-stat .label {
        color: var(--text-muted);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 700;
        margin-bottom: .15rem;
    }
    .rp-stat .value {
        color: var(--text-color);
        font-size: 1.4rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .rp-stat .value.accent { color: var(--accent-color); }

    .rp-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .rp-table thead th {
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
    .rp-table td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .rp-table .rp-center { text-align: center; font-weight: 700; }
    .rp-table .rp-bold   { font-weight: 700; }
    .rp-table tbody tr:hover td { background: rgba(var(--accent-rgb), 0.10); }

    .rp-bar {
        height: 8px;
        border-radius: 4px;
        background: rgba(var(--accent-rgb), 0.15);
        position: relative;
        overflow: hidden;
        min-width: 80px;
    }
    .rp-bar > span {
        display: block;
        height: 100%;
        background: var(--accent-color);
    }

    [data-theme="dark"] .rp-table { background: var(--surface-color); color: var(--text-color); }
    [data-theme="dark"] .rp-table thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .rp-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Attendance Reports</h4>
            <small class="text-muted">Attendance summary for the selected period</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="rpPrintBtn"
                    <?= empty($summary['totals']['registers']) ? 'disabled' : '' ?>>
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="rpExportBtn"
                    <?= empty($summary['totals']['registers']) ? 'disabled' : '' ?>>
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/attendance" class="row g-2" id="rpFilterForm">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Class</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        <?php foreach ($filters['classes'] as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($selectedFilters['class_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Session</label>
                    <select name="session_id" class="form-select form-select-sm">
                        <option value="">All Sessions</option>
                        <?php foreach ($filters['sessions'] as $sess): ?>
                            <option value="<?= (int)$sess['id'] ?>" <?= ($selectedFilters['session_id'] ?? 0) == $sess['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sess['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="draft"     <?= ($selectedFilters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="submitted" <?= ($selectedFilters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= BASE_URL ?>/reports/attendance" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php $t = $summary['totals']; ?>

    <div class="rp-stats">
        <div class="rp-stat">
            <div class="label">Registers</div>
            <div class="value"><?= (int)$t['registers'] ?></div>
        </div>
        <div class="rp-stat">
            <div class="label">Records</div>
            <div class="value"><?= (int)$t['records'] ?></div>
        </div>
        <div class="rp-stat">
            <div class="label">Present</div>
            <div class="value accent"><?= (int)$t['present'] ?></div>
        </div>
        <div class="rp-stat">
            <div class="label">Absent</div>
            <div class="value"><?= (int)$t['absent'] ?></div>
        </div>
        <div class="rp-stat">
            <div class="label">Late</div>
            <div class="value"><?= (int)$t['late'] ?></div>
        </div>
        <div class="rp-stat">
            <div class="label">Average</div>
            <div class="value accent"><?= (float)$t['avg_percent'] ?> %</div>
        </div>
    </div>

    <?php if (empty($summary['by_class'])): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-chart-line fa-3x d-block mb-3 opacity-25"></i>
            <h6 class="fw-bold">No attendance data</h6>
            <p class="small mb-0">Adjust the filters above to see attendance reports.</p>
        </div></div>
    <?php else: ?>

        <div class="card mb-3">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chalkboard me-2 text-secondary"></i>Attendance by Class</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table rp-table mb-0">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th class="text-center">Registers</th>
                                <th class="text-center">Records</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Late</th>
                                <th style="width: 180px;" class="text-center">Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary['by_class'] as $c): ?>
                                <tr>
                                    <td class="rp-bold"><?= htmlspecialchars($c['class_name'] ?? '-') ?></td>
                                    <td class="rp-center"><?= (int)$c['registers'] ?></td>
                                    <td class="rp-center"><?= (int)$c['records'] ?></td>
                                    <td class="rp-center"><?= (int)$c['present_count'] ?></td>
                                    <td class="rp-center"><?= (int)$c['absent_count'] ?></td>
                                    <td class="rp-center"><?= (int)$c['late_count'] ?></td>
                                    <td class="rp-center">
                                        <div class="rp-bar" title="<?= $c['avg_percent'] ?>%">
                                            <span style="width: <?= $c['avg_percent'] ?>%"></span>
                                        </div>
                                        <small class="text-muted"><?= $c['avg_percent'] ?> %</small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($summary['by_student'])): ?>
            <div class="card">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-users me-2 text-secondary"></i>Attendance by Student (Top 100)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                        <table class="table rp-table mb-0">
                            <thead class="sticky-top">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th style="width: 110px;">Adm No</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th class="text-center">Records</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center">Late</th>
                                    <th style="width: 160px;" class="text-center">Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($summary['by_student'] as $i => $s): ?>
                                    <tr>
                                        <td class="rp-center"><?= $i + 1 ?></td>
                                        <td class="rp-bold"><?= htmlspecialchars($s['admission_number'] ?? '-') ?></td>
                                        <td class="rp-bold"><?= htmlspecialchars(strtoupper(trim(($s['last_name'] ?? '') . ' ' . ($s['first_name'] ?? '')))) ?></td>
                                        <td><?= htmlspecialchars($s['class_name'] ?? '-') ?></td>
                                        <td class="rp-center"><?= (int)$s['records'] ?></td>
                                        <td class="rp-center"><?= (int)$s['present_count'] ?></td>
                                        <td class="rp-center"><?= (int)$s['absent_count'] ?></td>
                                        <td class="rp-center"><?= (int)$s['late_count'] ?></td>
                                        <td class="rp-center">
                                            <div class="rp-bar" title="<?= $s['avg_percent'] ?>%">
                                                <span style="width: <?= $s['avg_percent'] ?>%"></span>
                                            </div>
                                            <small class="text-muted"><?= $s['avg_percent'] ?> %</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
(function () {
    const url = '<?= BASE_URL ?>';
    function currentQs() { return window.location.search.replace(/^\?/, ''); }

    const printBtn  = document.getElementById('rpPrintBtn');
    const exportBtn = document.getElementById('rpExportBtn');
    if (printBtn && !printBtn.disabled) {
        printBtn.addEventListener('click', function () {
            window.open(url + '/reports/attendance/print?' + currentQs(), '_blank');
        });
    }
    if (exportBtn && !exportBtn.disabled) {
        exportBtn.addEventListener('click', function () {
            window.open(url + '/reports/attendance/export?' + currentQs(), '_blank');
        });
    }
})();
</script>