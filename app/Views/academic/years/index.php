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
    .years-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.5rem;
        --bs-btn-font-size: 0.78rem;
    }
    @media (max-width: 767.98px) {
        .years-page .current-banner { padding: 1rem; }
        .years-page .current-banner .value { font-size: 1rem; }
        .years-page .actions-cell { display: flex; flex-wrap: wrap; gap: 0.35rem; }
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
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($years as $year): ?>
                            <?php
                                $isCurrent = !empty($year['is_current']);
                                $termsCount = (int)($year['terms_count'] ?? 0);
                            ?>
                            <tr class="year-row <?= $isCurrent ? 'current' : '' ?>">
                                <td>
                                    <div class="fw-semibold">
                                        <?= htmlspecialchars($year['name']) ?>
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
                                    <a href="<?= BASE_URL ?>/academic/terms?year=<?= (int)$year['id'] ?>"
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
                                    <a href="<?= BASE_URL ?>/academic/terms?year=<?= (int)$year['id'] ?>"
                                       class="btn btn-sm btn-outline-info" title="Manage terms">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                    <a href="<?= BASE_URL ?>/academic/years/<?= (int)$year['id'] ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            onclick="deleteYear(<?= (int)$year['id'] ?>)"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete">
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

<script>
function deleteYear(id) {
    if (!confirm('Are you sure you want to delete this academic year? This cannot be undone.')) return;

    fetch('<?= BASE_URL ?>/academic/years/' + id, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': '<?= htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES) ?>'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Failed to delete academic year');
        }
    })
    .catch(() => alert('An error occurred. Please try again.'));
}
</script>