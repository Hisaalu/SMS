<!-- File: /app/Views/reports/academic/student_results_view.php -->
<style>
    .sr-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: .6px;
        text-transform: uppercase;
        font-size: 1rem;
        padding-bottom: .35rem;
        border-bottom: 2px solid var(--border-color);
        margin-bottom: .6rem;
        color: var(--text-color);
    }
    .sr-meta {
        font-size: .85rem;
        margin-bottom: .9rem;
        color: var(--text-color);
    }
    .sr-meta strong { color: var(--accent-color); }

    .sr-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.85rem;
        background: #ffffff;
        color: #000000;
    }
    .sr-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        text-align: center;
        vertical-align: middle;
        padding: 5px 6px;
    }
    .sr-table td {
        border: 1px solid #94a3b8;
        padding: 5px 8px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .sr-table .sr-code { font-weight: 700; text-align: center; }
    .sr-table .sr-name { font-weight: 700; }
    .sr-table .sr-mark,
    .sr-table .sr-grade { text-align: center; font-weight: 700; }
    .sr-table tfoot td {
        background: #eef2f7;
        font-weight: 800;
        color: #000000;
    }

    [data-theme="dark"] .sr-title { color: var(--text-color); border-bottom-color: var(--border-color); }
    [data-theme="dark"] .sr-meta  { color: var(--text-color); }
    [data-theme="dark"] .sr-table { background: var(--surface-color); color: var(--text-color); }
    [data-theme="dark"] .sr-table thead th {
        background: var(--border-color); color: var(--accent-color);
        border: 1px solid var(--border-color);
        border-bottom: 2px solid var(--border-color);
    }
    [data-theme="dark"] .sr-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
    }
    [data-theme="dark"] .sr-table tfoot td {
        background: rgba(var(--accent-rgb), 0.10);
        color: var(--text-color);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Student Results</h4>
            <small class="text-muted">Academic progress report</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($backUrl ?? (BASE_URL . '/reports/academic/student-results')) ?>"
               class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <?php if (!empty($summary['rows'])): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="printSrBtn">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($summary['student'])): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-user-slash fa-3x mb-3 d-block opacity-25"></i>
            <h6>Student not found</h6>
        </div></div>
    <?php elseif (empty($summary['exams'])): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-clipboard-list fa-3x mb-3 d-block opacity-25"></i>
            <h6>No exams scheduled</h6>
            <p class="small mb-0">No examinations were found for the selected year and term.</p>
        </div></div>
    <?php else: ?>

        <?php
            $student      = $summary['student'];
            $academicYear = $summary['academic_year'];
            $term         = $summary['term'];
            $class        = $summary['class'];
            $stream       = $summary['stream'];
            $exams        = $summary['exams'];
            $rows         = $summary['rows'];
            $totals       = $summary['totals'];
            $fullName     = strtoupper(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')));
        ?>

        <div class="card mb-3">
            <div class="card-body">
                <div class="sr-title">Academic Progress Report (<?= htmlspecialchars($fullName) ?>)</div>

                <div class="sr-meta">
                    Academic Year: <strong><?= htmlspecialchars($academicYear['name'] ?? '-') ?></strong>
                    &nbsp;|&nbsp;
                    Term: <strong><?= htmlspecialchars($term['name'] ?? '-') ?></strong>
                    &nbsp;|&nbsp;
                    Class: <strong><?= htmlspecialchars($class['name'] ?? '-') ?></strong>
                    <?php if (!empty($stream['name'])): ?>
                        &nbsp;|&nbsp; Stream: <strong><?= htmlspecialchars($stream['name']) ?></strong>
                    <?php endif; ?>
                    &nbsp;|&nbsp;
                    Reg No: <strong><?= htmlspecialchars($student['admission_number'] ?? '-') ?></strong>
                </div>

                <div class="table-responsive">
                    <table class="table sr-table mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 50px;">No</th>
                                <th rowspan="2" style="width: 90px;">Code</th>
                                <th rowspan="2">Subject</th>
                                <?php foreach ($exams as $exam): ?>
                                    <th colspan="2"><?= htmlspecialchars(strtoupper($exam['name'])) ?></th>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <?php foreach ($exams as $exam): ?>
                                    <th style="width: 70px;">Marks</th>
                                    <th style="width: 70px;">Grade</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $i => $row): ?>
                                <tr>
                                    <td class="text-center"><?= $i + 1 ?></td>
                                    <td class="sr-code"><?= htmlspecialchars(strtoupper($row['subject_code'] ?: $row['subject_name'])) ?></td>
                                    <td class="sr-name"><?= htmlspecialchars(strtoupper($row['subject_name'])) ?></td>
                                    <?php foreach ($exams as $exam): ?>
                                        <?php
                                            $examId = (int)$exam['id'];
                                            $cell   = $row['exams'][$examId] ?? null;
                                        ?>
                                        <td class="sr-mark"><?= $cell && $cell['mark'] !== null ? (int)$cell['mark'] : '-' ?></td>
                                        <td class="sr-grade"><?= $cell && $cell['grade'] !== null ? htmlspecialchars((string)$cell['grade']) : '-' ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-start">TOTAL</td>
                                <?php foreach ($exams as $exam): ?>
                                    <?php
                                        $examId = (int)$exam['id'];
                                        $t      = $totals[$examId] ?? ['sum' => 0, 'avg' => 0, 'score' => 0];
                                    ?>
                                    <td class="sr-mark">
                                        <?= (int)$t['sum'] ?><?= $t['count'] > 0 ? '(' . htmlspecialchars((string)$t['avg']) . ')' : '' ?>
                                    </td>
                                    <td class="sr-grade"><?= (int)$t['score'] ?></td>
                                <?php endforeach; ?>
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
    const printBtn = document.getElementById('printSrBtn');
    if (!printBtn) return;

    printBtn.addEventListener('click', function () {
        const params = new URLSearchParams({
            student_id:       <?= (int)($summary['student']['id'] ?? 0) ?>,
            academic_year_id: <?= (int)($summary['academic_year']['id'] ?? 0) ?>,
            term_id:          <?= (int)($summary['term']['id'] ?? 0) ?>,
        }).toString();

        window.open('<?= BASE_URL ?>/reports/academic/student-results/print?' + params, '_blank');
    });
})();
</script>