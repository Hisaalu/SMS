<!-- File: /app/Views/students/show.php -->
<div class="container-fluid px-3 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Student Profile</h4>
            <small class="text-muted">View and manage student details</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/student/edit?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
            <a href="<?= BASE_URL ?>/students" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Students
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- LEFT SIDEBAR: Profile Card -->
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <?php 
                        $hasPhoto = !empty($student['photo_path']) && file_exists(ROOT_PATH . '/public/' . $student['photo_path']);
                        $initials = strtoupper(substr($student['first_name'] ?? '', 0, 1) . substr($student['last_name'] ?? '', 0, 1));
                    ?>

                    <?php if ($hasPhoto): ?>
                        <img src="<?= BASE_URL . '/public/' . htmlspecialchars($student['photo_path']) ?>"
                             class="rounded-circle border shadow-sm mb-3"
                             style="width:130px; height:130px; object-fit:cover;"
                             alt="Student Photo">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3 shadow-sm"
                             style="width:130px; height:130px; font-size:48px; font-weight:600;">
                            <?= $initials ?: '<i class="fas fa-user"></i>' ?>
                        </div>
                    <?php endif; ?>

                    <h5 class="mb-1 fw-bold">
                        <?= htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . ($student['last_name'] ?? '')) ?>
                    </h5>

                    <?php if (!empty($student['preferred_name'])): ?>
                        <p class="text-muted small mb-2">"<?= htmlspecialchars($student['preferred_name']) ?>"</p>
                    <?php endif; ?>

                    <p class="mb-2">
                        <span class="text-muted small">Adm No:</span>
                        <code class="text-primary fw-bold"><?= htmlspecialchars($student['admission_number'] ?? '-') ?></code>
                    </p>

                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <?php if (!empty($student['category_name'])): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                <?= htmlspecialchars($student['category_name']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($student['status_name'])): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                <?= htmlspecialchars($student['status_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <hr class="my-3">

                    <div class="text-start small">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:45%;">Registration No.</td>
                                <td class="fw-semibold text-end"><?= htmlspecialchars($student['registration_number'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Gender</td>
                                <td class="fw-semibold text-end"><?= ucfirst(htmlspecialchars($student['gender'] ?? '-')) ?></td>
                            </tr>
                            <?php if (!empty($student['date_of_birth'])): ?>
                            <tr>
                                <td class="text-muted">Date of Birth</td>
                                <td class="fw-semibold text-end"><?= date('M d, Y', strtotime($student['date_of_birth'])) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($student['phone'])): ?>
                            <tr>
                                <td class="text-muted">Phone</td>
                                <td class="fw-semibold text-end"><?= htmlspecialchars($student['phone']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($student['email'])): ?>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td class="fw-semibold text-end small text-truncate" style="max-width:130px;"><?= htmlspecialchars($student['email']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($student['admission_date'])): ?>
                            <tr>
                                <td class="text-muted">Admitted</td>
                                <td class="fw-semibold text-end"><?= date('M d, Y', strtotime($student['admission_date'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT CONTENT: Details -->
        <div class="col-lg-8 col-md-7">
            <!-- Enrollment History -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white pt-3 pb-0 border-0">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-history me-2"></i>Enrollment History
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($enrollments)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Academic Year</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th class="pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollments as $e): ?>
                                        <?php $isActive = strtolower($e['status'] ?? '') === 'active'; ?>
                                        <tr>
                                            <td class="ps-3"><?= htmlspecialchars($e['academic_year_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($e['class_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($e['stream_name'] ?? 'N/A') ?></td>
                                            <td class="pe-3">
                                                <span class="badge bg-<?= $isActive ? 'success' : 'secondary' ?>-subtle text-<?= $isActive ? 'success' : 'secondary' ?> border border-<?= $isActive ? 'success' : 'secondary' ?>-subtle">
                                                    <?= htmlspecialchars(ucfirst($e['status_name'] ?? $e['status'] ?? 'N/A')) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2 opacity-50 d-block"></i>
                            <div class="small">No enrollment records found.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Guardians Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white pt-3 pb-0 border-0">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-users me-2"></i>Guardians Information
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($guardians)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($guardians as $g): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-3">
                                    <div>
                                        <div class="fw-semibold">
                                            <i class="fas fa-user-circle text-secondary me-1"></i>
                                            <?= htmlspecialchars($g['full_name']) ?>
                                        </div>
                                        <small class="text-muted">
                                            <span class="badge bg-light text-muted border me-2"><?= htmlspecialchars($g['relationship'] ?? 'Guardian') ?></span>
                                            <i class="fas fa-phone me-1"></i><?= htmlspecialchars($g['phone']) ?>
                                        </small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2 opacity-50 d-block"></i>
                            <div class="small">No guardian information attached.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>