<!-- File: /app/Views/students/enrollment.php -->
<div class="container-fluid px-3 py-3 bg-white border">

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="GET" action="<?= BASE_URL . '/students/enrollments' ?>" id="filterForm">
        <div class="card mb-3 bg-light border">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center">

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 90px;">Year</label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm" onchange="submitFilter();">
                            <option value="">-- All --</option>
                            <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= $selectedYear == $ay['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ay['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 90px;">Term</label>
                        <select name="term_id" id="term_id" class="form-select form-select-sm" onchange="submitFilter();">
                            <option value="">-- All --</option>
                            <?php foreach ($terms as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $selectedTerm == $t['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 90px;">Class</label>
                        <select name="class_id" id="class_id" class="form-select form-select-sm" onchange="submitFilter();">
                            <option value="">-- All --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 90px;">Stream</label>
                        <select name="stream_id" id="stream_id" class="form-select form-select-sm" onchange="submitFilter();">
                            <option value="">-- All --</option>
                            <?php foreach ($streams as $st): ?>
                                <option value="<?= $st['id'] ?>" <?= ($selectedStream ?? 0) == $st['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="row mt-2">
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="printEnrollmentBtn">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        <button type="submit" class="btn btn-sm btn-success fw-bold">
                            <i class="fas fa-user-plus me-1"></i> Enroll
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form method="POST" action="<?= BASE_URL . '/students/enrollments/store' ?>" id="enrollmentForm">
        <div class="table-responsive border" style="max-height: 550px; overflow-y: auto;">
            <table class="table table-bordered table-hover align-middle mb-0 text-nowrap small">
                <thead class="table-secondary sticky-top">
                    <tr>
                        <th style="width: 30px;" class="text-center">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th style="width: 40px;">No</th>
                        <th>Student No</th>
                        <th>Name</th>
                        <th>Sex</th>
                        <th>Class</th>
                        <th>Stream</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Term</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($candidateStudents)): ?>
                        <?php foreach ($candidateStudents as $index => $st): ?>
                            <?php 
                                $rawSex = strtoupper(trim($st['gender'] ?? ''));
                                $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F' : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                            ?>
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" name="student_ids[]" value="<?= $st['id'] ?>" class="form-check-input student-checkbox">
                                </td>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($st['admission_number'] ?? '-') ?></td>
                                <td class="fw-bold text-uppercase"><?= htmlspecialchars(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? '')) ?></td>
                                <td class="text-center"><?= $sexDisplay ?></td>
                                <td><?= htmlspecialchars($st['class_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($st['stream_name'] ?? 'GENERAL') ?></td>
                                <td><?= htmlspecialchars($st['section_name'] ?? 'Day') ?></td>
                                <td><?= htmlspecialchars($st['status_name'] ?? 'Old') ?></td>
                                <td><?= htmlspecialchars($st['term_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($st['academic_year_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                No enrolled students found for the selected Academic Year, Class, and Term.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script>

const ENROLL_TERMS = <?= json_encode(array_map(function ($t) {
    return [
        'id' => (int)$t['id'],
        'name' => $t['name'],
        'academic_year_id' => (int)($t['academic_year_id'] ?? 0),
    ];
}, $terms)) ?>;

const ENROLL_STREAMS = <?= json_encode(array_map(function ($st) {
    return [
        'id' => (int)$st['id'],
        'name' => $st['name'],
        'class_id' => (int)($st['class_id'] ?? 0),
    ];
}, $streams)) ?>;

const INITIAL = {
    year:   '<?= (int)$selectedYear ?>',
    term:   '<?= (int)$selectedTerm ?>',
    class:  '<?= (int)$selectedClass ?>',
    stream: '<?= (int)$selectedStream ?>'
};

function rebuildSelect(selectEl, items, placeholder, selectedId) {
    selectEl.innerHTML = '<option value="">' + placeholder + '</option>';
    items.forEach(function (item) {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.name;
        if (String(selectedId) === String(item.id)) opt.selected = true;
        selectEl.appendChild(opt);
    });
}

function onYearChange(preserve) {
    const yearId  = document.getElementById('academic_year_id').value;
    const termSel = document.getElementById('term_id');
    const strmSel = document.getElementById('stream_id');

    const keepTerm   = preserve ? (termSel.value || INITIAL.term) : '';
    const keepStream = preserve ? (strmSel.value || INITIAL.stream) : '';

    const terms = yearId
        ? ENROLL_TERMS.filter(t => String(t.academic_year_id) === String(yearId))
        : ENROLL_TERMS;

    rebuildSelect(termSel, terms, '-- All --', keepTerm);
    rebuildSelect(strmSel, [],    '-- All --', keepStream);
}

function onClassChange(preserve) {
    const classId = document.getElementById('class_id').value;
    const strmSel = document.getElementById('stream_id');

    const keepStream = preserve ? (strmSel.value || INITIAL.stream) : '';

    const streams = classId
        ? ENROLL_STREAMS.filter(s => String(s.class_id) === String(classId))
        : ENROLL_STREAMS;

    rebuildSelect(strmSel, streams, '-- All --', keepStream);
}

function submitFilter() {
    const form = document.getElementById('filterForm');

    form.querySelectorAll('input[type="hidden"]').forEach(el => el.remove());

    ['academic_year_id', 'term_id', 'class_id', 'stream_id'].forEach(function (id) {
        const sel = document.getElementById(id);
        if (!sel) return;
        const hidden = document.createElement('input');
        hidden.type  = 'hidden';
        hidden.name  = sel.name;
        hidden.value = sel.value;
        form.appendChild(hidden);
    });

    form.submit();
}

document.getElementById('printEnrollmentBtn').addEventListener('click', function () {
    const params = new URLSearchParams();
    ['academic_year_id', 'term_id', 'class_id', 'stream_id'].forEach(function (id) {
        const sel = document.getElementById(id);
        if (sel && sel.value) params.append(sel.name, sel.value);
    });
    window.open('<?= BASE_URL ?>/students/enrollments/print?' + params.toString(), '_blank');
});

document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = this.checked);
});

document.getElementById('academic_year_id').addEventListener('change', function () {
    onYearChange(false);
    submitFilter();
});
document.getElementById('class_id').addEventListener('change', function () {
    onClassChange(false);
    submitFilter();
});

<?php if (!empty($selectedYear)): ?>
onYearChange(true);
<?php endif; ?>
<?php if (!empty($selectedClass)): ?>
onClassChange(true);
<?php endif; ?>
</script>