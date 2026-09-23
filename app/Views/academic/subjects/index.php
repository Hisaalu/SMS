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
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Subject Name</th>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <?php
                            $type = strtolower((string)($subject->type ?? 'core'));
                            $badge = match ($type) {
                                'core'     => 'primary',
                                'elective' => 'info',
                                'optional' => 'warning',
                                'other'    => 'secondary',
                                default    => 'light',
                            };
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($subject->name) ?></strong></td>
                            <td><code><?= htmlspecialchars($subject->code) ?></code></td>
                            <td><?= htmlspecialchars($deptList[$subject->department_id] ?? 'None') ?></td>
                            <td>
                                <span class="badge bg-<?= $badge ?>">
                                    <?= htmlspecialchars(ucfirst($type)) ?>
                                    <?php if ($type === 'other'): ?>
                                        <span title="Not graded">&middot; N/G</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/academic/subjects/<?= (int)$subject->id ?>/edit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted text-center py-3">No subjects found.</p>
        <?php endif; ?>
    </div>
</div>