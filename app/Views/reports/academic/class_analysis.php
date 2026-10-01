<!-- File: /app/Views/reports/academic/class_analysis.php -->
<style>
    .ca-card-header {
        background: var(--surface-color);
        border-bottom: 1px solid var(--border-color);
        color: var(--text-color);
    }
    .ca-table {
        border-collapse: collapse;
        font-size: 0.82rem;
        white-space: nowrap;
        background: #ffffff;
        color: #000000;
    }
    .ca-table thead th {
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
    .ca-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .ca-table tbody tr:hover td {
        background: rgba(var(--accent-rgb), 0.10);
    }
    .ca-table .ca-num     { text-align: center; color: #475569; }
    .ca-table .ca-stu     { font-weight: 700; color: #000000; text-transform: uppercase; }
    .ca-table .ca-adm     { font-weight: 700; color: #000000; }
    .ca-table .ca-marks   { text-align: center; color: #000000; }
    .ca-table .ca-total,
    .ca-table .ca-avg,
    .ca-table .ca-agg,
    .ca-table .ca-div     { font-weight: 800; text-align: center; }
    .ca-table .ca-total,
    .ca-table .ca-avg,
    .ca-table .ca-agg     { color: #c62828; }
    .ca-table .ca-div     { color: #000000; }

    /* Non-contributing subject columns (e.g. ICT, Music) get muted styling */
    .ca-table thead th.ca-noncontrib,
    .ca-table td.ca-noncontrib {
        background: #f8fafc;
        color: #64748b;
        font-style: italic;
    }
    .ca-table thead th.ca-noncontrib {
        color: #64748b;
    }

    [data-theme="dark"] .ca-table {
        background: var(--surface-color);
        color: var(--text-color);
    }
    [data-theme="dark"] .ca-table thead th {
        background: var(--border-color);
        color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .ca-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .ca-table tbody tr:hover td {
        background: rgba(var(--accent-rgb), 0.14);
    }
    [data-theme="dark"] .ca-table .ca-num { color: var(--text-muted); }
    [data-theme="dark"] .ca-table .ca-stu,
    [data-theme="dark"] .ca-table .ca-adm,
    [data-theme="dark"] .ca-table .ca-marks,
    [data-theme="dark"] .ca-table .ca-div { color: var(--text-color); }
    [data-theme="dark"] .ca-table .ca-total,
    [data-theme="dark"] .ca-table .ca-avg,
    [data-theme="dark"] .ca-table .ca-agg { color: #ff7b7b; }

    [data-theme="dark"] .ca-table thead th.ca-noncontrib,
    [data-theme="dark"] .ca-table td.ca-noncontrib {
        background: rgba(var(--accent-rgb), 0.04);
        color: var(--text-muted);
        font-style: italic;
    }
    [data-theme="dark"] .ca-table thead th.ca-noncontrib {
        color: var(--text-muted);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Performance Analysis</h4>
            <small class="text-muted">Analyse Students Performance</small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="printCaBtn">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="exportCaBtn">
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/reports/academic/class-analysis" class="row g-2" id="caFilterForm">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Academic Year <span class="text-danger">*</span></label>
                    <select name="academic_year_id" id="ca_year" class="form-select form-select-sm" required>
                        <option value="">Select Academic Year</option>
                        <?php foreach ($filters['academic_years'] as $year): ?>
                            <option value="<?= (int)$year['id'] ?>" <?= ($selectedFilters['academic_year_id'] ?? '') == $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Term</label>
                    <select name="term_id" id="ca_term" class="form-select form-select-sm">
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
                    <select name="class_id" id="ca_class" class="form-select form-select-sm" required>
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
                    <select name="stream_id" id="ca_stream" class="form-select form-select-sm">
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

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Examination</label>
                    <select name="examination_id" id="ca_exam" class="form-select form-select-sm">
                        <option value="">All Exams (Average)</option>
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
                    <a href="<?= BASE_URL ?>/reports/academic/class-analysis" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($matrix['students']) && !empty($matrix['subjects'])): ?>
        <div class="card">
            <div class="card-header ca-card-header fw-bold d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-table me-2 text-secondary"></i>Marks Matrix
                </span>
                <small class="text-muted">
                    <?= count($matrix['students']) ?> students ·
                    <?= count($matrix['subjects']) ?> subjects
                    <span class="ms-2" style="font-style:italic; color: var(--text-muted);">
                        (<span style="text-transform:none;">*</span> non-contributing)
                    </span>
                </small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                    <table class="table ca-table mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th style="width: 110px;">ADM NO</th>
                                <th style="min-width: 200px;">STUDENT</th>
                                <th style="width: 50px;" class="text-center">SEX</th>
                                <?php foreach ($matrix['subjects'] as $subj): ?>
                                    <?php $isContrib = !isset($subj['contributes']) || !empty($subj['contributes']); ?>
                                    <th class="text-center <?= $isContrib ? '' : 'ca-noncontrib' ?>"
                                        style="width: 70px;"
                                        title="<?= htmlspecialchars($subj['name']) ?><?= $isContrib ? '' : ' (non-contributing)' ?>">
                                        <?= htmlspecialchars(strtoupper($subj['code'] ?: $subj['name'])) ?>
                                        <?= $isContrib ? '' : '*' ?>
                                    </th>
                                <?php endforeach; ?>
                                <th style="width: 70px;" class="text-center">TOTAL</th>
                                <th style="width: 70px;" class="text-center">AVG</th>
                                <th style="width: 70px;" class="text-center">AGG</th>
                                <th style="width: 70px;" class="text-center">DIV</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($matrix['students'] as $i => $student): ?>
                                <?php
                                    $sid    = (int)$student['id'];
                                    $totals = $matrix['totals'][$sid] ?? null;
                                    $rawSex = strtoupper(trim($student['gender'] ?? ''));
                                    $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true)
                                        ? 'F'
                                        : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');
                                ?>
                                <tr>
                                    <td class="ca-num"><?= $i + 1 ?></td>
                                    <td class="ca-adm"><?= htmlspecialchars($student['admission_number'] ?? '-') ?></td>
                                    <td class="ca-stu">
                                        <?= htmlspecialchars(trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? ''))) ?>
                                    </td>
                                    <td class="ca-num"><?= $sex ?></td>
                                    <?php foreach ($matrix['subjects'] as $subj): ?>
                                        <?php
                                            $subjId    = (int)$subj['id'];
                                            $mark      = $matrix['marks'][$sid][$subjId] ?? null;
                                            $isContrib = !isset($subj['contributes']) || !empty($subj['contributes']);
                                        ?>
                                        <td class="ca-marks <?= $isContrib ? '' : 'ca-noncontrib' ?>">
                                            <?= $mark !== null ? (int)$mark : '-' ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="ca-total"><?= $totals ? (int)$totals['total'] : '-' ?></td>
                                    <td class="ca-avg"><?= $totals ? htmlspecialchars((string)$totals['average']) : '-' ?></td>
                                    <td class="ca-agg">
                                        <?php if (!$totals): ?>
                                            -
                                        <?php elseif (!empty($totals['has_missing'])): ?>
                                            X
                                        <?php else: ?>
                                            <?= (int)$totals['aggregate'] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="ca-div">
                                        <?php if (!$totals): ?>
                                            -
                                        <?php elseif (!empty($totals['has_missing'])): ?>
                                            U
                                        <?php elseif (!empty($totals['division_code'])): ?>
                                            <?= htmlspecialchars(strtoupper((string)$totals['division_code'])) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
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
                <i class="fas fa-clipboard-list fa-3x d-block mb-3 opacity-25"></i>
                <h6 class="fw-bold">No data available</h6>
                <p class="small mb-0">Select an Academic Year and a Class to generate the marks matrix.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const url       = '<?= BASE_URL ?>';
    const yearSel   = document.getElementById('ca_year');
    const termSel   = document.getElementById('ca_term');
    const classSel  = document.getElementById('ca_class');
    const streamSel = document.getElementById('ca_stream');
    const examSel   = document.getElementById('ca_exam');

    // --- Cascade: Year → Term ------------------------------------------------
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

    // --- Cascade: Class → Stream ---------------------------------------------
    if (classSel && streamSel) {
        const allStreams = Array.from(streamSel.querySelectorAll('option[data-class]'));
        classSel.addEventListener('change', function () {
            const c = classSel.value;
            streamSel.innerHTML = '<option value="">All Streams</option>';
            allStreams.forEach(function (opt) {
                if (!c || opt.getAttribute('data-class') === c) {
                    streamSel.appendChild(opt.cloneNode(true));
                }
            });
        });
    }

    // --- Cascade: Year + Term → Examination ----------------------------------
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
                // If a term is chosen, exams must match it OR have no term set.
                const termOk = !t || ot === t || ot === '' || ot === '0';

                if (yearOk && termOk) {
                    examSel.appendChild(opt.cloneNode(true));
                }
            });
        }

        if (yearSel) yearSel.addEventListener('change', rebuildExams);
        if (termSel) termSel.addEventListener('change', rebuildExams);

        // Initial pass — respect any values already selected in the URL.
        rebuildExams();
    }

    function currentQs() {
        const p = new URLSearchParams();
        if (yearSel.value)   p.set('academic_year_id', yearSel.value);
        if (termSel.value)   p.set('term_id',          termSel.value);
        if (classSel.value)  p.set('class_id',         classSel.value);
        if (streamSel.value) p.set('stream_id',        streamSel.value);
        if (examSel && examSel.value) p.set('examination_id', examSel.value);
        return p.toString();
    }

    const printBtn  = document.getElementById('printCaBtn');
    const exportBtn = document.getElementById('exportCaBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.open(url + '/reports/academic/class-analysis/print?' + currentQs(), '_blank');
        });
    }
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            window.open(url + '/reports/academic/class-analysis/export?' + currentQs(), '_blank');
        });
    }
})();
</script>