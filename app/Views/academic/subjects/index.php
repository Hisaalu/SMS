<!-- File: /app/Views/academic/subjects/index.php -->
<style>
    .subjects-page .actions-cell {
        white-space: nowrap;
        min-width: 110px;
    }
    .subjects-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.75rem;
        margin-left: 0.25rem;
    }
    .subjects-page .actions-cell .btn:first-child { margin-left: 0; }
</style>

<div class="container-fluid px-0 subjects-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Subjects</h4>
            <small class="text-muted">Manage your Subjects</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/subjects/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Subject
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
        <?php if (!empty($subjects)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th class="d-none d-md-table-cell">Code</th>
                            <th class="d-none d-lg-table-cell">Grading System</th>
                            <th class="d-none d-lg-table-cell">Type</th>
                            <th class="d-none d-xl-table-cell">Department</th>
                            <th class="text-end actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <?php
                                $sId        = (int)($subject->id ?? 0);
                                $sName      = $subject->name ?? '-';
                                $sCode      = $subject->code ?? '-';
                                $typeRow    = $typeList[$subject->grading_subject_type_id] ?? null;
                                $typeName   = $typeRow['name'] ?? '—';
                                $isGraded   = $typeRow ? (bool)$typeRow['is_graded'] : true;
                                $typeBadge  = $isGraded ? 'primary' : 'secondary';
                                $systemName = $systemList[$subject->grading_system_id] ?? '—';
                                $deptName   = $deptList[$subject->department_id] ?? 'None';
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted d-md-none">
                                        <code><?= htmlspecialchars($sCode, ENT_QUOTES, 'UTF-8') ?></code>
                                        · <?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <code><?= htmlspecialchars($sCode, ENT_QUOTES, 'UTF-8') ?></code>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="badge bg-<?= $typeBadge ?>-subtle">
                                        <?= htmlspecialchars($typeName, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!$isGraded): ?>
                                            &middot; N/G
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    <?= htmlspecialchars($deptName, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-end actions-cell">
                                    <a href="<?= BASE_URL ?>/academic/subjects/<?= $sId ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteSubjectModal"
                                            data-id="<?= $sId ?>"
                                            data-name="<?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?>">
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
                <i class="fas fa-book fa-3x mb-3 opacity-25"></i>
                <h6 class="fw-bold">No subjects yet</h6>
                <p class="small mb-3">Add your first subject to start assigning teachers and recording marks.</p>
                <a href="<?= BASE_URL ?>/academic/subjects/create" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Subject
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-hidden="true">
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
                <p class="mb-1">Are you sure you want to delete <strong id="deleteSubjectName">this subject</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Subjects with marks, teaching assignments, or exam links cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSubjectBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Subject
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteSubjectModal');
    const nameEl    = document.getElementById('deleteSubjectName');
    const confirmEl = document.getElementById('confirmDeleteSubjectBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this subject';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        const url  = '<?= BASE_URL ?>/academic/subjects/' + currentId + '/delete';
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

    function showErrorToast(message) {
        if (window.NexaToast && typeof window.NexaToast.error === 'function') {
            window.NexaToast.error(message);
            return;
        }
        alert(message);
    }
})();
</script>