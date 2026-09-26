<!-- File: /app/Views/academic/subjects/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Edit Subject</h4>
            <small class="text-muted">Update the subject's details</small>
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
        <form method="POST" action="<?= BASE_URL ?>/academic/subjects/<?= (int)$subject->id ?>" id="subjectForm">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($subject->name) ?>" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Subject Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control"
                           value="<?= htmlspecialchars($subject->code) ?>" required>
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
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Subject Type <span class="text-danger">*</span></label>
                    <select name="grading_subject_type_id" id="subjectTypeSelect" class="form-select" required>
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

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">— Optional Department —</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept->id ?>"
                                <?= ((int)$subject->department_id === (int)$dept->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="<?= BASE_URL ?>/academic/subjects" class="btn btn-secondary">Cancel</a>

                    <button type="button"
                            class="btn btn-outline-danger ms-auto"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteSubjectEditModal">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteSubjectEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Subject</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong><?= htmlspecialchars($subject->name ?? 'this subject', ENT_QUOTES, 'UTF-8') ?></strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Subjects with marks, teaching assignments, or exam links cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEditSubjectBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Subject
                </button>
            </div>
        </div>
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

    const confirmEl = document.getElementById('confirmDeleteEditSubjectBtn');
    if (confirmEl) {
        confirmEl.addEventListener('click', function () {
            confirmEl.disabled = true;
            confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

            const deleteUrl  = '<?= BASE_URL ?>/academic/subjects/<?= (int)$subject->id ?>/delete';
            const body = new URLSearchParams();
            body.append('<?= CSRF_TOKEN_NAME ?>', '<?= htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES) ?>');

            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            })
            .then(function (r) {
                return r.text().then(function (text) {
                    try { return { ok: r.ok, status: r.status, json: JSON.parse(text) }; }
                    catch (e) { return { ok: r.ok, status: r.status, json: null }; }
                });
            })
            .then(function (res) {
                if (res.json && res.json.success) {
                    window.location.href = '<?= BASE_URL ?>/academic/subjects';
                    return;
                }
                const message = (res.json && res.json.error)
                    ? res.json.error
                    : 'Could not delete this subject (HTTP ' + res.status + ').';
                showErrorToast(message);
                confirmEl.disabled = false;
                confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Subject';
            })
            .catch(function () {
                showErrorToast('Network error. Please check your connection and try again.');
                confirmEl.disabled = false;
                confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Subject';
            });
        });
    }

    function showErrorToast(message) {
        if (window.NexaToast && typeof window.NexaToast.error === 'function') {
            window.NexaToast.error(message);
            return;
        }
        alert(message);
    }
})();
</script>