<!-- File: /app/Views/reports/academic/report_card_view.php -->
<?php
$settingsService = new \NexaT\Core\SettingsService();
$schoolName    = $settingsService->get('school.name', 'School Name');
$schoolMotto   = $settingsService->get('school.motto', '');
$schoolAddress = $settingsService->get('school.physical_address', '');
$schoolPhone   = $settingsService->get('school.telephone', '');
$schoolEmail   = $settingsService->get('school.email', '');
$customLogo    = $settingsService->get('branding.logo', '');
$customFavicon = $settingsService->get('branding.favicon', '');

$resolveDiskPath = function (?string $relativePath): ?string {
    if (empty($relativePath)) return null;
    $relativePath = ltrim($relativePath, '/');
    foreach ([ROOT_PATH . '/public/' . $relativePath, ROOT_PATH . '/' . $relativePath] as $p) {
        if (file_exists($p)) return $p;
    }
    return null;
};
$resolveUrl = function (string $relativePath): string {
    $cleanPath = ltrim($relativePath, '/');
    if (file_exists(ROOT_PATH . '/public/' . $cleanPath) && !str_contains(BASE_URL, '/public')) {
        return rtrim(BASE_URL, '/') . '/public/' . $cleanPath;
    }
    return rtrim(BASE_URL, '/') . '/' . $cleanPath;
};
$logoUrl    = $resolveDiskPath($customLogo)    ? $resolveUrl($customLogo)    : null;
$faviconUrl = $resolveDiskPath($customFavicon) ? $resolveUrl($customFavicon) : null;

$student = $data['student'];
$exams   = $data['exams'];
$subjects = $data['subjects'];
$marksByExam = $data['marks_by_exam'];
$examTotals  = $data['exam_totals'];
$subjectAverages = $data['subject_averages'] ?? [];
$options     = $data['options'] ?? [];

$isColor = ($options['report_color'] ?? 'bw') === 'color';
$headerBg  = $isColor ? '#1D9BF0' : '#1a1a1a';
$headerFg  = '#ffffff';
$subBg     = $isColor ? '#E8F5FD' : '#f5f5f5';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Card - <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></title>
    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>" type="image/x-icon">
    <?php endif; ?>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; color: #222; }
        .report-card { max-width: 900px; margin: 0 auto; background: white; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 2px solid #1a1a1a; }
        .header { text-align: center; border-bottom: 3px double #1a1a1a; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 5px 0; font-size: 22px; text-transform: uppercase; letter-spacing: 1px; }
        .header .motto { font-style: italic; font-size: 13px; color: #555; }
        .header .contact { font-size: 11px; color: #666; margin-top: 5px; }
        .header .logo { max-height: 80px; margin-bottom: 10px; }
        .report-title { text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0; text-transform: uppercase; background: <?= $headerBg ?>; color: <?= $headerFg ?>; padding: 8px; }
        .student-info { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 20px; margin-bottom: 20px; font-size: 13px; }
        .student-info .label { font-weight: bold; display: inline-block; min-width: 120px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 15px; }
        table th { background: <?= $headerBg ?>; color: <?= $headerFg ?>; padding: 6px 4px; text-align: center; border: 1px solid #333; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        table td { padding: 6px 6px; border: 1px solid #ccc; text-align: center; }
        table td.subject { text-align: left; font-weight: bold; }
        table tr:nth-child(even) td { background: #f9f9f9; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; margin: 20px 0; padding: 15px; background: #f5f5f5; border: 1px solid #ddd; }
        .summary-item { text-align: center; }
        .summary-item .label { font-size: 11px; color: #666; text-transform: uppercase; }
        .summary-item .value { font-size: 16px; font-weight: bold; color: #1a1a1a; margin-top: 4px; }
        .remarks-section { margin-top: 20px; padding: 15px; border: 1px solid #ddd; }
        .remarks-section h6 { margin: 0 0 8px; font-size: 12px; text-transform: uppercase; color: #555; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; font-size: 12px; }
        .signature-box { border-top: 1px solid #333; padding-top: 5px; text-align: center; }
        .print-controls { max-width: 900px; margin: 0 auto 15px; text-align: right; }
        .print-controls .btn { padding: 8px 20px; background: #1a1a1a; color: white; border: none; cursor: pointer; font-size: 14px; border-radius: 4px; text-decoration: none; display: inline-block; margin-left: 5px; }
        @media print {
            body { background: white; padding: 0; }
            .report-card { box-shadow: none; border: none; padding: 15px; max-width: 100%; }
            .print-controls, .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="print-controls no-print">
    <a href="<?= BASE_URL ?>/reports/academic/report-cards" class="btn" style="background:#555;">Back</a>
    <button onclick="window.print()" class="btn">🖨 Print</button>
</div>

<div class="report-card">
    <div class="header">
        <?php if ($logoUrl): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" class="logo" alt="Logo">
        <?php endif; ?>
        <h1><?= htmlspecialchars($schoolName) ?></h1>
        <?php if ($schoolMotto): ?><div class="motto">"<?= htmlspecialchars($schoolMotto) ?>"</div><?php endif; ?>
        <div class="contact">
            <?php if ($schoolAddress): ?><?= htmlspecialchars($schoolAddress) ?> | <?php endif; ?>
            <?php if ($schoolPhone): ?>Tel: <?= htmlspecialchars($schoolPhone) ?> | <?php endif; ?>
            <?php if ($schoolEmail): ?><?= htmlspecialchars($schoolEmail) ?><?php endif; ?>
        </div>
    </div>

    <div class="report-title"><?= htmlspecialchars($options['report_name'] ?? 'END OF TERM REPORT') ?></div>

    <div class="student-info">
        <div><span class="label">NAME:</span> <?= htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']) ?></div>
        <div><span class="label">REG NO:</span> <?= htmlspecialchars($student['admission_number'] ?? '-') ?></div>
        <div><span class="label">ACADEMIC YEAR:</span> <?= htmlspecialchars($academicYearName ?? '') ?></div>
        <div><span class="label">TERM:</span> <?= htmlspecialchars($termName ?? '') ?></div>
        <div><span class="label">CLASS:</span> <?= htmlspecialchars($student['class_name'] ?? '-') ?></div>
        <div><span class="label">STREAM:</span> <?= htmlspecialchars($student['stream_name'] ?? 'N/A') ?></div>
    </div>

    <?php if (!empty($subjects) && !empty($exams)): ?>
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="text-align:left;">SUBJECT</th>
                    <?php foreach ($exams as $exam): ?>
                        <th colspan="2"><?= htmlspecialchars($exam['name']) ?></th>
                    <?php endforeach; ?>
                    <th colspan="2">AVERAGE</th>
                </tr>
                <tr>
                    <?php foreach ($exams as $exam): ?>
                        <th>MARK</th>
                        <th>GRADE</th>
                    <?php endforeach; ?>
                    <th>MARK</th>
                    <th>GRADE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                    <?php
                        // Use pre-computed average + grade from the service
                        $sa       = $subjectAverages[$subject['id']] ?? ['mark' => 0, 'grade' => '-'];
                        $avg      = $sa['mark'];
                        $avgGrade = $sa['grade'];
                    ?>
                    <tr>
                        <td class="subject"><?= htmlspecialchars($subject['name']) ?></td>

                        <?php foreach ($exams as $exam): ?>
                            <?php $row = $marksByExam[$exam['id']][$subject['id']] ?? null; ?>
                            <td><?= $row ? htmlspecialchars($row['marks_obtained']) : '-' ?></td>
                            <td><?= $row ? htmlspecialchars($row['grade'] ?? '-') : '-' ?></td>
                        <?php endforeach; ?>

                        <td><strong><?= $avg > 0 ? htmlspecialchars($avg) : '-' ?></strong></td>
                        <td><strong><?= htmlspecialchars($avgGrade) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Summary per exam -->
        <div class="summary">
            <?php foreach ($exams as $exam): 
                $t = $examTotals[$exam['id']] ?? ['total'=>0,'average'=>0,'grade'=>'-']; ?>
                <div class="summary-item">
                    <div class="label"><?= htmlspecialchars($exam['name']) ?> Total</div>
                    <div class="value"><?= htmlspecialchars($t['total']) ?></div>
                </div>
                <div class="summary-item">
                    <div class="label"><?= htmlspecialchars($exam['name']) ?> Average</div>
                    <div class="value"><?= htmlspecialchars($t['average']) ?>%</div>
                </div>
                <div class="summary-item">
                    <div class="label"><?= htmlspecialchars($exam['name']) ?> Grade</div>
                    <div class="value"><?= htmlspecialchars($t['grade']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($options['hm_comment']) && $options['hm_comment'] !== 'no'): ?>
            <div class="remarks-section">
                <h6>Head Teacher's Comment:</h6>
                <p style="min-height: 40px; margin: 0; border-bottom: 1px dotted #ccc;"></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($options['ct_comment']) && $options['ct_comment'] !== 'no'): ?>
            <div class="remarks-section">
                <h6>Class Teacher's Comment:</h6>
                <p style="min-height: 40px; margin: 0; border-bottom: 1px dotted #ccc;"></p>
            </div>
        <?php endif; ?>

        <div class="signatures">
            <div class="signature-box">Class Teacher's Signature</div>
            <div class="signature-box">Head Teacher's Signature / Stamp</div>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <h3>No marks found for the selected criteria</h3>
            <p>Please verify the selected exams contain marks for this student.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>