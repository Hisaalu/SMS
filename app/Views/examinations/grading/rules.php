<!-- File: /app/Views/examinations/grading/rules.php -->
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Grading Rules</h4>
            <small class="text-muted">
                <i class="fas fa-sliders-h me-1"></i> <?= htmlspecialchars($system['name']) ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-layer-group me-1"></i> Divisions
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

    <!-- Add Rule Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 pt-3 pb-0">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New Grading Rule
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/grading/systems/<?= $system['id'] ?>/rules">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="grade" placeholder="A" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Min Mark <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="min_mark" step="0.01" placeholder="80" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Max Mark <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="max_mark" step="0.01" placeholder="100" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Score</label>
                        <input type="number" class="form-control form-control-sm" name="score" step="0.01" value="0" placeholder="e.g. 1">
                        <small class="text-muted">Numeric weight</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Description</label>
                        <input type="text" class="form-control form-control-sm" name="description" placeholder="Excellent">
                    </div>
                    <div class="col-md-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="pass" id="pass" value="1" checked>
                            <label class="form-check-label small" for="pass">Pass</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rules Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="fas fa-list-ol me-2"></i>Grade Boundaries (<?= count($rules) ?>)
            </h6>
            <small class="text-muted">Rules are sorted by min mark (highest first)</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" width="80">Grade</th>
                            <th>Mark Range</th>
                            <th>Score</th>
                            <th>Description</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3" width="130">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rules)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                    <div>No grading rules yet.</div>
                                    <div class="small">Add your first rule using the form above.</div>
                                </td>
                            </tr>
                        <?php else: foreach ($rules as $rule): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-primary fs-6 px-3 py-2"><?= htmlspecialchars($rule['grade']) ?></span>
                                </td>
                                <td>
                                    <strong><?= (float)$rule['min_mark'] ?></strong>
                                    <span class="text-muted">—</span>
                                    <strong><?= (float)$rule['max_mark'] ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <?= (float)($rule['score'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($rule['description'] ?? '-') ?></td>
                                <td class="text-center">
                                    <?php if ($rule['pass']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="fas fa-check me-1"></i>Pass
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="fas fa-times me-1"></i>Fail
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary edit-rule-btn"
                                            data-id="<?= $rule['id'] ?>"
                                            data-grade="<?= htmlspecialchars($rule['grade'], ENT_QUOTES) ?>"
                                            data-min="<?= (float)$rule['min_mark'] ?>"
                                            data-max="<?= (float)$rule['max_mark'] ?>"
                                            data-score="<?= (float)($rule['score'] ?? 0) ?>"
                                            data-description="<?= htmlspecialchars($rule['description'] ?? '', ENT_QUOTES) ?>"
                                            data-pass="<?= $rule['pass'] ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editRuleModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="deleteRule(<?= $rule['id'] ?>)">
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

<!-- Edit Rule Modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editRuleForm" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-1"></i> Edit Grading Rule
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Grade <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="grade" id="editGrade" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Score</label>
                            <input type="number" class="form-control form-control-sm" name="score" id="editScore" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Min Mark <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" name="min_mark" id="editMinMark" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Max Mark <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm" name="max_mark" id="editMaxMark" step="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Description</label>
                            <input type="text" class="form-control form-control-sm" name="description" id="editDescription">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pass" id="editPass" value="1">
                                <label class="form-check-label small" for="editPass">Pass</label>
                            </div>
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
document.querySelectorAll('.edit-rule-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('editGrade').value       = this.dataset.grade;
        document.getElementById('editMinMark').value     = this.dataset.min;
        document.getElementById('editMaxMark').value     = this.dataset.max;
        document.getElementById('editScore').value       = this.dataset.score || 0;
        document.getElementById('editDescription').value = this.dataset.description || '';
        document.getElementById('editPass').checked      = this.dataset.pass === '1';
        document.getElementById('editRuleForm').action   = '<?= BASE_URL ?>/grading/systems/rules/' + this.dataset.id;
    });
});

function deleteRule(id) {
    if (!confirm('Delete this grading rule?')) return;
    fetch('<?= BASE_URL ?>/grading/systems/rules/' + id, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => d.success ? location.reload() : alert(d.error || 'Failed'))
    .catch(() => alert('An error occurred'));
}
</script>