<!-- File: /app/Views/students/show.php -->
<?php
$hasPhoto = !empty($student['photo_path'])
         && file_exists(ROOT_PATH . '/public/' . $student['photo_path']);

$initials = strtoupper(
    substr($student['first_name'] ?? '', 0, 1) .
    substr($student['last_name']  ?? '', 0, 1)
);

$fullName = trim(
    ($student['first_name'] ?? '') . ' ' .
    (!empty($student['middle_name']) ? $student['middle_name'] . ' ' : '') .
    ($student['last_name'] ?? '')
);

$isActive = strtolower($student['status_name'] ?? '') === 'active';
?>

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Student Profile</h4>
            <small class="text-muted">View and manage student details</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/students/admission-letter?id=<?= (int)$student['id'] ?>"
               target="_blank"
               class="btn btn-sm btn-secondary">
                <i class="fas fa-file-lines me-1"></i> Admission Letter
            </a>
            <a href="<?= BASE_URL ?>/student/edit?id=<?= (int)$student['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
            <a href="<?= BASE_URL ?>/students" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Students
            </a>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-lg-4 col-md-5">
            <div class="card h-100">
                <div class="card-body text-center">

                    <?php if ($hasPhoto): ?>
                        <img src="<?= BASE_URL . '/public/' . htmlspecialchars($student['photo_path']) ?>"
                             alt="Student Photo"
                             class="rounded-circle border mb-3"
                             style="width:130px;height:130px;object-fit:cover;border-color:var(--border-color);">
                    <?php else: ?>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 fw-bold"
                             style="width:130px;height:130px;font-size:44px;background:var(--accent-color);color:#fff;">
                            <?= $initials !== '' ? htmlspecialchars($initials) : '<i class="fas fa-user"></i>' ?>
                        </div>
                    <?php endif; ?>

                    <h5 class="mb-1 fw-bold"><?= htmlspecialchars($fullName) ?></h5>

                    <?php if (!empty($student['preferred_name'])): ?>
                        <p class="text-muted small mb-2">"<?= htmlspecialchars($student['preferred_name']) ?>"</p>
                    <?php endif; ?>

                    <div class="mb-3">
                        <span class="text-muted small">Admission No: </span>
                        <code class="fw-bold"><?= htmlspecialchars($student['admission_number'] ?? '-') ?></code>
                    </div>

                    <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                        <?php if (!empty($student['category_name'])): ?>
                            <span class="badge bg-primary-subtle rounded-pill px-3 py-2">
                                <?= htmlspecialchars($student['category_name']) ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($student['status_name'])): ?>
                            <span class="badge <?= $isActive ? 'bg-success-subtle' : 'bg-secondary-subtle' ?> rounded-pill px-3 py-2">
                                <?= htmlspecialchars($student['status_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <hr style="border-color: var(--border-color);">

                    <div class="text-start small">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Registration No.</td>
                                    <td class="text-end fw-semibold"><?= htmlspecialchars($student['registration_number'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Gender</td>
                                    <td class="text-end fw-semibold"><?= htmlspecialchars(ucfirst($student['gender'] ?? '-')) ?></td>
                                </tr>
                                <?php if (!empty($student['date_of_birth'])): ?>
                                    <tr>
                                        <td class="text-muted">Date of Birth</td>
                                        <td class="text-end fw-semibold"><?= date('M d, Y', strtotime($student['date_of_birth'])) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($student['phone'])): ?>
                                    <tr>
                                        <td class="text-muted">Phone</td>
                                        <td class="text-end fw-semibold"><?= htmlspecialchars($student['phone']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($student['email'])): ?>
                                    <tr>
                                        <td class="text-muted">Email</td>
                                        <td class="text-end fw-semibold" style="word-break: break-all;"><?= htmlspecialchars($student['email']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($student['admission_date'])): ?>
                                    <tr>
                                        <td class="text-muted">Admitted</td>
                                        <td class="text-end fw-semibold"><?= date('M d, Y', strtotime($student['admission_date'])) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-7">

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-history me-2" style="color: var(--accent-color);"></i>Enrollment History
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($enrollments)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Academic Year</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th class="pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollments as $e): ?>
                                        <?php $rowActive = strtolower($e['status'] ?? '') === 'active'; ?>
                                        <tr>
                                            <td class="ps-3"><?= htmlspecialchars($e['academic_year_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($e['class_name'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($e['stream_name'] ?? 'N/A') ?></td>
                                            <td class="pe-3">
                                                <span class="badge <?= $rowActive ? 'bg-success-subtle' : 'bg-secondary-subtle' ?>">
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

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-users me-2" style="color: var(--accent-color);"></i>Guardians Information
                    </h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($guardians)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($guardians as $g): ?>
                                <li class="list-group-item px-3 py-3">
                                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                        <div>
                                            <div class="fw-semibold">
                                                <i class="fas fa-user-circle me-1" style="color: var(--text-muted);"></i>
                                                <?= htmlspecialchars($g['full_name'] ?? 'Unknown Guardian') ?>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                <span class="badge bg-secondary-subtle me-2"><?= htmlspecialchars($g['relationship'] ?? 'Guardian') ?></span>
                                                <?php if (!empty($g['phone'])): ?>
                                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($g['phone']) ?>
                                                <?php endif; ?>
                                                <?php if (!empty($g['email'])): ?>
                                                    <span class="ms-2"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($g['email']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
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