<!-- File: /app/Views/examinations/grading/index.php -->
<style>
    .grading-page .actions-cell {
        white-space: nowrap;
        min-width: 120px;
    }
    .grading-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.78rem;
        margin-left: 0.25rem;
    }
    .grading-page .actions-cell .btn:first-child { margin-left: 0; }

    .grading-page .grading-row td {
        vertical-align: middle;
    }
    .grading-page .grading-name {
        font-weight: 600;
        color: var(--text-color);
        overflow-wrap: anywhere;
    }
    .grading-page .grading-desc {
        color: var(--text-muted);
        font-size: 0.78rem;
        margin-top: 0.15rem;
        overflow-wrap: anywhere;
    }

    @media (max-width: 767.98px) {
        .grading-page .grading-row {
            display: block;
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 0.9rem;
        }
        .grading-page .grading-row td {
            display: block;
            border: 0;
            padding: 0.15rem 0;
        }
        .grading-page .grading-row td[data-label]::before {
            content: attr(data-label) ": ";
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-right: 0.35rem;
        }
        .grading-page .grading-row td.actions-cell {
            padding-top: 0.55rem;
        }
        .grading-page .grading-row td.actions-cell::before {
            content: none;
        }
    }
</style>

<div class="container-fluid px-0 grading-page">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Grading Systems</h4>
            <small class="text-muted">Configure grading systems per class or school-wide</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-sm btn-secondary">
                <i class="fas fa-layer-group me-1"></i> Divisions
            </a>
            <a href="<?= BASE_URL ?>/grading/systems/create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Grade System
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-0 overflow-hidden">
        <?php if (empty($systems)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-award fa-3x mb-3 opacity-25"></i>
                <h6 class="fw-bold">No grading systems yet</h6>
                <p class="small mb-3">Create your first grading system to define grading rules and subject types.</p>
                <a href="<?= BASE_URL ?>/grading/systems/create" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Grading System
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Name</th>
                            <th>Applies To</th>
                            <th class="d-none d-lg-table-cell">Academic Year</th>
                            <th class="text-center">Rules</th>
                            <th class="text-center">Subject Types</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th class="text-end pe-3 actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($systems as $s): ?>
                            <?php
                                $sId       = (int)$s['id'];
                                $sName     = $s['name'] ?? '-';
                                $sDesc     = $s['description'] ?? '';
                                $className = $s['class_name'] ?? '';
                                $yearName  = $s['academic_year_name'] ?? 'All Years';
                                $rules     = (int)($s['rule_count'] ?? 0);
                                $types     = (int)($s['type_count'] ?? 0);
                                $status    = $s['status'] ?? 'active';
                                $isDefault = !empty($s['is_default']);
                            ?>
                            <tr class="grading-row">
                                <td class="ps-3">
                                    <div class="grading-name"><?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if ($sDesc !== ''): ?>
                                        <div class="grading-desc"><?= htmlspecialchars($sDesc, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $systemClasses = is_array($s['classes'] ?? null) ? $s['classes'] : [];
                                    ?>
                                    <?php if (empty($systemClasses)): ?>
                                        <span class="badge bg-secondary-subtle">All Classes</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($systemClasses as $sc): ?>
                                                <span class="badge bg-primary-subtle"><?= htmlspecialchars($sc['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <?= htmlspecialchars($yearName, ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/grading/systems/<?= $sId ?>/rules"
                                       class="btn btn-sm btn-secondary" title="Manage grading rules">
                                        <i class="fas fa-list"></i> <?= $rules ?>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <a href="<?= BASE_URL ?>/grading/systems/<?= $sId ?>/subject-types"
                                       class="btn btn-sm btn-secondary" title="Manage subject types">
                                        <i class="fas fa-tags"></i> <?= $types ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $status === 'active' ? 'success-subtle' : 'secondary-subtle' ?>">
                                        <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($isDefault): ?>
                                        <span class="badge bg-primary-subtle">Default</span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 actions-cell">
                                    <a href="<?= BASE_URL ?>/grading/systems/<?= $sId ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteSystemModal"
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

            <div class="d-md-none">
                <?php foreach ($systems as $s): ?>
                    <?php
                        $sId       = (int)$s['id'];
                        $sName     = $s['name'] ?? '-';
                        $sDesc     = $s['description'] ?? '';
                        $className = $s['class_name'] ?? '';
                        $yearName  = $s['academic_year_name'] ?? 'All Years';
                        $rules     = (int)($s['rule_count'] ?? 0);
                        $types     = (int)($s['type_count'] ?? 0);
                        $status    = $s['status'] ?? 'active';
                        $isDefault = !empty($s['is_default']);
                    ?>
                    <div class="grading-row">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="flex-grow-1 min-width-0">
                                <div class="grading-name"><?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($sDesc !== ''): ?>
                                    <div class="grading-desc"><?= htmlspecialchars($sDesc, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-<?= $status === 'active' ? 'success-subtle' : 'secondary-subtle' ?>">
                                    <?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <?php if ($isDefault): ?>
                                    <span class="badge bg-primary-subtle ms-1">Default</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <?php
                                $systemClasses = is_array($s['classes'] ?? null) ? $s['classes'] : [];
                            ?>
                            <?php if (empty($systemClasses)): ?>
                                <span class="badge bg-secondary-subtle">All Classes</span>
                            <?php else: ?>
                                <?php foreach ($systemClasses as $sc): ?>
                                    <span class="badge bg-primary-subtle"><?= htmlspecialchars($sc['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <span class="text-muted small">
                                <i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($yearName, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <a href="<?= BASE_URL ?>/grading/systems/<?= $sId ?>/rules" class="btn btn-sm btn-secondary">
                                <i class="fas fa-list me-1"></i> <?= $rules ?> Rule<?= $rules === 1 ? '' : 's' ?>
                            </a>
                            <a href="<?= BASE_URL ?>/grading/systems/<?= $sId ?>/subject-types" class="btn btn-sm btn-secondary">
                                <i class="fas fa-tags me-1"></i> <?= $types ?> Type<?= $types === 1 ? '' : 's' ?>
                            </a>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/grading/systems/<?= $sId ?>/edit" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteSystemModal"
                                    data-id="<?= $sId ?>"
                                    data-name="<?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteSystemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Grading System</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong id="deleteSystemName">this grading system</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Grading systems that are in use by subjects or classes cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSystemBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteSystemModal');
    const nameEl    = document.getElementById('deleteSystemName');
    const confirmEl = document.getElementById('confirmDeleteSystemBtn');

    let deleteSystemId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteSystemId = parseInt(trigger.getAttribute('data-id'), 10) || 0;
        const name = trigger.getAttribute('data-name') || 'this grading system';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!deleteSystemId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/grading/systems/' + deleteSystemId, {
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
                : 'Failed to delete grading system (HTTP ' + res.status + ').';
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