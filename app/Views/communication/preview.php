<!-- File: /app/Views/communication/preview.php -->
<?php $audience = $audience ?? ($input['audience'] ?? 'parents'); ?>

<style>
    .cm-sample {
        white-space: pre-wrap;
        padding: 12px;
        background: var(--surface-color);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        border-radius: 6px;
    }
    .cm-recipients-table {
        font-size: .82rem;
        background: var(--surface-color);
        color: var(--text-color);
    }
    .cm-recipients-table thead th {
        background: var(--border-color);
        color: var(--text-color);
        border-color: var(--border-color);
    }
    .cm-recipients-table td {
        background: var(--surface-color);
        color: var(--text-color);
        border-color: var(--border-color);
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-start flex-nowrap mb-3 gap-2">
        <div class="flex-grow-1">
            <h4 class="mb-0 fw-bold">Preview Message</h4>
            <small class="text-muted">
                Send to
                <strong><?= (int)$total ?></strong> recipient<?= $total === 1 ? '' : 's' ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?><?= $audience === 'staff' ? '/staff/communication/compose' : '/communication/compose' ?>"
               class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <form method="POST" action="<?= BASE_URL ?>/communication/send">
                <?php foreach ($input as $k => $v): ?>
                    <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars((string)$v) ?>">
                <?php endforeach; ?>
                <button type="submit" class="btn btn-sm btn-primary" <?= $total === 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-paper-plane me-1"></i> Send
                </button>
            </form>
        </div>
    </div>

    <?php if (empty($recipients)): ?>
        <div class="card"><div class="card-body text-center text-muted py-5">
            <i class="fas fa-user-slash fa-3x d-block mb-3 opacity-25"></i>
            <h6>No recipients match the selected filters</h6>
            <p class="small mb-0">Go back and adjust the scope.</p>
        </div></div>
    <?php else: ?>

        <div class="card mb-3">
            <div class="card-header fw-bold">Sample (1 of <?= (int)$total ?>)</div>
            <div class="card-body">
                <p class="mb-1"><strong>To:</strong> <?= htmlspecialchars($recipients[0]['recipient_name']) ?>
                </p>
                <p class="mb-2"><strong>Subject:</strong> <?= htmlspecialchars($recipients[0]['subject']) ?></p>
                <div class="cm-sample"><?= htmlspecialchars($recipients[0]['resolved_body']) ?></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header fw-bold">All Recipients</div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 500px; overflow-y:auto;">
                    <table class="table cm-recipients-table mb-0">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>Recipient</th>
                                <th>Phone</th>
                                <th>Subject</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recipients as $i => $r): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($r['recipient_name']) ?></td>
                                    <td><?= htmlspecialchars($r['recipient_phone'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($r['subject']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>