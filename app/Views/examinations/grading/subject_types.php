<!-- File: /app/Views/examinations/grading/subject_types.php -->
<style>
    .subject-types-page .actions-cell {
        white-space: nowrap;
        min-width: 100px;
    }
    .subject-types-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.78rem;
        margin-left: 0.25rem;
    }
    .subject-types-page .actions-cell .btn:first-child { margin-left: 0; }

    .subject-types-page .type-name {
        font-weight: 600;
        color: var(--text-color);
        overflow-wrap: anywhere;
    }
    .subject-types-page code {
        font-size: 0.78rem;
    }

    @media (max-width: 767.98px) {
        .subject-types-page .type-row {
            display: block;
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 0.95rem;
        }
        .subject-types-page .type-row:last-child { border-bottom: 0; }
        .subject-types-page .type-row td {
            display: block;
            border: 0;
            padding: 0.15rem 0;
        }
        .subject-types-page .type-row td.actions-cell {
            padding-top: 0.6rem;
        }
        .subject-types-page .type-row td.actions-cell .btn {
            --bs-btn-padding-y: 0.35rem;
            --bs-btn-padding-x: 0.7rem;
            --bs-btn-font-size: 0.8rem;
            margin-left: 0;
            margin-right: 0.35rem;
        }
    }
</style>

<div class="container-fluid px-0 subject-types-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Subject Types</h4>
            <small class="text-muted">
                <i class="fas fa-sliders-h me-1"></i> <?= htmlspecialchars($system['name']) ?>
                <?php if (!empty($system['class_name'])): ?>
                    <span class="mx-2">·</span>
                    <i class="fas fa-chalkboard me-1"></i> <?= htmlspecialchars($system['class_name']) ?>
                <?php endif; ?>
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/grading/systems/<?= (int)$system['id'] ?>/rules"
               class="btn btn-sm btn-secondary">
                <i class="fas fa-list me-1"></i> Grading Rules
            </a>
            <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
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

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-plus-circle me-2"></i>Add Subject Type
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/grading/systems/<?= (int)$system['id'] ?>/subject-types">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm"
                                   placeholder="e.g. Principal, Subsidiary" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control form-control-sm"
                                   placeholder="e.g. PRINCIPAL" required>
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

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_graded"
                                   value="1" id="isGraded" checked>
                            <label class="form-check-label small" for="isGraded">
                                Counts toward totals (graded)
                            </label>
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_subsidiary"
                                   value="1" id="isSubsidiary">
                            <label class="form-check-label small" for="isSubsidiary">
                                Subsidiary — fixed points when passed
                            </label>
                        </div>

                        <div id="subsidiaryFields" class="border rounded p-2 mb-2" style="display:none;">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Pass Mark</label>
                                    <input type="number" name="subsidiary_pass_mark"
                                           class="form-control form-control-sm" value="40">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Fixed Points</label>
                                    <input type="number" name="subsidiary_score"
                                           class="form-control form-control-sm" value="1">
                                </div>
                            </div>
                            <div class="small text-muted mt-1">
                                A mark at or above the pass mark yields exactly the fixed points.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-save me-1"></i> Add Type
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card mb-3 p-0 overflow-hidden">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-tags me-2"></i>Configured Types
                    </h6>
                    <span class="badge bg-secondary-subtle"><?= count($types) ?> total</span>
                </div>

                <?php if (empty($types)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                        <h6 class="fw-bold">No subject types yet</h6>
                        <p class="small mb-0">Add your first subject type using the form on the left.</p>
                    </div>
                <?php else: ?>

                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Type</th>
                                    <th>Code</th>
                                    <th class="text-center">Graded</th>
                                    <th class="text-center">Subsidiary</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3 actions-cell">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($types as $t): ?>
                                    <?php
                                        $tId     = (int)$t['id'];
                                        $tName   = $t['name'] ?? '';
                                        $tCode   = $t['code'] ?? '';
                                        $tGraded = !empty($t['is_graded']);
                                        $tSub    = !empty($t['is_subsidiary']);
                                        $tPassM  = (int)($t['subsidiary_pass_mark'] ?? 40);
                                        $tFixed  = (int)($t['subsidiary_score'] ?? 1);
                                        $tOrder  = (int)($t['display_order'] ?? 0);
                                        $tStatus = $t['status'] ?? 'active';
                                    ?>
                                    <tr class="type-row">
                                        <td class="ps-3">
                                            <div class="type-name"><?= htmlspecialchars($tName) ?></div>
                                        </td>
                                        <td><code><?= htmlspecialchars($tCode) ?></code></td>
                                        <td class="text-center">
                                            <?php if ($tGraded): ?>
                                                <span class="badge bg-primary-subtle">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($tSub): ?>
                                                <span class="badge bg-info-subtle">
                                                    <i class="fas fa-star me-1"></i>
                                                    <?= $tFixed ?> pt
                                                    <span class="text-muted ms-1">@ <?= $tPassM ?>+</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($tStatus === 'active'): ?>
                                                <span class="badge bg-success-subtle">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3 actions-cell">
                                            <button type="button" class="btn btn-sm btn-outline-primary edit-type-btn"
                                                    data-id="<?= $tId ?>"
                                                    data-name="<?= htmlspecialchars($tName, ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($tCode, ENT_QUOTES) ?>"
                                                    data-graded="<?= $tGraded ? '1' : '0' ?>"
                                                    data-subsidiary="<?= $tSub ? '1' : '0' ?>"
                                                    data-pass-mark="<?= $tPassM ?>"
                                                    data-fixed-score="<?= $tFixed ?>"
                                                    data-order="<?= $tOrder ?>"
                                                    data-status="<?= htmlspecialchars($tStatus, ENT_QUOTES) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editTypeModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteTypeModal"
                                                    data-id="<?= $tId ?>"
                                                    data-name="<?= htmlspecialchars($tName, ENT_QUOTES) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-md-none">
                        <?php foreach ($types as $t): ?>
                            <?php
                                $tId     = (int)$t['id'];
                                $tName   = $t['name'] ?? '';
                                $tCode   = $t['code'] ?? '';
                                $tGraded = !empty($t['is_graded']);
                                $tSub    = !empty($t['is_subsidiary']);
                                $tPassM  = (int)($t['subsidiary_pass_mark'] ?? 40);
                                $tFixed  = (int)($t['subsidiary_score'] ?? 1);
                                $tOrder  = (int)($t['display_order'] ?? 0);
                                $tStatus = $t['status'] ?? 'active';
                            ?>
                            <div class="type-row">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="type-name"><?= htmlspecialchars($tName) ?></div>
                                        <code class="text-muted"><?= htmlspecialchars($tCode) ?></code>
                                    </div>
                                    <?php if ($tStatus === 'active'): ?>
                                        <span class="badge bg-success-subtle">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle">Inactive</span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                    <?php if ($tGraded): ?>
                                        <span class="badge bg-primary-subtle">Graded</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle">Not graded</span>
                                    <?php endif; ?>
                                    <?php if ($tSub): ?>
                                        <span class="badge bg-info-subtle">
                                            <i class="fas fa-star me-1"></i>
                                            Subsidiary — <?= $tFixed ?> pt @ <?= $tPassM ?>+
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-type-btn"
                                            data-id="<?= $tId ?>"
                                            data-name="<?= htmlspecialchars($tName, ENT_QUOTES) ?>"
                                            data-code="<?= htmlspecialchars($tCode, ENT_QUOTES) ?>"
                                            data-graded="<?= $tGraded ? '1' : '0' ?>"
                                            data-subsidiary="<?= $tSub ? '1' : '0' ?>"
                                            data-pass-mark="<?= $tPassM ?>"
                                            data-fixed-score="<?= $tFixed ?>"
                                            data-order="<?= $tOrder ?>"
                                            data-status="<?= htmlspecialchars($tStatus, ENT_QUOTES) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTypeModal">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteTypeModal"
                                            data-id="<?= $tId ?>"
                                            data-name="<?= htmlspecialchars($tName, ENT_QUOTES) ?>">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            </div>

            <div class="card p-0 overflow-hidden">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-book me-2"></i>Subjects on this System
                    </h6>
                    <span class="badge bg-secondary-subtle"><?= count($subjects) ?> total</span>
                </div>

                <?php if (empty($subjects)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-book fa-3x mb-3 d-block opacity-25"></i>
                        <p class="small mb-0">No subjects are currently assigned to this grading system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Subject</th>
                                    <th class="d-none d-md-table-cell">Code</th>
                                    <th>Type</th>
                                    <th class="text-end pe-3" width="80"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $s): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="type-name"><?= htmlspecialchars($s['name']) ?></div>
                                            <code class="text-muted d-md-none"><?= htmlspecialchars($s['code']) ?></code>
                                        </td>
                                        <td class="d-none d-md-table-cell"><code><?= htmlspecialchars($s['code']) ?></code></td>
                                        <td>
                                            <?php if (!empty($s['type_name'])): ?>
                                                <span class="badge bg-primary-subtle"><?= htmlspecialchars($s['type_name']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">No type assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= BASE_URL ?>/academic/subjects/<?= (int)$s['id'] ?>/edit"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editTypeForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Subject Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Name</label>
                            <input type="text" name="name" id="editTypeName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Code</label>
                            <input type="text" name="code" id="editTypeCode" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Display Order</label>
                            <input type="number" name="display_order" id="editTypeOrder" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" id="editTypeStatus" class="form-select form-select-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_graded"
                                       value="1" id="editTypeGraded">
                                <label class="form-check-label small" for="editTypeGraded">
                                    Counts toward totals (graded)
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_subsidiary"
                                       value="1" id="editTypeSubsidiary">
                                <label class="form-check-label small" for="editTypeSubsidiary">
                                    Subsidiary — fixed points when passed
                                </label>
                            </div>
                        </div>
                        <div class="col-12" id="editSubsidiaryFields" style="display:none;">
                            <div class="border rounded p-2">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Pass Mark</label>
                                        <input type="number" name="subsidiary_pass_mark" id="editPassMark"
                                               class="form-control form-control-sm">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-semibold">Fixed Points</label>
                                        <input type="number" name="subsidiary_score" id="editFixedScore"
                                               class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
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

<div class="modal fade" id="deleteTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Subject Type</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong id="deleteTypeName">this subject type</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Subjects currently assigned to this type will need a replacement.
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
    const toggle = (checkbox, target) => {
        const el = document.getElementById(target);
        if (!el || !checkbox) return;
        const sync = () => el.style.display = checkbox.checked ? 'block' : 'none';
        checkbox.addEventListener('change', sync);
        sync();
    };

    toggle(document.getElementById('isSubsidiary'),      'subsidiaryFields');
    toggle(document.getElementById('editTypeSubsidiary'), 'editSubsidiaryFields');

    document.querySelectorAll('.edit-type-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editTypeName').value         = this.dataset.name || '';
            document.getElementById('editTypeCode').value         = this.dataset.code || '';
            document.getElementById('editTypeOrder').value        = this.dataset.order || 0;
            document.getElementById('editTypeStatus').value       = this.dataset.status || 'active';
            document.getElementById('editTypeGraded').checked     = this.dataset.graded === '1';
            document.getElementById('editTypeSubsidiary').checked = this.dataset.subsidiary === '1';
            document.getElementById('editPassMark').value         = this.dataset.passMark || 40;
            document.getElementById('editFixedScore').value       = this.dataset.fixedScore || 1;

            document.getElementById('editTypeSubsidiary').dispatchEvent(new Event('change'));

            document.getElementById('editTypeForm').action =
                '<?= BASE_URL ?>/grading/systems/subject-types/' + this.dataset.id + '/update';
        });
    });

    const deleteModalEl = document.getElementById('deleteTypeModal');
    const deleteNameEl  = document.getElementById('deleteTypeName');
    const deleteBtnEl   = document.getElementById('confirmDeleteTypeBtn');
    let   deleteTypeId  = 0;

    deleteModalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteTypeId = parseInt(trigger.getAttribute('data-id'), 10) || 0;
        const name = trigger.getAttribute('data-name') || 'this subject type';
        deleteNameEl.textContent = name;
    });

    deleteBtnEl.addEventListener('click', function () {
        if (!deleteTypeId) return;

        deleteBtnEl.disabled = true;
        deleteBtnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/grading/systems/subject-types/' + deleteTypeId, {
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
                : 'Failed to delete subject type (HTTP ' + res.status + ').';
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