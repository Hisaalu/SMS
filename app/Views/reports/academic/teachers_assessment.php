<!-- File: /app/Views/reports/academic/teachers_assessment.php -->
<style>
    .ta-summary {
        display: flex; gap: 1.5rem; flex-wrap: wrap;
        padding: .75rem 1rem;
        border-radius: .5rem;
        background: rgba(var(--accent-rgb), 0.06);
        margin-bottom: 1rem;
        font-size: .85rem;
    }
    .ta-summary strong { color: var(--accent-color); }

    .ta-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        font-size: 1rem;
        margin: 1.25rem 0 .5rem;
        color: var(--text-color);
    }
    .ta-meta {
        text-align: center;
        font-size: .85rem;
        margin-bottom: 1rem;
        color: var(--text-color);
    }
    .ta-meta strong { color: var(--accent-color); }

    .ta-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .ta-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        vertical-align: middle;
        padding: 6px 8px;
    }
    .ta-table td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .ta-table .ta-center { text-align: center; font-weight: 700; }
    .ta-table .ta-bold   { font-weight: 700; }

    [data-theme="dark"] .ta-table { background: var(--surface-color); color: var(--text-color); }
    [data-theme="dark"] .ta-table thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .ta-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Assess Teachers</h4>
            <small class="text-muted">Teacher Ranking</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="taPrintBtn"
                    <?= empty($summary['teachers']) ? 'disabled' : '' ?>>
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="taExportBtn"
                    <?= empty($summary['teachers']) ? 'disabled' : '' ?>>
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/academic/teachers-assessment" class="row g-2" id="taFilterForm">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" id="ta_year" class="form-select form-select-sm" required>
                        <option value="">Select Year</option>
                        <?php foreach ($filters['academic_years'] as $year): ?>
                            <option value="<?= (int)$year['id'] ?>" <?= ($selectedFilters['academic_year_id'] ?? 0) == $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Term <span class="text-danger">*</span></label>
                    <select name="term_id" id="ta_term" class="form-select form-select-sm" required>
                        <option value="">Select Term</option>
                        <?php foreach ($filters['terms'] as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"
                                    data-year="<?= (int)($t['academic_year_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['term_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="ta_class" class="form-select form-select-sm" required>
                        <option value="">Select Class</option>
                        <?php foreach ($filters['classes'] as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($selectedFilters['class_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Stream</label>
                    <select name="stream_id" id="ta_stream" class="form-select form-select-sm">
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

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Generate
                    </button>
                    <a href="<?= BASE_URL ?>/reports/academic/teachers-assessment" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($summary['teachers'])): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-user-tie fa-3x d-block mb-3 opacity-25"></i>
            <h6 class="fw-bold">No data available</h6>
            <p class="small mb-0">Select an Academic Year, Term and Class to generate the teacher assessment.</p>
        </div></div>
    <?php else: ?>

        <div class="card">
            <div class="card-body">
                <div class="ta-summary">
                    <span>Students assessed: <strong><?= (int)$summary['students'] ?></strong></span>
                    <span>Exams used: <strong><?= count($summary['exams']) ?></strong></span>
                </div>

                <div class="ta-title">END OF TERM REPORT (TEACHER'S ASSESSMENT REPORT)</div>
                <div class="ta-meta">
                    ACADEMIC YEAR: <strong><?= htmlspecialchars($academicYear['name'] ?? '-') ?></strong>
                    &nbsp;&nbsp;&nbsp;
                    TERM: <strong><?= htmlspecialchars($term['name'] ?? '-') ?></strong>
                    &nbsp;&nbsp;&nbsp;
                    CLASS: <strong><?= htmlspecialchars($class['name'] ?? '-') ?><?= !empty($stream['name']) ? ' ' . htmlspecialchars($stream['name']) : '' ?></strong>
                </div>

                <?php if (!empty($summary['department'])): ?>
                    <div class="ta-title" style="font-size: .95rem; text-align: left;">DEPARTMENTAL RANKING</div>
                    <div class="table-responsive mb-3">
                        <table class="table ta-table mb-0">
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
                                        <td class="ta-bold"><?= htmlspecialchars($row['department_name']) ?></td>
                                        <td class="ta-center"><?= (int)$row['percentage'] ?> %</td>
                                        <td class="ta-center"><?= (int)$row['position'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="ta-title" style="font-size: .95rem; text-align: left;">GENERAL TEACHER'S RANKING</div>
                <div class="table-responsive">
                    <table class="table ta-table mb-0">
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
                                    <td class="ta-bold"><?= htmlspecialchars($row['teacher_name']) ?></td>
                                    <td class="ta-bold"><?= htmlspecialchars(strtoupper($row['subject_code'] ?: $row['subject_name'])) ?></td>
                                    <td class="ta-center"><?= (int)$row['percentage'] ?> %</td>
                                    <td class="ta-center"><?= (int)$row['position'] ?></td>
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
    const url       = '<?= BASE_URL ?>';
    const yearSel   = document.getElementById('ta_year');
    const termSel   = document.getElementById('ta_term');
    const classSel  = document.getElementById('ta_class');
    const streamSel = document.getElementById('ta_stream');

    if (yearSel && termSel) {
        const allTerms = Array.from(termSel.querySelectorAll('option[data-year]'));
        yearSel.addEventListener('change', function () {
            const y = yearSel.value;
            const keep = termSel.value;
            termSel.innerHTML = '<option value="">Select Term</option>';
            allTerms.forEach(function (opt) {
                if (!y || opt.getAttribute('data-year') === y) {
                    const c = opt.cloneNode(true);
                    if (c.value === keep) c.selected = true;
                    termSel.appendChild(c);
                }
            });
        });
    }

    if (classSel && streamSel) {
        const allStreams = Array.from(streamSel.querySelectorAll('option[data-class]'));
        classSel.addEventListener('change', function () {
            const c = classSel.value;
            const keep = streamSel.value;
            streamSel.innerHTML = '<option value="">All Streams</option>';
            allStreams.forEach(function (opt) {
                if (!c || opt.getAttribute('data-class') === c) {
                    const clone = opt.cloneNode(true);
                    if (clone.value === keep) clone.selected = true;
                    streamSel.appendChild(clone);
                }
            });
        });
    }

    function currentQs() {
        const p = new URLSearchParams();
        if (yearSel.value)   p.set('academic_year_id', yearSel.value);
        if (termSel.value)   p.set('term_id',          termSel.value);
        if (classSel.value)  p.set('class_id',         classSel.value);
        if (streamSel && streamSel.value) p.set('stream_id', streamSel.value);
        return p.toString();
    }

    const printBtn  = document.getElementById('taPrintBtn');
    const exportBtn = document.getElementById('taExportBtn');
    if (printBtn && !printBtn.disabled) {
        printBtn.addEventListener('click', function () {
            window.open(url + '/reports/academic/teachers-assessment/print?' + currentQs(), '_blank');
        });
    }
    if (exportBtn && !exportBtn.disabled) {
        exportBtn.addEventListener('click', function () {
            window.open(url + '/reports/academic/teachers-assessment/export?' + currentQs(), '_blank');
        });
    }
})();
</script>