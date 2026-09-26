<!-- File: /app/Views/academic/terms/index.php -->
<style>
    .terms-page .filter-bar {
        background: var(--surface-color);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
    }
    .terms-page .term-row.current {
        background: rgba(var(--accent-rgb), 0.05);
    }
    .terms-page .term-row.current td:first-child {
        border-left: 3px solid var(--accent-color);
    }
    .terms-page .actions-cell {
        white-space: nowrap;
        min-width: 110px;
    }
    .terms-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.75rem;
        margin-left: 0.25rem;
    }
    .terms-page .actions-cell .btn:first-child { margin-left: 0; }
</style>

<div class="container-fluid px-0 terms-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Academic Terms</h4>
            <?php if ($selectedYear): ?>
                <small class="text-muted">
                    Terms for
                    <strong><?= htmlspecialchars($selectedYear['name']) ?></strong>
                    <?php if (!empty($selectedYear['is_current'])): ?>
                        <span class="badge bg-primary-subtle ms-1">Current Year</span>
                    <?php endif; ?>
                </small>
            <?php else: ?>
                <small class="text-muted">All academic years</small>
            <?php endif; ?>
        </div>
        <a href="<?= BASE_URL ?>/academic/terms/create<?= $selectedYear ? '?year=' . (int)$selectedYear['id'] : '' ?>"
           class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Term
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="filter-bar mb-3">
        <form method="GET" action="<?= BASE_URL ?>/academic/terms" class="row g-2 align-items-center">
            <div class="col-12 col-md-auto">
                <label for="year" class="form-label small fw-semibold mb-1 mb-md-0">
                    <i class="fas fa-filter me-1 text-muted"></i>Filter by Academic Year
                </label>
            </div>
            <div class="col-12 col-md">
                <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Academic Years</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= (int)$year['id'] ?>"
                                <?= ($selectedYear && (int)$selectedYear['id'] === (int)$year['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($year['name']) ?><?= !empty($year['is_current']) ? ' — Current' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selectedYear): ?>
                <div class="col-12 col-md-auto">
                    <a href="<?= BASE_URL ?>/academic/terms" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i>Clear Filter
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="card p-0 overflow-hidden">
        <?php if (!empty($terms)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th class="d-none d-md-table-cell">Academic Year</th>
                            <th class="d-none d-lg-table-cell">Duration</th>
                            <th class="text-center">Current</th>
                            <th class="text-end actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terms as $term): ?>
                            <?php
                                $tId       = (int)($term['id'] ?? 0);
                                $tName     = $term['name'] ?? '';
                                $isCurrent = !empty($term['is_current']);
                            ?>
                            <tr class="term-row <?= $isCurrent ? 'current' : '' ?>">
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($tName) ?></div>
                                    <div class="small text-muted">
                                        Term <?= (int)($term['term_number'] ?? 0) ?>
                                        <?php if (!$selectedYear): ?>
                                            · <span class="d-md-none"><?= htmlspecialchars($term['academic_year_name'] ?? '-') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?= htmlspecialchars($term['academic_year_name'] ?? '-') ?>
                                </td>
                                <td class="d-none d-lg-table-cell small">
                                    <?= !empty($term['start_date']) ? date('M d, Y', strtotime($term['start_date'])) : '-' ?>
                                    &rarr;
                                    <?= !empty($term['end_date']) ? date('M d, Y', strtotime($term['end_date'])) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($isCurrent): ?>
                                        <span class="badge bg-primary">Current</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end actions-cell">
                                    <a href="<?= BASE_URL ?>/academic/terms/<?= $tId ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteTermModal"
                                            data-id="<?= $tId ?>"
                                            data-name="<?= htmlspecialchars($tName, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-alt fa-3x mb-3 opacity-25"></i>
                <h6 class="fw-bold">No terms found</h6>
                <p class="small mb-3">
                    <?= $selectedYear
                        ? 'This academic year has no terms yet.'
                        : 'No terms have been created yet.' ?>
                </p>
                <a href="<?= BASE_URL ?>/academic/terms/create<?= $selectedYear ? '?year=' . (int)$selectedYear['id'] : '' ?>"
                   class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Term
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteTermModal" tabindex="-1" aria-hidden="true">
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
                <p class="mb-1">Are you sure you want to delete <strong id="deleteTermName">this term</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Terms with linked records cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteTermBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Term
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteTermModal');
    const nameEl    = document.getElementById('deleteTermName');
    const confirmEl = document.getElementById('confirmDeleteTermBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this term';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        const url  = '<?= BASE_URL ?>/academic/terms/' + currentId;
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
                window.location.reload();
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