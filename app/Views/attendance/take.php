<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Take Attendance</h4>
            <small class="text-muted">Select class criteria to load students and record daily attendance</small>
        </div>
        <a href="<?= BASE_URL ?>/attendance" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Registers
        </a>
    </div>

    <!-- Selection Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/attendance/take" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="attendance_date" class="form-control form-control-sm" value="<?= htmlspecialchars($selectedDate) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Class <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select form-select-sm" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>" <?= ($selectedClassId == $class['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Stream</label>
                    <select name="stream_id" class="form-select form-select-sm">
                        <option value="">All Streams</option>
                        <?php foreach ($streams as $stream): ?>
                            <option value="<?= $stream['id'] ?>" <?= ($selectedStreamId == $stream['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stream['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Session <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="attendance_session_id" required>
                        <option value="">-- Select Session --</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?= $session['id'] ?>" <?= ($selectedSessionId == $session['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($session['name']) ?> (<?= htmlspecialchars($session['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-users me-1"></i> Load Student Sheet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Form -->
    <?php if ($selectedClassId): ?>
        <form method="POST" action="<?= BASE_URL ?>/attendance/save">
            <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($selectedDate) ?>">
            <input type="hidden" name="class_id" value="<?= htmlspecialchars($selectedClassId) ?>">
            <input type="hidden" name="stream_id" value="<?= htmlspecialchars($selectedStreamId ?? '') ?>">
            <input type="hidden" name="attendance_session_id" value="<?= htmlspecialchars($selectedSessionId) ?>">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 font-weight-bold text-primary">
                        Student List (<?= count($students) ?> Students)
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success" onclick="markAllStatus(1)">Mark All Present</button>
                        <button type="button" class="btn btn-outline-danger" onclick="markAllStatus(2)">Mark All Absent</button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Admin No</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th>Reason (If applicable)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            No active enrollments found for this class and stream selection.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $index => $student): 
                                        $rec = $existingRecords[$student['id']] ?? null;
                                        $currentStatusId = $rec['attendance_status_id'] ?? ($statuses[0]['id'] ?? '');
                                    ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><code><?= htmlspecialchars($student['admission_number'] ?? 'N/A') ?></code></td>
                                            <td><strong><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></strong></td>
                                            <td>
                                                <!-- Hidden input to submit the student's enrollment ID -->
                                                <input type="hidden" name="records[<?= $student['id'] ?>][enrollment_id]" value="<?= htmlspecialchars($student['enrollment_id'] ?? '') ?>">
                                                <select name="records[<?= $student['id'] ?>][status_id]" class="form-select form-select-sm status-select" required>
                                                    <?php foreach ($statuses as $st): ?>
                                                        <option value="<?= $st['id'] ?>" <?= ($currentStatusId == $st['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($st['name']) ?> (<?= htmlspecialchars($st['code']) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="records[<?= $student['id'] ?>][reason]" class="form-control form-control-sm" placeholder="Reason..." value="<?= htmlspecialchars($rec['reason'] ?? '') ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="records[<?= $student['id'] ?>][remarks]" class="form-control form-control-sm" placeholder="Remarks..." value="<?= htmlspecialchars($rec['remarks'] ?? '') ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (!empty($students)): ?>
                    <div class="card-footer bg-white text-end py-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Attendance Register
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function markAllStatus(statusId) {
    document.querySelectorAll('.status-select').forEach(select => {
        select.value = statusId;
    });
}
</script>