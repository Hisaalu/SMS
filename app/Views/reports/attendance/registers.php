<!-- File: /app/Views/reports/attendance/registers.php -->
<style>
    .ar-summary {
        display: flex; gap: 1.5rem; flex-wrap: wrap;
        padding: .75rem 1rem;
        border-radius: .5rem;
        background: rgba(var(--accent-rgb), 0.06);
        margin-bottom: 1rem;
        font-size: .85rem;
    }
    .ar-summary strong { color: var(--accent-color); }

    .ar-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .ar-table thead th {
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
    .ar-table td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .ar-table .ar-center { text-align: center; font-weight: 700; }
    .ar-table .ar-bold   { font-weight: 700; }
    .ar-table tbody tr:hover td { background: rgba(var(--accent-rgb), 0.10); }

    .ar-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .ar-badge.submitted { background: #16A34A; color: #fff; }
    .ar-badge.draft     { background: #EAB308; color: #422006; }

    .ar-bar {
        height: 8px;
        border-radius: 4px;
        background: rgba(var(--accent-rgb), 0.15);
        position: relative;
        overflow: hidden;
        min-width: 80px;
    }
    .ar-bar > span {
        display: block;
        height: 100%;
        background: var(--accent-color);
    }

    [data-theme="dark"] .ar-table { background: var(--surface-color); color: var(--text-color); }
    [data-theme="dark"] .ar-table thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .ar-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .ar-table tbody tr:hover td { background: rgba(var(--accent-rgb), 0.14); }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Attendance Registers</h4>
            <small class="text-muted">All recorded attendance registers</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="arPrintBtn"
                    <?= empty($registers) ? 'disabled' : '' ?>>
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="arExportBtn"
                    <?= empty($registers) ? 'disabled' : '' ?>>
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/attendance/registers" class="row g-2" id="arFilterForm">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">From</label>
                    <input type="date" name="date_from" id="ar_from" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">To</label>
                    <input type="date" name="date_to" id="ar_to" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Class</label>
                    <select name="class_id" id="ar_class" class="form-select form-select-sm">
                        <option value="">All Classes</option>
                        <?php foreach ($filters['classes'] as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($selectedFilters['class_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Stream</label>
                    <select name="stream_id" id="ar_stream" class="form-select form-select-sm">
                        <option value="">All Streams</option>
                        <?php foreach ($filters['streams'] as $stream): ?>
                            <option value="<?= (int)$stream['id'] ?>"
                                    data-class="<?= (int)($stream['class_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['stream_id'] ?? 0) == $stream['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stream['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Session</label>
                    <select name="session_id" id="ar_session" class="form-select form-select-sm">
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
                    <select name="status" id="ar_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="draft"     <?= ($selectedFilters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="submitted" <?= ($selectedFilters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    </select>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-dark btn-sm">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= BASE_URL ?>/reports/attendance/registers" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($registers)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-clipboard-list fa-3x d-block mb-3 opacity-25"></i>
            <h6 class="fw-bold">No registers found</h6>
            <p class="small mb-0">Adjust the filters above to see attendance registers.</p>
        </div></div>
    <?php else: ?>

        <div class="ar-summary">
            <span>Registers: <strong><?= count($registers) ?></strong></span>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 620px; overflow-y: auto;">
                    <table class="table ar-table mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 110px;">Date</th>
                                <th>Class</th>
                                <th>Stream</th>
                                <th>Session</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Students</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Late</th>
                                <th style="width: 130px;" class="text-center">%</th>
                                <th style="width: 100px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registers as $i => $r): ?>
                                <?php
                                    $total   = (int)$r['total_students'];
                                    $present = (int)$r['present_count'];
                                    $pct     = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                                    $isSubmitted = ($r['status'] ?? '') === 'submitted';
                                ?>
                                <tr>
                                    <td class="ar-center"><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars(date('d M Y', strtotime($r['attendance_date']))) ?></td>
                                    <td class="ar-bold"><?= htmlspecialchars($r['class_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($r['stream_name'])): ?>
                                            <?= htmlspecialchars($r['stream_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">All Streams</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['session_name'] ?? '-') ?></td>
                                    <td class="ar-center">
                                        <span class="ar-badge <?= $isSubmitted ? 'submitted' : 'draft' ?>">
                                            <?= ucfirst((string)($r['status'] ?? 'draft')) ?>
                                        </span>
                                    </td>
                                    <td class="ar-center"><?= $total ?></td>
                                    <td class="ar-center"><?= $present ?></td>
                                    <td class="ar-center"><?= (int)$r['absent_count'] ?></td>
                                    <td class="ar-center"><?= (int)$r['late_count'] ?></td>
                                    <td class="ar-center">
                                        <div class="ar-bar" title="<?= $pct ?>%">
                                            <span style="width: <?= $pct ?>%"></span>
                                        </div>
                                        <small class="text-muted"><?= $pct ?> %</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/attendance/view/<?= (int)$r['id'] ?>"
                                           class="btn btn-sm btn-secondary py-0 px-2" title="View register">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
(function () {
    const url      = '<?= BASE_URL ?>';
    const classSel = document.getElementById('ar_class');
    const strmSel  = document.getElementById('ar_stream');

    if (classSel && strmSel) {
        const allStreams = Array.from(strmSel.querySelectorAll('option[data-class]'));
        classSel.addEventListener('change', function () {
            const c = classSel.value;
            const keep = strmSel.value;
            strmSel.innerHTML = '<option value="">All Streams</option>';
            allStreams.forEach(function (opt) {
                if (!c || opt.getAttribute('data-class') === c) {
                    const clone = opt.cloneNode(true);
                    if (clone.value === keep) clone.selected = true;
                    strmSel.appendChild(clone);
                }
            });
        });
    }

    function currentQs() {
        const p = new URLSearchParams(window.location.search);
        return p.toString();
    }

    const printBtn  = document.getElementById('arPrintBtn');
    const exportBtn = document.getElementById('arExportBtn');
    if (printBtn && !printBtn.disabled) {
        printBtn.addEventListener('click', function () {
            window.open(url + '/reports/attendance/registers/print?' + currentQs(), '_blank');
        });
    }
    if (exportBtn && !exportBtn.disabled) {
        exportBtn.addEventListener('click', function () {
            window.open(url + '/reports/attendance/registers/export?' + currentQs(), '_blank');
        });
    }
})();
</script>