<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Attendance Register Details</h4>
            <small class="text-muted">Viewing recorded register summary and individual records</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Registers
        </a>
    </div>

    <!-- Metadata Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <span class="text-muted d-block small text-uppercase">Date</span>
                    <strong><?= date('F d, Y', strtotime($register['attendance_date'])) ?></strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small text-uppercase">Class / Stream</span>
                    <strong><?= htmlspecialchars($register['class_name'] ?? 'N/A') ?> <?= !empty($register['stream_name']) ? '(' . htmlspecialchars($register['stream_name']) . ')' : '' ?></strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small text-uppercase">Session</span>
                    <strong><?= htmlspecialchars($register['session_name'] ?? 'Daily') ?></strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted d-block small text-uppercase">Status</span>
                    <?php if (($register['status'] ?? 'draft') === 'submitted'): ?>
                        <span class="badge bg-success">Submitted</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Draft</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Details Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Admin No</th>
                            <th>Student Name</th>
                            <th>Status Code</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No attendance entries saved for this register.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $index => $rec): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><code><?= htmlspecialchars($rec['admission_number'] ?? 'N/A') ?></code></td>
                                    <td><strong><?= htmlspecialchars($rec['first_name'] . ' ' . $rec['last_name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($rec['status_code']) ?></code></td>
                                    <td>
                                        <?php if ($rec['counts_as_present']??0): ?>
                                            <span class="badge bg-success"><?= htmlspecialchars($rec['status_name']) ?></span>
                                        <?php elseif ($rec['counts_as_absent']): ?>
                                            <span class="badge bg-danger"><?= htmlspecialchars($rec['status_name']) ?></span>
                                        <?php elseif ($rec['counts_as_late']): ?>
                                            <span class="badge bg-warning text-dark"><?= htmlspecialchars($rec['status_name']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($rec['status_name']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($rec['reason'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($rec['remarks'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>