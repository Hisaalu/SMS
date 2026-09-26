<!-- File: /app/Views/academic/terms/create.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 fw-bold">Create Academic Term</h4>
            <small class="text-muted">Add a new term to an academic year</small>
        </div>
        <a href="<?= BASE_URL ?>/academic/terms<?= !empty($selectedYearId) ? '?year=' . (int)$selectedYearId : '' ?>"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Terms
        </a>
    </div>

    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 p-md-4">
        <form method="POST" action="<?= BASE_URL ?>/academic/terms">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?? '' ?>">

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label for="academic_year_id" class="form-label fw-semibold">
                        Academic Year <span class="text-danger">*</span>
                    </label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select" required>
                        <option value="">— Select Academic Year —</option>
                        <?php foreach ($years as $year): ?>
                            <?php
                                $yId      = (int)($year['id'] ?? 0);
                                $yName    = $year['name'] ?? '';
                                $yCurrent = !empty($year['is_current']);
                                $selected = ((int)$selectedYearId === $yId);
                            ?>
                            <option value="<?= $yId ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($yName, ENT_QUOTES, 'UTF-8') ?><?= $yCurrent ? ' — Current' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="name" class="form-label fw-semibold">
                        Term Name <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control"
                           placeholder="e.g. Term 1 or First Term"
                           required>
                </div>

                <div class="col-12 col-md-4">
                    <label for="term_number" class="form-label fw-semibold">Term Sequence</label>
                    <input type="number"
                           name="term_number"
                           id="term_number"
                           class="form-control"
                           value="1"
                           min="1" max="4">
                </div>

                <div class="col-12 col-md-4">
                    <label for="start_date" class="form-label fw-semibold">
                        Start Date <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           name="start_date"
                           id="start_date"
                           class="form-control"
                           required>
                </div>

                <div class="col-12 col-md-4">
                    <label for="end_date" class="form-label fw-semibold">
                        End Date <span class="text-danger">*</span>
                    </label>
                    <input type="date"
                           name="end_date"
                           id="end_date"
                           class="form-control"
                           required>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="checkbox"
                               name="is_current"
                               id="is_current"
                               class="form-check-input"
                               value="1">
                        <label for="is_current" class="form-check-label fw-semibold">
                            Set as Current Term
                        </label>
                    </div>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 mt-3 pt-3 border-top">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Term
                    </button>
                    <a href="<?= BASE_URL ?>/academic/terms<?= !empty($selectedYearId) ? '?year=' . (int)$selectedYearId : '' ?>"
                       class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>