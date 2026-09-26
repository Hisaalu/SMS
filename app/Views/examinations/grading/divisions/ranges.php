<!-- File: /app/Views/examinations/grading/divisions/ranges.php -->
<style>
    .ranges-page .actions-cell {
        white-space: nowrap;
        min-width: 100px;
    }
    .ranges-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.78rem;
        margin-left: 0.25rem;
    }
    .ranges-page .actions-cell .btn:first-child { margin-left: 0; }

    .ranges-page .range-code-badge {
        font-size: 0.95rem;
        padding: 0.35rem 0.75rem;
        letter-spacing: 0.02em;
    }
    .ranges-page .range-name {
        font-weight: 600;
        color: var(--text-color);
        font-size: 0.85rem;
        margin-top: 0.35rem;
        overflow-wrap: anywhere;
    }
    .ranges-page .range-desc {
        color: var(--text-muted);
        font-size: 0.8rem;
        overflow-wrap: anywhere;
    }

    @media (max-width: 767.98px) {
        .ranges-page .range-row {
            display: block;
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 0.95rem;
        }
        .ranges-page .range-row:last-child {
            border-bottom: 0;
        }
        .ranges-page .range-row td {
            display: block;
            border: 0;
            padding: 0.15rem 0;
        }
        .ranges-page .range-row td.actions-cell {
            padding-top: 0.6rem;
        }
        .ranges-page .range-row td.actions-cell .btn {
            --bs-btn-padding-y: 0.35rem;
            --bs-btn-padding-x: 0.7rem;
            --bs-btn-font-size: 0.8rem;
            margin-left: 0;
            margin-right: 0.35rem;
        }
    }
</style>

<div class="container-fluid px-0 ranges-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Aggregate Ranges</h4>
            <small class="text-muted">
                <i class="fas fa-layer-group me-1"></i> <?= htmlspecialchars($scheme['name']) ?>
                <span class="mx-2">·</span>
                <i class="fas fa-sliders-h me-1"></i> <?= htmlspecialchars($scheme['system_name']) ?>
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/grading/divisions/<?= (int)$scheme['id'] ?>/edit" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit me-1"></i> Edit Scheme
            </a>
            <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
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

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-plus-circle me-2"></i>Add Range
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/grading/divisions/<?= (int)$scheme['id'] ?>/ranges">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Division 1" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. D1" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Min <span class="text-danger">*</span></label>
                                <input type="number" name="min_aggregate" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Max <span class="text-danger">*</span></label>
                                <input type="number" name="max_aggregate" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm">
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Order</label>
                                <input type="number" name="display_order" class="form-control form-control-sm" value="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                            <i class="fas fa-save me-1"></i> Add Range
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card p-0 overflow-hidden">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-list-ol me-2"></i>Ranges in this Scheme
                    </h6>
                    <span class="badge bg-secondary-subtle"><?= count($ranges) ?> total</span>
                </div>

                <?php if (empty($ranges)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                        <h6 class="fw-bold">No ranges yet</h6>
                        <p class="small mb-0">Add your first range using the form on the left.</p>
                    </div>
                <?php else: ?>

                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Division</th>
                                    <th class="text-center">Range</th>
                                    <th>Description</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3 actions-cell">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ranges as $r): ?>
                                    <?php
                                        $rId     = (int)$r['id'];
                                        $rName   = $r['name'] ?? '';
                                        $rCode   = $r['code'] ?? '';
                                        $rMin    = (int)($r['min_aggregate'] ?? 0);
                                        $rMax    = (int)($r['max_aggregate'] ?? 0);
                                        $rDesc   = $r['description'] ?? '';
                                        $rOrder  = (int)($r['display_order'] ?? 0);
                                        $rStatus = $r['status'] ?? 'inactive';
                                    ?>
                                    <tr class="range-row">
                                        <td class="ps-3">
                                            <span class="badge bg-primary range-code-badge"><?= htmlspecialchars($rCode) ?></span>
                                            <div class="range-name"><?= htmlspecialchars($rName) ?></div>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= $rMin ?></strong>
                                            <span class="text-muted mx-1">—</span>
                                            <strong><?= $rMax ?></strong>
                                        </td>
                                        <td class="range-desc"><?= htmlspecialchars($rDesc !== '' ? $rDesc : '—') ?></td>
                                        <td class="text-center">
                                            <?php if ($rStatus === 'active'): ?>
                                                <span class="badge bg-success-subtle">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3 actions-cell">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary edit-range-btn"
                                                    data-id="<?= $rId ?>"
                                                    data-name="<?= htmlspecialchars($rName, ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($rCode, ENT_QUOTES) ?>"
                                                    data-min="<?= $rMin ?>"
                                                    data-max="<?= $rMax ?>"
                                                    data-description="<?= htmlspecialchars($rDesc, ENT_QUOTES) ?>"
                                                    data-order="<?= $rOrder ?>"
                                                    data-status="<?= htmlspecialchars($rStatus, ENT_QUOTES) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editRangeModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteRangeModal"
                                                    data-id="<?= $rId ?>"
                                                    data-name="<?= htmlspecialchars($rCode . ' – ' . $rName, ENT_QUOTES) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-md-none">
                        <?php foreach ($ranges as $r): ?>
                            <?php
                                $rId     = (int)$r['id'];
                                $rName   = $r['name'] ?? '';
                                $rCode   = $r['code'] ?? '';
                                $rMin    = (int)($r['min_aggregate'] ?? 0);
                                $rMax    = (int)($r['max_aggregate'] ?? 0);
                                $rDesc   = $r['description'] ?? '';
                                $rOrder  = (int)($r['display_order'] ?? 0);
                                $rStatus = $r['status'] ?? 'inactive';
                            ?>
                            <div class="range-row">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="flex-grow-1 min-width-0">
                                        <span class="badge bg-primary range-code-badge"><?= htmlspecialchars($rCode) ?></span>
                                        <div class="range-name"><?= htmlspecialchars($rName) ?></div>
                                    </div>
                                    <?php if ($rStatus === 'active'): ?>
                                        <span class="badge bg-success-subtle">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle">Inactive</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <span class="small fw-semibold">
                                        <i class="fas fa-arrows-alt-h text-muted me-1"></i>
                                        <?= $rMin ?> – <?= $rMax ?>
                                    </span>
                                    <?php if ($rDesc !== ''): ?>
                                        <span class="small text-muted">· <?= htmlspecialchars($rDesc) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary edit-range-btn"
                                            data-id="<?= $rId ?>"
                                            data-name="<?= htmlspecialchars($rName, ENT_QUOTES) ?>"
                                            data-code="<?= htmlspecialchars($rCode, ENT_QUOTES) ?>"
                                            data-min="<?= $rMin ?>"
                                            data-max="<?= $rMax ?>"
                                            data-description="<?= htmlspecialchars($rDesc, ENT_QUOTES) ?>"
                                            data-order="<?= $rOrder ?>"
                                            data-status="<?= htmlspecialchars($rStatus, ENT_QUOTES) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editRangeModal">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteRangeModal"
                                            data-id="<?= $rId ?>"
                                            data-name="<?= htmlspecialchars($rCode . ' – ' . $rName, ENT_QUOTES) ?>">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editRangeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editRangeForm" method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Range</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Name</label>
                            <input type="text" name="name" id="editName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Code</label>
                            <input type="text" name="code" id="editCode" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Min Aggregate</label>
                            <input type="number" name="min_aggregate" id="editMin" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Max Aggregate</label>
                            <input type="number" name="max_aggregate" id="editMax" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" id="editDesc" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Display Order</label>
                            <input type="number" name="display_order" id="editOrder" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" id="editStatus" class="form-select form-select-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteRangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Range</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong id="deleteRangeName">this range</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Results that previously fell into this range will no longer be assigned a division.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteRangeBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    document.querySelectorAll('.edit-range-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editName').value        = this.dataset.name        || '';
            document.getElementById('editCode').value        = this.dataset.code        || '';
            document.getElementById('editMin').value         = this.dataset.min         || '';
            document.getElementById('editMax').value         = this.dataset.max         || '';
            document.getElementById('editDesc').value        = this.dataset.description || '';
            document.getElementById('editOrder').value       = this.dataset.order       || 0;
            document.getElementById('editStatus').value      = this.dataset.status      || 'active';

            document.getElementById('editRangeForm').action =
                '<?= BASE_URL ?>/grading/divisions/ranges/' + this.dataset.id + '/update';
        });
    });

    const deleteModalEl = document.getElementById('deleteRangeModal');
    const deleteNameEl  = document.getElementById('deleteRangeName');
    const deleteBtnEl   = document.getElementById('confirmDeleteRangeBtn');
    let   deleteRangeId = 0;

    deleteModalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteRangeId = parseInt(trigger.getAttribute('data-id'), 10) || 0;
        const name = trigger.getAttribute('data-name') || 'this range';
        deleteNameEl.textContent = name;
    });

    deleteBtnEl.addEventListener('click', function () {
        if (!deleteRangeId) return;

        deleteBtnEl.disabled = true;
        deleteBtnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/grading/divisions/ranges/' + deleteRangeId, {
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
                : 'Failed to delete range (HTTP ' + res.status + ').';
            showErrorToast(message);
            deleteBtnEl.disabled = false;
            deleteBtnEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
        })
        .catch(function () {
            showErrorToast('An error occurred. Please try again.');
            deleteBtnEl.disabled = false;
            deleteBtnEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete';
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