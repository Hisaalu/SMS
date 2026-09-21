<!-- File: /app/Views/reports/academic/batch_report_cards_view.php -->
<?php
$settingsService = new \NexaT\Core\SettingsService();

$schoolName    = $settingsService->get('school.name', 'School Name');
$schoolMotto   = $settingsService->get('school.motto', '');
$schoolPoBox   = $settingsService->get('school.po_box', '');
$schoolAddress = $settingsService->get('school.physical_address', '');
$schoolPhone   = $settingsService->get('school.telephone', '');
$schoolEmail   = $settingsService->get('school.email', '');

$customLogo    = $settingsService->get('branding.logo', '');
$customFavicon = $settingsService->get('branding.favicon', '');

$primaryColor = $settingsService->get('branding.primary_color', '#1a237e');
$accentColor  = $settingsService->get('branding.accent_color',  '#e91e63');
$mottoColor   = $settingsService->get('branding.motto_color',   '#b71c1c');
$textColor    = $settingsService->get('branding.text_color',    '#000000');
$borderColor  = '#1a1a1a';
$tableHeadBg  = '#eef1f7';
$softBg       = '#f8f9fb';

$resolveDiskPath = function (?string $p): ?string {
    if (empty($p)) return null;
    $p = ltrim($p, '/');
    foreach ([ROOT_PATH . '/public/' . $p, ROOT_PATH . '/' . $p] as $full) {
        if (file_exists($full)) return $full;
    }
    return null;
};
$resolveUrl = function (string $p): string {
    $c = ltrim($p, '/');
    if (file_exists(ROOT_PATH . '/public/' . $c) && !str_contains(BASE_URL, '/public')) {
        return rtrim(BASE_URL, '/') . '/public/' . $c;
    }
    return rtrim(BASE_URL, '/') . '/' . $c;
};
$logoUrl    = $resolveDiskPath($customLogo)    ? $resolveUrl($customLogo)    : null;
$faviconUrl = $resolveDiskPath($customFavicon) ? $resolveUrl($customFavicon) : null;

$options = $options ?? [];

// =================== OPTION FLAGS ===================
$truthy = function ($v) {
    return $v === true || $v === 'yes' || $v === 1 || $v === '1';
};

$showPhoto       = $truthy($options['show_photo']       ?? false);
$showPositions   = $truthy($options['show_positions']   ?? false);
$showDivision    = $truthy($options['show_division']    ?? false);
$showGrades      = $truthy($options['show_grades']      ?? false);
$showInitials    = array_key_exists('show_initials', $options)
                      ? $truthy($options['show_initials'])
                      : true;
$showNextTerm    = $truthy($options['show_next_term']   ?? false);
$showCtComment   = !empty($options['ct_comment']) && $options['ct_comment'] !== 'no';
$showHmComment   = !empty($options['hm_comment']) && $options['hm_comment'] !== 'no';
// =====================================================

$thStyle = "border: 1px solid {$borderColor}; padding: 8px 6px; background: {$tableHeadBg}; color: {$primaryColor}; font-weight: 700; letter-spacing: 0.6px; text-transform: uppercase;";
$tdStyle = "border: 1px solid {$borderColor}; padding: 8px 6px; text-align: center;";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Batch Report Cards</title>
    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>" type="image/x-icon">
    <?php endif; ?>
    <style>
        body {
            font-family: 'Cambria', 'Book Antiqua', 'Palatino Linotype', 'Times New Roman', serif;
            margin: 0;
            padding: 24px 16px;
            background: #dfe3e8;
            color: <?= htmlspecialchars($textColor) ?>;
            font-size: 14px;
        }
        .report-card {
            max-width: 1000px;
            margin: 0 auto 40px;
            background: #ffffff;
            padding: 40px 46px;
            border-radius: 6px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            page-break-after: always;
        }
        .report-card:last-child { page-break-after: auto; }
        .controls {
            max-width: 1000px;
            margin: 0 auto 18px;
            text-align: right;
        }
        .controls button, .controls a {
            padding: 10px 24px;
            background: <?= htmlspecialchars($primaryColor) ?>;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 8px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-family: Arial, sans-serif;
        }
        .controls a {
            background: #607d8b;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .report-card { box-shadow: none; border-radius: 0; padding: 20px 24px; }
            .controls { display: none; }
        }
    </style>
</head>
<body>

<div class="controls">
    <a href="<?= BASE_URL ?>/reports/academic/batch-report-cards">← Back</a>
    <button onclick="window.print()">Print All</button>
</div>

<?php if (empty($allData)): ?>
    <div class="report-card" style="text-align:center; padding: 80px 20px;">
        <h3 style="color:#888;">No report cards generated</h3>
        <p style="color:#aaa;">No marks found for the selected criteria.</p>
    </div>
<?php else: ?>
    <?php foreach ($allData as $data): ?>
        <?php
            $student         = $data['student'];
            $exams           = $data['exams'];
            $subjects        = $data['subjects'];
            $marksByExam     = $data['marks_by_exam'];
            $examTotals      = $data['exam_totals'];
            $subjectAverages = $data['subject_averages'] ?? [];
            $gradingSystem   = $data['grading_system'] ?? null;

            $totalScore     = $data['total_score']     ?? 0;
            $totalAggregate = $data['total_aggregate'] ?? 0;
            $divisionCode   = $data['division_code']   ?? null;
            $position       = $data['position']        ?? null;
            $classSize      = $data['class_size']      ?? 0;
            $ctRemark       = $data['ct_remark']       ?? '';
            $nextTerm       = $data['next_term']       ?? null;

            // Student photo
            $studentPhoto = null;
            if ($showPhoto && !empty($student['photo_path'])) {
                $clean = ltrim($student['photo_path'], '/');
                if (file_exists(ROOT_PATH . '/public/' . $clean)) {
                    $studentPhoto = rtrim(BASE_URL, '/') . '/public/' . $clean;
                } elseif (file_exists(ROOT_PATH . '/' . $clean)) {
                    $studentPhoto = rtrim(BASE_URL, '/') . '/' . $clean;
                }
            }

            $displayExams = array_slice($exams, 0, 3);
            $subjectCount = count($subjects);
            $average      = $subjectCount > 0 ? round($totalScore / $subjectCount, 0) : 0;
        ?>
        <div class="report-card">

            <!-- ==================== HEADER ==================== -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
                <tr>
                    <td style="width: 120px; vertical-align: middle; padding: 0;">
                        <?php if ($logoUrl): ?>
                            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo"
                                 style="width: 110px; height: 110px; object-fit: contain; display: block;">
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center; vertical-align: middle; padding: 0 10px;">
                        <h1 style="margin: 0; font-family: 'Cambria', Georgia, serif; font-size: 34px; font-weight: 900; color: <?= htmlspecialchars($accentColor) ?>; letter-spacing: 4px; text-transform: uppercase; line-height: 1.1;">
                            <?= htmlspecialchars($schoolName) ?>
                        </h1>

                        <div style="margin-top: 10px; font-size: 13px; color: <?= htmlspecialchars($primaryColor) ?>; font-weight: 700; line-height: 1.7; letter-spacing: 0.3px;">
                            <?php if ($schoolPoBox): ?>
                                <div><?= htmlspecialchars($schoolPoBox) ?></div>
                            <?php endif; ?>
                            <?php if ($schoolAddress): ?>
                                <div><?= htmlspecialchars($schoolAddress) ?></div>
                            <?php endif; ?>
                            <div>
                                <?php if ($schoolPhone): ?>
                                    Tel: <?= htmlspecialchars($schoolPhone) ?>
                                <?php endif; ?>
                                <?php if ($schoolPhone && $schoolEmail): ?>&nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php endif; ?>
                                <?php if ($schoolEmail): ?>
                                    Email: <?= htmlspecialchars($schoolEmail) ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($schoolMotto): ?>
                            <div style="font-size: 12px; color: <?= htmlspecialchars($mottoColor) ?>; font-style: italic; font-weight: 700; letter-spacing: 2.5px; margin-top: 10px; text-transform: uppercase;">
                                <?= htmlspecialchars($schoolMotto) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="width: 120px; vertical-align: top; padding: 0;">
                        <?php if ($studentPhoto): ?>
                            <img src="<?= htmlspecialchars($studentPhoto) ?>" alt="Student"
                                 style="width: 110px; height: 120px; object-fit: cover; display: block; margin: 0 auto;">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <!-- ==================== TITLE ==================== -->
            <div style="text-align: center; font-family: 'Cambria', Georgia, serif; font-size: 19px; font-weight: 900; letter-spacing: 5px; text-transform: uppercase; padding: 12px 0; margin: 22px 0 20px; color: <?= htmlspecialchars($primaryColor) ?>; border-top: 3px double <?= htmlspecialchars($primaryColor) ?>; border-bottom: 3px double <?= htmlspecialchars($primaryColor) ?>;">
                <?= htmlspecialchars($options['report_name'] ?? 'END OF TERM REPORT') ?>
            </div>

            <!-- ==================== STUDENT INFO ==================== -->
            <table style="width: 100%; border-collapse: collapse; font-size: 14px; font-weight: 700; margin-bottom: 22px; line-height: 1.9;">
                <tr>
                    <td style="padding: 4px 0; width: 50%; vertical-align: top;">
                        <span style="color: <?= htmlspecialchars($primaryColor) ?>; display: inline-block; min-width: 135px;">NAME:</span>
                        <?= htmlspecialchars(strtoupper($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name'])) ?>
                    </td>
                    <td style="padding: 4px 0; vertical-align: top;">
                        <span style="color: <?= htmlspecialchars($primaryColor) ?>; display: inline-block; min-width: 135px;">REG NO:</span>
                        <?= htmlspecialchars($student['admission_number'] ?? '-') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;">
                        <span style="color: <?= htmlspecialchars($primaryColor) ?>; display: inline-block; min-width: 135px;">ACADEMIC YEAR:</span>
                        <?= htmlspecialchars($academicYearName ?? '') ?>
                    </td>
                    <td style="padding: 4px 0; vertical-align: top;">
                        <span style="color: <?= htmlspecialchars($primaryColor) ?>; display: inline-block; min-width: 135px;">TERM:</span>
                        <?= htmlspecialchars($termName ?? '') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; vertical-align: top;">
                        <span style="color: <?= htmlspecialchars($primaryColor) ?>; display: inline-block; min-width: 135px;">CLASS:</span>
                        <?= htmlspecialchars($student['class_name'] ?? '-') ?>
                    </td>
                    <td style="padding: 4px 0; vertical-align: top;">
                        <span style="color: <?= htmlspecialchars($primaryColor) ?>; display: inline-block; min-width: 135px;">STREAM:</span>
                        <?= htmlspecialchars($student['stream_name'] ?? 'N/A') ?>
                    </td>
                </tr>
            </table>

            <!-- ==================== MARKS TABLE ==================== -->
            <?php if (!empty($subjects) && !empty($displayExams)): ?>

                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="<?= $thStyle ?> text-align: left; padding-left: 12px; font-size: 11px; width: 20%;">SUBJECT</th>
                            <th colspan="<?= count($displayExams) * 2 ?>" style="<?= $thStyle ?> font-size: 12px; letter-spacing: 3px;">
                                MARKS
                            </th>
                            <?php if ($showGrades): ?>
                                <th rowspan="2" style="<?= $thStyle ?> font-size: 11px; width: 9%;">GRADE</th>
                            <?php endif; ?>
                            <th rowspan="2" style="<?= $thStyle ?> font-size: 11px; <?= $showInitials ? 'width: 18%;' : 'width: 24%;' ?>">COMMENTS</th>
                            <?php if ($showInitials): ?>
                                <th rowspan="2" style="<?= $thStyle ?> font-size: 11px; width: 10%; line-height: 1.3;">TR'S<br>INITIALS</th>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <?php foreach ($displayExams as $exam): ?>
                                <th colspan="2" style="<?= $thStyle ?> font-size: 11px; letter-spacing: 1.2px;">
                                    <?= htmlspecialchars(strtoupper($exam['name'])) ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th style="<?= $thStyle ?> height: 6px;"></th>
                            <?php foreach ($displayExams as $exam): ?>
                                <th style="<?= $thStyle ?> font-size: 10px; letter-spacing: 1px;">MARK</th>
                                <th style="<?= $thStyle ?> font-size: 10px; letter-spacing: 1px;">SCORE</th>
                            <?php endforeach; ?>
                            <?php if ($showGrades): ?>
                                <th style="<?= $thStyle ?> font-size: 10px;">&nbsp;</th>
                            <?php endif; ?>
                            <th style="<?= $thStyle ?> font-size: 10px;">&nbsp;</th>
                            <?php if ($showInitials): ?>
                                <th style="<?= $thStyle ?> font-size: 10px;">&nbsp;</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalMarksPerExam = [];
                        foreach ($displayExams as $exam) $totalMarksPerExam[$exam['id']] = 0;
                        ?>
                        <?php foreach ($subjects as $subject): ?>
                            <?php
                                $sa = $subjectAverages[$subject['id']] ?? ['mark' => 0, 'grade' => '-', 'score' => 0, 'remark' => '', 'initials' => ''];
                            ?>
                            <tr>
                                <td style="border: 1px solid <?= htmlspecialchars($borderColor) ?>; padding: 9px 12px; text-align: left; font-weight: 700; color: <?= htmlspecialchars($primaryColor) ?>; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <?= htmlspecialchars($subject['name']) ?>
                                </td>

                                <?php foreach ($displayExams as $exam): ?>
                                    <?php
                                        $row      = $marksByExam[$exam['id']][$subject['id']] ?? null;
                                        $markVal  = $row ? (int)round((float)$row['marks_obtained']) : null;
                                        $scoreRow = $row ? (int)round((float)($row['score'] ?? 0)) : null;
                                        if ($markVal !== null) $totalMarksPerExam[$exam['id']] += $markVal;
                                    ?>
                                    <td style="<?= $tdStyle ?> font-size: 13px;">
                                        <?= $markVal !== null ? $markVal : '-' ?>
                                    </td>
                                    <td style="<?= $tdStyle ?> font-weight: 700; color: <?= htmlspecialchars($primaryColor) ?>;">
                                        <?= $scoreRow !== null ? $scoreRow : '-' ?>
                                    </td>
                                <?php endforeach; ?>

                                <?php if ($showGrades): ?>
                                    <td style="<?= $tdStyle ?> font-weight: 700; color: <?= htmlspecialchars($primaryColor) ?>;">
                                        <?= htmlspecialchars($sa['grade'] ?? '-') ?>
                                    </td>
                                <?php endif; ?>

                                <td style="border: 1px solid <?= htmlspecialchars($borderColor) ?>; padding: 9px 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: <?= htmlspecialchars($mottoColor) ?>; font-weight: 700; letter-spacing: 0.6px;">
                                    <?= htmlspecialchars($sa['remark'] ?? '') ?>
                                </td>

                                <?php if ($showInitials): ?>
                                    <td style="<?= $tdStyle ?> color: <?= htmlspecialchars($primaryColor) ?>; font-weight: 700; letter-spacing: 1.5px;">
                                        <?= htmlspecialchars($sa['initials'] ?? '') ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <!-- TOTAL ROW -->
                        <tr style="background: <?= htmlspecialchars($tableHeadBg) ?>; font-weight: 900;">
                            <td style="border: 1px solid <?= htmlspecialchars($borderColor) ?>; padding: 10px 12px; text-align: left; color: <?= htmlspecialchars($primaryColor) ?>; text-transform: uppercase; letter-spacing: 2px;">
                                TOTAL
                            </td>
                            <?php foreach ($displayExams as $exam): ?>
                                <?php $t = $examTotals[$exam['id']] ?? ['total'=>0, 'score'=>0]; ?>
                                <td style="<?= $tdStyle ?> font-size: 14px;">
                                    <?= (int)round($totalMarksPerExam[$exam['id']]) ?>
                                </td>
                                <td style="<?= $tdStyle ?> color: <?= htmlspecialchars($primaryColor) ?>; font-size: 14px;">
                                    <?= (int)round($t['score'] ?? 0) ?>
                                </td>
                            <?php endforeach; ?>

                            <?php if ($showGrades): ?>
                                <td style="<?= $tdStyle ?> color: <?= htmlspecialchars($primaryColor) ?>; font-size: 14px;">
                                    <?= (int)round($totalAggregate) ?>
                                </td>
                            <?php endif; ?>

                            <td colspan="<?= $showInitials ? 2 : 1 ?>" style="border: 1px solid <?= htmlspecialchars($borderColor) ?>; padding: 10px 12px; text-align: center; color: <?= htmlspecialchars($primaryColor) ?>; font-weight: 900; letter-spacing: 1.5px; font-size: 12px;">
                                <?php if ($showDivision): ?>
                                    DIV: <?= htmlspecialchars($divisionCode ?? '-') ?>
                                <?php endif; ?>
                                <?php if ($showDivision && $showPositions): ?>
                                    &nbsp;&nbsp;<span style="color:#b0bec5;">|</span>&nbsp;&nbsp;
                                <?php endif; ?>
                                <?php if ($showPositions): ?>
                                    POS: <?= $position ? (int)$position : '-' ?><?= $classSize ? ' / ' . (int)$classSize : '' ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- ==================== SUMMARY STRIP ==================== -->
                <table style="width: 100%; border-collapse: collapse; margin-top: 18px; font-size: 13px; font-weight: 700; border: 2px solid <?= htmlspecialchars($borderColor) ?>; background: <?= htmlspecialchars($softBg) ?>;">
                    <tr>
                        <td style="padding: 14px 16px; border-right: 1px solid <?= htmlspecialchars($borderColor) ?>; text-align: center;">
                            <div style="font-size: 10px; color: #607d8b; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 4px;">Total Score</div>
                            <div style="font-size: 18px; color: <?= htmlspecialchars($primaryColor) ?>;"><?= (int)round($totalScore) ?></div>
                        </td>
                        <td style="padding: 14px 16px; border-right: 1px solid <?= htmlspecialchars($borderColor) ?>; text-align: center;">
                            <div style="font-size: 10px; color: #607d8b; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 4px;">Average</div>
                            <div style="font-size: 18px; color: <?= htmlspecialchars($primaryColor) ?>;"><?= (int)$average ?></div>
                        </td>
                        <td style="padding: 14px 16px; border-right: 1px solid <?= htmlspecialchars($borderColor) ?>; text-align: center;">
                            <div style="font-size: 10px; color: #607d8b; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 4px;">Aggregate</div>
                            <div style="font-size: 18px; color: <?= htmlspecialchars($primaryColor) ?>;"><?= (int)round($totalAggregate) ?></div>
                        </td>
                        <?php if ($showDivision): ?>
                            <td style="padding: 14px 16px; border-right: 1px solid <?= htmlspecialchars($borderColor) ?>; text-align: center;">
                                <div style="font-size: 10px; color: #607d8b; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 4px;">Division</div>
                                <div style="font-size: 18px; color: <?= htmlspecialchars($primaryColor) ?>;"><?= htmlspecialchars($divisionCode ?? '-') ?></div>
                            </td>
                        <?php endif; ?>
                        <?php if ($showPositions): ?>
                            <td style="padding: 14px 16px; border-right: 1px solid <?= htmlspecialchars($borderColor) ?>; text-align: center;">
                                <div style="font-size: 10px; color: #607d8b; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 4px;">Position</div>
                                <div style="font-size: 18px; color: <?= htmlspecialchars($primaryColor) ?>;">
                                    <?= $position ? (int)$position : '-' ?><span style="font-size: 12px; color: #607d8b;"> / <?= $classSize ?: '-' ?></span>
                                </div>
                            </td>
                        <?php endif; ?>
                        <td style="padding: 14px 16px; text-align: center;">
                            <div style="font-size: 10px; color: #607d8b; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 4px;">Subjects</div>
                            <div style="font-size: 18px; color: <?= htmlspecialchars($primaryColor) ?>;"><?= (int)$subjectCount ?></div>
                        </td>
                    </tr>
                </table>

                <!-- ==================== CLASS TEACHER COMMENT ==================== -->
                <?php if ($showCtComment && !empty($ctRemark)): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 18px;">
                        <tr>
                            <td style="border: 1px solid <?= htmlspecialchars($borderColor) ?>; padding: 0;">
                                <div style="background: <?= htmlspecialchars($tableHeadBg) ?>; color: <?= htmlspecialchars($primaryColor) ?>; font-weight: 700; padding: 8px 14px; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid <?= htmlspecialchars($borderColor) ?>; letter-spacing: 1.5px;">
                                    Class Teacher's Comment
                                </div>
                                <div style="padding: 16px 14px; font-size: 12.5px; min-height: 34px; font-style: italic; color: #37474f; line-height: 1.6;">
                                    <?= htmlspecialchars($ctRemark) ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>

                <!-- ==================== HEAD TEACHER COMMENT ==================== -->
                <?php if ($showHmComment): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 12px;">
                        <tr>
                            <td style="border: 1px solid <?= htmlspecialchars($borderColor) ?>; padding: 0;">
                                <div style="background: <?= htmlspecialchars($tableHeadBg) ?>; color: <?= htmlspecialchars($primaryColor) ?>; font-weight: 700; padding: 8px 14px; font-size: 11px; text-transform: uppercase; border-bottom: 1px solid <?= htmlspecialchars($borderColor) ?>; letter-spacing: 1.5px;">
                                    Head Teacher's Comment
                                </div>
                                <div style="padding: 16px 14px; font-size: 12.5px; min-height: 34px;"></div>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>

                <!-- ==================== NEXT TERM ==================== -->
                <?php if ($showNextTerm && $nextTerm): ?>
                    <div style="margin-top: 16px; padding: 12px 16px; border: 1px solid <?= htmlspecialchars($borderColor) ?>; font-size: 12px; font-weight: 700; color: <?= htmlspecialchars($primaryColor) ?>; background: <?= htmlspecialchars($softBg) ?>; letter-spacing: 0.8px; text-align: center;">
                        NEXT TERM BEGINS:
                        <span style="color: #37474f; font-weight: 600;"><?= htmlspecialchars(date('d/m/Y', strtotime($nextTerm['start_date']))) ?></span>
                        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
                        ENDS:
                        <span style="color: #37474f; font-weight: 600;"><?= htmlspecialchars(date('d/m/Y', strtotime($nextTerm['end_date']))) ?></span>
                    </div>
                <?php endif; ?>

                <!-- ==================== SIGNATURES ==================== -->
                <table style="width: 100%; border-collapse: collapse; margin-top: 45px; font-size: 11px;">
                    <tr>
                        <td style="width: 50%; border-top: 1px solid <?= htmlspecialchars($borderColor) ?>; padding-top: 10px; text-align: center; color: <?= htmlspecialchars($primaryColor) ?>; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                            Class Teacher's Signature
                        </td>
                        <td style="width: 50%; border-top: 1px solid <?= htmlspecialchars($borderColor) ?>; padding-top: 10px; text-align: center; color: <?= htmlspecialchars($primaryColor) ?>; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                            Head Teacher's Signature / Stamp
                        </td>
                    </tr>
                </table>

            <?php else: ?>
                <div style="text-align: center; padding: 40px 20px; color: #999;">
                    <p>No marks available for this student.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>