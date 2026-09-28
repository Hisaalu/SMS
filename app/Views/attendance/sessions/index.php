<!-- File: /app/Views/attendance/sessions/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Attendance Sessions</h4>
            <small class="text-muted">Manage Your Sessions</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance/sessions/create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Session
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Time</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No attendance sessions configured. Click <strong>Add Session</strong> to create one.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $session): 
                                $s = (object)$session;
                            ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($s->display_order ?? 0) ?></span></td>
                                    <td><strong><?= htmlspecialchars($s->name) ?></strong></td>
                                    <td><span class="badge bg-outline-primary border border-primary text-primary"><?= htmlspecialchars($s->code) ?></span></td>
                                    <td>
                                        <?php if (!empty($s->start_time) || !empty($s->end_time)): ?>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?= $s->start_time ? date('h:i A', strtotime($s->start_time)) : 'N/A' ?> - 
                                                <?= $s->end_time ? date('h:i A', strtotime($s->end_time)) : 'N/A' ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted small">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($s->description ?? '-') ?></td>
                                    <td>
                                        <?php if (($s->status ?? 'active') === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/attendance/sessions/<?= $s->id ?>/edit" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteSessionModal"
                                                data-id="<?= (int)$s->id ?>"
                                                data-name="<?= htmlspecialchars($s->name, ENT_QUOTES, 'UTF-8') ?>">
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

<div class="modal fade" id="deleteSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Attendance Session</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteSessionName">this session</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteSessionBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Session
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteSessionModal');
    const nameEl    = document.getElementById('deleteSessionName');
    const confirmEl = document.getElementById('confirmDeleteSessionBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this session';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        fetch('<?= BASE_URL ?>/attendance/sessions/' + currentId, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
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
                : 'Could not delete this session (HTTP ' + res.status + ').';

            showErrorToast(message);

            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Session';
        })
        .catch(function () {
            showErrorToast('Network error. Please check your connection and try again.');

            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Session';
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