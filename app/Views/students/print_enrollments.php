<!-- File: /app/Views/students/print_enrollments.php -->
<style>
    .enroll-print-table {
        border: 1px solid #000000 !important;
        border-collapse: collapse !important;
        border-top: 1px solid #000000 !important;
    }
    .enroll-print-table > thead > tr > th {
        border-top: 1px solid #000000 !important;
        border-bottom-width: 2px !important;
        background: #f5f5f5;
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        font-weight: 600;
    }
</style>

<?php
    $print = [
        'title'    => 'Enrollment List',
        'subtitle' => 'Students in Class / Stream',
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../partials/print_header.php';
?>

<?php if (empty($students)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No students found</h6>
        <p class="small mb-0">Adjust the filters or verify that students are enrolled in this class/stream.</p>
    </div>
<?php else: ?>

    <table class="table table-bordered align-middle mb-0 text-nowrap enroll-print-table">
        <thead class="sticky-top">
            <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th style="width: 100px;">ADM NO</th>
                <th style="min-width: 220px;">NAME</th>
                <th style="width: 40px;" class="text-center">SEX</th>
                <th>CLASS</th>
                <th>STREAM</th>
                <th>SECTION</th>
                <th>STATUS</th>
                <th>TERM</th>
                <th>YEAR</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $st): ?>
                <?php
                    $rawSex = strtoupper(trim($st['gender'] ?? ''));
                    $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F'
                                : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                ?>
                <tr>
                    <td class="text-center text-muted small"><?= $index + 1 ?></td>
                    <td class="fw-semibold small"><?= htmlspecialchars($st['admission_number'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-uppercase fw-semibold">
                        <?= htmlspecialchars(
                            trim(($st['last_name'] ?? '') . ' ' . ($st['first_name'] ?? '')),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </td>
                    <td class="text-center small"><?= $sexDisplay ?></td>
                    <td><?= htmlspecialchars($st['class_name']    ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($st['stream_name']   ?? 'GENERAL', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($st['section_name']  ?? 'Day', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($st['status_name']   ?? 'Old', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($st['term_name']     ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($st['academic_year_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<div class="mt-4 pt-3 border-top small">
    <div class="d-flex flex-wrap align-items-end gap-3 mb-2" style="font-size: 0.85rem;">
        <span>
            Approved By: <span style="display:inline-block; min-width: 180px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
        </span>
        <span>
            Signature <span style="display:inline-block; min-width: 180px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
        </span>
        <span>
            Date:
            <span style="display:inline-block; min-width: 22px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
            /
            <span style="display:inline-block; min-width: 22px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
            /
            <span style="display:inline-block; min-width: 30px; border-bottom: 1px solid #000; height: 14px;">&nbsp;</span>
        </span>
    </div>

    <div class="d-flex flex-wrap gap-3" style="font-size: 0.8rem;">
        <span>
            Printed By : <strong><?= htmlspecialchars($printedBy ?? 'System', ENT_QUOTES, 'UTF-8') ?></strong>
        </span>
        <span>
            Date : <?= date('Y/m/d, H:i:s') ?>
        </span>
    </div>
</div>