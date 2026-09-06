<!-- File: /app/Views/examinations/grading/rules.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Grading Rules</h4>
            <small class="text-muted"><?= htmlspecialchars($system['name']) ?> - Configure grade boundaries</small>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/grading/systems" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Systems
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <!-- Add Rule Form -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0">Add Grading Rule</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/grading/systems/<?= $system['id'] ?>/rules" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small">Grade</label>
                    <input type="text" class="form-control form-control-sm" name="grade" placeholder="A" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Min Mark</label>
                    <input type="number" class="form-control form-control-sm" name="min_mark" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Max Mark</label>
                    <input type="number" class="form-control form-control-sm" name="max_mark" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Points</label>
                    <input type="number" class="form-control form-control-sm" name="points" step="0.01" value="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Description</label>
                    <input type="text" class="form-control form-control-sm" name="description" placeholder="Excellent">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check me-2">
                        <input class="form-check-input" type="checkbox" name="pass" id="pass" value="1" checked>
                        <label class="form-check-label small" for="pass">Pass</label>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Rule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rules Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Grade</th>
                            <th>Min Mark</th>
                            <th>Max Mark</th>
                            <th>Points</th>
                            <th>Description</th>
                            <th>Pass</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rules) || count($rules) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No grading rules found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($rule['grade']) ?></strong></td>
                                    <td><?= $rule['min_mark'] ?></td>
                                    <td><?= $rule['max_mark'] ?></td>
                                    <td><?= $rule['points'] ?? 0 ?></td>
                                    <td><?= htmlspecialchars($rule['description'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($rule['pass']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button onclick="editRule(<?= $rule['id'] ?>, '<?= htmlspecialchars($rule['grade']) ?>', <?= $rule['min_mark'] ?>, <?= $rule['max_mark'] ?>, <?= $rule['points'] ?? 0 ?>, '<?= htmlspecialchars($rule['description'] ?? '') ?>', <?= $rule['pass'] ?>)" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteRule(<?= $rule['id'] ?>)" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
            <div class="modal-header">
                <h5 class="modal-title">Edit Grading Rule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editRuleForm" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Grade</label>
                            <input type="text" class="form-control form-control-sm" name="grade" id="editGrade" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Points</label>
                            <input type="number" class="form-control form-control-sm" name="points" id="editPoints" step="0.01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Min Mark</label>
                            <input type="number" class="form-control form-control-sm" name="min_mark" id="editMinMark" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Max Mark</label>
                            <input type="number" class="form-control form-control-sm" name="max_mark" id="editMaxMark" step="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Description</label>
                            <input type="text" class="form-control form-control-sm" name="description" id="editDescription">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pass" id="editPass" value="1">
                                <label class="form-check-label small" for="editPass">Pass</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('editRuleForm').submit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
function editRule(id, grade, minMark, maxMark, points, description, pass) {
    document.getElementById('editGrade').value = grade;
    document.getElementById('editMinMark').value = minMark;
    document.getElementById('editMaxMark').value = maxMark;
    document.getElementById('editPoints').value = points || 0;
    document.getElementById('editDescription').value = description || '';
    document.getElementById('editPass').checked = pass === 1;
    document.getElementById('editRuleForm').action = '<?= BASE_URL ?>/grading/systems/rules/' + id;
    
    new bootstrap.Modal(document.getElementById('editRuleModal')).show();
}

function deleteRule(id) {
    if (confirm('Are you sure you want to delete this grading rule?')) {
        fetch('<?= BASE_URL ?>/grading/systems/rules/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || 'Failed to delete rule');
            }
        })
        .catch(error => {
            alert('An error occurred');
        });
    }
}
</script>