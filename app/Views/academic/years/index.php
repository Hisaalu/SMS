<!-- File: /app/Views/academic/years/index.php -->
<style>
    .years-page .current-banner {
        background: linear-gradient(135deg, var(--accent-color), color-mix(in srgb, var(--accent-color) 70%, #000));
        color: #fff;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        box-shadow: 0 10px 25px -5px rgba(var(--accent-rgb), 0.35);
    }
    .years-page .current-banner .label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        opacity: 0.85;
        margin-bottom: 0.15rem;
    }
    .years-page .current-banner .value {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
    }
    .years-page .current-banner .meta {
        font-size: 0.82rem;
        opacity: 0.85;
    }
    .years-page .year-row.current {
        background: rgba(var(--accent-rgb), 0.05);
    }
    .years-page .year-row.current td:first-child {
        border-left: 3px solid var(--accent-color);
    }
    .years-page .actions-cell {
        white-space: nowrap;
        min-width: 140px;
    }
    .years-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.75rem;
        margin-left: 0.25rem;
    }
    .years-page .actions-cell .btn:first-child { margin-left: 0; }
    @media (max-width: 767.98px) {
        .years-page .current-banner { padding: 1rem; }
        .years-page .current-banner .value { font-size: 1rem; }
    }
</style>

<div class="container-fluid px-0 years-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Academic Years</h4>
            <small class="text-muted">Manage Academic Years</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/years/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Year
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

    <div class="card p-0 overflow-hidden">
        <?php if (!empty($years)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="d-none d-md-table-cell">Duration</th>
                            <th class="d-none d-lg-table-cell">Terms</th>
                            <th>Status</th>
                            <th class="text-end actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $year): ?>
                            <?php
                                $yId        = (int)($year['id'] ?? 0);
                                $yName      = $year['name'] ?? '';
                                $isCurrent  = !empty($year['is_current']);
                                $termsCount = (int)($year['terms_count'] ?? 0);
                            ?>
                            <tr class="year-row <?= $isCurrent ? 'current' : '' ?>">
                                <td>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($yName) ?>
                                        <?php if ($isCurrent): ?>
                                            <span class="badge bg-primary ms-1">Current</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="small text-muted d-md-none">
                                        <?= date('M d, Y', strtotime($year['start_date'])) ?>
                                        &rarr;
                                        <?= date('M d, Y', strtotime($year['end_date'])) ?>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell small">
                                    <?= date('M d, Y', strtotime($year['start_date'])) ?>
                                    &rarr;
                                    <?= date('M d, Y', strtotime($year['end_date'])) ?>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <a href="<?= BASE_URL ?>/academic/terms?year=<?= $yId ?>"
                                       class="badge bg-info-subtle text-decoration-none">
                                        <?= $termsCount ?> term<?= $termsCount === 1 ? '' : 's' ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (($year['status'] ?? '') === 'active'): ?>
                                        <span class="badge bg-success-subtle">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle"><?= htmlspecialchars($year['status'] ?? '-') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end actions-cell">
                                    <a href="<?= BASE_URL ?>/academic/terms?year=<?= $yId ?>"
                                       class="btn btn-sm btn-outline-info" title="Manage terms">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/academic/years/<?= $yId ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteYearModal"
                                            data-id="<?= $yId ?>"
                                            data-name="<?= htmlspecialchars($yName, ENT_QUOTES, 'UTF-8') ?>">
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
                <h6 class="fw-bold">No academic years yet</h6>
                <p class="small mb-3">Create your first academic year to start enrolling students and taking attendance.</p>
                <a href="<?= BASE_URL ?>/academic/years/create" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Year
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteYearModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Academic Year</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteYearName">this academic year</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Academic years with linked terms or enrollments cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteYearBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Year
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteYearModal');
    const nameEl    = document.getElementById('deleteYearName');
    const confirmEl = document.getElementById('confirmDeleteYearBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this academic year';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        const url  = '<?= BASE_URL ?>/academic/years/' + currentId;
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
                : 'Could not delete this academic year (HTTP ' + res.status + ').';
            showErrorToast(message);
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Year';
        })
        .catch(function () {
            showErrorToast('Network error. Please check your connection and try again.');
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Year';
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