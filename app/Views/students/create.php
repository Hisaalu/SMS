<div class="container-fluid px-4 py-3">
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Admit New Student</h4>
        <a href="<?= BASE_URL ?>/students" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form action="<?= BASE_URL ?>/students/store" method="POST" enctype="multipart/form-data" class="needs-validation">
        <!-- Personal Details -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary">Personal & Contact Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Preferred / Nick Name</label>
                        <input type="text" name="preferred_name" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Registration No. (Auto Generated)</label>
                        <input type="text" name="registration_number" value="<?= htmlspecialchars($registration_number) ?>" class="form-control bg-light" readonly required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Admission No. (Auto Generated)</label>
                        <input type="text" name="admission_number" value="<?= htmlspecialchars($admission_number) ?>" class="form-control bg-light" readonly required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Admission Date *</label>
                        <input type="date" name="admission_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Student Telephone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+256...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Student Email</label>
                        <input type="email" name="email" class="form-control" placeholder="student@example.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/jpg">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Home Address</label>
                        <input type="text" name="address" class="form-control" placeholder="City, District, or Residence Area">
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Placement -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary">Academic & Classification Placement</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Student Category *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Initial Status *</label>
                        <select name="status_id" class="form-select" required>
                            <option value="">Select Status</option>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Academic Year *</label>
                        <select name="academic_year_id" class="form-select" required>
                            <option value="">Select Year</option>
                            <?php foreach ($years as $yr): ?>
                                <option value="<?= $yr['id'] ?>"><?= htmlspecialchars($yr['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Class *</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $cl): ?>
                                <option value="<?= $cl['id'] ?>"><?= htmlspecialchars($cl['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Stream</label>
                        <select name="stream_id" class="form-select">
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
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary">Primary Guardian Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Guardian Full Name *</label>
                        <input type="text" name="guardian_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="guardian_phone" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Relationship *</label>
                        <select name="guardian_relationship" class="form-select" required>
                            <option value="Parent">Parent</option>
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Guardian">Legal Guardian</option>
                            <option value="Sponsor">Sponsor</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Occupation (Optional)</label>
                        <input type="text" name="guardian_occupation" class="form-control" placeholder="e.g. Teacher, Business">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Residence / Address (Optional)</label>
                        <input type="text" name="guardian_address" class="form-control" placeholder="Village / Town / Street">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-4">Save & Admit Student</button>
        </div>
    </form>
</div>