<div class="container-fluid px-3 py-3 bg-white border">

    <!-- Flash Notifications -->
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

    <!-- Filter & Action Bar Form -->
    <form method="GET" action="<?= BASE_URL . '/students/enrollments' ?>" id="filterForm">
        <div class="card mb-3 bg-light border">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 100px;">Academic Year</label>
                        <select name="academic_year_id" form="enrollmentForm" class="form-select form-select-sm" onchange="submitFilter();">
                            <option value="">-- Select Year --</option>
                            <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= $selectedYear == $ay['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ay['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Class</label>
                        <select name="class_id" form="enrollmentForm" class="form-select form-select-sm" onchange="submitFilter();">
                            <option value="">-- Select Class --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $selectedClass == $c['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-center">
                        <label class="fw-bold me-2 mb-0 text-nowrap" style="min-width: 60px;">Term</label>
                        <select name="term_id" form="enrollmentForm" class="form-select form-select-sm" onchange="submitFilter();">
                            <option value="">-- Select Term --</option>
                            <?php foreach ($terms as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $selectedTerm == $t['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 text-end">
                        <button type="submit" form="enrollmentForm" class="btn btn-sm btn-success fw-bold">
                            <i class="fas fa-user-plus me-1"></i> Enroll Selected Students
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Student Table Grid Form -->
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
function submitFilter() {
    let form = document.getElementById('filterForm');
    
    // Copy select elements temporarily into filterForm to execute GET request
    document.querySelectorAll('[form="enrollmentForm"]').forEach(select => {
        if(select.tagName === 'SELECT') {
            let hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = select.name;
            hidden.value = select.value;
            form.appendChild(hidden);
        }
    });

    form.submit();
}

document.getElementById('selectAll').addEventListener('change', function() {
    let checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>