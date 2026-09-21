<!-- File: /app/Views/students/edit.php -->
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Edit Student Details</h4>
            <small class="text-muted">Update information for <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></small>
        </div>
        <a href="<?= BASE_URL ?>/students/show?id=<?= $student['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php 
        $hasPhoto = !empty($student['photo_path']) && file_exists(ROOT_PATH . '/' . $student['photo_path']);
        $initials = strtoupper(substr($student['first_name'] ?? '', 0, 1) . substr($student['last_name'] ?? '', 0, 1));
    ?>

    <form action="<?= BASE_URL ?>/students/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $student['id'] ?>">

        <div class="row g-3">
            <!-- LEFT COLUMN -->
            <div class="col-lg-8">
                <!-- Personal Details -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white pt-3 pb-0 border-0">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-user me-2"></i>Personal & Contact Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Middle Name</label>
                                <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Preferred / Nickname</label>
                                <input type="text" name="preferred_name" value="<?= htmlspecialchars($student['preferred_name'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Registration No.</label>
                                <input type="text" value="<?= htmlspecialchars($student['registration_number'] ?? '') ?>" class="form-control form-control-sm bg-light" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Admission No.</label>
                                <input type="text" value="<?= htmlspecialchars($student['admission_number'] ?? '') ?>" class="form-control form-control-sm bg-light" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Admission Date <span class="text-danger">*</span></label>
                                <input type="date" name="admission_date" value="<?= htmlspecialchars($student['admission_date'] ?? '') ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select form-select-sm" required>
                                    <option value="male" <?= ($student['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= ($student['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Student Telephone</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Student Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Home Address</label>
                                <input type="text" name="address" value="<?= htmlspecialchars($student['address'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Placement -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white pt-3 pb-0 border-0">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-graduation-cap me-2"></i>Academic & Classification Placement
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Student Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select form-select-sm" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($student['current_category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Student Status <span class="text-danger">*</span></label>
                                <select name="status_id" class="form-select form-select-sm" required>
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= $st['id'] ?>" <?= ($student['current_status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($st['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Class <span class="text-danger">*</span></label>
                                <select name="class_id" class="form-select form-select-sm" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $cl): ?>
                                        <option value="<?= $cl['id'] ?>" <?= ($currentEnrollment['class_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cl['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Stream</label>
                                <select name="stream_id" class="form-select form-select-sm">
                                    <option value="">Select Stream (Optional)</option>
                                    <?php foreach ($streams as $str): ?>
                                        <option value="<?= $str['id'] ?>" <?= ($currentEnrollment['stream_id'] ?? '') == $str['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($str['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guardian Information -->
                <?php $primaryGuardian = $primaryGuardian ?? null; ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white pt-3 pb-0 border-0">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-users me-2"></i>Primary Guardian Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="guardian_id" value="<?= $primaryGuardian['id'] ?? '' ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guardian Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="guardian_name" value="<?= htmlspecialchars($primaryGuardian['full_name'] ?? '') ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="guardian_phone" value="<?= htmlspecialchars($primaryGuardian['phone'] ?? '') ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Relationship <span class="text-danger">*</span></label>
                                <select name="guardian_relationship" class="form-select form-select-sm" required>
                                    <?php $rel = strtolower($primaryGuardian['relationship'] ?? 'father'); ?>
                                    <option value="Father" <?= $rel === 'father' ? 'selected' : '' ?>>Father</option>
                                    <option value="Mother" <?= $rel === 'mother' ? 'selected' : '' ?>>Mother</option>
                                    <option value="Guardian" <?= $rel === 'guardian' ? 'selected' : '' ?>>Guardian</option>
                                    <option value="Other" <?= !in_array($rel, ['father','mother','guardian']) ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Occupation (Optional)</label>
                                <input type="text" name="guardian_occupation" value="<?= htmlspecialchars($primaryGuardian['occupation'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Residence / Address (Optional)</label>
                                <input type="text" name="guardian_address" value="<?= htmlspecialchars($primaryGuardian['address'] ?? '') ?>" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Photo + Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white pt-3 pb-0 border-0">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-camera me-2"></i>Profile Photo
                        </h6>
                    </div>
                    <div class="card-body text-center">

                        <?php if ($hasPhoto): ?>
                            <!-- Existing photo -->
                            <img id="photoPreview"
                                 src="<?= BASE_URL . '/' . htmlspecialchars($student['photo_path']) ?>"
                                 class="rounded-circle border shadow-sm mb-3"
                                 style="width:140px; height:140px; object-fit:cover;"
                                 alt="Student Photo">
                            <div id="photoPlaceholder"
                                 class="rounded-circle bg-primary text-white d-none align-items-center justify-content-center shadow-sm mb-3"
                                 style="width:140px; height:140px; font-size:52px; font-weight:600;">
                                <?= $initials ?: '<i class="fas fa-user"></i>' ?>
                            </div>
                        <?php else: ?>
                            <!-- No photo: show initials -->
                            <img id="photoPreview"
                                 class="rounded-circle border shadow-sm mb-3 d-none"
                                 style="width:140px; height:140px; object-fit:cover;"
                                 alt="Photo Preview">
                            <div id="photoPlaceholder"
                                 class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow-sm mb-3"
                                 style="width:140px; height:140px; font-size:52px; font-weight:600;">
                                <?= $initials ?: '<i class="fas fa-user"></i>' ?>
                            </div>
                        <?php endif; ?>

                        <input type="file" name="photo" id="photoInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/jpg">
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i> Leave blank to keep current photo
                        </small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="<?= BASE_URL ?>/students/show?id=<?= $student['id'] ?>" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        const preview = document.getElementById('photoPreview');
        const placeholder = document.getElementById('photoPlaceholder');
        preview.src = evt.target.result;
        preview.classList.remove('d-none');
        placeholder.classList.remove('d-inline-flex');
        placeholder.classList.add('d-none');
    };
    reader.readAsDataURL(file);
});
</script>