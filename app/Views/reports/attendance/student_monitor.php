<!-- File: /app/Views/reports/attendance/student_monitor.php -->
<style>
    .sm-list-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .sm-list-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        padding: 6px 8px;
    }
    .sm-list-table td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .sm-list-table tbody tr:hover td { background: rgba(var(--accent-rgb), 0.10); }
    .sm-list-table .col-num   { text-align: center; color: #475569; }
    .sm-list-table .col-adm   { font-weight: 700; }
    .sm-list-table .col-name  { font-weight: 700; text-transform: uppercase; }
    .sm-list-table .col-sex   { text-align: center; }
    .sm-list-table .col-act   { text-align: center; white-space: nowrap; }
    .sm-list-table tbody tr { cursor: pointer; }

    [data-theme="dark"] .sm-list-table { background: var(--surface-color); color: var(--text-color); }
    [data-theme="dark"] .sm-list-table thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .sm-list-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .sm-list-table tbody tr:hover td { background: rgba(var(--accent-rgb), 0.14); }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Student Attendance Monitor</h4>
            <small class="text-muted">Search a student and open their attendance record</small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/attendance/student-monitor" class="row g-2" id="smFilterForm">
                <div class="col-md-3 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Name, Adm..."
                           value="<?= htmlspecialchars($selectedFilters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Class</label>
                    <select name="class_id" id="sm_class" class="form-select form-select-sm">
                        <option value="">-- All --</option>
                        <?php foreach ($filters['classes'] as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($selectedFilters['class_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Stream</label>
                    <select name="stream_id" id="sm_stream" class="form-select form-select-sm">
                        <option value="">-- All --</option>
                        <?php foreach ($filters['streams'] as $stream): ?>
                            <option value="<?= (int)$stream['id'] ?>"
                                    data-class="<?= (int)($stream['class_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['stream_id'] ?? 0) == $stream['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stream['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Section</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">-- All --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($selectedFilters['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-center justify-content-end gap-2">
                    <button type="submit" class="btn btn-dark btn-sm">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="<?= BASE_URL ?>/reports/attendance/student-monitor" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white pt-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-users me-2 text-secondary"></i>Students
                <small class="text-muted fw-normal ms-1">(<span id="smStudentCount"><?= count($students) ?></span> total)</small>
            </h6>
        </div>
        <div class="table-responsive" style="max-height: 620px; overflow-y: auto;">
            <table class="table sm-list-table mb-0">
                <thead class="sticky-top">
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Student No</th>
                        <th>Name</th>
                        <th class="text-center">Sex</th>
                        <th>Class</th>
                        <th>Stream</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="smStudentRows">
                    <?php foreach ($students as $index => $st): ?>
                        <?php
                            $rawSex = strtoupper(trim($st['gender'] ?? ''));
                            $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true) ? 'F'
                                    : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');
                        ?>
                        <tr class="student-row"
                            data-id="<?= (int)$st['id'] ?>"
                            data-adm="<?= htmlspecialchars($st['admission_number'] ?? '') ?>"
                            data-name="<?= htmlspecialchars(strtoupper(trim(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? '')))) ?>"
                            data-class="<?= htmlspecialchars($st['class_name'] ?? '') ?>"
                            data-stream="<?= htmlspecialchars($st['stream_name'] ?? '') ?>"
                            data-section="<?= htmlspecialchars($st['category_name'] ?? '') ?>"
                            data-status="<?= htmlspecialchars($st['status_name'] ?? '') ?>">
                            <td class="col-num"><?= $index + 1 ?></td>
                            <td class="col-adm"><?= htmlspecialchars($st['admission_number'] ?? '-') ?></td>
                            <td class="col-name"><?= htmlspecialchars(trim(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? ''))) ?></td>
                            <td class="col-sex"><?= $sex ?></td>
                            <td><?= htmlspecialchars($st['class_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($st['stream_name'] ?? 'GENERAL') ?></td>
                            <td><?= htmlspecialchars($st['category_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($st['status_name'] ?? 'Active') ?></td>
                            <td class="col-act">
                                <button type="button"
                                        class="btn btn-sm btn-primary py-0 px-2"
                                        data-action="monitor"
                                        data-id="<?= (int)$st['id'] ?>">
                                    <i class="fas fa-user-clock me-1"></i> Monitor
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($students)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No students found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const url       = '<?= BASE_URL ?>';
    const classSel  = document.getElementById('sm_class');
    const streamSel = document.getElementById('sm_stream');
    const rowsWrap  = document.getElementById('smStudentRows');
    const countEl   = document.getElementById('smStudentCount');

    if (classSel && streamSel) {
        const allStreams = Array.from(streamSel.querySelectorAll('option[data-class]'));
        classSel.addEventListener('change', function () {
            const c = classSel.value;
            const keep = streamSel.value;
            streamSel.innerHTML = '<option value="">-- All --</option>';
            allStreams.forEach(function (opt) {
                if (!c || opt.getAttribute('data-class') === c) {
                    const clone = opt.cloneNode(true);
                    if (clone.value === keep) clone.selected = true;
                    streamSel.appendChild(clone);
                }
            });
            if (countEl) countEl.textContent = rowsWrap.querySelectorAll('tr.student-row:not(.d-none)').length;
        });
    }

    function monitor(studentId) {
        window.location.href = url + '/reports/attendance/student-monitor/view?student_id=' + studentId;
    }

    rowsWrap.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action="monitor"]');
        if (btn) {
            e.stopPropagation();
            monitor(btn.getAttribute('data-id'));
        }
    });
    rowsWrap.addEventListener('dblclick', function (e) {
        const row = e.target.closest('tr.student-row');
        if (row) monitor(row.getAttribute('data-id'));
    });
})();
</script>