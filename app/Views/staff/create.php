<!-- File: /app/Views/staff/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Register New Staff Member</h4>
            <small class="text-muted">Add a new staff member to the school</small>
        </div>
        <a href="<?= BASE_URL ?>/staff" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Directory
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <?php 
    // Get form data from session if it exists (on error)
    $formData = $_SESSION['staff_form_data'] ?? [];
    ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/staff/store" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Personal Information -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary">Personal Information</h6>
                        <hr>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Staff Number</label>
                        <input type="text" class="form-control" name="staff_number" value="<?= htmlspecialchars($suggestedStaffNum ?? 'STF-0001') ?>" readonly>
                        <small class="text-muted">Auto-generated</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($formData['username'] ?? '') ?>" placeholder="e.g., jdoe" required>
                        <small class="text-muted">Unique login username</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($formData['middle_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select class="form-select" name="gender">
                            <option value="male" <?= ($formData['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($formData['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($formData['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="date_of_birth" value="<?= htmlspecialchars($formData['date_of_birth'] ?? '') ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Marital Status</label>
                        <select class="form-select" name="marital_status">
                            <option value="single" <?= ($formData['marital_status'] ?? '') == 'single' ? 'selected' : '' ?>>Single</option>
                            <option value="married" <?= ($formData['marital_status'] ?? '') == 'married' ? 'selected' : '' ?>>Married</option>
                            <option value="divorced" <?= ($formData['marital_status'] ?? '') == 'divorced' ? 'selected' : '' ?>>Divorced</option>
                            <option value="widowed" <?= ($formData['marital_status'] ?? '') == 'widowed' ? 'selected' : '' ?>>Widowed</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Photo</label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>

                    <!-- Contact Information -->
                    <div class="col-12 mt-3">
                        <h6 class="fw-bold text-primary">Contact Information</h6>
                        <hr>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($formData['phone'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Alternative Phone</label>
                        <input type="text" class="form-control" name="alt_phone" value="<?= htmlspecialchars($formData['alt_phone'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                    </div>

                    <!-- Employment Information -->
                    <div class="col-12 mt-3">
                        <h6 class="fw-bold text-primary">Employment Information</h6>
                        <hr>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="staff_category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($formData['staff_category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $st['id'] ?>" <?= ($formData['staff_status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $dept['id'] ?>" <?= ($formData['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" name="position" value="<?= htmlspecialchars($formData['position'] ?? '') ?>" placeholder="e.g., Head Teacher">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Employment Date</label>
                        <input type="date" class="form-control" name="employment_date" value="<?= htmlspecialchars($formData['employment_date'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Employment Type</label>
                        <select class="form-select" name="employment_type">
                            <option value="full_time" <?= ($formData['employment_type'] ?? '') == 'full_time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="part_time" <?= ($formData['employment_type'] ?? '') == 'part_time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contract" <?= ($formData['employment_type'] ?? '') == 'contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="volunteer" <?= ($formData['employment_type'] ?? '') == 'volunteer' ? 'selected' : '' ?>>Volunteer</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Register Staff
                    </button>
                    <a href="<?= BASE_URL ?>/staff" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const firstName = document.querySelector('input[name="first_name"]');
    const lastName = document.querySelector('input[name="last_name"]');
    const username = document.querySelector('input[name="username"]');
    
    function generateUsername() {
        if (firstName.value && lastName.value) {
            const base = (firstName.value.charAt(0) + lastName.value).toLowerCase().replace(/[^a-z0-9]/g, '');
            if (!username.value || username.value === '') {
                username.value = base;
            }
        }
    }
    
    firstName.addEventListener('input', function() {
        if (!username.value || username.value === '') {
            generateUsername();
        }
    });
    
    lastName.addEventListener('input', function() {
        if (!username.value || username.value === '') {
            generateUsername();
        }
    });
});
</script>