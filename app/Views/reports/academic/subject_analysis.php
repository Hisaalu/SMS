<!-- File: /app/Views/reports/academic/subject_analysis.php -->
<style>
    .sa-card-header {
        background: var(--surface-color);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-color);
    }
    .sa-table {
        border-collapse: collapse;
        font-size: 0.82rem;
        white-space: nowrap;
        background: #ffffff;
        color: #000000;
    }
    .sa-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        vertical-align: middle;
    }
    .sa-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .sa-table tbody tr:hover td {
        background: rgba(var(--accent-rgb), 0.10);
    }
    .sa-table .sa-num    { text-align: center; color: #475569; }
    .sa-table .sa-stu    { font-weight: 700; text-transform: uppercase; color: #000000; }
    .sa-table .sa-adm    { font-weight: 700; color: #000000; }
    .sa-table .sa-center { text-align: center; color: #000000; }
    .sa-table .sa-mark   { text-align: center; font-weight: 800; color: #c62828; }
    .sa-table .sa-grade  { text-align: center; font-weight: 800; color: #000000; }
    .sa-table .sa-remark { font-size: 0.78rem; text-transform: uppercase; color: #c62828; font-weight: 700; }

    .sa-summary {
        display: flex; gap: 1.5rem; flex-wrap: wrap;
        padding: .75rem 1rem;
        border-radius: .5rem;
        background: rgba(var(--accent-rgb), 0.06);
        margin-bottom: 1rem;
        font-size: .85rem;
    }
    .sa-summary strong { color: var(--accent-color); }

    [data-theme="dark"] .sa-table {
        background: var(--surface-color);
        color: var(--text-color);
    }
    [data-theme="dark"] .sa-table thead th {
        background: var(--border-color);
        color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .sa-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .sa-table .sa-num    { color: var(--text-muted); }
    [data-theme="dark"] .sa-table .sa-stu,
    [data-theme="dark"] .sa-table .sa-adm,
    [data-theme="dark"] .sa-table .sa-center,
    [data-theme="dark"] .sa-table .sa-grade  { color: var(--text-color); }
    [data-theme="dark"] .sa-table .sa-mark   { color: #ff7b7b; }
    [data-theme="dark"] .sa-table .sa-remark { color: #ff7b7b; }
    [data-theme="dark"] .sa-table tbody tr:hover td {
        background: rgba(var(--accent-rgb), 0.14);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Subject Analysis</h4>
            <small class="text-muted">Per-student breakdown for a single subject</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="printSaBtn">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="exportSaBtn">
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/academic/subject-analysis" class="row g-2" id="saFilterForm">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" id="sa_year" class="form-select form-select-sm" required>
                        <option value="">Select Year</option>
                        <?php foreach ($filters['academic_years'] as $year): ?>
                            <option value="<?= (int)$year['id'] ?>" <?= ($selectedFilters['academic_year_id'] ?? '') == $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Term</label>
                    <select name="term_id" id="sa_term" class="form-select form-select-sm">
                        <option value="">All Terms</option>
                        <?php foreach ($filters['terms'] as $term): ?>
                            <option value="<?= (int)$term['id'] ?>"
                                    data-year="<?= (int)($term['academic_year_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['term_id'] ?? '') == $term['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($term['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="sa_class" class="form-select form-select-sm" required>
                        <option value="">Select Class</option>
                        <?php foreach ($filters['classes'] as $class): ?>
                            <option value="<?= (int)$class['id'] ?>" <?= ($selectedFilters['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Stream</label>
                    <select name="stream_id" id="sa_stream" class="form-select form-select-sm">
                        <option value="">All Streams</option>
                        <?php foreach ($filters['streams'] as $stream): ?>
                            <option value="<?= (int)$stream['id'] ?>"
                                    data-class="<?= (int)($stream['class_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['stream_id'] ?? '') == $stream['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stream['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" id="sa_subject" class="form-select form-select-sm" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($filters['subjects'] as $subj): ?>
                            <option value="<?= (int)$subj['id'] ?>" <?= ($selectedFilters['subject_id'] ?? '') == $subj['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subj['code'] ?: $subj['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Examination</label>
                    <select name="examination_id" id="sa_exam" class="form-select form-select-sm">
                        <option value="">All Exams (blended)</option>
                        <?php foreach ($filters['examinations'] as $exam): ?>
                            <option value="<?= (int)$exam['id'] ?>"
                                    data-year="<?= (int)($exam['academic_year_id'] ?? 0) ?>"
                                    data-term="<?= (int)($exam['academic_period_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['examination_id'] ?? '') == $exam['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($exam['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Generate
                    </button>
                    <a href="<?= BASE_URL ?>/reports/academic/subject-analysis" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($matrix['students']) && !empty($matrix['subject'])): ?>
        <?php $sum = $matrix['summary'] ?? []; ?>
        <div class="sa-summary">
            <span>Subject: <strong><?= htmlspecialchars($matrix['subject']['code'] ?: $matrix['subject']['name']) ?></strong></span>
            <span>Students: <strong><?= count($matrix['students']) ?></strong></span>
            <span>Entered: <strong><?= (int)($sum['entered'] ?? 0) ?></strong></span>
            <span>Missing: <strong><?= (int)($sum['missing'] ?? 0) ?></strong></span>
            <span>Avg mark: <strong><?= htmlspecialchars((string)($sum['avg_mark'] ?? 0)) ?></strong></span>
            <span>Highest: <strong><?= htmlspecialchars((string)($sum['highest'] ?? '-')) ?></strong></span>
            <span>Lowest: <strong><?= htmlspecialchars((string)($sum['lowest'] ?? '-')) ?></strong></span>
        </div>

        <div class="card">
            <div class="card-header sa-card-header fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fas fa-book me-2 text-secondary"></i>Marks Breakdown</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table sa-table mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 110px;">ADM NO</th>
                                <th style="min-width: 200px;">STUDENT</th>
                                <th style="width: 50px;" class="text-center">SEX</th>
                                <th style="width: 80px;" class="text-center">MARK</th>
                                <th style="width: 80px;" class="text-center">GRADE</th>
                                <th style="width: 80px;" class="text-center">SCORE</th>
                                <th style="min-width: 220px;">REMARK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matrix['students'] as $i => $st): ?>
                                <?php
                                    $sid = (int)$st['id'];
                                    $m   = $matrix['marks'][$sid] ?? [];
                                    $rawSex = strtoupper(trim($st['gender'] ?? ''));
                                    $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true) ? 'F'
                                            : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');
                                ?>
                                <tr>
                                    <td class="sa-num"><?= $i + 1 ?></td>
                                    <td class="sa-adm"><?= htmlspecialchars($st['admission_number'] ?? '-') ?></td>
                                    <td class="sa-stu"><?= htmlspecialchars(trim(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? ''))) ?></td>
                                    <td class="sa-center"><?= $sex ?></td>
                                    <td class="sa-mark"><?= $m['mark'] !== null ? (int)$m['mark'] : '-' ?></td>
                                    <td class="sa-grade"><?= htmlspecialchars((string)($m['grade'] ?? '-')) ?></td>
                                    <td class="sa-center"><?= (int)($m['score'] ?? 0) ?></td>
                                    <td class="sa-remark"><?= htmlspecialchars((string)($m['remark'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-book-open fa-3x d-block mb-3 opacity-25"></i>
                <h6 class="fw-bold">No data available</h6>
                <p class="small mb-0">Select an Academic Year, Class, and Subject to generate the analysis.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const url      = '<?= BASE_URL ?>';
    const yearSel  = document.getElementById('sa_year');
    const termSel  = document.getElementById('sa_term');
    const classSel = document.getElementById('sa_class');
    const strmSel  = document.getElementById('sa_stream');
    const examSel  = document.getElementById('sa_exam');

    if (yearSel && termSel) {
        const allTerms = Array.from(termSel.querySelectorAll('option[data-year]'));
        yearSel.addEventListener('change', function () {
            const y = yearSel.value;
            termSel.innerHTML = '<option value="">All Terms</option>';
            allTerms.forEach(function (opt) {
                if (!y || opt.getAttribute('data-year') === y) {
                    termSel.appendChild(opt.cloneNode(true));
                }
            });
        });
    }

    if (classSel && strmSel) {
        const allStreams = Array.from(strmSel.querySelectorAll('option[data-class]'));
        classSel.addEventListener('change', function () {
            const c = classSel.value;
            strmSel.innerHTML = '<option value="">All Streams</option>';
            allStreams.forEach(function (opt) {
                if (!c || opt.getAttribute('data-class') === c) {
                    strmSel.appendChild(opt.cloneNode(true));
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
        ['sa_year','sa_term','sa_class','sa_stream','sa_subject','sa_exam'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.value) p.set(el.name, el.value);
        });
        return p.toString();
    }

    document.getElementById('printSaBtn').addEventListener('click', function () {
        window.open(url + '/reports/academic/subject-analysis/print?' + currentQs(), '_blank');
    });
    document.getElementById('exportSaBtn').addEventListener('click', function () {
        window.open(url + '/reports/academic/subject-analysis/export?' + currentQs(), '_blank');
    });
})();
</script>