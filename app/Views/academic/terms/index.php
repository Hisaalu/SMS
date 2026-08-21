<!-- File: /app/Views/academic/terms/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Academic Terms</h4>
            <?php if ($selectedYear): ?>
                <small class="text-muted">Showing terms for: <strong><?= htmlspecialchars($selectedYear->name) ?></strong></small>
            <?php endif; ?>
        </div>
        <a href="<?= BASE_URL ?>/academic/terms/create<?= $selectedYear ? '?year=' . $selectedYear->id : '' ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Term
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success mb-2"><?= $flash ?></div>
    <?php endif; ?>
    
    <?php if ($flash = $this->getFlash('error')): ?>
        <div class="alert alert-danger mb-2"><?= $flash ?></div>
    <?php endif; ?>

    <!-- Academic Year Filter -->
    <div class="card mb-3 p-3">
        <form method="GET" action="<?= BASE_URL ?>/academic/terms" class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="year" class="col-form-label fw-bold">Filter by Academic Year:</label>
            </div>
            <div class="col-auto">
                <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Academic Years</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year->id ?>" <?= ($selectedYear && $selectedYear->id == $year->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($year->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selectedYear): ?>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>/academic/terms" class="btn btn-sm btn-outline-secondary">Clear Filter</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Terms List Table -->
    <div class="card p-3">
        <?php if (!empty($terms) && count($terms) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Term Name</th>
                            <th>Academic Year</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Current</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terms as $term): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($term->name ?? $term->term_name ?? 'Term ' . ($term->term_number ?? '')) ?></strong></td>
                                <td><?= htmlspecialchars($term->academic_year_name ?? $selectedYear->name ?? 'N/A') ?></td>
                                <td><?= isset($term->start_date) ? date('M d, Y', strtotime($term->start_date)) : '-' ?></td>
                                <td><?= isset($term->end_date) ? date('M d, Y', strtotime($term->end_date)) : '-' ?></td>
                                <td>
                                    <?php if (!empty($term->is_current)): ?>
                                        <span class="badge bg-primary">Current</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/academic/terms/<?= $term->id ?>/edit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                <p>No terms found for the selected academic year.</p>
            </div>
        <?php endif; ?>
    </div>
</div>