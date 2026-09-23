<!-- File: /app/Views/settings/school.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 fw-bold">School Profile</h4>
        <a href="<?= BASE_URL ?>/settings" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if ($flashSuccess = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST" action="<?= BASE_URL ?>/settings/school">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">School Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name"
                           value="<?= htmlspecialchars($school['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Short Name / Abbreviation</label>
                    <input type="text" class="form-control" name="short_name"
                           value="<?= htmlspecialchars($school['short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g., KSS">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Motto</label>
                    <input type="text" class="form-control" name="motto"
                           value="<?= htmlspecialchars($school['motto'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Slogan</label>
                    <input type="text" class="form-control" name="slogan"
                           value="<?= htmlspecialchars($school['slogan'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Description / Summary</label>
                    <textarea class="form-control" name="description" rows="3"
                              placeholder="Brief overview of the institution..."><?= htmlspecialchars($school['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">School Level / Type</label>
                    <select class="form-select" name="type">
                        <option value="Primary" <?= ($school['type'] ?? '') === 'Primary' ? 'selected' : '' ?>>Primary</option>
                        <option value="Secondary" <?= ($school['type'] ?? '') === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                        <option value="Primary & Secondary" <?= ($school['type'] ?? '') === 'Primary & Secondary' ? 'selected' : '' ?>>Primary &amp; Secondary</option>
                        <option value="Tertiary" <?= ($school['type'] ?? '') === 'Tertiary' ? 'selected' : '' ?>>Tertiary / Vocational</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">UNEB / Center Reg. No.</label>
                    <input type="text" class="form-control" name="registration_number"
                           value="<?= htmlspecialchars($school['registration_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Official Website</label>
                    <input type="url" class="form-control" name="website"
                           placeholder="https://example.com"
                           value="<?= htmlspecialchars($school['website'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Physical Address</label>
                    <input type="text" class="form-control" name="physical_address"
                           value="<?= htmlspecialchars($school['physical_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Plot, Street, City">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Postal Address</label>
                    <input type="text" class="form-control" name="postal_address"
                           value="<?= htmlspecialchars($school['postal_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="P.O. Box ...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telephone <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="telephone"
                           value="<?= htmlspecialchars($school['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email"
                           value="<?= htmlspecialchars($school['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>