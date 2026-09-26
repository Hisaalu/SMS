<!-- File: /app/Views/examinations/grading/rules.php -->
<style>
    .rules-page .actions-cell {
        white-space: nowrap;
        min-width: 100px;
    }
    .rules-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.78rem;
        margin-left: 0.25rem;
    }
    .rules-page .actions-cell .btn:first-child { margin-left: 0; }

    .rules-page .grade-badge {
        font-size: 0.95rem;
        padding: 0.35rem 0.75rem;
        letter-spacing: 0.02em;
    }
    .rules-page .rule-desc {
        color: var(--text-muted);
        font-size: 0.82rem;
        overflow-wrap: anywhere;
    }

    @media (max-width: 767.98px) {
        .rules-page .rule-row {
            display: block;
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 0.95rem;
        }
        .rules-page .rule-row:last-child { border-bottom: 0; }
        .rules-page .rule-row td {
            display: block;
            border: 0;
            padding: 0.15rem 0;
        }
        .rules-page .rule-row td.actions-cell {
            padding-top: 0.6rem;
        }
        .rules-page .rule-row td.actions-cell .btn {
            --bs-btn-padding-y: 0.35rem;
            --bs-btn-padding-x: 0.7rem;
            --bs-btn-font-size: 0.8rem;
            margin-left: 0;
            margin-right: 0.35rem;
        }
    }
</style>

<div class="container-fluid px-0 rules-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Grading Rules</h4>
            <small class="text-muted">
                <i class="fas fa-sliders-h me-1"></i> <?= htmlspecialchars($system['name']) ?>
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/grading/divisions" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-layer-group me-1"></i> Divisions
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

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                <i class="fas fa-plus-circle me-2"></i>Add New Grading Rule
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/grading/systems/<?= $system['id'] ?>/rules">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold">Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="grade" placeholder="A" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold">Min Mark <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="min_mark" step="0.01" placeholder="80" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold">Max Mark <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="max_mark" step="0.01" placeholder="100" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-semibold">Score</label>
                        <input type="number" class="form-control form-control-sm" name="score" step="0.01" value="0" placeholder="e.g. 1">
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-semibold">Description</label>
                        <input type="text" class="form-control form-control-sm" name="description" placeholder="Excellent">
                    </div>
                    <div class="col-6 col-md-1">
                        <div class="form-check mt-md-4">
                            <input class="form-check-input" type="checkbox" name="pass" id="pass" value="1" checked>
                            <label class="form-check-label small" for="pass">Pass</label>
                        </div>
                    </div>
                    <div class="col-6 col-md-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color: var(--accent-color);">
                <i class="fas fa-list-ol me-2"></i>Grade Boundaries
            </h6>
            <span class="badge bg-secondary-subtle"><?= count($rules) ?> total</span>
        </div>

        <?php if (empty($rules)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                <h6 class="fw-bold">No grading rules yet</h6>
                <p class="small mb-0">Add your first rule using the form above.</p>
            </div>
        <?php else: ?>

            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" width="80">Grade</th>
                            <th>Mark Range</th>
                            <th class="text-center">Score</th>
                            <th>Description</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-3 actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $rule): ?>
                            <?php
                                $rId     = (int)$rule['id'];
                                $rGrade  = $rule['grade'] ?? '';
                                $rMin    = (float)$rule['min_mark'];
                                $rMax    = (float)$rule['max_mark'];
                                $rScore  = (float)($rule['score'] ?? 0);
                                $rDesc   = $rule['description'] ?? '';
                                $rPass   = !empty($rule['pass']);
                            ?>
                            <tr class="rule-row">
                                <td class="ps-3">
                                    <span class="badge bg-primary grade-badge"><?= htmlspecialchars($rGrade) ?></span>
                                </td>
                                <td>
                                    <strong><?= $rMin ?></strong>
                                    <span class="text-muted mx-1">—</span>
                                    <strong><?= $rMax ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle"><?= $rScore ?></span>
                                </td>
                                <td class="rule-desc"><?= htmlspecialchars($rDesc !== '' ? $rDesc : '—') ?></td>
                                <td class="text-center">
                                    <?php if ($rPass): ?>
                                        <span class="badge bg-success-subtle">
                                            <i class="fas fa-check me-1"></i>Pass
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle">
                                            <i class="fas fa-times me-1"></i>Fail
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 actions-cell">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary edit-rule-btn"
                                            data-id="<?= $rId ?>"
                                            data-grade="<?= htmlspecialchars($rGrade, ENT_QUOTES) ?>"
                                            data-min="<?= $rMin ?>"
                                            data-max="<?= $rMax ?>"
                                            data-score="<?= $rScore ?>"
                                            data-description="<?= htmlspecialchars($rDesc, ENT_QUOTES) ?>"
                                            data-pass="<?= $rPass ? '1' : '0' ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editRuleModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteRuleModal"
                                            data-id="<?= $rId ?>"
                                            data-name="<?= htmlspecialchars($rGrade . ' (' . $rMin . '–' . $rMax . ')', ENT_QUOTES) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-md-none">
                <?php foreach ($rules as $rule): ?>
                    <?php
                        $rId     = (int)$rule['id'];
                        $rGrade  = $rule['grade'] ?? '';
                        $rMin    = (float)$rule['min_mark'];
                        $rMax    = (float)$rule['max_mark'];
                        $rScore  = (float)($rule['score'] ?? 0);
                        $rDesc   = $rule['description'] ?? '';
                        $rPass   = !empty($rule['pass']);
                    ?>
                    <div class="rule-row">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="flex-grow-1 min-width-0">
                                <span class="badge bg-primary grade-badge"><?= htmlspecialchars($rGrade) ?></span>
                                <?php if ($rDesc !== ''): ?>
                                    <div class="rule-desc mt-2"><?= htmlspecialchars($rDesc) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($rPass): ?>
                                <span class="badge bg-success-subtle"><i class="fas fa-check me-1"></i>Pass</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle"><i class="fas fa-times me-1"></i>Fail</span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2 small">
                            <span><i class="fas fa-arrows-alt-h text-muted me-1"></i>
                                <strong><?= $rMin ?></strong> – <strong><?= $rMax ?></strong>
                            </span>
                            <span class="text-muted">·</span>
                            <span>Score: <span class="badge bg-info-subtle"><?= $rScore ?></span></span>
                        </div>

                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary edit-rule-btn"
                                    data-id="<?= $rId ?>"
                                    data-grade="<?= htmlspecialchars($rGrade, ENT_QUOTES) ?>"
                                    data-min="<?= $rMin ?>"
                                    data-max="<?= $rMax ?>"
                                    data-score="<?= $rScore ?>"
                                    data-description="<?= htmlspecialchars($rDesc, ENT_QUOTES) ?>"
                                    data-pass="<?= $rPass ? '1' : '0' ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editRuleModal">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteRuleModal"
                                    data-id="<?= $rId ?>"
                                    data-name="<?= htmlspecialchars($rGrade . ' (' . $rMin . '–' . $rMax . ')', ENT_QUOTES) ?>">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="editRuleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editRuleForm" method="POST">
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
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

<div class="modal fade" id="deleteRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Grading Rule</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete the rule
                    <strong id="deleteRuleName">this rule</strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Marks previously graded by this rule will be rescored on the next results calculation.
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
    document.querySelectorAll('.edit-rule-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('editGrade').value       = this.dataset.grade || '';
            document.getElementById('editMinMark').value     = this.dataset.min || '';
            document.getElementById('editMaxMark').value     = this.dataset.max || '';
            document.getElementById('editScore').value       = this.dataset.score || 0;
            document.getElementById('editDescription').value = this.dataset.description || '';
            document.getElementById('editPass').checked      = this.dataset.pass === '1';

            document.getElementById('editRuleForm').action =
                '<?= BASE_URL ?>/grading/systems/rules/' + this.dataset.id;
        });
    });

    const deleteModalEl = document.getElementById('deleteRuleModal');
    const deleteNameEl  = document.getElementById('deleteRuleName');
    const deleteBtnEl   = document.getElementById('confirmDeleteRuleBtn');
    let   deleteRuleId  = 0;

    deleteModalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        deleteRuleId = parseInt(trigger.getAttribute('data-id'), 10) || 0;
        const name = trigger.getAttribute('data-name') || 'this rule';
        deleteNameEl.textContent = name;
    });

    deleteBtnEl.addEventListener('click', function () {
        if (!deleteRuleId) return;

        deleteBtnEl.disabled = true;
        deleteBtnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/grading/systems/rules/' + deleteRuleId, {
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
                : 'Failed to delete rule (HTTP ' + res.status + ').';
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