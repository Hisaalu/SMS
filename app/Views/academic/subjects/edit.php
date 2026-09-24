<!-- File: /app/Views/academic/subjects/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Subject</h4>
        <a href="<?= BASE_URL ?>/academic/subjects" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/subjects/<?= (int)$subject->id ?>" id="subjectForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($subject->name) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control"
                           value="<?= htmlspecialchars($subject->code) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Grading System <span class="text-danger">*</span></label>
                    <select name="grading_system_id" id="gradingSystemSelect"
                            class="form-select" required>
                        <option value="">— Choose a grading system —</option>
                        <?php foreach ($systems as $sys): ?>
                            <option value="<?= (int)$sys['id'] ?>"
                                <?= ((int)$sys['id'] === (int)$selectedSystem) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sys['name']) ?>
                                <?= !empty($sys['is_default']) ? ' (default)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Subject Type <span class="text-danger">*</span></label>
                    <select name="grading_subject_type_id" id="subjectTypeSelect"
                            class="form-select" required>
                        <option value="">— Choose a type —</option>
                        <?php foreach ($subjectTypes as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"
                                <?= ((int)$t['id'] === (int)$subject->grading_subject_type_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['name']) ?>
                                <?= empty($t['is_graded']) ? ' (not graded)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- Optional Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept->id ?>"
                                <?= ((int)$subject->department_id === (int)$dept->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                    <a href="<?= BASE_URL ?>/academic/subjects" class="btn btn-secondary ms-2">Cancel</a>
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

    let currentTypeId = '<?= (int)$subject->grading_subject_type_id ?>';

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
        currentTypeId = null;
        loadTypes(this.value, null);
    });
})();
</script>