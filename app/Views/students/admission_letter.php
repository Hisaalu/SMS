<?php
// File: /app/Views/students/admission_letter.php

$settings = new \NexaT\Core\SettingsService();

$schoolName  = $settings->get('school.name', 'School Name');
$schoolMotto = $settings->get('school.motto', '');
$schoolPhone = $settings->get('school.telephone', '');
$schoolEmail = $settings->get('school.email', '');
$schoolFax   = $settings->get('school.fax', '');

// Strip the scheme from the website URL for display purposes.
$schoolWebsite = trim((string) $settings->get('school.website', ''));
$schoolWebsite = preg_replace('#^https?://#i', '', $schoolWebsite);
$schoolWebsite = rtrim($schoolWebsite, '/');

$schoolPoBox = $settings->get('school.po_box', '');
if ($schoolPoBox === '') {
    $schoolPoBox = $settings->get('school.postal_address', '');
}

$primaryColor = $settings->get('branding.primary_color', '#1a237e');
$accentColor  = $settings->get('branding.accent_color',  '#e91e63');
$mottoColor   = $settings->get('branding.motto_color',   '#b71c1c');
$textColor    = $settings->get('branding.text_color',    '#111111');

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

$logoUrl      = $assetUrl($settings->get('branding.logo', ''));
$signatureUrl = $assetUrl($settings->get('branding.head_signature', ''));
$faviconUrl   = $assetUrl($settings->get('branding.favicon', ''));

$student    = is_array($student    ?? null) ? $student    : [];
$guardian   = is_array($guardian   ?? null) ? $guardian   : null;
$enrollment = is_array($enrollment ?? null) ? $enrollment : null;

$fullName = strtoupper(trim(
    ($student['first_name'] ?? '') . ' ' .
    ($student['middle_name'] ?? '') . ' ' .
    ($student['last_name'] ?? '')
));

$admissionDate = !empty($student['admission_date'])
    ? date('d M Y', strtotime($student['admission_date']))
    : ($generatedAt ?? date('d M Y'));

$className  = $enrollment['class_name']         ?? '—';
$streamName = $enrollment['stream_name']        ?? null;
$yearName   = $enrollment['academic_year_name'] ?? '—';

$guardianName = $guardian['full_name'] ?? '';
$guardianRel  = $guardian['relationship'] ?? 'Parent / Guardian';

$serial = $student['admission_number'] ?? '—';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Letter - <?= htmlspecialchars($fullName) ?></title>
    <?php if ($faviconUrl): ?>
        <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>">
    <?php endif; ?>
    <style>
        @page { size: A4; margin: 16mm 18mm; }

        * { box-sizing: border-box; }

        body {
            font-family: 'Cambria', 'Georgia', 'Book Antiqua', 'Palatino Linotype', serif;
            margin: 0;
            padding: 0;
            background: #e4e7eb;
            color: <?= htmlspecialchars($textColor) ?>;
            font-size: 13.5px;
            line-height: 1.55;
        }

        .letter {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            padding: 14mm 18mm 16mm 18mm;
            border-radius: 4px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, .08);
            position: relative;
            overflow: hidden;
        }

        /* ---------- Watermark: upright, centred ---------- */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 165mm;
            max-width: 85%;
            opacity: 0.06;
            pointer-events: none;
            z-index: 0;
            user-select: none;
        }

        .watermark img {
            width: 100%;
            height: auto;
            display: block;
        }

        .letter-body {
            position: relative;
            z-index: 1;
        }

        /* ---------- Letterhead ---------- */
        .letter-head {
            text-align: center;
            margin-bottom: 6px;
        }

        .letter-head .logo {
            width: 110px;
            height: 110px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        .letter-head h1 {
            margin: 0 0 4px 0;
            font-family: 'Cambria', 'Georgia', serif;
            font-size: 30px;
            font-weight: 900;
            color: <?= htmlspecialchars($primaryColor) ?>;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .letter-head .office {
            font-size: 13.5px;
            font-weight: 800;
            color: <?= htmlspecialchars($primaryColor) ?>;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .letter-head .contact {
            font-size: 12px;
            font-weight: 600;
            color: <?= htmlspecialchars($primaryColor) ?>;
            margin-bottom: 2px;
        }

        .letter-head .motto {
            font-size: 12px;
            color: <?= htmlspecialchars($mottoColor) ?>;
            font-style: italic;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .letter-head .head-rule {
            border-bottom: 2px solid <?= htmlspecialchars($primaryColor) ?>;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        /* ---------- Reference block ---------- */
        .ref-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .ref-grid td {
            padding: 3px 0;
            vertical-align: top;
        }

        .ref-grid .key {
            font-weight: 700;
            color: <?= htmlspecialchars($primaryColor) ?>;
            white-space: nowrap;
            padding-right: 6px;
        }

        .ref-grid .val {
            font-weight: 600;
        }

        /* ---------- Title ---------- */
        .letter-title {
            text-align: center;
            font-size: 15.5px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: <?= htmlspecialchars($textColor) ?>;
            margin: 20px 0 14px 0;
        }

        .salutation {
            margin: 4px 0 12px 0;
            font-weight: 600;
        }

        p { margin: 0 0 11px 0; text-align: justify; }

        /* ---------- Programme / offer line ---------- */
        .programme-line {
            text-align: center;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: <?= htmlspecialchars($primaryColor) ?>;
            margin: 12px 0 16px 0;
        }

        /* ---------- Roman numeral list (policy) ---------- */
        .policy-list {
            list-style: none;
            counter-reset: policy;
            margin: 6px 0 14px 0;
            padding-left: 24px;
            font-size: 13px;
        }

        .policy-list li {
            counter-increment: policy;
            position: relative;
            margin-bottom: 7px;
            padding-left: 4px;
            text-align: justify;
        }

        .policy-list li::before {
            content: counter(policy, lower-roman) ".";
            position: absolute;
            left: -22px;
            font-weight: 700;
            color: <?= htmlspecialchars($textColor) ?>;
        }

        .policy-list li strong { font-weight: 800; }

        /* ---------- Closing + signature ---------- */
        .closing { margin-top: 18px; }

        .signature-block {
            margin-top: 28px;
        }

        .signature-block .handwriting {
            height: 52px;
            margin-bottom: 4px;
        }

        .signature-block .handwriting img {
            max-height: 52px;
            max-width: 220px;
            object-fit: contain;
            display: block;
        }

        .signature-block .sig-rule {
            border-top: 1px solid <?= htmlspecialchars($primaryColor) ?>;
            padding-top: 6px;
            font-weight: 800;
            font-size: 13px;
            color: <?= htmlspecialchars($primaryColor) ?>;
            letter-spacing: 0.4px;
        }

        .signature-block .role {
            font-size: 12.5px;
            font-weight: 900;
            color: <?= htmlspecialchars($textColor) ?>;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* ---------- Footer ---------- */
        .letter-footer {
            margin-top: 24px;
            font-size: 11px;
            color: #546e7a;
            text-align: center;
            letter-spacing: 0.3px;
        }

        /* ---------- Print bar ---------- */
        .print-bar {
            width: 210mm;
            margin: 12px auto 6px;
            text-align: right;
            font-family: Arial, sans-serif;
        }

        .print-bar a,
        .print-bar button {
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
        }

        .print-bar a { background: #546e7a; }

        /* ---------- Print ---------- */
        @media print {
            body { background: #fff; }

            .letter {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                margin: 0;
            }

            .print-bar { display: none; }

            .watermark {
                opacity: 0.07 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <a href="<?= BASE_URL ?>/students/show?id=<?= (int)($student['id'] ?? 0) ?>">&larr; Back to Student</a>
    <button onclick="window.print()">Print Letter</button>
</div>

<div class="letter">

    <?php if ($logoUrl): ?>
        <div class="watermark" aria-hidden="true">
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="">
        </div>
    <?php endif; ?>

    <div class="letter-body">

        <!-- Letterhead -->
        <div class="letter-head">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo" class="logo">
            <?php endif; ?>

            <h1><?= htmlspecialchars($schoolName) ?></h1>
            <div class="office">Office of the Academic Registrar</div>

            <!-- Line 1: address + phone (+ fax) -->
            <?php if ($schoolPoBox || $schoolPhone || $schoolFax): ?>
                <div class="contact">
                    <?php if ($schoolPoBox): ?>
                        <?= htmlspecialchars($schoolPoBox) ?>
                    <?php endif; ?>
                    <?php if ($schoolPhone): ?>
                        <?= $schoolPoBox ? ',&nbsp; ' : '' ?>Tel: <?= htmlspecialchars($schoolPhone) ?>
                    <?php endif; ?>
                    <?php if ($schoolFax): ?>
                        <?= ($schoolPoBox || $schoolPhone) ? '&nbsp;·&nbsp; ' : '' ?>Fax: <?= htmlspecialchars($schoolFax) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Line 2: email + website -->
            <?php if ($schoolEmail || $schoolWebsite): ?>
                <div class="contact">
                    <?php if ($schoolEmail): ?>
                        Email: <?= htmlspecialchars($schoolEmail) ?>
                    <?php endif; ?>
                    <?php if ($schoolWebsite): ?>
                        <?= $schoolEmail ? '&nbsp;·&nbsp; ' : '' ?>Website: <?= htmlspecialchars($schoolWebsite) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($schoolMotto): ?>
                <div class="motto">&ldquo;<?= htmlspecialchars($schoolMotto) ?>&rdquo;</div>
            <?php endif; ?>

            <div class="head-rule"></div>
        </div>

        <!-- Reference grid -->
        <table class="ref-grid">
            <tr>
                <td style="width: 55%;">
                    <span class="key">Name:</span>
                    <span class="val"><?= htmlspecialchars($fullName) ?></span>
                </td>
                <td style="width: 45%;">
                    <span class="key">Date:</span>
                    <span class="val"><?= htmlspecialchars($admissionDate) ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="key">Registration No.:</span>
                    <span class="val"><?= htmlspecialchars($student['registration_number'] ?? '—') ?></span>
                </td>
                <td>
                    <span class="key">Class:</span>
                    <span class="val"><?= htmlspecialchars($className) ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="key">Admission No.:</span>
                    <span class="val"><?= htmlspecialchars($student['admission_number'] ?? '—') ?></span>
                </td>
                <td>
                    <span class="key">Stream:</span>
                    <span class="val"><?= htmlspecialchars($streamName ?: 'General') ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="key">Gender:</span>
                    <span class="val"><?= htmlspecialchars(strtoupper((string)($student['gender'] ?? '—'))) ?></span>
                </td>
                <td>
                    <span class="key">Academic Year:</span>
                    <span class="val"><?= htmlspecialchars($yearName) ?></span>
                </td>
            </tr>
            <?php if (!empty($student['date_of_birth'])): ?>
                <tr>
                    <td>
                        <span class="key">Date of Birth:</span>
                        <span class="val"><?= htmlspecialchars(date('d M Y', strtotime($student['date_of_birth']))) ?></span>
                    </td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </table>

        <p class="salutation">
            Dear
            <?php if ($guardianName !== ''): ?>
                <?= htmlspecialchars($guardianName) ?>
                (<?= htmlspecialchars($guardianRel) ?>)
            <?php else: ?>
                Sir/Madam
            <?php endif; ?>,
        </p>

        <div class="letter-title">
            Provisional Admission Letter for Academic Year <?= htmlspecialchars($yearName) ?>
        </div>

        <p>
            I write to offer
            <?php if ($guardianName !== ''): ?> your ward, <?php else: ?> you, <?php endif; ?>
            a place at <strong><?= htmlspecialchars($schoolName) ?></strong>
            for a programme of study leading to the following award:
        </p>

        <div class="programme-line">
            <?= htmlspecialchars($className) ?>
            <?= $streamName ? ' — ' . htmlspecialchars(strtoupper($streamName)) : '' ?>
        </div>

        <p>
            This is a provisional offer made on the basis of the statement of
            qualification as presented on the application form. The offer is subject
            to payment of <strong>60% of tuition and all functional fees</strong>
            before issuance of the Admission Letter, and it is valid only when the
            student officially reports when the school opens.
        </p>

        <p><strong>The fees policy states as follows:</strong></p>

        <ol class="policy-list">
            <li>
                All first-year students should pay <strong>60% tuition and full
                functional fees</strong> before the beginning of the orientation week,
                which will be communicated at a later date.
            </li>
            <li>
                All first-year students should pay <strong>60% tuition and all
                functional fees</strong> before issuance of the original admission letter.
            </li>
            <li>
                All students should have paid <strong>100% tuition</strong> by the
                12th week of the term as stipulated in the fees policy approved by
                the School Board.
            </li>
            <li>
                All students should have paid the <strong>full functional and
                accommodation fees</strong> before staying in the boarding section.
            </li>
            <li>
                <strong>Registration is a mandatory requirement</strong> of the school
                and MUST be completed within the <strong>first two (2) weeks</strong>
                of the term.
            </li>
            <li>
                Students are expected to have the <strong>required textbooks,
                stationery, and uniform</strong> as listed in the school prospectus
                before the end of the first week of the term.
            </li>
        </ol>

        <p>
            Please see the detailed <strong>Fee Structure</strong> attached to this letter.
            Please also note that all requirements, including the reporting checklist,
            will be communicated in the official opening announcement.
        </p>

        <?php if (!empty($nextTerm)): ?>
            <p>
                The next academic term is scheduled to begin on
                <strong><?= htmlspecialchars(date('d M Y', strtotime($nextTerm['start_date']))) ?></strong>
                <?php if (!empty($nextTerm['end_date'])): ?>
                    and to end on
                    <strong><?= htmlspecialchars(date('d M Y', strtotime($nextTerm['end_date']))) ?></strong>
                <?php endif; ?>.
            </p>
        <?php endif; ?>

        <p>
            Please retain this letter and present it whenever proof of admission
            is required. We look forward to welcoming
            <?= $guardianName !== '' ? 'your ward' : 'you' ?>
            to our school community.
        </p>

        <div class="closing">
            <div class="signature-block">
                <div class="handwriting">
                    <?php if ($signatureUrl): ?>
                        <img src="<?= htmlspecialchars($signatureUrl) ?>" alt="Signature">
                    <?php endif; ?>
                </div>
                <div class="sig-rule">
                    <?php if ($headInitials): ?>
                        <?= htmlspecialchars($headInitials) ?>,
                    <?php endif; ?>
                </div>
                <div class="role">Academic Registrar</div>
            </div>
        </div>

        <div class="letter-footer">
            This letter is issued without alteration or erasure.
            Any alteration renders it null and void.
            &nbsp;·&nbsp;
            Ref: <?= htmlspecialchars($serial) ?>
            &nbsp;·&nbsp;
            Generated: <?= htmlspecialchars($generatedAt) ?>
        </div>

    </div>
</div>

</body>
</html>