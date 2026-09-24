<!-- File: /app/Views/examinations/grading/divisions/index.php -->
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Divisions</h4>
            <small class="text-muted">
                <i class="fas fa-layer-group me-1"></i> Configure division ranges based on aggregate scores
            </small>
        </div>
        <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Grading Systems
        </a>
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
        <!-- Add Division Form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add New Division
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/grading/divisions">
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Division Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Division 1" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. D1" required>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Min Aggregate <span class="text-danger">*</span></label>
                                <input type="number" name="min_aggregate" class="form-control form-control-sm" placeholder="4" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Max Aggregate <span class="text-danger">*</span></label>
                                <input type="number" name="max_aggregate" class="form-control form-control-sm" placeholder="12" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm" placeholder="e.g. Excellent">
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
                            <i class="fas fa-save me-1"></i> Create Division
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Divisions List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-layer-group me-2"></i>Configured Divisions (<?= count($divisions) ?>)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Division</th>
                                    <th>Aggregate Range</th>
                                    <th>Description</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3" width="130">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($divisions)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                            <div>No divisions configured yet.</div>
                                            <div class="small">Add your first division using the form on the left.</div>
                                        </td>
                                    </tr>
                                <?php else: foreach ($divisions as $d): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-primary fs-6 px-3 py-2"><?= htmlspecialchars($d['code']) ?></span>
                                            <div class="small fw-semibold mt-1"><?= htmlspecialchars($d['name']) ?></div>
                                        </td>
                                        <td>
                                            <strong><?= (int)$d['min_aggregate'] ?></strong>
                                            <span class="text-muted">—</span>
                                            <strong><?= (int)$d['max_aggregate'] ?></strong>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($d['description'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?php if ($d['status'] === 'active'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary edit-division-btn"
                                                    data-id="<?= $d['id'] ?>"
                                                    data-name="<?= htmlspecialchars($d['name'], ENT_QUOTES) ?>"
                                                    data-code="<?= htmlspecialchars($d['code'], ENT_QUOTES) ?>"
                                                    data-min="<?= (int)$d['min_aggregate'] ?>"
                                                    data-max="<?= (int)$d['max_aggregate'] ?>"
                                                    data-description="<?= htmlspecialchars($d['description'] ?? '', ENT_QUOTES) ?>"
                                                    data-order="<?= (int)$d['display_order'] ?>"
                                                    data-status="<?= htmlspecialchars($d['status'], ENT_QUOTES) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editDivisionModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteDivision(<?= $d['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Division Modal -->
<div class="modal fade" id="editDivisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editDivisionForm" method="POST">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-edit me-1"></i> Edit Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Name</label>
                            <input type="text" name="name" id="editDivName" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Code</label>
                            <input type="text" name="code" id="editDivCode" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Min Aggregate</label>
                            <input type="number" name="min_aggregate" id="editDivMin" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Max Aggregate</label>
                            <input type="number" name="max_aggregate" id="editDivMax" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" name="description" id="editDivDesc" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Display Order</label>
                            <input type="number" name="display_order" id="editDivOrder" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" id="editDivStatus" class="form-select form-select-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-division-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editDivName').value   = this.dataset.name;
        document.getElementById('editDivCode').value   = this.dataset.code;
        document.getElementById('editDivMin').value    = this.dataset.min;
        document.getElementById('editDivMax').value    = this.dataset.max;
        document.getElementById('editDivDesc').value   = this.dataset.description || '';
        document.getElementById('editDivOrder').value  = this.dataset.order;
        document.getElementById('editDivStatus').value = this.dataset.status;
        document.getElementById('editDivisionForm').action = '<?= BASE_URL ?>/grading/divisions/' + this.dataset.id;
    });
});

function deleteDivision(id) {
    if (!confirm('Delete this division?')) return;
    fetch('<?= BASE_URL ?>/grading/divisions/' + id, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => d.success ? location.reload() : alert(d.error || 'Failed'))
    .catch(() => alert('An error occurred'));
}
</script>