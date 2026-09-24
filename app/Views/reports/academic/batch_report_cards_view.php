<?php
// File: app/Views/reports/academic/batch_report_cards_view.php

$settings = new \NexaT\Core\SettingsService();

$schoolName  = $settings->get('school.name', 'School Name');
$schoolMotto = $settings->get('school.motto', '');
$schoolPhone = $settings->get('school.telephone', '');
$schoolEmail = $settings->get('school.email', '');

$schoolPoBox = $settings->get('school.po_box', '');
if ($schoolPoBox === '') {
    $schoolPoBox = $settings->get('school.postal_address', '');
}

if ($schoolPoBox !== '' && stripos($schoolPoBox, 'box') !== false && stripos($schoolPoBox, 'p.o') === false && stripos($schoolPoBox, 'po ') === false) {
    $schoolPoBox = 'P.O. Box ' . preg_replace('/[^0-9]/', '', $schoolPoBox);
}

$primaryColor = $settings->get('branding.primary_color', '#1a237e');
$accentColor  = $settings->get('branding.accent_color',  '#e91e63');
$mottoColor   = $settings->get('branding.motto_color',   '#b71c1c');
$textColor    = $settings->get('branding.text_color',    '#111111');
$borderColor  = '#222222';
$tableHeadBg  = '#eef2f7';
$softBg       = '#f9fafc';

$assetUrl = static function (?string $path): ?string {
    if (empty($path)) return null;
    $clean = ltrim($path, '/');
    if (file_exists(ROOT_PATH . '/public/' . $clean)) {
        return rtrim(BASE_URL, '/') . '/public/' . $clean;
    }
    if (file_exists(ROOT_PATH . '/' . $clean)) {
        return rtrim(BASE_URL, '/') . '/' . $clean;
    }
    return null;
};

$logoUrl    = $assetUrl($settings->get('branding.logo', ''));
$faviconUrl = $assetUrl($settings->get('branding.favicon', ''));

$options = is_array($options ?? null) ? $options : [];
$truthy  = static fn($v) => $v === true || $v === 'yes' || $v === 1 || $v === '1';

$showPhoto         = $truthy($options['show_photo']         ?? false);
$showPositions     = $truthy($options['show_positions']     ?? false);
$showDivision      = $truthy($options['show_division']      ?? false);
$showGrades        = $truthy($options['show_grades']        ?? false);
$showInitials      = array_key_exists('show_initials', $options)       ? $truthy($options['show_initials'])       : true;
$showOtherSubjects = array_key_exists('show_other_subjects', $options) ? $truthy($options['show_other_subjects']) : false;
$showNextTerm      = $truthy($options['show_next_term']     ?? false);
$showCtComment     = !empty($options['ct_comment']) && $options['ct_comment'] !== 'no';
$showHmComment     = !empty($options['hm_comment']) && $options['hm_comment'] !== 'no';

$thStyle = "border:1px solid {$borderColor};padding:4px 4px;background:{$tableHeadBg};color:{$primaryColor};font-weight:700;letter-spacing:.3px;text-transform:uppercase;font-size:10px;";
$tdStyle = "border:1px solid {$borderColor};padding:4px 4px;text-align:center;font-size:11px;";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Batch Report Cards</title>
    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>">
    <?php endif; ?>
    <style>
        @page { size: A4; margin: 8mm 10mm; }
        body {
            font-family: 'Cambria', 'Book Antiqua', 'Palatino Linotype', serif;
            margin: 0;
            padding: 0;
            background: #e4e7eb;
            color: <?= htmlspecialchars($textColor) ?>;
            font-size: 12px;
        }
        .report-card {
            width: 210mm;
            min-height: 297mm;
            box-sizing: border-box;
            margin: 0 auto 20px;
            background: #fff;
            padding: 12mm 12mm;
            border-radius: 4px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
            page-break-after: always;
        }
        .report-card:last-child { page-break-after: auto; }
        .controls {
            width: 210mm;
            margin: 0 auto 8px;
            text-align: right;
            box-sizing: border-box;
        }
        .controls button, .controls a {
            padding: 6px 14px;
            background: <?= htmlspecialchars($primaryColor) ?>;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 3px;
            text-decoration: none;
            margin-left: 6px;
            font-size: 12px;
            font-weight: 700;
            font-family: Arial, sans-serif;
        }
        .controls a { background: #546e7a; }
        .school-name {
            font-family: 'Cambria', Georgia, serif;
            font-size: 45px;
            font-weight: 1000;
            color: <?= htmlspecialchars($accentColor) ?>;
            letter-spacing: 2px;
            text-transform: uppercase;
            line-height: 1.1;
            text-align: center;
            margin: 0 0 2px 0;
            white-space: nowrap;
        }
        .school-contact {
            font-size: 13px;
            color: <?= htmlspecialchars($primaryColor) ?>;
            font-weight: 700;
            line-height: 1.5;
            text-align: center;
        }
        .school-motto {
            font-size: 13px;
            color: <?= htmlspecialchars($mottoColor) ?>;
            font-style: italic;
            font-weight: 800;
            letter-spacing: 2px;
            margin-top: 3px;
            text-transform: uppercase;
            text-align: center;
        }
        .report-title {
            text-align: center;
            font-family: 'Cambria', Georgia, serif;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 3px;
            text-transform: uppercase;
            padding: 4px 0 3px 0;
            margin: 4px 0 10px 0;
            color: <?= htmlspecialchars($accentColor) ?>;
            border-bottom: 3px solid <?= htmlspecialchars($primaryColor) ?>;
        }
        @media print {
            body { background: #fff; }
            .report-card {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                margin: 0;
                page-break-after: always;
            }
            .controls { display: none; }
        }
    </style>
</head>
<body>

<div class="controls">
    <a href="<?= BASE_URL ?>/reports/academic/batch-report-cards">&larr; Back</a>
    <button onclick="window.print()">Print All</button>
</div>

<?php if (empty($allData)): ?>
    <div class="report-card" style="text-align:center;padding:70px 20px;">
        <h3 style="color:#666;font-family:Arial,sans-serif;margin-bottom:8px;">No report cards generated</h3>
        <p style="color:#888;font-family:Arial,sans-serif;font-size:13px;">No marks found for the selected criteria.</p>
    </div>
<?php else: ?>
    <?php foreach ($allData as $data): ?>
        <?php
            $student         = is_array($data['student'] ?? null)          ? $data['student']          : [];
            $exams           = is_array($data['exams'] ?? null)            ? $data['exams']            : [];
            $subjects        = is_array($data['subjects'] ?? null)         ? $data['subjects']         : [];
            $marksByExam     = is_array($data['marks_by_exam'] ?? null)    ? $data['marks_by_exam']    : [];
            $examTotals      = is_array($data['exam_totals'] ?? null)      ? $data['exam_totals']      : [];
            $subjectAverages = is_array($data['subject_averages'] ?? null) ? $data['subject_averages'] : [];
            $gradingSystem   = is_array($data['grading_system'] ?? null)   ? $data['grading_system']   : null;

            $totalScore        = (float)($data['total_score']         ?? 0);
            $totalAggregate    = (float)($data['total_aggregate']     ?? 0);
            $gradedSubjects    = (int)  ($data['graded_subjects']     ?? 0);
            $nonGradedCount    = (int)  ($data['non_graded_count']    ?? 0);
            $bestSubjects      = is_array($data['best_subjects'] ?? null) ? $data['best_subjects'] : [];
            $bestSubjectsCount = (int)  ($data['best_subjects_count'] ?? $gradedSubjects);
            $divisionCode      = $data['division_code'] ?? null;
            $position          = $data['position']      ?? null;
            $classSize         = (int)  ($data['class_size'] ?? 0);
            $ctRemark          = $data['ct_remark'] ?? '';
            $nextTerm          = is_array($data['next_term'] ?? null) ? $data['next_term'] : null;

            $studentPhoto = $showPhoto ? $assetUrl($student['photo_path'] ?? null) : null;

            try {
                $divisions = (new \NexaT\Services\DivisionService())->getAll((int)($student['school_id'] ?? 0), true);
            } catch (\Throwable $e) {
                $divisions = [];
            }
            $gradingRules = is_array($gradingSystem['rules'] ?? null) ? $gradingSystem['rules'] : [];

            $contributingSubjectRows = [];
            $otherSubjectRows        = [];
            foreach ($subjects as $subj) {
                $sid = (int)($subj['id'] ?? 0);
                $sa  = $subjectAverages[$sid] ?? null;
                if ($sa && empty($sa['contributes'])) {
                    $otherSubjectRows[] = $subj;
                } else {
                    $contributingSubjectRows[] = $subj;
                }
            }
            $otherSubjectRows = $showOtherSubjects ? $otherSubjectRows : [];

            $displayExams   = array_slice($exams, 0, 3);
            $average        = $gradedSubjects > 0 ? round($totalScore / $gradedSubjects, 1) : 0;
            $totalExamCount = count($displayExams);
            $colspanBottom  = $totalExamCount * 2 + ($showGrades ? 1 : 0) + ($showInitials ? 2 : 1);
        ?>
        <div class="report-card">

            <table style="width:100%;border-collapse:collapse;margin-bottom:2px;">

                <tr>
                    <td colspan="3" style="padding:0 0 4px 0;text-align:center;">
                        <h1 class="school-name"><?= htmlspecialchars($schoolName) ?></h1>
                    </td>
                </tr>

                <tr>
                    <td style="width:110px;vertical-align:top;padding:4px 0 0 0;text-align:left;">
                        <?php if ($logoUrl): ?>
                            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo"
                                 style="width:100px;height:100px;object-fit:contain;display:block;">
                        <?php endif; ?>
                    </td>

                    <td style="vertical-align:top;padding:4px 10px 0 10px;text-align:center;">
                        <?php if ($schoolPoBox): ?>
                            <div class="school-contact"><?= htmlspecialchars($schoolPoBox) ?></div>
                        <?php endif; ?>

                        <?php if ($schoolPhone): ?>
                            <div class="school-contact">Tel: <?= htmlspecialchars($schoolPhone) ?></div>
                        <?php endif; ?>

                        <?php if ($schoolEmail): ?>
                            <div class="school-contact">Email: <?= htmlspecialchars($schoolEmail) ?></div>
                        <?php endif; ?>

                        <?php if ($schoolMotto): ?>
                            <div class="school-motto"><?= htmlspecialchars($schoolMotto) ?></div>
                        <?php endif; ?>
                    </td>

                    <td style="width:110px;vertical-align:top;padding:4px 0 0 0;text-align:right;">
                        <?php if ($studentPhoto): ?>
                            <img src="<?= htmlspecialchars($studentPhoto) ?>" alt="Student Photo"
                                 style="width:95px;height:115px;object-fit:cover;display:block;margin-left:auto;border:1px solid #ccc;border-radius:2px;">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <div class="report-title">
                <?= htmlspecialchars($options['report_name'] ?? 'END OF TERM REPORT') ?>
            </div>

            <table style="width:100%;border-collapse:collapse;font-size:13px;font-weight:700;margin-bottom:10px;line-height:1.65;">
                <tr>
                    <td style="padding:1px 0;width:52%;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:120px;">NAME :</span>
                        <?= htmlspecialchars(strtoupper(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))) ?>
                    </td>
                    <td style="padding:1px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:100px;">REG NO :</span>
                        <?= htmlspecialchars($student['admission_number'] ?? '-') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:1px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:120px;">ACADEMIC YEAR :</span>
                        <?= htmlspecialchars($academicYearName ?? '') ?>
                    </td>
                    <td style="padding:1px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:100px;">TERM :</span>
                        <?= htmlspecialchars($termName ?? '') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:1px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:120px;">CLASS :</span>
                        <?= htmlspecialchars($student['class_name'] ?? '-') ?>
                    </td>
                    <td style="padding:1px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:100px;">STREAM :</span>
                        <?= htmlspecialchars($student['stream_name'] ?? 'N/A') ?>
                    </td>
                </tr>
            </table>

            <?php if (!empty($contributingSubjectRows) && !empty($displayExams)): ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="<?= $thStyle ?>text-align:left;padding-left:6px;width:20%;">SUBJECT</th>
                            <th colspan="<?= $totalExamCount * 2 ?>" style="<?= $thStyle ?>">MARKS</th>
                            <?php if ($showGrades): ?><th rowspan="2" style="<?= $thStyle ?>width:7%;">GRADE</th><?php endif; ?>
                            <th rowspan="2" style="<?= $thStyle ?><?= $showInitials ? 'width:20%;' : 'width:27%;' ?>">COMMENTS</th>
                            <?php if ($showInitials): ?><th rowspan="2" style="<?= $thStyle ?>width:8%;line-height:1.1;">TR'S<br>INITIALS</th><?php endif; ?>
                        </tr>
                        <tr>
                            <?php foreach ($displayExams as $exam): ?>
                                <th colspan="2" style="<?= $thStyle ?>"><?= htmlspecialchars(strtoupper($exam['name'])) ?> <span style="font-weight:400;text-transform:none;">(100%)</span></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th style="<?= $thStyle ?>">&nbsp;</th>
                            <?php foreach ($displayExams as $exam): ?>
                                <th style="<?= $thStyle ?>">MARK</th>
                                <th style="<?= $thStyle ?>">SCORE</th>
                            <?php endforeach; ?>
                            <?php if ($showGrades): ?><th style="<?= $thStyle ?>">&nbsp;</th><?php endif; ?>
                            <th style="<?= $thStyle ?>">&nbsp;</th>
                            <?php if ($showInitials): ?><th style="<?= $thStyle ?>">&nbsp;</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalMarksPerExam = array_fill_keys(array_column($displayExams, 'id'), 0); ?>
                        <?php foreach ($contributingSubjectRows as $subject): ?>
                            <?php $sa = $subjectAverages[$subject['id']] ?? ['grade' => '-', 'score' => 0, 'remark' => '', 'initials' => '']; ?>
                            <tr>
                                <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:4px 6px;text-align:left;font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;text-transform:uppercase;letter-spacing:.2px;"><?= htmlspecialchars($subject['name']) ?></td>
                                <?php foreach ($displayExams as $exam): ?>
                                    <?php
                                        $row      = $marksByExam[$exam['id']][$subject['id']] ?? null;
                                        $markVal  = $row !== null ? (int)round((float)$row['marks_obtained']) : null;
                                        $scoreVal = $row !== null ? (int)round((float)($row['score'] ?? 0)) : null;
                                        if ($markVal !== null) $totalMarksPerExam[$exam['id']] += $markVal;
                                    ?>
                                    <td style="<?= $tdStyle ?>"><?= $markVal !== null ? $markVal : '-' ?></td>
                                    <td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= $scoreVal !== null ? $scoreVal : '-' ?></td>
                                <?php endforeach; ?>
                                <?php if ($showGrades): ?><td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= htmlspecialchars($sa['grade'] ?? '-') ?></td><?php endif; ?>
                                <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:4px 6px;text-align:left;font-size:11px;text-transform:uppercase;color:<?= htmlspecialchars($mottoColor) ?>;font-weight:700;"><?= htmlspecialchars($sa['remark'] ?? '') ?></td>
                                <?php if ($showInitials): ?><td style="<?= $tdStyle ?>color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;letter-spacing:.5px;"><?= htmlspecialchars($sa['initials'] ?? '') ?></td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <tr style="background:<?= htmlspecialchars($tableHeadBg) ?>;font-weight:900;">
                            <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:5px 6px;text-align:left;color:<?= htmlspecialchars($primaryColor) ?>;text-transform:uppercase;letter-spacing:1px;">TOTAL</td>
                            <?php foreach ($displayExams as $exam): ?>
                                <?php $t = $examTotals[$exam['id']] ?? ['score' => 0]; ?>
                                <td style="<?= $tdStyle ?>"><?= (int)round($totalMarksPerExam[$exam['id']]) ?></td>
                                <td style="<?= $tdStyle ?>color:<?= htmlspecialchars($primaryColor) ?>;"><?= (int)round($t['score'] ?? 0) ?></td>
                            <?php endforeach; ?>
                            <?php if ($showGrades): ?><td style="<?= $tdStyle ?>color:<?= htmlspecialchars($primaryColor) ?>;"><?= (int)round($totalAggregate) ?></td><?php endif; ?>
                            <td colspan="<?= $showInitials ? 2 : 1 ?>" style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:5px 6px;text-align:center;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:900;">
                                <?php if ($showDivision): ?>DIV : <?= htmlspecialchars($divisionCode ?? '-') ?><?php endif; ?>
                            </td>
                        </tr>

                        <tr style="background:<?= htmlspecialchars($softBg) ?>;">
                            <td colspan="<?= $colspanBottom ?>" style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 10px;font-size:12px;font-weight:700;">
                                <span style="color:#546e7a;">TOTAL SCORE :</span>
                                <strong style="color:#c62828;"><?= (int)round($totalScore) ?></strong>
                                &nbsp;&nbsp;
                                <span style="color:#546e7a;">AVERAGE :</span>
                                <strong style="color:#1565c0;"><?= htmlspecialchars((string)$average) ?></strong>
                                &nbsp;&nbsp;
                                <span style="color:#546e7a;">AGGREGATE :</span>
                                <strong style="color:#c62828;"><?= (int)round($totalAggregate) ?></strong>
                                &nbsp;&nbsp;
                                <span style="color:#546e7a;">IN THE BEST :</span>
                                <strong style="color:#c62828;"><?= (int)$bestSubjectsCount ?></strong>
                                &nbsp;&nbsp;
                                <span style="color:#546e7a;">SUBJECTS</span>
                            </td>
                        </tr>

                        <?php if ($showPositions): ?>
                            <tr style="background:<?= htmlspecialchars($softBg) ?>;">
                                <td colspan="<?= $colspanBottom ?>" style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 10px;font-size:12px;font-weight:700;">
                                    <span style="color:#546e7a;">POS:</span>
                                    <strong style="color:#1565c0;"><?= $position ? (int)$position : '-' ?></strong>
                                    <span style="color:#546e7a;">OUTOF:</span>
                                    <strong style="color:#c62828;"><?= (int)$classSize ?></strong>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align:center;padding:40px 20px;color:#888;border:1px solid #ddd;">
                    <h3 style="font-family:Arial,sans-serif;margin-bottom:6px;">No marks found for the selected criteria</h3>
                    <p style="font-family:Arial,sans-serif;font-size:12px;">Please verify the selected exams contain marks for this student.</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($otherSubjectRows) && !empty($displayExams)): ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:10px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="<?= $thStyle ?>text-align:left;padding-left:6px;width:20%;">OTHER ASSESSMENT AREAS</th>
                            <th colspan="<?= $totalExamCount * 2 ?>" style="<?= $thStyle ?>">MARKS</th>
                            <?php if ($showGrades): ?><th rowspan="2" style="<?= $thStyle ?>width:7%;">GRADE</th><?php endif; ?>
                            <th rowspan="2" style="<?= $thStyle ?><?= $showInitials ? 'width:20%;' : 'width:27%;' ?>">COMMENTS</th>
                            <?php if ($showInitials): ?><th rowspan="2" style="<?= $thStyle ?>width:8%;line-height:1.1;">TR'S<br>INITIALS</th><?php endif; ?>
                        </tr>
                        <tr>
                            <?php foreach ($displayExams as $exam): ?>
                                <th colspan="2" style="<?= $thStyle ?>"><?= htmlspecialchars(strtoupper($exam['name'])) ?> <span style="font-weight:400;text-transform:none;">(100%)</span></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th style="<?= $thStyle ?>">&nbsp;</th>
                            <?php foreach ($displayExams as $exam): ?>
                                <th style="<?= $thStyle ?>">MARK</th>
                                <th style="<?= $thStyle ?>">SCORE</th>
                            <?php endforeach; ?>
                            <?php if ($showGrades): ?><th style="<?= $thStyle ?>">&nbsp;</th><?php endif; ?>
                            <th style="<?= $thStyle ?>">&nbsp;</th>
                            <?php if ($showInitials): ?><th style="<?= $thStyle ?>">&nbsp;</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($otherSubjectRows as $subject): ?>
                            <?php $sa = $subjectAverages[$subject['id']] ?? ['grade' => '-', 'remark' => '', 'initials' => '']; ?>
                            <tr>
                                <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:4px 6px;text-align:left;font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;text-transform:uppercase;letter-spacing:.2px;"><?= htmlspecialchars($subject['name']) ?></td>
                                <?php foreach ($displayExams as $exam): ?>
                                    <?php
                                        $row      = $marksByExam[$exam['id']][$subject['id']] ?? null;
                                        $markVal  = $row !== null ? (int)round((float)$row['marks_obtained']) : null;
                                        $scoreVal = $row !== null ? (int)round((float)($row['score'] ?? 0)) : null;
                                    ?>
                                    <td style="<?= $tdStyle ?>"><?= $markVal !== null ? $markVal : '-' ?></td>
                                    <td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= $scoreVal !== null ? $scoreVal : '-' ?></td>
                                <?php endforeach; ?>
                                <?php if ($showGrades): ?><td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= htmlspecialchars($sa['grade'] ?? '-') ?></td><?php endif; ?>
                                <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:4px 6px;text-align:left;font-size:11px;text-transform:uppercase;color:<?= htmlspecialchars($mottoColor) ?>;font-weight:700;"><?= htmlspecialchars($sa['remark'] ?? '') ?></td>
                                <?php if ($showInitials): ?><td style="<?= $tdStyle ?>color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;letter-spacing:.5px;"><?= htmlspecialchars($sa['initials'] ?? '') ?></td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($gradingRules)): ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:12px;">
                    <thead>
                        <tr><th colspan="2" style="<?= $thStyle ?>text-align:center;font-size:12px;letter-spacing:1.5px;">GRADING SCALE</th></tr>
                        <tr>
                            <th style="<?= $thStyle ?>text-align:left;padding-left:6px;width:20%;">Range</th>
                            <?php
                                $scaleRules = $gradingRules;
                                usort($scaleRules, fn($a, $b) => (float)$b['min_mark'] <=> (float)$a['min_mark']);
                            ?>
                            <?php foreach ($scaleRules as $rule): ?>
                                <th style="<?= $thStyle ?>text-align:center;"><?= htmlspecialchars((int)$rule['min_mark'] . '-' . (int)$rule['max_mark']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:4px 6px;font-weight:700;">Grade</td>
                            <?php foreach ($scaleRules as $rule): ?>
                                <td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= htmlspecialchars($rule['grade']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:4px 6px;font-weight:700;">Aggregate</td>
                            <?php foreach ($scaleRules as $rule): ?>
                                <td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= (int)round((float)$rule['score']) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($divisions)): ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:6px;">
                    <thead>
                        <tr>
                            <th style="<?= $thStyle ?>text-align:left;padding-left:6px;width:20%;">Division</th>
                            <?php foreach ($divisions as $div): ?>
                                <th style="<?= $thStyle ?>text-align:center;"><?= htmlspecialchars($div['name']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:4px 6px;font-weight:700;">Aggregate</td>
                            <?php foreach ($divisions as $div): ?>
                                <td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= (int)$div['min_aggregate'] ?> - <?= (int)$div['max_aggregate'] ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if ($showCtComment): ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:12px;">
                    <tr>
                        <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 10px;width:22%;font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;background:<?= htmlspecialchars($tableHeadBg) ?>;">Class Teacher's Comment:</td>
                        <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 10px;font-style:italic;color:#37474f;"><?= htmlspecialchars($ctRemark) ?></td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php if ($showHmComment): ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px;margin-top:6px;">
                    <tr>
                        <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 10px;width:22%;font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;background:<?= htmlspecialchars($tableHeadBg) ?>;">Head Teacher's Comment:</td>
                        <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 10px;font-style:italic;color:#37474f;min-height:20px;"></td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php if ($showNextTerm && $nextTerm): ?>
                <div style="margin-top:8px;font-size:12px;font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;">
                    Next Term Begins on:
                    <span style="color:#37474f;font-weight:600;"><?= htmlspecialchars(date('d, M Y', strtotime($nextTerm['start_date']))) ?></span>
                </div>
            <?php endif; ?>

            <table style="width:100%;border-collapse:collapse;margin-top:30px;font-size:11px;">
                <tr>
                    <td style="width:50%;border-top:1px solid <?= htmlspecialchars($borderColor) ?>;padding-top:5px;text-align:center;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;letter-spacing:.5px;text-transform:uppercase;">Class Teacher's Signature</td>
                    <td style="width:50%;border-top:1px solid <?= htmlspecialchars($borderColor) ?>;padding-top:5px;text-align:center;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;letter-spacing:.5px;text-transform:uppercase;">Head Teacher's Signature / Stamp</td>
                </tr>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>