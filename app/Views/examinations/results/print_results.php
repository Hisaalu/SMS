<!-- File: /app/Views/examinations/results/print_results.php -->
<style>
    .marks-results-table {
        border: 1px solid #000000 !important;
        border-collapse: collapse !important;
        border-top: 1px solid #000000 !important;
    }
    .marks-results-table > thead {
        border-top: 1px solid #000000 !important;
    }
    .marks-results-table > thead > tr > th {
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
    $meta = [
        ['label' => 'Academic Year', 'value' => $academicYear['name']],
        ['label' => 'Term',          'value' => $term['name']],
        ['label' => 'Class',         'value' => $class['name']],
    ];

    if (!empty($stream)) {
        $meta[] = ['label' => 'Stream', 'value' => $stream['name']];
    }

    $meta[] = ['label' => 'Examination', 'value' => $examination['name']];
    $meta[] = ['label' => 'Students',    'value' => count($students)];

    $gradingName = !empty($gradingSystem['name']) ? $gradingSystem['name'] : '';

    $print = [
        'title'    => 'Class Results',
        'subtitle' => trim($examination['name'] . ($gradingName !== '' ? ' · ' . $gradingName : '')),
        'meta'     => $meta,
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($students)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No students found</h6>
        <p class="small mb-0">Adjust the filters or verify that students are enrolled in this class/stream.</p>
    </div>
<?php else: ?>

    <table class="table table-bordered align-middle mb-0 text-nowrap marks-results-table">
        <thead class="sticky-top">
            <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th style="width: 100px;">ADM NO</th>
                <th style="min-width: 200px;">NAME</th>
                <th style="width: 40px;" class="text-center">SEX</th>
                <?php foreach ($subjects as $subj): ?>
                    <th style="width: 70px;" class="text-center" title="<?= htmlspecialchars($subj['name']) ?>">
                        <?= htmlspecialchars(strtoupper($subj['code'] ?: $subj['name'])) ?>
                    </th>
                <?php endforeach; ?>
                <th style="width: 55px;" class="text-center">TOT</th>
                <th style="width: 55px;" class="text-center">AVG</th>
                <th style="width: 55px;" class="text-center">T.A</th>
                <th style="width: 55px;" class="text-center">DIV</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $index => $student): ?>
                <?php
                    $sid    = $student['id'];
                    $totals = $studentTotals[$sid] ?? null;
                    $rawSex = strtoupper(trim($student['gender'] ?? ''));
                    $sexDisplay = in_array($rawSex, ['F', 'FEMALE', '2']) ? 'F'
                                : (in_array($rawSex, ['M', 'MALE', '1']) ? 'M' : '-');
                ?>
                <tr>
                    <td class="text-center text-muted small"><?= $index + 1 ?></td>
                    <td class="fw-semibold small"><?= htmlspecialchars($student['admission_number'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="text-uppercase fw-semibold">
                        <?= htmlspecialchars(
                            trim(($student['last_name'] ?? '') . ' ' . ($student['first_name'] ?? '')),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </td>
                    <td class="text-center small"><?= $sexDisplay ?></td>

                    <?php foreach ($subjects as $subj): ?>
                        <?php
                            $raw   = $resultsMatrix[$sid][$subj['id']] ?? null;
                            $score = $scoreMatrix[$sid][$subj['id']] ?? null;
                        ?>
                        <td class="text-center">
                            <?php if ($raw !== null): ?>
                                <span class="fw-semibold"><?= (int)round($raw) ?></span><?php if ($score !== null): ?><sup class="text-muted ms-1 score-power"><?= (int)round($score) ?></sup><?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>

                    <td class="text-center fw-bold"><?= $totals ? (int)round($totals['total']) : '-' ?></td>
                    <td class="text-center"><?= $totals ? (int)round($totals['avg']) : '-' ?></td>
                    <td class="text-center fw-bold"><?= $totals ? (int)round($totals['ta']) : '-' ?></td>
                    <td class="text-center fw-bold">
                        <?php if ($totals && !empty($totals['div'])): ?>
                            <?= htmlspecialchars(strtoupper($totals['div']['code']), ENT_QUOTES, 'UTF-8') ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($divisions)): ?>
        <div class="d-flex flex-wrap gap-3 small text-muted mt-3 pt-3 border-top">
            <span class="fw-semibold">Division Key:</span>
            <?php foreach ($divisions as $d): ?>
                <span>
                    <strong><?= htmlspecialchars($d['code'], ENT_QUOTES, 'UTF-8') ?></strong>
                    &nbsp;<?= (int)$d['min_aggregate'] ?>–<?= (int)$d['max_aggregate'] ?>
                    <?php if (!empty($d['description'])): ?>
                        <span class="text-muted">(<?= htmlspecialchars($d['description'], ENT_QUOTES, 'UTF-8') ?>)</span>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

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