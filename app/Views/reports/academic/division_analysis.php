<!-- File: /app/Views/reports/academic/division_analysis.php -->
<style>
    .da-summary {
        display: flex; gap: 1.5rem; flex-wrap: wrap;
        padding: .75rem 1rem;
        border-radius: .5rem;
        background: rgba(var(--accent-rgb), 0.06);
        margin-bottom: 1rem;
        font-size: .85rem;
    }
    .da-summary strong { color: var(--accent-color); }

    .da-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .da-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        text-align: left;
        vertical-align: middle;
        padding: 6px 8px;
    }
    .da-table td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .da-table .da-center { text-align: center; font-weight: 700; }
    .da-table .da-bold   { font-weight: 700; }
    .da-table tfoot td {
        background: #eef2f7;
        font-weight: 800;
    }
    .da-bar {
        height: 8px;
        border-radius: 4px;
        background: rgba(var(--accent-rgb), 0.15);
        position: relative;
        overflow: hidden;
        min-width: 80px;
    }
    .da-bar > span {
        display: block;
        height: 100%;
        background: var(--accent-color);
    }

    [data-theme="dark"] .da-table { background: var(--surface-color); color: var(--text-color); }
    [data-theme="dark"] .da-table thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .da-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .da-table tfoot td {
        background: rgba(var(--accent-rgb), 0.10);
        color: var(--text-color);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Divisions</h4>
            <small class="text-muted">Look at your Divisions</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="daPrintBtn"
                    <?= empty($summary['divisions']) ? 'disabled' : '' ?>>
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="daExportBtn"
                    <?= empty($summary['divisions']) ? 'disabled' : '' ?>>
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/academic/division-analysis" class="row g-2" id="daFilterForm">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" id="da_year" class="form-select form-select-sm" required>
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
                    <select name="term_id" id="da_term" class="form-select form-select-sm" required>
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
                    <select name="class_id" id="da_class" class="form-select form-select-sm" required>
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
                    <select name="stream_id" id="da_stream" class="form-select form-select-sm">
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

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Examination</label>
                    <select name="examination_id" id="da_exam" class="form-select form-select-sm">
                        <option value="">All Exams (blended)</option>
                        <?php foreach ($filters['examinations'] as $exam): ?>
                            <option value="<?= (int)$exam['id'] ?>"
                                    data-year="<?= (int)($exam['academic_year_id'] ?? 0) ?>"
                                    data-term="<?= (int)($exam['academic_period_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['examination_id'] ?? 0) == $exam['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($exam['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Generate
                    </button>
                    <a href="<?= BASE_URL ?>/reports/academic/division-analysis" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($summary['divisions'])): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-layer-group fa-3x d-block mb-3 opacity-25"></i>
            <h6 class="fw-bold">No data available</h6>
            <p class="small mb-0">Select an Academic Year, Term and Class to generate the division analysis.</p>
        </div></div>
    <?php else: ?>

        <div class="da-summary">
            <span>Total students: <strong><?= (int)$summary['total'] ?></strong></span>
            <span>Exams used: <strong><?= count($summary['exams']) ?></strong></span>
        </div>

        <div class="card">
            <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-layer-group me-2 text-secondary"></i>Division Distribution</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table da-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 90px;">DIVISION</th>
                                <th style="width: 140px;">AGGREGATE RANGE</th>
                                <th style="width: 110px;" class="text-center">STUDENTS</th>
                                <th style="width: 90px;" class="text-center">PERCENT</th>
                                <th>DISTRIBUTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $maxCount = max(array_column($summary['divisions'], 'count') ?: [1]); $maxCount = max(1, $maxCount); ?>
                            <?php foreach ($summary['divisions'] as $d): ?>
                                <?php $pct = $summary['total'] > 0 ? round(($d['count'] / $summary['total']) * 100, 1) : 0; ?>
                                <tr>
                                    <td class="da-bold">DIV <?= htmlspecialchars($d['code']) ?></td>
                                    <td>
                                        <?= $d['min_agg'] !== null && $d['max_agg'] !== null
                                            ? (int)$d['min_agg'] . ' - ' . (int)$d['max_agg']
                                            : '-' ?>
                                    </td>
                                    <td class="da-center"><?= (int)$d['count'] ?></td>
                                    <td class="da-center"><?= $pct ?> %</td>
                                    <td>
                                        <div class="da-bar">
                                            <span style="width: <?= (int)round(($d['count'] / $maxCount) * 100) ?>%"></span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">TOTAL</td>
                                <td class="da-center"><?= (int)$summary['total'] ?></td>
                                <td class="da-center">100 %</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<script>
(function () {
    const url       = '<?= BASE_URL ?>';
    const yearSel   = document.getElementById('da_year');
    const termSel   = document.getElementById('da_term');
    const classSel  = document.getElementById('da_class');
    const streamSel = document.getElementById('da_stream');
    const examSel   = document.getElementById('da_exam');

    // Year → Term
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

    if (examSel) {
        const allExams = Array.from(examSel.querySelectorAll('option[data-year]'));
        function rebuildExams() {
            const y = yearSel ? yearSel.value : '';
            const t = termSel ? termSel.value : '';
            examSel.innerHTML = '<option value="">All Exams (blended)</option>';
            allExams.forEach(function (opt) {
                const oy = opt.getAttribute('data-year');
                const ot = opt.getAttribute('data-term');
                const yearOk = !y || oy === y;
                const termOk = !t || ot === t || ot === '' || ot === '0';
                if (yearOk && termOk) examSel.appendChild(opt.cloneNode(true));
            });
        }
        if (yearSel) yearSel.addEventListener('change', rebuildExams);
        if (termSel) termSel.addEventListener('change', rebuildExams);
        rebuildExams();
    }

    function currentQs() {
        const p = new URLSearchParams();
        if (yearSel.value)   p.set('academic_year_id', yearSel.value);
        if (termSel.value)   p.set('term_id',          termSel.value);
        if (classSel.value)  p.set('class_id',         classSel.value);
        if (streamSel && streamSel.value) p.set('stream_id', streamSel.value);
        if (examSel && examSel.value)     p.set('examination_id', examSel.value);
        return p.toString();
    }

    const printBtn  = document.getElementById('daPrintBtn');
    const exportBtn = document.getElementById('daExportBtn');
    if (printBtn && !printBtn.disabled) {
        printBtn.addEventListener('click', function () {
            window.open(url + '/reports/academic/division-analysis/print?' + currentQs(), '_blank');
        });
    }
    if (exportBtn && !exportBtn.disabled) {
        exportBtn.addEventListener('click', function () {
            window.open(url + '/reports/academic/division-analysis/export?' + currentQs(), '_blank');
        });
    }
})();
</script>