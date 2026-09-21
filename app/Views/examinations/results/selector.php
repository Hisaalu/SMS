<!-- File: /app/Views/examinations/results/selector.php -->
<div class="container-fluid px-3 py-3">

    <!-- Flash -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white pt-3 pb-0 border-0">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="fas fa-filter me-2 text-secondary"></i>Filter Class Results
            </h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>/results/class" method="GET" id="resultsFilterForm">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Academic Year <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm" required>
                            <option value="">-- Select Year --</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= (isset($_GET['academic_year_id']) && $_GET['academic_year_id'] == $year['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($year['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Term <span class="text-danger">*</span></label>
                        <select name="term_id" id="term_id" class="form-select form-select-sm" required>
                            <option value="">-- Term --</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= $term['id'] ?>" <?= (isset($_GET['term_id']) && $_GET['term_id'] == $term['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-select form-select-sm" required>
                            <option value="">-- Class --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>" <?= (isset($_GET['class_id']) && $_GET['class_id'] == $class['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($class['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Stream</label>
                        <select name="stream_id" id="stream_id" class="form-select form-select-sm">
                            <option value="">-- All Streams --</option>
                            <?php foreach ($streams as $str): ?>
                                <option value="<?= $str['id'] ?>" <?= (isset($_GET['stream_id']) && $_GET['stream_id'] == $str['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($str['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Examination <span class="text-danger">*</span></label>
                        <select name="examination_id" id="examination_id" class="form-select form-select-sm" required>
                            <option value="">-- Exam --</option>
                            <?php foreach ($examinations as $exam): ?>
                                <option value="<?= $exam['id'] ?>" <?= (isset($_GET['examination_id']) && $_GET['examination_id'] == $exam['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($exam['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <a href="<?= BASE_URL ?>/results/class" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-sm btn-dark">
                            <i class="fas fa-search me-1"></i> View Results
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <?php if (isset($examination) && $examination): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white pt-3 pb-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-table me-2 text-secondary"></i>
                        <?= htmlspecialchars($examination['name']) ?>
                    </h6>
                    <?php if (!empty($gradingSystem)): ?>
                        <small class="text-muted">
                            Grading System: <?= htmlspecialchars($gradingSystem['name']) ?>
                        </small>
                    <?php endif; ?>
                </div>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>

            <div class="card-body p-0">
                <?php if (empty($students)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                        <h6>No students found</h6>
                        <p class="small mb-0">Try adjusting your filters or check that students are enrolled in this class/stream.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                        <table class="table table-bordered align-middle mb-0 text-nowrap results-table">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;" class="text-center text-secondary small">#</th>
                                    <th style="width: 100px;" class="text-secondary small">ADM NO</th>
                                    <th style="min-width: 220px;" class="text-secondary small">NAME</th>
                                    <th style="width: 40px;" class="text-center text-secondary small">SEX</th>
                                    <?php foreach ($subjects as $subj): ?>
                                        <th style="width: 70px;" class="text-center text-secondary small" title="<?= htmlspecialchars($subj['name']) ?>">
                                            <?= htmlspecialchars(strtoupper($subj['code'] ?: $subj['name'])) ?>
                                        </th>
                                    <?php endforeach; ?>
                                    <th style="width: 60px;" class="text-center text-secondary small">TOT</th>
                                    <th style="width: 60px;" class="text-center text-secondary small">AVG</th>
                                    <th style="width: 60px;" class="text-center text-secondary small">T.A</th>
                                    <th style="width: 60px;" class="text-center text-secondary small">DIV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                    <?php
                                        $sid = $student['id'];
                                        $totals = $studentTotals[$sid] ?? null;
                                        $rawSex = strtoupper(trim($student['gender'] ?? ''));
                                        $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F' : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                                    ?>
                                    <tr>
                                        <td class="text-center text-muted small"><?= $index + 1 ?></td>
                                        <td class="fw-semibold small"><?= htmlspecialchars($student['admission_number']) ?></td>
                                        <td class="text-uppercase fw-semibold"><?= htmlspecialchars(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')) ?></td>
                                        <td class="text-center small"><?= $sexDisplay ?></td>

                                        <?php foreach ($subjects as $subj): ?>
                                            <?php
                                                $raw   = $resultsMatrix[$sid][$subj['id']] ?? null;
                                                $score = $scoreMatrix[$sid][$subj['id']] ?? null;
                                            ?>
                                            <td class="text-center">
                                                <?php if ($raw !== null): ?>
                                                    <span class="fw-semibold"><?= (int)round($raw) ?></span><?php if ($score !== null): ?><sup class="text-muted ms-1 score-power"><?= (int)round($score) ?></sup><?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>

                                        <td class="text-center fw-bold"><?= $totals ? (int)round($totals['total']) : '-' ?></td>
                                        <td class="text-center"><?= $totals ? (int)round($totals['avg']) : '-' ?></td>
                                        <td class="text-center fw-bold"><?= $totals ? (int)round($totals['ta']) : '-' ?></td>
                                        <td class="text-center fw-bold">
                                            <?php if ($totals && !empty($totals['div'])): ?>
                                                <?= htmlspecialchars(strtoupper($totals['div']['code'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($divisions)): ?>
                <div class="card-footer bg-white border-top">
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span class="fw-semibold">Division Key:</span>
                        <?php foreach ($divisions as $d): ?>
                            <span>
                                <strong><?= htmlspecialchars($d['code']) ?></strong>
                                &nbsp;<?= (int)$d['min_aggregate'] ?>–<?= (int)$d['max_aggregate'] ?>
                                <?php if (!empty($d['description'])): ?>
                                    <span class="text-muted">(<?= htmlspecialchars($d['description']) ?>)</span>
                                <?php endif; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-clipboard-list fa-3x mb-3 d-block opacity-50"></i>
                <h5>Select Filters to View Results</h5>
                <p class="small mb-0">Choose an academic year, term, class, and examination to generate the results matrix.</p>
            </div>
        </div>
    <?php endif; ?>

</div>

<style>
    .results-table {
        font-size: 0.85rem;
    }
    .results-table thead th {
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6 !important;
    }
    .results-table tbody td {
        border-color: #e9ecef;
        vertical-align: middle;
    }
    .results-table tbody tr:hover {
        background: #f8f9fa;
    }
    .results-table sup.score-power {
        font-size: 0.65rem;
        font-weight: 500;
    }
    @media print {
        .card-header button, .card-footer, form, .btn { display: none !important; }
        .results-table { font-size: 10px; }
        .results-table thead th { font-size: 9px; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect  = document.getElementById('class_id');
    const streamSelect = document.getElementById('stream_id');
    const selectedStream = "<?= $_GET['stream_id'] ?? '' ?>";

    function updateStreams(classId, preserveSelected = false) {
        if (!classId) {
            streamSelect.innerHTML = '<option value="">-- All Streams --</option>';
            streamSelect.disabled = false;
            return;
        }
        fetch(`<?= BASE_URL ?>/api/streams-by-class?class_id=${classId}`)
            .then(r => r.json())
            .then(data => {
                streamSelect.innerHTML = '<option value="">-- All Streams --</option>';
                if (!data || data.length === 0) {
                    streamSelect.innerHTML = '<option value="">No streams assigned</option>';
                    streamSelect.disabled = true;
                } else {
                    streamSelect.disabled = false;
                    data.forEach(stream => {
                        const option = document.createElement('option');
                        option.value = stream.id;
                        option.textContent = stream.name;
                        if (preserveSelected && selectedStream == stream.id) option.selected = true;
                        streamSelect.appendChild(option);
                    });
                }
            })
            .catch(() => { streamSelect.disabled = false; });
    }

    if (classSelect) {
        classSelect.addEventListener('change', function () { updateStreams(this.value); });
        if (classSelect.value) updateStreams(classSelect.value, true);
    }
});
</script>