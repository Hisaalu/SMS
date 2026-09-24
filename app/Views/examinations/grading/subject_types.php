<!-- File: /app/Views/examinations/grading/subject_types.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
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
        <div class="d-flex gap-2">
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
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-4">
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
                                <label class="form-label small fw-semibold">Display Order</label>
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

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-tags me-2"></i>Configured Types
                    </h6>
                    <span class="badge bg-secondary-subtle"><?= count($types) ?> total</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($types)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            <div>No subject types yet.</div>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Type</th>
                                    <th>Code</th>
                                    <th class="text-center">Graded</th>
                                    <th class="text-center">Subsidiary</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3" width="130">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($types as $t): ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold"><?= htmlspecialchars($t['name']) ?></td>
                                        <td><code><?= htmlspecialchars($t['code']) ?></code></td>
                                        <td class="text-center">
                                            <?php if (!empty($t['is_graded'])): ?>
                                                <span class="badge bg-primary-subtle">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($t['is_subsidiary'])): ?>
                                                <span class="badge bg-info-subtle">
                                                    <i class="fas fa-star me-1"></i>
                                                    <?= (int)($t['subsidiary_score'] ?? 1) ?> pt
                                                    <span class="text-muted ms-1">@ <?= (int)($t['subsidiary_pass_mark'] ?? 0) ?>+</span>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($t['status'] === 'active'): ?>
                                                <span class="badge bg-success-subtle">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-secondary edit-type-btn"
                                                    data-id="<?= (int)$t['id'] ?>"
                                                    data-name="<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($t['code'], ENT_QUOTES) ?>"
                                                    data-graded="<?= !empty($t['is_graded']) ? '1' : '0' ?>"
                                                    data-subsidiary="<?= !empty($t['is_subsidiary']) ? '1' : '0' ?>"
                                                    data-pass-mark="<?= (int)($t['subsidiary_pass_mark'] ?? 40) ?>"
                                                    data-fixed-score="<?= (int)($t['subsidiary_score'] ?? 1) ?>"
                                                    data-order="<?= (int)$t['display_order'] ?>"
                                                    data-status="<?= htmlspecialchars($t['status'], ENT_QUOTES) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editTypeModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                    onclick="deleteType(<?= (int)$t['id'] ?>)">
                                                <i class="fas fa-trash" style="color: var(--danger-color);"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-book me-2"></i>Subjects on this System
                    </h6>
                    <span class="badge bg-secondary-subtle"><?= count($subjects) ?> total</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($subjects)): ?>
                        <div class="text-center py-4 text-muted small">
                            No subjects are currently assigned to this grading system.
                        </div>
                    <?php else: ?>
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Subject</th>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th class="text-end pe-3" width="80"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $s): ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold"><?= htmlspecialchars($s['name']) ?></td>
                                        <td><code><?= htmlspecialchars($s['code']) ?></code></td>
                                        <td>
                                            <?php if (!empty($s['type_name'])): ?>
                                                <span class="badge bg-primary-subtle"><?= htmlspecialchars($s['type_name']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">No type assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="<?= BASE_URL ?>/academic/subjects/<?= (int)$s['id'] ?>/edit"
                                               class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTypeForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject Type</h5>
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

<script>
(function () {
    const toggle = (checkbox, target) => {
        const el = document.getElementById(target);
        if (!el) return;
        const sync = () => el.style.display = checkbox.checked ? 'block' : 'none';
        checkbox.addEventListener('change', sync);
        sync();
    };

    toggle(document.getElementById('isSubsidiary'),      'subsidiaryFields');
    toggle(document.getElementById('editTypeSubsidiary'), 'editSubsidiaryFields');

    document.querySelectorAll('.edit-type-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('editTypeName').value       = this.dataset.name;
            document.getElementById('editTypeCode').value       = this.dataset.code;
            document.getElementById('editTypeOrder').value      = this.dataset.order;
            document.getElementById('editTypeStatus').value     = this.dataset.status;
            document.getElementById('editTypeGraded').checked   = this.dataset.graded === '1';
            document.getElementById('editTypeSubsidiary').checked = this.dataset.subsidiary === '1';
            document.getElementById('editPassMark').value       = this.dataset.passMark;
            document.getElementById('editFixedScore').value     = this.dataset.fixedScore;

            document.getElementById('editTypeSubsidiary')
                .dispatchEvent(new Event('change'));

            document.getElementById('editTypeForm').action =
                '<?= BASE_URL ?>/grading/systems/subject-types/' + this.dataset.id + '/update';
        });
    });
})();

function deleteType(id) {
    if (!confirm('Delete this subject type?')) return;
    fetch('<?= BASE_URL ?>/grading/systems/subject-types/' + id, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => d.success ? location.reload() : alert(d.error || 'Failed'))
    .catch(() => alert('An error occurred'));
}
</script>