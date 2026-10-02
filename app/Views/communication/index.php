<!-- File: /app/Views/communication/index.php -->
<?php $audience = $audience ?? 'parents'; ?>
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold"><?= $audience === 'staff' ? 'Staff Communication' : 'Communication' ?></h4>
            <small class="text-muted">
                <?= $audience === 'staff'
                    ? 'Send your Messages'
                    : 'Send your Messages' ?>
            </small>
        </div>
        <a href="<?= BASE_URL ?><?= $audience === 'staff' ? '/staff/communication/compose' : '/communication/compose' ?>"
           class="btn btn-sm btn-primary">
            <i class="fas fa-paper-plane me-1"></i> Compose
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="fas fa-exclamation-circle me-1"></i> <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET"
                  action="<?= BASE_URL ?><?= $audience === 'staff' ? '/staff/communication' : '/communication' ?>"
                  class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($selectedFilters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($selectedFilters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Type</label>
                    <select name="message_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <?php foreach ($messageTypes as $key => $tpl): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= ($selectedFilters['message_type'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tpl['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-fill">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="<?= BASE_URL ?><?= $audience === 'staff' ? '/staff/communication' : '/communication' ?>"
                       class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($history)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-envelope-open-text fa-3x d-block mb-3 opacity-25"></i>
            <h6 class="fw-bold">No messages yet</h6>
            <p class="small mb-0">Click <strong>Compose</strong> to send your first message.</p>
        </div></div>
    <?php else: ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Scope</th>
                                <th class="text-center">Recipients</th>
                                <th>Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($h['subject']) ?></td>
                                    <td>
                                        <?php $label = $messageTypes[$h['message_type']]['label'] ?? ucfirst($h['message_type']); ?>
                                        <span class="badge bg-secondary-subtle"><?= htmlspecialchars($label) ?></span>
                                    </td>
                                    <td class="text-muted small">
                                        <?php
                                            $bits = [];
                                            if (!empty($h['academic_year_id'])) $bits[] = 'Year';
                                            if (!empty($h['term_id']))          $bits[] = 'Term';
                                            if (!empty($h['section_id']))       $bits[] = 'Section';
                                            if (!empty($h['class_id']))         $bits[] = 'Class';
                                            if (!empty($h['stream_id']))        $bits[] = 'Stream';
                                            echo htmlspecialchars(implode(' · ', $bits) ?: 'All');
                                        ?>
                                    </td>
                                    <td class="text-center fw-bold"><?= (int)$h['recipients'] ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars(date('d M Y, H:i', strtotime($h['last_created_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>