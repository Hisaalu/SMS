<!-- File: /app/Views/examinations/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Create Examination</h4>
            <small class="text-muted">Create a new examination</small>
        </div>
        <a href="<?= BASE_URL ?>/examinations" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/examinations" id="examCreateForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Examination Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" placeholder="e.g., FINAL-2024">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" placeholder="Brief description of the examination"></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select class="form-select" name="academic_year_id" id="exam_year" required>
                            <option value="">Select Year</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= (int)$year['id'] ?>"><?= htmlspecialchars($year['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Term <span class="text-danger">*</span></label>
                        <select class="form-select" name="academic_period_id" id="exam_term" required>
                            <option value="">Select Year First</option>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= (int)$term['id'] ?>"
                                        data-year="<?= (int)($term['academic_year_id'] ?? 0) ?>">
                                    <?= htmlspecialchars($term['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Assessment Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="assessment_type_id" required>
                            <option value="">Select Type</option>
                            <?php foreach ($assessmentTypes as $type): ?>
                                <option value="<?= (int)$type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Create
                    </button>
                    <a href="<?= BASE_URL ?>/examinations" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const yearSel = document.getElementById('exam_year');
    const termSel = document.getElementById('exam_term');
    if (!yearSel || !termSel) return;

    const allTerms = Array.from(termSel.querySelectorAll('option[data-year]'));

    function rebuildTerms() {
        const y = yearSel.value;
        const keep = termSel.value;

        termSel.innerHTML = y
            ? '<option value="">Select Term</option>'
            : '<option value="">Select Year First</option>';

        if (!y) return;

        allTerms.forEach(function (opt) {
            if (opt.getAttribute('data-year') === y) {
                const c = opt.cloneNode(true);
                if (c.value === keep) c.selected = true;
                termSel.appendChild(c);
            }
        });
    }

    yearSel.addEventListener('change', rebuildTerms);

    rebuildTerms();
})();
</script>