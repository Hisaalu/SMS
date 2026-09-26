<!-- File: /app/Views/examinations/grading/divisions/index.php -->
<style>
    .div-schemes-page .actions-cell {
        white-space: nowrap;
        min-width: 140px;
    }
    .div-schemes-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.5rem;
        --bs-btn-font-size: 0.78rem;
        margin-left: 0.25rem;
    }
    .div-schemes-page .actions-cell .btn:first-child { margin-left: 0; }

    .div-schemes-page .scheme-name {
        font-weight: 700;
        color: var(--text-color);
        overflow-wrap: anywhere;
    }
    .div-schemes-page .scheme-desc {
        color: var(--text-muted);
        font-size: 0.78rem;
        margin-top: 0.15rem;
        overflow-wrap: anywhere;
    }

    @media (max-width: 767.98px) {
        .div-schemes-page .scheme-row {
            display: block;
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 0.95rem;
        }
        .div-schemes-page .scheme-row:last-child {
            border-bottom: 0;
        }
        .div-schemes-page .scheme-row td {
            display: block;
            border: 0;
            padding: 0.15rem 0;
        }
        .div-schemes-page .scheme-row td[data-label]::before {
            content: attr(data-label) ": ";
            color: var(--text-muted);
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-right: 0.35rem;
        }
        .div-schemes-page .scheme-row td.actions-cell {
            padding-top: 0.55rem;
        }
        .div-schemes-page .scheme-row td.actions-cell::before {
            content: none;
        }
        .div-schemes-page .scheme-row td.actions-cell .btn {
            --bs-btn-padding-y: 0.35rem;
            --bs-btn-padding-x: 0.7rem;
            --bs-btn-font-size: 0.8rem;
            margin-left: 0;
            margin-right: 0.35rem;
        }
    }
</style>

<div class="container-fluid px-0 div-schemes-page">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Division Schemes</h4>
            <small class="text-muted">
                <i class="fas fa-layer-group me-1"></i>
                Groupings of your Ranges
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Grade Systems
            </a>
            <a href="<?= BASE_URL ?>/grading/divisions/create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Division Scheme
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-0 overflow-hidden">
        <?php if (empty($schemes)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-layer-group fa-3x mb-3 d-block opacity-25"></i>
                <h6 class="fw-bold">No division schemes yet</h6>
                <p class="small mb-3">
                    Create one to group division ranges under a grading system.
                </p>
                <a href="<?= BASE_URL ?>/grading/divisions/create" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Scheme
                </a>
            </div>
        <?php else: ?>

            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Scheme</th>
                            <th>Grading System</th>
                            <th>Applies To</th>
                            <th class="text-center">Ranges</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3 actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schemes as $s): ?>
                            <?php
                                $schemeId   = (int)$s['id'];
                                $schemeName = $s['name'] ?? '-';
                                $schemeDesc = $s['description'] ?? '';
                                $systemName = $s['system_name'] ?? '-';
                                $className  = $s['class_name'] ?? '';
                                $rangeCount = (int)($s['range_count'] ?? 0);
                                $status     = $s['status'] ?? 'inactive';
                            ?>
                            <tr class="scheme-row">
                                <td class="ps-3">
                                    <div class="scheme-name"><?= htmlspecialchars($schemeName, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if ($schemeDesc !== ''): ?>
                                        <div class="scheme-desc"><?= htmlspecialchars($schemeDesc, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle"><?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <?php if ($className !== ''): ?>
                                        <span class="badge bg-secondary-subtle"><?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">All classes</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle"><?= $rangeCount ?> range<?= $rangeCount === 1 ? '' : 's' ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($status === 'active'): ?>
                                        <span class="badge bg-success-subtle">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 actions-cell">
                                    <a href="<?= BASE_URL ?>/grading/divisions/<?= $schemeId ?>/ranges"
                                       class="btn btn-sm btn-outline-primary" title="Manage ranges">
                                        <i class="fas fa-list"></i> Ranges
                                    </a>
                                    <a href="<?= BASE_URL ?>/grading/divisions/<?= $schemeId ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteSchemeModal"
                                            data-id="<?= $schemeId ?>"
                                            data-name="<?= htmlspecialchars($schemeName, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-md-none">
                <?php foreach ($schemes as $s): ?>
                    <?php
                        $schemeId   = (int)$s['id'];
                        $schemeName = $s['name'] ?? '-';
                        $schemeDesc = $s['description'] ?? '';
                        $systemName = $s['system_name'] ?? '-';
                        $className  = $s['class_name'] ?? '';
                        $rangeCount = (int)($s['range_count'] ?? 0);
                        $status     = $s['status'] ?? 'inactive';
                    ?>
                    <div class="scheme-row">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="flex-grow-1 min-width-0">
                                <div class="scheme-name"><?= htmlspecialchars($schemeName, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($schemeDesc !== ''): ?>
                                    <div class="scheme-desc"><?= htmlspecialchars($schemeDesc, ENT_QUOTES, 'UTF-8') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end">
                                <?php if ($status === 'active'): ?>
                                    <span class="badge bg-success-subtle">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle">Inactive</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge bg-primary-subtle"><?= htmlspecialchars($systemName, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($className !== ''): ?>
                                <span class="badge bg-secondary-subtle"><?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php else: ?>
                                <span class="text-muted small">All classes</span>
                            <?php endif; ?>
                            <span class="badge bg-secondary-subtle"><?= $rangeCount ?> range<?= $rangeCount === 1 ? '' : 's' ?></span>
                        </div>

                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="<?= BASE_URL ?>/grading/divisions/<?= $schemeId ?>/ranges"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-list me-1"></i> Ranges
                            </a>
                            <a href="<?= BASE_URL ?>/grading/divisions/<?= $schemeId ?>/edit"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteSchemeModal"
                                    data-id="<?= $schemeId ?>"
                                    data-name="<?= htmlspecialchars($schemeName, ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteSchemeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Division Scheme</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong id="deleteSchemeName">this division scheme</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This will also delete all ranges belonging to this scheme. This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSchemeBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteSchemeModal');
    const nameEl    = document.getElementById('deleteSchemeName');
    const confirmEl = document.getElementById('confirmDeleteSchemeBtn');

    let deleteSchemeId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteSchemeId = parseInt(trigger.getAttribute('data-id'), 10) || 0;
        const name = trigger.getAttribute('data-name') || 'this division scheme';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!deleteSchemeId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/grading/divisions/' + deleteSchemeId, {
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
                : 'Failed to delete scheme (HTTP ' + res.status + ').';
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