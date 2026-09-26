<!-- File: /app/Views/examinations/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Examinations</h4>
            <small class="text-muted">Manage School Exams</small>
        </div>
        <a href="<?= BASE_URL ?>/examinations/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Exam
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Year</th>
                            <th>Term</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($examinations) || count($examinations) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No examinations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($examinations as $exam): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($exam['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($exam['code'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($exam['assessment_type_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($exam['academic_year_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($exam['term_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($exam['status'] === 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php elseif ($exam['status'] === 'completed'): ?>
                                            <span class="badge bg-info">Completed</span>
                                        <?php elseif ($exam['status'] === 'archived'): ?>
                                            <span class="badge bg-secondary">Archived</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/examinations/<?= $exam['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit Examination">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/results/examination/<?= $exam['id'] ?>" class="btn btn-sm btn-outline-info" title="View Results">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Examination"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteExamModal"
                                                data-exam-id="<?= $exam['id'] ?>"
                                                data-exam-name="<?= htmlspecialchars($exam['name'], ENT_QUOTES, 'UTF-8') ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteExamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Examination</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong id="deleteExamName">this examination</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Examinations with existing marks cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteExamBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl     = document.getElementById('deleteExamModal');
    const nameEl      = document.getElementById('deleteExamName');
    const confirmEl   = document.getElementById('confirmDeleteExamBtn');

    let deleteExamId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteExamId = parseInt(trigger.getAttribute('data-exam-id'), 10) || 0;
        const name = trigger.getAttribute('data-exam-name') || 'this examination';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!deleteExamId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/examinations/' + deleteExamId, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': '<?= htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES) ?>'
            }
        })
        .then(function (r) {
            return r.text().then(function (text) {
                try { return { ok: r.ok, status: r.status, json: JSON.parse(text) }; }
                catch (e) { return { ok: r.ok, status: r.status, json: null }; }
            });
        })
        .then(function (res) {
            if (res.json && res.json.success) {
                window.location.reload();
                return;
            }
            const message = (res.json && res.json.error)
                ? res.json.error
                : 'Failed to delete examination (HTTP ' + res.status + ').';
            showErrorToast(message);
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
        })
        .catch(function () {
            showErrorToast('An error occurred. Please try again.');
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
        });
    });

    function showErrorToast(message) {
        if (window.NexaToast && typeof window.NexaToast.error === 'function') {
            window.NexaToast.error(message);
            return;
        }
        alert(message);
    }
})();
</script>