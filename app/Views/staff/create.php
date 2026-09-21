<!-- File: /app/Views/staff/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Register New Staff</h4>
            <small class="text-muted">Add staff member and optionally create a login account</small>
        </div>
        <a href="<?= BASE_URL ?>/staff" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <?php $formData = $_SESSION['staff_form_data'] ?? []; ?>

    <form method="POST" action="<?= BASE_URL ?>/staff/store" enctype="multipart/form-data">
        <!-- ============================================= -->
        <!-- 1. Personal Information -->
        <!-- ============================================= -->
        <div class="card mb-3">
            <div class="card-header fw-bold bg-light">
                <i class="fas fa-user me-1"></i> Personal Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Staff Number</label>
                        <input type="text" class="form-control" name="staff_number"
                               value="<?= htmlspecialchars($suggestedStaffNum ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="first_name"
                               value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control" name="middle_name"
                               value="<?= htmlspecialchars($formData['middle_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="last_name"
                               value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="male"   <?= ($formData['gender'] ?? '')==='male'  ?'selected':'' ?>>Male</option>
                            <option value="female" <?= ($formData['gender'] ?? '')==='female'?'selected':'' ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth"
                               value="<?= htmlspecialchars($formData['date_of_birth'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Marital Status</label>
                        <select class="form-select" name="marital_status">
                            <option value="single"   <?= ($formData['marital_status'] ?? '')==='single'  ?'selected':'' ?>>Single</option>
                            <option value="married"  <?= ($formData['marital_status'] ?? '')==='married' ?'selected':'' ?>>Married</option>
                            <option value="divorced" <?= ($formData['marital_status'] ?? '')==='divorced'?'selected':'' ?>>Divorced</option>
                            <option value="widowed"  <?= ($formData['marital_status'] ?? '')==='widowed' ?'selected':'' ?>>Widowed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Photo</label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone"
                               value="<?= htmlspecialchars($formData['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alternate Phone</label>
                        <input type="text" class="form-control" name="alt_phone"
                               value="<?= htmlspecialchars($formData['alt_phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Personal Email</label>
                        <input type="email" class="form-control" name="email"
                               value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                        <small class="text-muted">Contact email (can differ from login)</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- 2. Employment Information -->
        <!-- ============================================= -->
        <div class="card mb-3">
            <div class="card-header fw-bold bg-light">
                <i class="fas fa-briefcase me-1"></i> Employment Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="staff_category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($formData['staff_category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="staff_status_id" required>
                            <option value="">Select Status</option>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st['id'] ?>"
                                    <?= ($formData['staff_status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department_id">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"
                                    <?= ($formData['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="position"
                               value="<?= htmlspecialchars($formData['position'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employment Date</label>
                        <input type="date" class="form-control" name="employment_date"
                               value="<?= htmlspecialchars($formData['employment_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Employment Type</label>
                        <select class="form-select" name="employment_type">
                            <?php $et = $formData['employment_type'] ?? 'full_time'; ?>
                            <option value="full_time" <?= $et==='full_time'?'selected':'' ?>>Full Time</option>
                            <option value="part_time" <?= $et==='part_time'?'selected':'' ?>>Part Time</option>
                            <option value="contract"  <?= $et==='contract' ?'selected':'' ?>>Contract</option>
                            <option value="volunteer" <?= $et==='volunteer'?'selected':'' ?>>Volunteer</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- 3. System Login Account -->
        <!-- ============================================= -->
        <div class="card mb-3">
            <div class="card-header fw-bold bg-light">
                <i class="fas fa-key me-1"></i> System Login Account
            </div>
            <div class="card-body">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="create_login"
                           id="create_login" value="1" checked>
                    <label class="form-check-label fw-bold" for="create_login">
                        Create a login account for this staff member
                    </label>
                    <div class="form-text">Link a user account so this staff can log into the system</div>
                </div>

                <div id="login-fields">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username"
                                   value="<?= htmlspecialchars($formData['username'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Login Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="login_email"
                                   value="<?= htmlspecialchars($formData['login_email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_id">
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password_confirm" minlength="8">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================= -->
        <!-- 4. Teaching Assignments (Optional) -->
        <!-- ============================================= -->
        <div class="card mb-3">
            <div class="card-header fw-bold bg-light d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chalkboard-teacher me-1"></i> Teaching Assignments (Optional)</span>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addAssignmentRow()">
                    <i class="fas fa-plus me-1"></i> Add Class/Subject
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small">Assign the classes and subjects this teacher will teach. Skip if not a teacher.</p>
                <div id="assignment-list"></div>
            </div>
        </div>

        <!-- Submit -->
        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Staff
            </button>
            <a href="<?= BASE_URL ?>/staff" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<template id="assignment-template">
    <div class="row g-2 mb-2 assignment-row align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Class</label>
            <select name="assignments[INDEX][class_id]" class="form-select form-select-sm" required>
                <option value="">Select Class</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Stream</label>
            <select name="assignments[INDEX][stream_id]" class="form-select form-select-sm">
                <option value="">All Streams</option>
                <?php foreach ($streams as $st): ?>
                    <option value="<?= $st['id'] ?>"><?= htmlspecialchars($st['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Subject</label>
            <select name="assignments[INDEX][subject_id]" class="form-select form-select-sm" required>
                <option value="">Select Subject</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Assignment Type</label>
            <select name="assignments[INDEX][assignment_type_id]" class="form-select form-select-sm">
                <option value="">Select Type</option>
                <?php foreach ($assignmentTypes as $type): ?>
                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-outline-danger w-100"
                    onclick="this.closest('.assignment-row').remove()">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<script>
let assignmentIndex = 0;

function addAssignmentRow() {
    const tpl = document.getElementById('assignment-template').innerHTML.replace(/INDEX/g, assignmentIndex++);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = tpl.trim();
    document.getElementById('assignment-list').appendChild(wrapper.firstElementChild);
}

// Toggle login fields
document.getElementById('create_login').addEventListener('change', function () {
    document.getElementById('login-fields').style.display = this.checked ? 'block' : 'none';
});
</script>