<!-- File: /app/Views/staff/edit.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Edit Staff Member</h4>
            <small class="text-muted">Update information for <?= htmlspecialchars(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?></small>
        </div>
        <a href="<?= BASE_URL ?>/staff/show?id=<?= $staff['id'] ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/staff/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $staff['id'] ?>">

        <div class="row g-3">
            <!-- Personal Details -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control form-control-sm" required 
                                       value="<?= htmlspecialchars($staff['first_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['middle_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control form-control-sm" required 
                                       value="<?= htmlspecialchars($staff['last_name'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Gender</label>
                                <select name="gender" class="form-select form-select-sm">
                                    <option value="male" <?= (($staff['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= (($staff['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= (($staff['gender'] ?? '') === 'other') ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['dob'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Marital Status</label>
                                <select name="marital_status" class="form-select form-select-sm">
                                    <option value="single" <?= (($staff['marital_status'] ?? '') === 'single') ? 'selected' : '' ?>>Single</option>
                                    <option value="married" <?= (($staff['marital_status'] ?? '') === 'married') ? 'selected' : '' ?>>Married</option>
                                    <option value="divorced" <?= (($staff['marital_status'] ?? '') === 'divorced') ? 'selected' : '' ?>>Divorced</option>
                                    <option value="widowed" <?= (($staff['marital_status'] ?? '') === 'widowed') ? 'selected' : '' ?>>Widowed</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone Number</label>
                                <input type="text" name="phone" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Alt Phone Number</label>
                                <input type="text" name="alt_phone" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['alt_phone'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Address</label>
                                <input type="text" name="address" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['address'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Employment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Staff Category <span class="text-danger">*</span></label>
                                <select name="staff_category_id" class="form-select form-select-sm" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= (($staff['staff_category_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Staff Status <span class="text-danger">*</span></label>
                                <select name="staff_status_id" class="form-select form-select-sm" required>
                                    <option value="">Select Status</option>
                                    <?php foreach ($statuses as $st): ?>
                                        <option value="<?= $st['id'] ?>" <?= (($staff['staff_status_id'] ?? 0) == $st['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($st['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Department</label>
                                <select name="department_id" class="form-select form-select-sm">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= (($staff['department_id'] ?? 0) == $dept['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Position / Title</label>
                                <input type="text" name="position" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['position'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Employment Date</label>
                                <input type="date" name="employment_date" class="form-control form-control-sm" 
                                       value="<?= htmlspecialchars($staff['emp_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Employment Type</label>
                                <select name="employment_type" class="form-select form-select-sm">
                                    <option value="full_time" <?= (($staff['emp_type'] ?? '') === 'full_time') ? 'selected' : '' ?>>Full Time</option>
                                    <option value="part_time" <?= (($staff['emp_type'] ?? '') === 'part_time') ? 'selected' : '' ?>>Part Time</option>
                                    <option value="contract" <?= (($staff['emp_type'] ?? '') === 'contract') ? 'selected' : '' ?>>Contract</option>
                                    <option value="temporary" <?= (($staff['emp_type'] ?? '') === 'temporary') ? 'selected' : '' ?>>Temporary</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Photo & Actions -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Profile Picture</h6>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($staff['photo_path']) && file_exists(ROOT_PATH . '/public/' . $staff['photo_path'])): ?>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($staff['photo_path']) ?>" class="img-thumbnail mb-3" style="max-height: 150px;" alt="Staff Photo">
                        <?php else: ?>
                            <div class="bg-light border rounded p-4 mb-3 text-muted">
                                <i class="fas fa-user fa-3x mb-2 d-block"></i>
                                No Photo Uploaded
                            </div>
                        <?php endif; ?>

                        <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted d-block mt-1">PNG, JPG or WEBP up to 2MB</small>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="<?= BASE_URL ?>/staff/show?id=<?= $staff['id'] ?>" class="btn btn-outline-secondary btn-sm">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>