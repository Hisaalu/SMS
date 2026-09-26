<!-- File: /app/Views/academic/terms/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Edit Term</h4>
            <small class="text-muted">
                Update the term's details
            </small>
        </div>
        <a href="<?= BASE_URL ?>/academic/terms?year=<?= (int)($term['academic_year_id'] ?? 0) ?>"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 p-md-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/terms/<?= (int)($term['id'] ?? 0) ?>">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label for="academic_year_id" class="form-label fw-semibold">
                        Academic Year <span class="text-danger">*</span>
                    </label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                        <option value="">— Select Academic Year —</option>
                        <?php foreach ($years as $year): ?>
                            <?php
                                $yId      = (int)($year['id'] ?? 0);
                                $yName    = $year['name'] ?? '';
                                $yCurrent = !empty($year['is_current']);
                                $selected = ((int)($term['academic_year_id'] ?? 0) === $yId);
                            ?>
                            <option value="<?= $yId ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($yName, ENT_QUOTES, 'UTF-8') ?><?= $yCurrent ? ' — Current' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="name" class="form-label fw-semibold">
                        Term Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control"
                           placeholder="e.g. Term 1"
                           value="<?= htmlspecialchars($term['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>

                <div class="col-12 col-md-4">
                    <label for="term_number" class="form-label fw-semibold">Term Sequence</label>
                    <input type="number"
                           name="term_number"
                           id="term_number"
                           class="form-control"
                           value="<?= (int)($term['term_number'] ?? 1) ?>"
                           min="1" max="4">
                </div>

                <div class="col-12 col-md-4">
                    <label for="start_date" class="form-label fw-semibold">
                        Start Date <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           class="form-control"
                           value="<?= htmlspecialchars($term['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>

                <div class="col-12 col-md-4">
                    <label for="end_date" class="form-label fw-semibold">
                        End Date <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           name="end_date"
                           id="end_date"
                           class="form-control"
                           value="<?= htmlspecialchars($term['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           required>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                               name="is_current"
                               id="is_current"
                               class="form-check-input"
                               value="1"
                               <?= !empty($term['is_current']) ? 'checked' : '' ?>>
                        <label for="is_current" class="form-check-label fw-semibold">
                            Set as Current Term
                        </label>
                    </div>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update
                    </button>
                    <a href="<?= BASE_URL ?>/academic/terms?year=<?= (int)($term['academic_year_id'] ?? 0) ?>"
                       class="btn btn-secondary">
                        Cancel
                    </a>

                    <button type="button"
                            class="btn btn-outline-danger ms-auto"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteTermEditModal">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="deleteTermEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Term</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">
                    Are you sure you want to delete
                    <strong><?= htmlspecialchars($term['name'] ?? 'this term', ENT_QUOTES, 'UTF-8') ?></strong>?
                </p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Terms with linked records cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEditTermBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Term
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const confirmEl = document.getElementById('confirmDeleteEditTermBtn');
    if (!confirmEl) return;

    confirmEl.addEventListener('click', function () {
        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        const url  = '<?= BASE_URL ?>/academic/terms/<?= (int)($term['id'] ?? 0) ?>';
        const body = new URLSearchParams();
        body.append('<?= CSRF_TOKEN_NAME ?>', '<?= htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES) ?>');

        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': '<?= htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES) ?>'
            },
            body: body.toString()
        })
        .then(function (r) {
            return r.text().then(function (text) {
                try { return { ok: r.ok, status: r.status, json: JSON.parse(text) }; }
                catch (e) { return { ok: r.ok, status: r.status, json: null }; }
            });
        })
        .then(function (res) {
            if (res.json && res.json.success) {
                window.location.href = '<?= BASE_URL ?>/academic/terms?year=<?= (int)($term['academic_year_id'] ?? 0) ?>';
                return;
            }
            const message = (res.json && res.json.error)
                ? res.json.error
                : 'Could not delete this term (HTTP ' + res.status + ').';
            showErrorToast(message);
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Term';
        })
        .catch(function () {
            showErrorToast('Network error. Please check your connection and try again.');
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Term';
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