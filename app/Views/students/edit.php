<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Edit Student Details</h4>
        <a href="<?= BASE_URL ?>/students/show?id=<?= $student['id'] ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <form action="<?= BASE_URL ?>/students/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $student['id'] ?>">

        <!-- Personal & Contact Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary">Personal & Contact Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Preferred Name / Nickname</label>
                        <input type="text" name="preferred_name" value="<?= htmlspecialchars($student['preferred_name'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Registration No.</label>
                        <input type="text" value="<?= htmlspecialchars($student['registration_number'] ?? '') ?>" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Admission No.</label>
                        <input type="text" value="<?= htmlspecialchars($student['admission_number'] ?? '') ?>" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Admission Date *</label>
                        <input type="date" name="admission_date" value="<?= htmlspecialchars($student['admission_date'] ?? '') ?>" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender *</label>
                        <select name="gender" class="form-select" required>
                            <option value="male" <?= ($student['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($student['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($student['date_of_birth'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Student Telephone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Student Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Home Address</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($student['address'] ?? '') ?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Placement & Classification -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary">Academic & Classification Placement</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Student Category *</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($student['current_category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Student Status *</label>
                        <select name="status_id" class="form-select" required>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st['id'] ?>" <?= ($student['current_status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($st['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Class *</label>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= ($currentEnrollment['class_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Stream</label>
                        <select name="stream_id" class="form-select">
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
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-primary">Primary Guardian Details</h6>
            </div>
            <div class="card-body">
                <input type="hidden" name="guardian_id" value="<?= $primaryGuardian['id'] ?? '' ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Guardian Full Name *</label>
                        <input type="text" name="guardian_name" value="<?= htmlspecialchars($primaryGuardian['full_name'] ?? '') ?>" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Phone Number *</label>
                        <input type="text" name="guardian_phone" value="<?= htmlspecialchars($primaryGuardian['phone'] ?? '') ?>" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Relationship *</label>
                        <select name="guardian_relationship" class="form-select" required>
                            <?php $rel = strtolower($primaryGuardian['relationship'] ?? 'father'); ?>
                            <option value="Father" <?= $rel === 'father' ? 'selected' : '' ?>>Father</option>
                            <option value="Mother" <?= $rel === 'mother' ? 'selected' : '' ?>>Mother</option>
                            <option value="Guardian" <?= $rel === 'guardian' ? 'selected' : '' ?>>Guardian</option>
                            <option value="Other" <?= !in_array($rel, ['father','mother','guardian']) ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Occupation (Optional)</label>
                        <input type="text" name="guardian_occupation" value="<?= htmlspecialchars($primaryGuardian['occupation'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Residence / Address (Optional)</label>
                        <input type="text" name="guardian_address" value="<?= htmlspecialchars($primaryGuardian['address'] ?? '') ?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary px-4">Update Student Details</button>
        </div>
    </form>
</div>