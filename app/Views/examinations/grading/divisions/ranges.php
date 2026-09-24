<!-- File: /app/Views/examinations/grading/divisions/ranges.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Aggregate Ranges</h4>
            <small class="text-muted">
                <i class="fas fa-layer-group me-1"></i> <?= htmlspecialchars($scheme['name']) ?>
                <span class="mx-2">·</span>
                <i class="fas fa-sliders-h me-1"></i> <?= htmlspecialchars($scheme['system_name']) ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/grading/divisions/<?= (int)$scheme['id'] ?>/edit" class="btn btn-sm btn-secondary">
                <i class="fas fa-edit me-1"></i> Edit Scheme
            </a>
            <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-sm btn-secondary">
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
                                <label class="form-label small fw-semibold">Min Aggregate <span class="text-danger">*</span></label>
                                <input type="number" name="min_aggregate" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Max Aggregate <span class="text-danger">*</span></label>
                                <input type="number" name="max_aggregate" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm">
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
                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                            <i class="fas fa-save me-1"></i> Add Range
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                        <i class="fas fa-list-ol me-2"></i>Ranges in this Scheme
                    </h6>
                    <span class="badge bg-secondary-subtle"><?= count($ranges) ?> total</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($ranges)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            <div>No ranges in this scheme yet.</div>
                            <div class="small">Add your first range using the form on the left.</div>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Division</th>
                                    <th>Aggregate Range</th>
                                    <th>Description</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3" width="130">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ranges as $r): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-primary fs-6 px-3 py-2"><?= htmlspecialchars($r['code']) ?></span>
                                            <div class="small fw-semibold mt-1"><?= htmlspecialchars($r['name']) ?></div>
                                        </td>
                                        <td>
                                            <strong><?= (int)$r['min_aggregate'] ?></strong>
                                            <span class="text-muted">—</span>
                                            <strong><?= (int)$r['max_aggregate'] ?></strong>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($r['description'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?php if ($r['status'] === 'active'): ?>
                                                <span class="badge bg-success-subtle">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button"
                                                    class="btn btn-sm btn-secondary edit-range-btn"
                                                    data-id="<?= (int)$r['id'] ?>"
                                                    data-name="<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($r['code'], ENT_QUOTES) ?>"
                                                    data-min="<?= (int)$r['min_aggregate'] ?>"
                                                    data-max="<?= (int)$r['max_aggregate'] ?>"
                                                    data-description="<?= htmlspecialchars($r['description'] ?? '', ENT_QUOTES) ?>"
                                                    data-order="<?= (int)$r['display_order'] ?>"
                                                    data-status="<?= htmlspecialchars($r['status'], ENT_QUOTES) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editRangeModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                    onclick="deleteRange(<?= (int)$r['id'] ?>)">
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
        </div>
    </div>
</div>

<div class="modal fade" id="editRangeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRangeForm" method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Range</h5>
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

<script>
document.querySelectorAll('.edit-range-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editName').value   = this.dataset.name;
        document.getElementById('editCode').value   = this.dataset.code;
        document.getElementById('editMin').value    = this.dataset.min;
        document.getElementById('editMax').value    = this.dataset.max;
        document.getElementById('editDesc').value   = this.dataset.description || '';
        document.getElementById('editOrder').value  = this.dataset.order;
        document.getElementById('editStatus').value = this.dataset.status;
        document.getElementById('editRangeForm').action =
            '<?= BASE_URL ?>/grading/divisions/ranges/' + this.dataset.id + '/update';
    });
});

function deleteRange(id) {
    if (!confirm('Delete this range?')) return;
    fetch('<?= BASE_URL ?>/grading/divisions/ranges/' + id, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => d.success ? location.reload() : alert(d.error || 'Failed'))
    .catch(() => alert('An error occurred'));
}
</script>