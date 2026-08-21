<div class="container-fluid px-3 py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">Student Profile</h5>
        <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Students
        </a>
    </div>

    <div class="row g-3">
        <!-- Left Sidebar: Profile Card -->
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm border-0 p-3 text-center h-100">
                <img src="<?= !empty($student['photo_path']) ? BASE_URL . '/' . htmlspecialchars($student['photo_path']) : BASE_URL . '/assets/img/avatar.png' ?>" 
                     class="rounded-circle mx-auto mb-3 border" 
                     style="width:110px; height:110px; object-fit:cover;" 
                     alt="Student Photo">
                <h5 class="mb-1 fw-bold"><?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']) ?></h5>
                <p class="text-muted mb-2">Adm No: <code class="text-primary fw-bold"><?= htmlspecialchars($student['admission_number']) ?></code></p>
                <div>
                    <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($student['category_name'] ?? 'N/A') ?></span>
                    <span class="badge bg-info rounded-pill"><?= htmlspecialchars($student['status_name'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>

        <!-- Right Content: Information Details -->
        <div class="col-lg-8 col-md-7">
            <!-- Enrollment History -->
            <div class="card shadow-sm border-0 p-3 mb-3">
                <h6 class="border-bottom pb-2 fw-bold text-primary">Enrollment History</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Academic Year</th>
                                <th>Class</th>
                                <th>Stream</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($enrollments)): ?>
                                <?php foreach ($enrollments as $e): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($e['academic_year_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($e['class_name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($e['stream_name'] ?? 'N/A') ?></td>
                                        <td><span class="badge bg-success"><?= htmlspecialchars(ucfirst($e['status_name'] ?? $e['status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-2">No enrollment records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Guardians Information -->
            <div class="card shadow-sm border-0 p-3">
                <h6 class="border-bottom pb-2 fw-bold text-primary">Guardians Information</h6>
                <ul class="list-group list-group-flush">
                    <?php if (!empty($guardians)): ?>
                        <?php foreach ($guardians as $g): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong><?= htmlspecialchars($g['full_name']) ?></strong> 
                                    <span class="text-muted">(<?= htmlspecialchars($g['relationship'] ?? 'Guardian') ?>)</span><br>
                                    <small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($g['phone']) ?></small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item px-0 text-muted border-0">No guardian information attached.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>