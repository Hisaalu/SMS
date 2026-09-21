<!-- File: /app/Views/students/create.php -->
<div class="container-fluid px-4 py-3">
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Admit New Student</h4>
            <small class="text-muted">Register a new student into the system</small>
        </div>
        <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form action="<?= BASE_URL ?>/students/store" method="POST" enctype="multipart/form-data">
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
                                <input type="text" name="first_name" id="firstNameInput" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" id="lastNameInput" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Preferred / Nick Name</label>
                                <input type="text" name="preferred_name" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Registration No.</label>
                                <input type="text" name="registration_number" value="<?= htmlspecialchars($registration_number) ?>" class="form-control form-control-sm bg-light" readonly required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Admission No.</label>
                                <input type="text" name="admission_number" value="<?= htmlspecialchars($admission_number) ?>" class="form-control form-control-sm bg-light" readonly required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Admission Date <span class="text-danger">*</span></label>
                                <input type="date" name="admission_date" value="<?= date('Y-m-d') ?>" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select form-select-sm" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Student Telephone</label>
                                <input type="text" name="phone" class="form-control form-control-sm" placeholder="+256...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Student Email</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="student@example.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Home Address</label>
                                <input type="text" name="address" class="form-control form-control-sm" placeholder="Village / Town / Street">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic Placement -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white pt-3 pb-0 border-0">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-graduation-cap me-2"></i>Academic Placement
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Student Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select form-select-sm" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Initial Status <span class="text-danger">*</span></label>
                                <select name="status_id" class="form-select form-select-sm" required>
                                    <option value="">Select Status</option>
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Academic Year <span class="text-danger">*</span></label>
                                <select name="academic_year_id" class="form-select form-select-sm" required>
                                    <option value="">Select Year</option>
                                    <?php foreach ($years as $yr): ?>
                                        <option value="<?= $yr['id'] ?>"><?= htmlspecialchars($yr['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Class <span class="text-danger">*</span></label>
                                <select name="class_id" class="form-select form-select-sm" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $cl): ?>
                                        <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Stream</label>
                                <select name="stream_id" class="form-select form-select-sm">
                                    <option value="">Select Stream (Optional)</option>
                                    <?php foreach ($streams as $str): ?>
                                        <option value="<?= $str['id'] ?>"><?= htmlspecialchars($str['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guardian Information -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white pt-3 pb-0 border-0">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fas fa-users me-2"></i>Primary Guardian Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Guardian Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="guardian_name" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="guardian_phone" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Relationship <span class="text-danger">*</span></label>
                                <select name="guardian_relationship" class="form-select form-select-sm" required>
                                    <option value="Parent">Parent</option>
                                    <option value="Father">Father</option>
                                    <option value="Mother">Mother</option>
                                    <option value="Guardian">Legal Guardian</option>
                                    <option value="Sponsor">Sponsor</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Occupation (Optional)</label>
                                <input type="text" name="guardian_occupation" class="form-control form-control-sm" placeholder="e.g. Teacher, Business">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Residence / Address (Optional)</label>
                                <input type="text" name="guardian_address" class="form-control form-control-sm" placeholder="Village / Town / Street">
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
                        <!-- Initials circle (always shown until a photo is picked) -->
                        <div id="photoPlaceholder"
                             class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center shadow-sm mb-3"
                             style="width:140px; height:140px; font-size:52px; font-weight:600;">
                            <i class="fas fa-user"></i>
                        </div>

                        <!-- Hidden img that replaces the placeholder when a file is picked -->
                        <img id="photoPreview"
                             class="rounded-circle border shadow-sm mb-3 d-none"
                             style="width:140px; height:140px; object-fit:cover;"
                             alt="Photo Preview">

                        <input type="file" name="photo" id="photoInput" class="form-control form-control-sm" accept="image/jpeg,image/png,image/jpg">
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i> JPG or PNG, max 2MB
                        </small>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save & Admit Student
                        </button>
                        <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary">Cancel</a>
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
        placeholder.classList.add('d-none');
    };
    reader.readAsDataURL(file);
});
</script>