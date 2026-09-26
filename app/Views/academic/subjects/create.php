<!-- File: /app/Views/academic/subjects/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Create Subject</h4>
            <small class="text-muted">Add a new subject to your school</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/subjects" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 p-md-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/subjects" id="subjectForm">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                           placeholder="e.g. Mathematics, Music" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control"
                           placeholder="e.g. MATH, MUS" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Grading System <span class="text-danger">*</span></label>
                    <select name="grading_system_id" id="gradingSystemSelect" class="form-select" required>
                        <option value="">— Choose a grading system —</option>
                        <?php foreach ($systems as $sys): ?>
                            <option value="<?= (int)$sys['id'] ?>"
                                <?= ((int)$sys['id'] === (int)$selectedSystem) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sys['name']) ?>
                                <?= !empty($sys['is_default']) ? ' (default)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">The subject inherits its division scheme from this system.</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Subject Type <span class="text-danger">*</span></label>
                    <select name="grading_subject_type_id" id="subjectTypeSelect" class="form-select" required>
                        <option value="">— Choose a type —</option>
                        <?php foreach ($subjectTypes as $t): ?>
                            <option value="<?= (int)$t['id'] ?>">
                                <?= htmlspecialchars($t['name']) ?>
                                <?= empty($t['is_graded']) ? ' (not graded)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Types depend on the selected grading system.</div>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">— Optional Department —</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept->id ?>"><?= htmlspecialchars($dept->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Subject
                    </button>
                    <a href="<?= BASE_URL ?>/academic/subjects" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const systemSelect = document.getElementById('gradingSystemSelect');
    const typeSelect   = document.getElementById('subjectTypeSelect');
    const url          = '<?= BASE_URL ?>';

    async function loadTypes(systemId, preselect) {
        typeSelect.innerHTML = '<option value="">Loading…</option>';

        if (!systemId) {
            typeSelect.innerHTML = '<option value="">— Choose a type —</option>';
            return;
        }

        try {
            const res  = await fetch(url + '/api/grading/systems/' + systemId + '/subject-types');
            const list = await res.json();

            typeSelect.innerHTML = '<option value="">— Choose a type —</option>';

            if (!Array.isArray(list) || list.length === 0) {
                typeSelect.innerHTML = '<option value="">No types configured for this system</option>';
                return;
            }

            list.forEach(function (t) {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name + (t.is_graded ? '' : ' (not graded)');
                if (preselect && String(preselect) === String(t.id)) opt.selected = true;
                typeSelect.appendChild(opt);
            });
        } catch (e) {
            typeSelect.innerHTML = '<option value="">Failed to load types</option>';
        }
    }

    systemSelect.addEventListener('change', function () {
        loadTypes(this.value, null);
    });

    if (systemSelect.value) {
        loadTypes(systemSelect.value, null);
    }
})();
</script>