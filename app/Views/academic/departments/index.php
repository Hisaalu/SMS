<!-- File: /app/Views/academic/departments/index.php -->
<style>
    .departments-page .actions-cell {
        white-space: nowrap;
        min-width: 110px;
    }
    .departments-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.75rem;
        margin-left: 0.25rem;
    }
    .departments-page .actions-cell .btn:first-child { margin-left: 0; }
</style>

<div class="container-fluid px-0 departments-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Departments</h4>
            <small class="text-muted">Manage school departments</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/departments/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Dept
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-0 overflow-hidden">
        <?php if (!empty($departments)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th class="d-none d-md-table-cell">Code</th>
                            <th class="d-none d-lg-table-cell text-center">Subjects</th>
                            <th class="text-end actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $dept): ?>
                            <?php
                                $dId            = (int)($dept['id'] ?? 0);
                                $dName          = $dept['name'] ?? '-';
                                $dCode          = $dept['code'] ?? '-';
                                $subjectsCount  = (int)($dept['subjects_count'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($dName, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted d-md-none">
                                        <?= htmlspecialchars($dCode, ENT_QUOTES, 'UTF-8') ?>
                                        · <?= $subjectsCount ?> subject<?= $subjectsCount === 1 ? '' : 's' ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?= htmlspecialchars($dCode, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="d-none d-lg-table-cell text-center">
                                    <span class="badge bg-primary-subtle"><?= $subjectsCount ?></span>
                                </td>
                                <td class="text-end actions-cell">
                                    <a href="<?= BASE_URL ?>/academic/departments/<?= $dId ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteDepartmentModal"
                                            data-id="<?= $dId ?>"
                                            data-name="<?= htmlspecialchars($dName, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-building fa-3x mb-3 opacity-25"></i>
                <h6 class="fw-bold">No departments yet</h6>
                <p class="small mb-3">Create your first department to organise subjects across the school.</p>
                <a href="<?= BASE_URL ?>/academic/departments/create" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Department
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Department</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteDepartmentName">this department</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Departments with assigned subjects cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteDepartmentBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Department
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteDepartmentModal');
    const nameEl    = document.getElementById('deleteDepartmentName');
    const confirmEl = document.getElementById('confirmDeleteDepartmentBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this department';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        const url  = '<?= BASE_URL ?>/academic/departments/' + currentId + '/delete';
        const body = new URLSearchParams();
        body.append('<?= CSRF_TOKEN_NAME ?>', '<?= htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES) ?>');

        fetch(url, {
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
                window.location.reload();
                return;
            }
            const message = (res.json && res.json.error)
                ? res.json.error
                : 'Could not delete this department (HTTP ' + res.status + ').';
            showErrorToast(message);
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Department';
        })
        .catch(function () {
            showErrorToast('Network error. Please check your connection and try again.');
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Department';
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