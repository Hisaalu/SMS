<!-- File: /app/Views/examinations/assessment/types.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Assessment Types</h4>
            <small class="text-muted">Configure Assessment Types</small>
        </div>
        <a href="<?= BASE_URL ?>/assessment/types/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Asse.. Type
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
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Max Marks</th>
                            <th>Weight (%)</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($types) || count($types) === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-tasks fa-2x mb-2 d-block"></i>
                                    No assessment types found.
                                    <br>
                                    <a href="<?= BASE_URL ?>/assessment/types/create" class="btn btn-sm btn-primary mt-2">Create Your First Assessment Type</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($types as $type): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($type['name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($type['code']) ?></code></td>
                                    <td><?= htmlspecialchars($type['description'] ?? '-') ?></td>
                                    <td><?= $type['max_marks'] ?? 100 ?></td>
                                    <td><?= $type['weight'] ?? 0 ?></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/assessment/types/<?= $type['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteTypeModal"
                                                data-type-id="<?= $type['id'] ?>"
                                                data-type-name="<?= htmlspecialchars($type['name'], ENT_QUOTES, 'UTF-8') ?>">
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

<div class="modal fade" id="deleteTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Assessment Type</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong id="deleteTypeName">this assessment type</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Assessment types with linked examinations cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTypeBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl    = document.getElementById('deleteTypeModal');
    const nameEl     = document.getElementById('deleteTypeName');
    const confirmEl  = document.getElementById('confirmDeleteTypeBtn');

    let deleteTypeId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteTypeId = parseInt(trigger.getAttribute('data-type-id'), 10) || 0;
        const name = trigger.getAttribute('data-type-name') || 'this assessment type';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!deleteTypeId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/assessment/types/' + deleteTypeId, {
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
                : 'Failed to delete assessment type (HTTP ' + res.status + ').';
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