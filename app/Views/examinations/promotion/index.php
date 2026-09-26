<!-- File: /app/Views/examinations/promotion/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Promotion Rules</h4>
            <small class="text-muted">Configure Promotion Rules</small>
        </div>
        <a href="<?= BASE_URL ?>/promotion/rules/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Rule
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Pass Mark</th>
                            <th>Min Subjects Passed</th>
                            <th>Require All Subjects</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rules) || count($rules) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                                    No promotion rules found.
                                    <br>
                                    <a href="<?= BASE_URL ?>/promotion/rules/create" class="btn btn-sm btn-primary mt-2">Create Your First Promotion Rule</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rules as $rule): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($rule['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($rule['description'] ?? '-') ?></td>
                                    <td><?= $rule['pass_mark'] ?>%</td>
                                    <td><?= $rule['min_subjects_passed'] > 0 ? $rule['min_subjects_passed'] : 'Not Required' ?></td>
                                    <td>
                                        <?php if ($rule['require_all_subjects']): ?>
                                            <span class="badge bg-info">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rule['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/promotion/rules/<?= $rule['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteRuleModal"
                                                data-rule-id="<?= $rule['id'] ?>"
                                                data-rule-name="<?= htmlspecialchars($rule['name'], ENT_QUOTES, 'UTF-8') ?>">
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

<div class="modal fade" id="deleteRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Promotion Rule</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong id="deleteRuleName">this promotion rule</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Promotion rules that are actively in use cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteRuleBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteRuleModal');
    const nameEl    = document.getElementById('deleteRuleName');
    const confirmEl = document.getElementById('confirmDeleteRuleBtn');

    let deleteRuleId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteRuleId = parseInt(trigger.getAttribute('data-rule-id'), 10) || 0;
        const name = trigger.getAttribute('data-rule-name') || 'this promotion rule';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!deleteRuleId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/promotion/rules/' + deleteRuleId, {
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
                : 'Failed to delete promotion rule (HTTP ' + res.status + ').';
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