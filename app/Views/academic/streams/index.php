<!-- File: /app/Views/academic/streams/index.php -->
<style>
    .streams-page .actions-cell {
        white-space: nowrap;
        min-width: 110px;
    }
    .streams-page .actions-cell .btn {
        --bs-btn-padding-y: 0.25rem;
        --bs-btn-padding-x: 0.45rem;
        --bs-btn-font-size: 0.75rem;
        margin-left: 0.25rem;
    }
    .streams-page .actions-cell .btn:first-child { margin-left: 0; }
</style>

<div class="container-fluid px-0 streams-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Streams</h4>
            <small class="text-muted">Manage Class Streams</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/streams/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Stream
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
        <?php if (!empty($streams)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Class</th>
                            <th class="d-none d-lg-table-cell text-center">Students</th>
                            <th class="text-end actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($streams as $stream): ?>
                            <?php
                                $sId           = (int)($stream['id'] ?? 0);
                                $sName         = $stream['name'] ?? '-';
                                $className     = $stream['class_name'] ?? 'Unassigned';
                                $studentsCount = (int)($stream['students_count'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted d-lg-none">
                                        <?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?>
                                        · <?= $studentsCount ?> student<?= $studentsCount === 1 ? '' : 's' ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle">
                                        <?= htmlspecialchars($className, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell text-center">
                                    <span class="badge bg-primary-subtle"><?= $studentsCount ?></span>
                                </td>
                                <td class="text-end actions-cell">
                                    <a href="<?= BASE_URL ?>/academic/streams/<?= $sId ?>/edit"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteStreamModal"
                                            data-id="<?= $sId ?>"
                                            data-name="<?= htmlspecialchars($sName, ENT_QUOTES, 'UTF-8') ?>">
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
                <i class="fas fa-layer-group fa-3x mb-3 opacity-25"></i>
                <h6 class="fw-bold">No streams yet</h6>
                <p class="small mb-3">Create your first stream to organise students within a class.</p>
                <a href="<?= BASE_URL ?>/academic/streams/create" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Stream
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="deleteStreamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:40px;height:40px;background:rgba(220,38,38,0.12);color:#DC2626;">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <h5 class="modal-title fw-bold mb-0">Delete Stream</h5>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-1">Are you sure you want to delete <strong id="deleteStreamName">this stream</strong>?</p>
                <p class="small text-muted mb-0">
                    This action cannot be undone. Streams with active student enrollments cannot be deleted.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteStreamBtn">
                    <i class="fas fa-trash-alt me-1"></i> Delete Stream
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl   = document.getElementById('deleteStreamModal');
    const nameEl    = document.getElementById('deleteStreamName');
    const confirmEl = document.getElementById('confirmDeleteStreamBtn');

    let currentId = 0;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        currentId = parseInt(button.getAttribute('data-id'), 10) || 0;
        const name = button.getAttribute('data-name') || 'this stream';
        nameEl.textContent = name;
    });

    confirmEl.addEventListener('click', function () {
        if (!currentId) return;

        confirmEl.disabled = true;
        confirmEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

        const url  = '<?= BASE_URL ?>/academic/streams/' + currentId + '/delete';
        const body = new URLSearchParams();
        body.append('<?= CSRF_TOKEN_NAME ?>', '<?= htmlspecialchars($_SESSION[CSRF_TOKEN_NAME] ?? '', ENT_QUOTES) ?>');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
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
                : 'Could not delete this stream (HTTP ' + res.status + ').';
            showErrorToast(message);
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Stream';
        })
        .catch(function () {
            showErrorToast('Network error. Please check your connection and try again.');
            confirmEl.disabled = false;
            confirmEl.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete Stream';
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