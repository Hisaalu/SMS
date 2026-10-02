<!-- File: /app/Views/reports/academic/print_student_results.php -->
<style>
    .srp-title {
        text-align: center;
        font-weight: 800;
        letter-spacing: .6px;
        text-transform: uppercase;
        font-size: 0.95rem;
        padding-bottom: .35rem;
        border-bottom: 2px solid #000;
        margin-bottom: .5rem;
        color: #000;
    }
    .srp-meta {
        font-size: .8rem;
        margin-bottom: .6rem;
        color: #000;
    }
    .srp-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.78rem;
        background: #ffffff;
        color: #000000;
    }
    .srp-table thead th {
        background: #f1f5f9;
        color: #1e293b;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: .3px;
        font-weight: 800;
        border: 1px solid #94a3b8;
        border-bottom: 2px solid #64748b;
        text-align: center;
        vertical-align: middle;
        padding: 4px 6px;
    }
    .srp-table td {
        border: 1px solid #94a3b8;
        padding: 4px 6px;
        vertical-align: middle;
        background: #ffffff;
        color: #000000;
    }
    .srp-table .srp-code { font-weight: 700; text-align: center; }
    .srp-table .srp-name { font-weight: 700; }
    .srp-table .srp-mark,
    .srp-table .srp-grade { text-align: center; font-weight: 700; }
    .srp-table tfoot td {
        background: #eef2f7;
        font-weight: 800;
        color: #000000;
    }
    @media print {
        .srp-table { font-size: 10px; }
        .srp-table thead th { font-size: 9px; }
    }
</style>

<?php
    $student      = $summary['student']       ?? null;
    $academicYear = $summary['academic_year'] ?? null;
    $term         = $summary['term']          ?? null;
    $class        = $summary['class']         ?? null;
    $stream       = $summary['stream']        ?? null;
    $exams        = $summary['exams']         ?? [];
    $rows         = $summary['rows']          ?? [];
    $totals       = $summary['totals']        ?? [];
    $fullName     = strtoupper(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')));

    $print = [
        'title'    => 'Student Results',
        'subtitle' => $fullName,
        'meta'     => [
            ['label' => 'Academic Year', 'value' => $academicYear['name'] ?? ''],
            ['label' => 'Term',          'value' => $term['name']         ?? ''],
            ['label' => 'Class',         'value' => $class['name']        ?? ''],
            ['label' => 'Stream',        'value' => $stream['name']       ?? 'N/A'],
            ['label' => 'Reg No',        'value' => $student['admission_number'] ?? ''],
        ],
        'compact'  => true,
    ];
    include __DIR__ . '/../../partials/print_header.php';
?>

<?php if (empty($rows) || empty($exams)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
        <h6>No results available</h6>
        <p class="small mb-0">Select a student, year, and term to generate the results sheet.</p>
    </div>
<?php else: ?>

    <div class="srp-title">Academic Progress Report (<?= htmlspecialchars($fullName) ?>)</div>

    <div class="srp-meta">
        Academic Year: <strong><?= htmlspecialchars($academicYear['name'] ?? '-') ?></strong>
        &nbsp;|&nbsp;
        Term: <strong><?= htmlspecialchars($term['name'] ?? '-') ?></strong>
        &nbsp;|&nbsp;
        Class: <strong><?= htmlspecialchars($class['name'] ?? '-') ?></strong>
        <?php if (!empty($stream['name'])): ?>
            &nbsp;|&nbsp; Stream: <strong><?= htmlspecialchars($stream['name']) ?></strong>
        <?php endif; ?>
        &nbsp;|&nbsp;
        Reg No: <strong><?= htmlspecialchars($student['admission_number'] ?? '-') ?></strong>
    </div>

    <table class="srp-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 45px;">No</th>
                <th rowspan="2" style="width: 70px;">Code</th>
                <th rowspan="2">Subject</th>
                <?php foreach ($exams as $exam): ?>
                    <th colspan="2"><?= htmlspecialchars(strtoupper($exam['name'])) ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($exams as $exam): ?>
                    <th style="width: 60px;">Marks</th>
                    <th style="width: 60px;">Grade</th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $row): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td class="srp-code"><?= htmlspecialchars(strtoupper($row['subject_code'] ?: $row['subject_name'])) ?></td>
                    <td class="srp-name"><?= htmlspecialchars(strtoupper($row['subject_name'])) ?></td>
                    <?php foreach ($exams as $exam): ?>
                        <?php
                            $examId = (int)$exam['id'];
                            $cell   = $row['exams'][$examId] ?? null;
                        ?>
                        <td class="srp-mark"><?= $cell && $cell['mark'] !== null ? (int)$cell['mark'] : '-' ?></td>
                        <td class="srp-grade"><?= $cell && $cell['grade'] !== null ? htmlspecialchars((string)$cell['grade']) : '-' ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-start">TOTAL</td>
                <?php foreach ($exams as $exam): ?>
                    <?php
                        $examId = (int)$exam['id'];
                        $t      = $totals[$examId] ?? ['sum' => 0, 'avg' => 0, 'score' => 0];
                    ?>
                    <td class="srp-mark">
                        <?= (int)$t['sum'] ?><?= $t['count'] > 0 ? '(' . htmlspecialchars((string)$t['avg']) . ')' : '' ?>
                    </td>
                    <td class="srp-grade"><?= (int)$t['score'] ?></td>
                <?php endforeach; ?>
            </tr>
        </tfoot>
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