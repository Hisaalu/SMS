<!-- File: /app/Views/attendance/statuses/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Attendance Statuses</h4>
            <small class="text-muted">Configure Attendences</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance/statuses/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Status
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
                            <th>Code</th>
                            <th>Description</th>
                            <th>Counts</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($statuses) || count($statuses) === 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No attendance statuses found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($statuses as $status): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($status['name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($status['code']) ?></code></td>
                                    <td><?= htmlspecialchars($status['description'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($status['counts_as_present']): ?>
                                            <span class="badge bg-success">Present</span>
                                        <?php endif; ?>
                                        <?php if ($status['counts_as_absent']): ?>
                                            <span class="badge bg-danger">Absent</span>
                                        <?php endif; ?>
                                        <?php if ($status['counts_as_late']): ?>
                                            <span class="badge bg-warning text-dark">Late</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($status['requires_reason']): ?>
                                            <span class="badge bg-info text-dark">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($status['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/attendance/statuses/<?= $status['id'] ?>/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteStatusModal"
                                                data-id="<?= (int)$status['id'] ?>"
                                                data-name="<?= htmlspecialchars($status['name'], ENT_QUOTES, 'UTF-8') ?>">
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

<div class="modal fade" id="deleteStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Attendance Status</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteStatusName">this status</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteStatusBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Status
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteStatusModal');
    const nameEl    = document.getElementById('deleteStatusName');
    const confirmEl = document.getElementById('confirmDeleteStatusBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this status';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/attendance/statuses/' + currentId, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(function (r) {
            return r.text().then(function (text) {
                try { return { ok: r.ok, status: r.status, json: JSON.parse(text) }; }
                catch (e) { return { ok: r.ok, status: r.status, json: null, raw: text }; }
            });
        })
        .then(function (res) {
            if (res.json && res.json.success) {
                window.location.reload();
                return;
            }

            const message = (res.json && res.json.error)
                ? res.json.error
                : 'Could not delete this status (HTTP ' + res.status + ').';

            showErrorToast(message);

            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Status';
        })
        .catch(function () {
            showErrorToast('Network error. Please check your connection and try again.');

            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Status';
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