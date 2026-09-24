<!-- File: /app/Views/academic/subjects/index.php -->
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Subjects</h4>
        <a href="<?= BASE_URL ?>/academic/subjects/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Subject
        </a>
    </div>

    <?php if ($flash = $this->getFlash('success')): ?>
        <div class="alert alert-success mb-2"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <div class="card p-3">
        <?php if (!empty($subjects)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Code</th>
                            <th>Grading System</th>
                            <th>Type</th>
                            <th>Department</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <?php
                                $typeRow    = $typeList[$subject->grading_subject_type_id] ?? null;
                                $typeName   = $typeRow['name'] ?? '—';
                                $isGraded   = $typeRow ? (bool)$typeRow['is_graded'] : true;
                                $typeBadge  = $isGraded ? 'primary' : 'secondary';
                                $systemName = $systemList[$subject->grading_system_id] ?? '—';
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($subject->name) ?></strong></td>
                                <td><code><?= htmlspecialchars($subject->code) ?></code></td>
                                <td><?= htmlspecialchars($systemName) ?></td>
                                <td>
                                    <span class="badge bg-<?= $typeBadge ?>">
                                        <?= htmlspecialchars($typeName) ?>
                                        <?php if (!$isGraded): ?>
                                            &middot; N/G
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($deptList[$subject->department_id] ?? 'None') ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/academic/subjects/<?= (int)$subject->id ?>/edit"
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center py-3">No subjects found.</p>
        <?php endif; ?>
    </div>
</div>