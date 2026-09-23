<?php
//File: app/Views/reports/academic/batch_report_cards_view.php
$settings = new \NexaT\Core\SettingsService();

$schoolName    = $settings->get('school.name', 'School Name');
$schoolMotto   = $settings->get('school.motto', '');
$schoolPoBox   = $settings->get('school.po_box', '');
$schoolAddress = $settings->get('school.physical_address', '');
$schoolPhone   = $settings->get('school.telephone', '');
$schoolEmail   = $settings->get('school.email', '');

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

$options = $options ?? [];
$truthy  = static fn($v) => $v === true || $v === 'yes' || $v === 1 || $v === '1';

$showPhoto     = $truthy($options['show_photo']     ?? false);
$showPositions = $truthy($options['show_positions'] ?? false);
$showDivision  = $truthy($options['show_division']  ?? false);
$showGrades    = $truthy($options['show_grades']    ?? false);
$showInitials  = array_key_exists('show_initials', $options) ? $truthy($options['show_initials']) : true;
$showNextTerm  = $truthy($options['show_next_term'] ?? false);
$showCtComment = !empty($options['ct_comment']) && $options['ct_comment'] !== 'no';
$showHmComment = !empty($options['hm_comment']) && $options['hm_comment'] !== 'no';

$thStyle = "border:1px solid {$borderColor};padding:7px 5px;background:{$tableHeadBg};color:{$primaryColor};font-weight:700;letter-spacing:.4px;text-transform:uppercase;";
$tdStyle = "border:1px solid {$borderColor};padding:6px 5px;text-align:center;";
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
        @page { size: A4; margin: 10mm 12mm; }
        body { font-family: 'Cambria', 'Book Antiqua', 'Palatino Linotype', serif; margin: 0; padding: 0; background: #e4e7eb; color: <?= htmlspecialchars($textColor) ?>; font-size: 13px; }
        .report-card { width: 210mm; min-height: 297mm; box-sizing: border-box; margin: 0 auto 20px; background: #fff; padding: 18mm 20mm; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,.08); page-break-after: always; }
        .report-card:last-child { page-break-after: auto; }
        .controls { width: 210mm; margin: 0 auto 12px; text-align: right; box-sizing: border-box; }
        .controls button, .controls a { padding: 8px 18px; background: <?= htmlspecialchars($primaryColor) ?>; color: #fff; border: none; cursor: pointer; border-radius: 4px; text-decoration: none; margin-left: 6px; font-size: 12px; font-weight: 700; font-family: Arial, sans-serif; }
        .controls a { background: #546e7a; }
        @media print { body { background: #fff; } .report-card { width: 100%; min-height: auto; box-shadow: none; border-radius: 0; padding: 0; margin: 0; page-break-after: always; } .controls { display: none; } }
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
            $student         = $data['student'];
            $exams           = $data['exams'] ?? [];
            $subjects        = $data['subjects'] ?? [];
            $marksByExam     = $data['marks_by_exam'] ?? [];
            $examTotals      = $data['exam_totals'] ?? [];
            $subjectAverages = $data['subject_averages'] ?? [];

            $totalScore     = $data['total_score']     ?? 0;
            $totalAggregate = $data['total_aggregate'] ?? 0;
            $divisionCode   = $data['division_code']   ?? null;
            $position       = $data['position']        ?? null;
            $classSize      = $data['class_size']      ?? 0;
            $ctRemark       = $data['ct_remark']       ?? '';
            $nextTerm       = $data['next_term']       ?? null;

            $studentPhoto = $showPhoto ? $assetUrl($student['photo_path'] ?? null) : null;

            $displayExams = array_slice($exams, 0, 3);
            $subjectCount = count($subjects);
            $average      = $subjectCount > 0 ? round($totalScore / $subjectCount) : 0;

            $totalMarksPerExam = array_fill_keys(array_column($displayExams, 'id'), 0);

            $summaryCells = [
                ['Total Score', (int)round($totalScore)],
                ['Average',     (int)$average],
                ['Aggregate',   (int)round($totalAggregate)],
            ];
        ?>
        <div class="report-card">

            <div style="text-align:center;margin-bottom:6px;">
                <h1 style="margin:0;font-family:'Cambria',Georgia,serif;font-size:26px;font-weight:900;color:<?= htmlspecialchars($accentColor) ?>;letter-spacing:2px;text-transform:uppercase;line-height:1.2;">
                    <?= htmlspecialchars($schoolName) ?>
                </h1>
            </div>

            <table style="width:100%;border-collapse:collapse;margin-bottom:2px;">
                <tr>
                    <td style="width:110px;vertical-align:bottom;padding:0;">
                        <?php if ($logoUrl): ?>
                            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo" style="width:95px;height:95px;object-fit:contain;display:block;">
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;vertical-align:middle;padding:0 6px;">
                        <div style="font-size:12px;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;line-height:1.5;letter-spacing:.2px;">
                            <?php if ($schoolPoBox): ?><div><?= htmlspecialchars($schoolPoBox) ?></div><?php endif; ?>
                            <?php if ($schoolPhone): ?><div>Tel: <?= htmlspecialchars($schoolPhone) ?></div><?php endif; ?>
                            <?php if ($schoolEmail): ?><div>EMAIL: <?= htmlspecialchars($schoolEmail) ?></div><?php endif; ?>
                        </div>

                        <?php if ($schoolMotto): ?>
                            <div style="font-size:11px;color:<?= htmlspecialchars($mottoColor) ?>;font-style:italic;font-weight:700;letter-spacing:1.8px;margin-top:6px;text-transform:uppercase;">
                                &ldquo;<?= htmlspecialchars($schoolMotto) ?>&rdquo;
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="width:110px;vertical-align:bottom;padding:0;text-align:right;">
                        <?php if ($studentPhoto): ?>
                            <img src="<?= htmlspecialchars($studentPhoto) ?>" alt="Student Photo" style="width:95px;height:110px;object-fit:cover;display:block;margin-left:auto;border:1px solid #ccc;border-radius:3px;">
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <div style="text-align:center;font-family:'Cambria',Georgia,serif;font-size:16px;font-weight:900;letter-spacing:3.5px;text-transform:uppercase;padding:8px 0;margin:14px 0 14px;color:<?= htmlspecialchars($primaryColor) ?>;border-top:2px double <?= htmlspecialchars($primaryColor) ?>;border-bottom:2px double <?= htmlspecialchars($primaryColor) ?>;background:<?= htmlspecialchars($softBg) ?>;">
                <?= htmlspecialchars($options['report_name'] ?? 'END OF TERM REPORT CARD') ?>
            </div>

            <table style="width:100%;border-collapse:collapse;font-size:13px;font-weight:700;margin-bottom:14px;line-height:1.7;">
                <tr>
                    <td style="padding:2px 0;width:52%;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:125px;">NAME :</span>
                        <?= htmlspecialchars(strtoupper(($student['first_name'] ?? '') . ' ' . ($student['middle_name'] ?? '') . ' ' . ($student['last_name'] ?? ''))) ?>
                    </td>
                    <td style="padding:2px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:120px;">REG NO :</span>
                        <?= htmlspecialchars($student['admission_number'] ?? '-') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:2px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:125px;">ACADEMIC YEAR :</span>
                        <?= htmlspecialchars($academicYearName ?? '') ?>
                    </td>
                    <td style="padding:2px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:120px;">TERM :</span>
                        <?= htmlspecialchars($termName ?? '') ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:2px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:125px;">CLASS :</span>
                        <?= htmlspecialchars($student['class_name'] ?? '-') ?>
                    </td>
                    <td style="padding:2px 0;vertical-align:top;">
                        <span style="color:<?= htmlspecialchars($primaryColor) ?>;display:inline-block;min-width:120px;">STREAM :</span>
                        <?= htmlspecialchars($student['stream_name'] ?? 'N/A') ?>
                    </td>
                </tr>
            </table>

            <?php if (!empty($subjects) && !empty($displayExams)): ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="<?= $thStyle ?>text-align:left;padding-left:8px;font-size:10px;width:22%;">SUBJECT</th>
                            <th colspan="<?= count($displayExams) * 2 ?>" style="<?= $thStyle ?>font-size:11px;letter-spacing:1.8px;">MARKS</th>
                            <?php if ($showGrades): ?><th rowspan="2" style="<?= $thStyle ?>font-size:10px;width:8%;">GRADE</th><?php endif; ?>
                            <th rowspan="2" style="<?= $thStyle ?>font-size:10px;<?= $showInitials ? 'width:18%;' : 'width:24%;' ?>">COMMENTS</th>
                            <?php if ($showInitials): ?><th rowspan="2" style="<?= $thStyle ?>font-size:10px;width:9%;line-height:1.1;">TR'S<br>INITIALS</th><?php endif; ?>
                        </tr>
                        <tr>
                            <?php foreach ($displayExams as $exam): ?>
                                <th colspan="2" style="<?= $thStyle ?>font-size:10px;letter-spacing:.8px;"><?= htmlspecialchars(strtoupper($exam['name'])) ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th style="<?= $thStyle ?>height:3px;padding:1px;"></th>
                            <?php foreach ($displayExams as $exam): ?>
                                <th style="<?= $thStyle ?>font-size:9px;letter-spacing:.5px;width:9%;">MARK</th>
                                <th style="<?= $thStyle ?>font-size:9px;letter-spacing:.5px;width:9%;">SCORE</th>
                            <?php endforeach; ?>
                            <?php if ($showGrades): ?><th style="<?= $thStyle ?>font-size:9px;"></th><?php endif; ?>
                            <th style="<?= $thStyle ?>font-size:9px;"></th>
                            <?php if ($showInitials): ?><th style="<?= $thStyle ?>font-size:9px;"></th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $subject): ?>
                            <?php $sa = $subjectAverages[$subject['id']] ?? ['grade' => '-', 'remark' => '', 'initials' => '']; ?>
                            <tr>
                                <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 8px;text-align:left;font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;text-transform:uppercase;letter-spacing:.3px;">
                                    <?= htmlspecialchars($subject['name']) ?>
                                </td>
                                <?php foreach ($displayExams as $exam): ?>
                                    <?php
                                        $row      = $marksByExam[$exam['id']][$subject['id']] ?? null;
                                        $markVal  = $row !== null ? (int)round((float)$row['marks_obtained']) : null;
                                        $scoreVal = $row !== null ? (int)round((float)($row['score'] ?? 0)) : null;
                                        if ($markVal !== null) {
                                            $totalMarksPerExam[$exam['id']] += $markVal;
                                        }
                                    ?>
                                    <td style="<?= $tdStyle ?>font-size:12px;"><?= $markVal !== null ? $markVal : '-' ?></td>
                                    <td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= $scoreVal !== null ? $scoreVal : '-' ?></td>
                                <?php endforeach; ?>
                                <?php if ($showGrades): ?>
                                    <td style="<?= $tdStyle ?>font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;"><?= htmlspecialchars($sa['grade'] ?? '-') ?></td>
                                <?php endif; ?>
                                <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:6px 6px;text-align:left;font-size:10px;text-transform:uppercase;color:<?= htmlspecialchars($mottoColor) ?>;font-weight:700;letter-spacing:.3px;">
                                    <?= htmlspecialchars($sa['remark'] ?? '') ?>
                                </td>
                                <?php if ($showInitials): ?>
                                    <td style="<?= $tdStyle ?>color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;letter-spacing:.8px;font-size:10.5px;"><?= htmlspecialchars($sa['initials'] ?? '') ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>

                        <tr style="background:<?= htmlspecialchars($tableHeadBg) ?>;font-weight:900;">
                            <td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:7px 8px;text-align:left;color:<?= htmlspecialchars($primaryColor) ?>;text-transform:uppercase;letter-spacing:1.2px;">TOTAL</td>
                            <?php foreach ($displayExams as $exam): ?>
                                <?php $t = $examTotals[$exam['id']] ?? ['score' => 0]; ?>
                                <td style="<?= $tdStyle ?>font-size:12px;"><?= (int)round($totalMarksPerExam[$exam['id']]) ?></td>
                                <td style="<?= $tdStyle ?>color:<?= htmlspecialchars($primaryColor) ?>;font-size:12px;"><?= (int)round($t['score'] ?? 0) ?></td>
                            <?php endforeach; ?>
                            <?php if ($showGrades): ?>
                                <td style="<?= $tdStyle ?>color:<?= htmlspecialchars($primaryColor) ?>;font-size:12px;"><?= (int)round($totalAggregate) ?></td>
                            <?php endif; ?>
                            <td colspan="<?= $showInitials ? 2 : 1 ?>" style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:7px 8px;text-align:center;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:900;letter-spacing:.8px;font-size:11px;">
                                <?php if ($showDivision): ?>DIV : <?= htmlspecialchars($divisionCode ?? '-') ?><?php endif; ?>
                                <?php if ($showDivision && $showPositions): ?>&nbsp;&nbsp;<span style="color:#90a4ae;">|</span>&nbsp;&nbsp;<?php endif; ?>
                                <?php if ($showPositions): ?>POS : <?= $position ? (int)$position : '-' ?><?= $classSize ? ' / ' . (int)$classSize : '' ?><?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table style="width:100%;border-collapse:collapse;margin-top:14px;font-size:12px;font-weight:700;border:1.2px solid <?= htmlspecialchars($borderColor) ?>;background:<?= htmlspecialchars($softBg) ?>;">
                    <tr>
                        <?php foreach ($summaryCells as [$label, $value]): ?>
                            <td style="padding:10px 12px;border-right:1px solid <?= htmlspecialchars($borderColor) ?>;text-align:center;">
                                <div style="font-size:9px;color:#546e7a;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px;"><?= $label ?></div>
                                <div style="font-size:16px;color:<?= htmlspecialchars($primaryColor) ?>;"><?= $value ?></div>
                            </td>
                        <?php endforeach; ?>
                        <?php if ($showDivision): ?>
                            <td style="padding:10px 12px;border-right:1px solid <?= htmlspecialchars($borderColor) ?>;text-align:center;">
                                <div style="font-size:9px;color:#546e7a;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px;">Division</div>
                                <div style="font-size:16px;color:<?= htmlspecialchars($primaryColor) ?>;"><?= htmlspecialchars($divisionCode ?? '-') ?></div>
                            </td>
                        <?php endif; ?>
                        <?php if ($showPositions): ?>
                            <td style="padding:10px 12px;border-right:1px solid <?= htmlspecialchars($borderColor) ?>;text-align:center;">
                                <div style="font-size:9px;color:#546e7a;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px;">Position</div>
                                <div style="font-size:16px;color:<?= htmlspecialchars($primaryColor) ?>;"><?= $position ? (int)$position : '-' ?><span style="font-size:10.5px;color:#546e7a;"> / <?= $classSize ?: '-' ?></span></div>
                            </td>
                        <?php endif; ?>
                        <td style="padding:10px 12px;text-align:center;">
                            <div style="font-size:9px;color:#546e7a;letter-spacing:1px;text-transform:uppercase;margin-bottom:2px;">Subjects</div>
                            <div style="font-size:16px;color:<?= htmlspecialchars($primaryColor) ?>;"><?= $subjectCount ?></div>
                        </td>
                    </tr>
                </table>

                <?php if ($showCtComment && $ctRemark !== ''): ?>
                    <table style="width:100%;border-collapse:collapse;margin-top:14px;">
                        <tr><td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:0;">
                            <div style="background:<?= htmlspecialchars($tableHeadBg) ?>;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;padding:6px 10px;font-size:10px;text-transform:uppercase;border-bottom:1px solid <?= htmlspecialchars($borderColor) ?>;letter-spacing:1px;">Class Teacher's Comment</div>
                            <div style="padding:10px 10px;font-size:11.5px;min-height:24px;font-style:italic;color:#37474f;line-height:1.4;"><?= htmlspecialchars($ctRemark) ?></div>
                        </td></tr>
                    </table>
                <?php endif; ?>

                <?php if ($showHmComment): ?>
                    <table style="width:100%;border-collapse:collapse;margin-top:10px;">
                        <tr><td style="border:1px solid <?= htmlspecialchars($borderColor) ?>;padding:0;">
                            <div style="background:<?= htmlspecialchars($tableHeadBg) ?>;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;padding:6px 10px;font-size:10px;text-transform:uppercase;border-bottom:1px solid <?= htmlspecialchars($borderColor) ?>;letter-spacing:1px;">Head Teacher's Comment</div>
                            <div style="padding:10px 10px;font-size:11.5px;min-height:24px;"></div>
                        </td></tr>
                    </table>
                <?php endif; ?>

                <?php if ($showNextTerm && $nextTerm): ?>
                    <div style="margin-top:12px;padding:8px 12px;border:1px solid <?= htmlspecialchars($borderColor) ?>;font-size:11px;font-weight:700;color:<?= htmlspecialchars($primaryColor) ?>;background:<?= htmlspecialchars($softBg) ?>;letter-spacing:.5px;text-align:center;">
                        NEXT TERM BEGINS:
                        <span style="color:#37474f;font-weight:600;"><?= htmlspecialchars(date('d/m/Y', strtotime($nextTerm['start_date']))) ?></span>
                        &nbsp;&nbsp;&bull;&nbsp;&nbsp;
                        ENDS:
                        <span style="color:#37474f;font-weight:600;"><?= htmlspecialchars(date('d/m/Y', strtotime($nextTerm['end_date']))) ?></span>
                    </div>
                <?php endif; ?>

                <table style="width:100%;border-collapse:collapse;margin-top:35px;font-size:10px;">
                    <tr>
                        <td style="width:50%;border-top:1px solid <?= htmlspecialchars($borderColor) ?>;padding-top:6px;text-align:center;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;letter-spacing:.6px;text-transform:uppercase;">Class Teacher's Signature</td>
                        <td style="width:50%;border-top:1px solid <?= htmlspecialchars($borderColor) ?>;padding-top:6px;text-align:center;color:<?= htmlspecialchars($primaryColor) ?>;font-weight:700;letter-spacing:.6px;text-transform:uppercase;">Head Teacher's Signature / Stamp</td>
                    </tr>
                </table>

            <?php else: ?>
                <div style="text-align:center;padding:30px 20px;color:#888;">
                    <p style="font-family:Arial,sans-serif;font-size:12px;">No marks available for this student under the selected criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>