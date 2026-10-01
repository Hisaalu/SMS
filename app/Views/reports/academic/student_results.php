<!-- File: /app/Views/reports/academic/student_results.php -->
<style>
    .srp-students {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .srp-students thead th {
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
    .srp-students td {
        border: 1px solid #94a3b8;
        padding: 6px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .srp-students tbody tr:hover td { background: rgba(var(--accent-rgb), 0.10); }
    .srp-students .col-num  { text-align: center; color: #475569; }
    .srp-students .col-adm  { font-weight: 700; }
    .srp-students .col-name { font-weight: 700; text-transform: uppercase; }
    .srp-students .col-sex  { text-align: center; }
    .srp-students .col-act  { text-align: center; white-space: nowrap; }
    .srp-students tbody tr { cursor: pointer; }
    .srp-students tbody tr.active-row td { background: rgba(var(--accent-rgb), 0.18); }

    [data-theme="dark"] .srp-students {
        background: var(--surface-color); color: var(--text-color);
    }
    [data-theme="dark"] .srp-students thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .srp-students td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .srp-students tbody tr:hover td { background: rgba(var(--accent-rgb), 0.14); }
    [data-theme="dark"] .srp-students .col-num { color: var(--text-muted); }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Student Results</h4>
            <small class="text-muted">View Student Results</small>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-3 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Search</label>
                    <input type="text" id="srSearch" class="form-control form-control-sm"
                           placeholder="Name, Adm, Reg...">
                </div>

                <div class="col-md-2 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Year</label>
                    <select id="srYear" class="form-select form-select-sm">
                        <option value="">-- All --</option>
                        <?php foreach ($filters['academic_years'] as $year): ?>
                            <option value="<?= (int)$year['id'] ?>" <?= ($selectedFilters['academic_year_id'] ?? 0) == $year['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Term</label>
                    <select id="srTerm" class="form-select form-select-sm">
                        <option value="">-- All --</option>
                        <?php foreach ($filters['terms'] as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"
                                    data-year="<?= (int)($t['academic_year_id'] ?? 0) ?>"
                                    <?= ($selectedFilters['term_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-center">
                    <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Class</label>
                    <select id="srClass" class="form-select form-select-sm">
                        <option value="">-- All --</option>
                        <?php foreach ($filters['classes'] as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($selectedFilters['class_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="srClearBtn">
                        <i class="fas fa-redo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white pt-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-users me-2 text-secondary"></i>Students
                <small class="text-muted fw-normal ms-1">(<span id="srStudentCount"><?= count($students) ?></span> total)</small>
            </h6>
        </div>

        <div class="table-responsive" style="max-height: 620px; overflow-y: auto;">
            <table class="table srp-students mb-0">
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
                <tbody id="srStudentRows">
                    <?php foreach ($students as $index => $st): ?>
                        <?php
                            $rawSex = strtoupper(trim($st['gender'] ?? ''));
                            $sex    = in_array($rawSex, ['F', 'FEMALE', '2'], true) ? 'F'
                                    : (in_array($rawSex, ['M', 'MALE', '1'], true) ? 'M' : '-');
                            $isActive = !empty($summary['student']['id']) && (int)$summary['student']['id'] === (int)$st['id'];
                        ?>
                        <tr class="student-row <?= $isActive ? 'active-row' : '' ?>"
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
                                        data-action="view-results"
                                        data-id="<?= (int)$st['id'] ?>">
                                    <i class="fas fa-chart-line me-1"></i> View Results
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
    const yearSel   = document.getElementById('srYear');
    const termSel   = document.getElementById('srTerm');
    const classSel  = document.getElementById('srClass');
    const searchEl  = document.getElementById('srSearch');
    const clearBtn  = document.getElementById('srClearBtn');
    const rowsWrap  = document.getElementById('srStudentRows');
    const countEl   = document.getElementById('srStudentCount');

    if (yearSel && termSel) {
        const allTerms = Array.from(termSel.querySelectorAll('option[data-year]'));
        yearSel.addEventListener('change', function () {
            const y = yearSel.value;
            const keep = termSel.value;
            termSel.innerHTML = '<option value="">-- All --</option>';
            allTerms.forEach(function (opt) {
                if (!y || opt.getAttribute('data-year') === y) {
                    const c = opt.cloneNode(true);
                    if (c.value === keep) c.selected = true;
                    termSel.appendChild(c);
                }
            });
        });
    }

    function applyFilters() {
        const term = (searchEl.value || '').trim().toUpperCase();
        const clsName = classSel && classSel.value
            ? (classSel.options[classSel.selectedIndex].text || '').trim().toUpperCase()
            : '';
        let visible = 0;

        rowsWrap.querySelectorAll('tr.student-row').forEach(function (row) {
            const name = (row.getAttribute('data-name')  || '').toUpperCase();
            const adm  = (row.getAttribute('data-adm')   || '').toUpperCase();
            const rCls = (row.getAttribute('data-class') || '').toUpperCase();

            const searchOk = term === '' || name.indexOf(term) !== -1 || adm.indexOf(term) !== -1;
            const classOk  = clsName === '' || rCls === clsName;

            if (searchOk && classOk) {
                row.classList.remove('d-none');
                visible++;
            } else {
                row.classList.add('d-none');
            }
        });

        if (countEl) countEl.textContent = visible;
    }

    if (searchEl) searchEl.addEventListener('input', applyFilters);
    if (classSel) classSel.addEventListener('change', applyFilters);
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (searchEl) searchEl.value = '';
            if (yearSel)  yearSel.value  = '';
            if (termSel)  termSel.value  = '';
            if (classSel) classSel.value = '';
            applyFilters();
        });
    }

    function viewResults(studentId) {
        const y = yearSel ? yearSel.value : '';
        const t = termSel ? termSel.value : '';

        if (!y || !t) {
            alert('Please select an Academic Year and a Term first.');
            return;
        }

        const params = new URLSearchParams({
            student_id:       studentId,
            academic_year_id: y,
            term_id:          t,
            class_id:         classSel ? classSel.value || '' : '',
        });

        window.location.href = url + '/reports/academic/student-results/view?' + params.toString();
    }

    rowsWrap.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action="view-results"]');
        if (btn) {
            e.stopPropagation();
            viewResults(btn.getAttribute('data-id'));
        }
    });
    rowsWrap.addEventListener('dblclick', function (e) {
        const row = e.target.closest('tr.student-row');
        if (row) viewResults(row.getAttribute('data-id'));
    });
})();
</script>