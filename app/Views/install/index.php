<!-- File: app/Views/install/index.php -->
<?php
    try {
        $settingsService = new \NexaT\Core\SettingsService();
        $theme      = $settingsService->getTheme();
        $accent     = $theme['accent']     ?? '#1D9BF0';
        $accentRgb  = (new \NexaT\Core\ThemeService($settingsService))->hexToRgb($accent);
        $textColor  = $theme['text']       ?? '#0F1419';
        $mutedColor = $theme['muted']      ?? '#6c757d';
        $borderCol  = $theme['border']     ?? '#EFF3F4';
        $bgColor    = $theme['background'] ?? '#f7f9f9';
        $surfaceCol = $theme['surface']    ?? '#ffffff';
        $radius     = ($theme['border_radius'] ?? 0.5) . 'rem';
    } catch (\Throwable $e) {
        $accent     = '#1D9BF0';
        $accentRgb  = '29,155,240';
        $textColor  = '#0F1419';
        $mutedColor = '#6c757d';
        $borderCol  = '#EFF3F4';
        $bgColor    = '#f7f9f9';
        $surfaceCol = '#ffffff';
        $radius     = '0.5rem';
    }

    $csrfToken = '';
    if (function_exists('csrf_token')) {
        $csrfToken = csrf_token();
    } elseif (isset($_SESSION['csrf_token'])) {
        $csrfToken = $_SESSION['csrf_token'];
    } else {
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
    }

    $rootDomain = 'nexat.com';

    $logoRelative = 'assets/images/logo.png';

    $logoCandidates = array_filter([
        defined('PUBLIC_PATH') ? PUBLIC_PATH . '/' . $logoRelative : null,
        ROOT_PATH . '/public/' . $logoRelative,
        ROOT_PATH . '/' . $logoRelative,
        __DIR__ . '/../../../public/' . $logoRelative,
    ]);

    $logoDiskPath = null;
    foreach ($logoCandidates as $candidate) {
        if (is_file($candidate)) {
            $logoDiskPath = $candidate;
            break;
        }
    }

    $logoUrl = null;
    if ($logoDiskPath) {
        $cleanRel = ltrim(str_replace('\\', '/', $logoRelative), '/');
        if (str_contains(BASE_URL, '/public')) {
            $logoUrl = rtrim(BASE_URL, '/') . '/' . $cleanRel;
        } else {
            if (is_file(ROOT_PATH . '/public/' . $cleanRel)) {
                $logoUrl = rtrim(BASE_URL, '/') . '/public/' . $cleanRel;
            } else {
                $logoUrl = rtrim(BASE_URL, '/') . '/' . $cleanRel;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install / NexaT School</title>

    <?php if ($logoUrl): ?>
        <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>">
        <link rel="shortcut icon" type="image/png" href="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>">
        <link rel="apple-touch-icon" href="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --accent-color: <?= htmlspecialchars($accent, ENT_QUOTES, 'UTF-8') ?>;
            --accent-rgb:   <?= $accentRgb ?>;
            --text-color:   <?= htmlspecialchars($textColor, ENT_QUOTES, 'UTF-8') ?>;
            --muted-color:  <?= htmlspecialchars($mutedColor, ENT_QUOTES, 'UTF-8') ?>;
            --radius-base:  <?= $radius ?>;

            --input-bg:     #ffffff;
            --input-border: #D0D7DE;
            --error-bg:     #FEF2F2;
            --error-bd:     #FECACA;
            --error-tx:     #991B1B;
            --success-bg:   #ECFDF5;
            --success-bd:   #A7F3D0;
            --success-tx:   #065F46;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            background: #f0f3f7;
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
        }

        .install-shell {
            width: 100%;
            max-width: 720px;
            margin: 0 auto;
        }

        .brand {
            text-align: center;
            margin-bottom: 1rem;
        }
        .brand .logo-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            border-radius: 16px;
            background: rgba(var(--accent-rgb), 0.1);
            color: var(--accent-color);
            font-size: 1.7rem;
            margin-bottom: 0.5rem;
        }
        .brand img.logo-img {
            display: block;
            max-width: 76px;
            max-height: 76px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin: 0 auto 0.5rem;
        }
        .brand h1 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 0.15rem;
            letter-spacing: -0.015em;
            color: var(--text-color);
            line-height: 1.2;
        }
        .brand .tagline {
            color: var(--muted-color);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            margin: 0;
        }

        .wizard-progress {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            margin-bottom: 0.9rem;
        }
        .step-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.8rem 0.35rem 0.4rem;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #EFF3F4;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted-color);
            transition: all 0.2s ease;
        }
        .step-pill .step-num {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #EFF3F4;
            color: var(--muted-color);
            font-size: 0.7rem;
            font-weight: 700;
        }
        .step-pill.active {
            color: var(--accent-color);
            border-color: rgba(var(--accent-rgb), 0.4);
            box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.10);
        }
        .step-pill.active .step-num {
            background: var(--accent-color);
            color: #fff;
        }
        .step-pill.done .step-num {
            background: #10B981;
            color: #fff;
        }
        .step-pill.done {
            color: #065F46;
            border-color: rgba(16, 185, 129, 0.35);
        }
        .step-divider {
            width: 24px;
            height: 2px;
            background: #EFF3F4;
            border-radius: 2px;
        }

        .install-card {
            background: #ffffff;
            border: 1px solid #EFF3F4;
            border-radius: calc(var(--radius-base) * 1.5);
            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.04),
                0 12px 32px -12px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .install-card-body { padding: 1.25rem 1.5rem 1.25rem; }

        .section { margin-bottom: 1rem; }
        .section:last-of-type { margin-bottom: 0; }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.75rem;
        }
        .section-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--accent-rgb), 0.1);
            color: var(--accent-color);
            font-size: 0.78rem;
            flex-shrink: 0;
        }
        .section-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
            letter-spacing: -0.005em;
        }

        .grid { display: grid; gap: 0.7rem; }
        .grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .field label {
            display: block;
            font-size: 0.74rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.3rem;
            letter-spacing: 0.005em;
        }
        .field label .req { color: #DC2626; margin-left: 2px; }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap .field-icon {
            position: absolute;
            left: 0.75rem;
            color: var(--muted-color);
            font-size: 0.78rem;
            pointer-events: none;
            transition: color 0.15s ease;
        }
        .input-wrap input {
            width: 100%;
            padding: 0.5rem 0.75rem 0.5rem 2.1rem;
            font-size: 0.83rem;
            color: var(--text-color);
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: var(--radius-base);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }
        .input-wrap input::placeholder { color: #9CA3AF; }
        .input-wrap input:hover { border-color: #B8C0CC; }
        .input-wrap input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.25);
        }
        .input-wrap:focus-within .field-icon { color: var(--accent-color); }

        .input-wrap.has-suffix input { padding-right: 6rem; }
        .input-suffix {
            position: absolute;
            right: 0.6rem;
            font-size: 0.72rem;
            color: var(--muted-color);
            font-weight: 600;
            pointer-events: none;
            background: #F8FAFC;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            border: 1px dashed #E2E8F0;
        }

        .input-wrap.has-toggle input { padding-right: 2.3rem; }
        .toggle-password {
            position: absolute;
            right: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border: none;
            background: transparent;
            color: var(--muted-color);
            cursor: pointer;
            border-radius: 6px;
            transition: color 0.15s ease, background-color 0.15s ease;
        }
        .toggle-password:hover {
            color: var(--accent-color);
            background: rgba(var(--accent-rgb), 0.08);
        }
        .toggle-password:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
        }

        .field-error {
            display: none;
            font-size: 0.68rem;
            color: #DC2626;
            margin-top: 0.3rem;
            font-weight: 600;
        }
        .field-error.visible { display: block; }
        .field.has-error input {
            border-color: #DC2626;
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
        }

        .wizard-step { display: none; }
        .wizard-step.active {
            display: block;
            animation: fadeSlide 0.25s ease;
        }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            padding: 0.65rem 0.8rem;
            font-size: 0.8rem;
            border-radius: var(--radius-base);
            margin-bottom: 0.85rem;
            border: 1px solid transparent;
            line-height: 1.4;
        }
        .alert i { margin-top: 0.15rem; flex-shrink: 0; }
        .alert-danger  { background: var(--error-bg);   border-color: var(--error-bd);   color: var(--error-tx); }
        .alert-success { background: var(--success-bg); border-color: var(--success-bd); color: var(--success-tx); }
        .alert.d-none  { display: none; }

        .actions {
            display: flex;
            gap: 0.6rem;
            margin-top: 1rem;
        }
        .actions .btn-back,
        .actions .btn-next,
        .actions .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            padding: 0.65rem 1rem;
            font-size: 0.87rem;
            font-weight: 600;
            border-radius: var(--radius-base);
            border: none;
            cursor: pointer;
            transition: filter 0.15s ease, transform 0.05s ease, box-shadow 0.15s ease, background 0.15s ease;
        }
        .actions .btn-next,
        .actions .btn-submit {
            flex: 1 1 auto;
            color: #ffffff;
            background: var(--accent-color);
            box-shadow: 0 1px 2px rgba(var(--accent-rgb), 0.25), 0 8px 20px -8px rgba(var(--accent-rgb), 0.5);
        }
        .actions .btn-next:hover,
        .actions .btn-submit:hover { filter: brightness(0.95); }
        .actions .btn-next:active,
        .actions .btn-submit:active { transform: translateY(1px); }
        .actions .btn-next:focus-visible,
        .actions .btn-submit:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
        }
        .actions .btn-back {
            background: #F1F5F9;
            color: var(--text-color);
            border: 1px solid #E2E8F0;
            flex: 0 0 auto;
        }
        .actions .btn-back:hover { background: #E2E8F0; }
        .actions .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            filter: none;
        }
        .actions * { color: inherit; }
        .actions .btn-next *,
        .actions .btn-submit * { color: #ffffff !important; }

        .card-footer {
            padding: 0.75rem 1.5rem 0.9rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--muted-color);
            border-top: 1px solid #EFF3F4;
            background: #fcfcfd;
        }
        .card-footer a {
            color: var(--accent-color);
            font-weight: 600;
            text-decoration: none;
        }
        .card-footer a:hover { text-decoration: underline; }

        .page-footer {
            text-align: center;
            margin-top: 0.9rem;
            font-size: 0.68rem;
            color: var(--muted-color);
        }

        @media (max-width: 576px) {
            body { padding: 1rem 0.75rem; }
            .install-card-body { padding: 1rem 1rem 1rem; }
            .card-footer { padding-left: 1rem; padding-right: 1rem; }
            .grid.cols-2 { grid-template-columns: 1fr; }
            .brand h1 { font-size: 1.05rem; }
            .brand img.logo-img { max-width: 64px; max-height: 64px; }
            .brand .logo-mark { width: 54px; height: 54px; font-size: 1.4rem; border-radius: 14px; }
            .input-wrap.has-suffix input { padding-right: 5rem; }
            .input-suffix { font-size: 0.68rem; }
            .step-pill span.step-label { display: none; }
        }
    </style>
</head>
<body>
    <?php $old = $old ?? []; ?>
    <div class="install-shell">
        <div class="brand">
            <?php if ($logoUrl): ?>
                <img class="logo-img" src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="NexaT School">
            <?php else: ?>
                <div class="logo-mark"><i class="fas fa-school"></i></div>
            <?php endif; ?>

            <h1>NexaT School</h1>
            <div class="tagline">Register your School today</div>
        </div>

        <div class="wizard-progress">
            <div class="step-pill active" id="pillStep1">
                <span class="step-num">1</span>
                <span class="step-label">School details</span>
            </div>
            <div class="step-divider"></div>
            <div class="step-pill" id="pillStep2">
                <span class="step-num">2</span>
                <span class="step-label">Administrator</span>
            </div>
        </div>

        <div class="install-card">

            <div class="install-card-body">
                <div id="formError" class="alert alert-danger d-none" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span></span>
                </div>

                <?php if ($flash = $this->getFlash('error')): ?>
                    <div class="alert alert-danger" role="alert" aria-live="assertive">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($flash = $this->getFlash('success')): ?>
                    <div class="alert alert-success" role="alert" aria-live="polite">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <form id="installForm" method="POST" action="<?= BASE_URL . '/install' ?>" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="wizard-step active" id="step1" data-step="1">
                        <div class="section">
                            <div class="section-header">
                                <span class="section-icon"><i class="fas fa-school"></i></span>
                                <h3 class="section-title">School Information</h3>
                            </div>

                            <div class="grid">
                                <div class="grid cols-2">
                                    <div class="field" id="fieldSchoolName">
                                        <label for="school_name">School Name <span class="req">*</span></label>
                                        <div class="input-wrap">
                                            <i class="fas fa-building field-icon"></i>
                                            <input type="text" id="school_name" name="school_name"
                                                   placeholder="e.g. Rays of Grace Junior School"
                                                   value="<?= htmlspecialchars($old['school_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   required>
                                        </div>
                                        <div class="field-error" id="errorSchoolName">Please enter your school name.</div>
                                    </div>

                                    <div class="field" id="fieldShortName">
                                        <label for="school_short_name">Short Name <span class="req">*</span></label>
                                        <div class="input-wrap has-suffix">
                                            <i class="fas fa-globe field-icon"></i>
                                            <input type="text" id="school_short_name" name="school_short_name"
                                                   placeholder="e.g. rog"
                                                   value="<?= htmlspecialchars($old['school_short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   maxlength="30"
                                                   autocomplete="off"
                                                   autocapitalize="none"
                                                   spellcheck="false"
                                                   required>
                                            <span class="input-suffix">.<?= htmlspecialchars($rootDomain) ?></span>
                                        </div>
                                        <div class="field-error" id="errorShortName">Only letters, numbers and hyphens — no spaces.</div>
                                    </div>
                                </div>

                                <div class="grid cols-2">
                                    <div class="field">
                                        <label for="school_motto">Motto</label>
                                        <div class="input-wrap">
                                            <i class="fas fa-quote-left field-icon"></i>
                                            <input type="text" id="school_motto" name="school_motto"
                                                   placeholder="e.g. Excellence through technology"
                                                   value="<?= htmlspecialchars($old['school_motto'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label for="school_telephone">Phone</label>
                                        <div class="input-wrap">
                                            <i class="fas fa-phone field-icon"></i>
                                            <input type="text" id="school_telephone" name="school_telephone"
                                                   placeholder="+256 700 000 000"
                                                   value="<?= htmlspecialchars($old['school_telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid cols-2">
                                    <div class="field">
                                        <label for="school_email">School Email</label>
                                        <div class="input-wrap">
                                            <i class="fas fa-envelope field-icon"></i>
                                            <input type="email" id="school_email" name="school_email"
                                                   placeholder="info@school.ac.ug"
                                                   value="<?= htmlspecialchars($old['school_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label for="school_address">Physical Address</label>
                                        <div class="input-wrap">
                                            <i class="fas fa-map-marker-alt field-icon"></i>
                                            <input type="text" id="school_address" name="school_address"
                                                   placeholder="e.g. P.O. Box 123, Kampala"
                                                   value="<?= htmlspecialchars($old['school_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="button" class="btn-next" id="toStep2">
                                Continue <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="wizard-step" id="step2" data-step="2">
                        <div class="section">
                            <div class="section-header">
                                <span class="section-icon"><i class="fas fa-user-shield"></i></span>
                                <h3 class="section-title">Administrator Account</h3>
                            </div>

                            <div class="grid">
                                <div class="grid cols-2">
                                    <div class="field">
                                        <label for="admin_first_name">First Name <span class="req">*</span></label>
                                        <div class="input-wrap">
                                            <i class="fas fa-user field-icon"></i>
                                            <input type="text" id="admin_first_name" name="admin_first_name"
                                                   placeholder="John"
                                                   value="<?= htmlspecialchars($old['admin_first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label for="admin_last_name">Last Name <span class="req">*</span></label>
                                        <div class="input-wrap">
                                            <i class="fas fa-user field-icon"></i>
                                            <input type="text" id="admin_last_name" name="admin_last_name"
                                                   placeholder="Doe"
                                                   value="<?= htmlspecialchars($old['admin_last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid cols-2">
                                    <div class="field">
                                        <label for="admin_username">Username <span class="req">*</span></label>
                                        <div class="input-wrap">
                                            <i class="fas fa-at field-icon"></i>
                                            <input type="text" id="admin_username" name="admin_username"
                                                   placeholder="johndoe"
                                                   value="<?= htmlspecialchars($old['admin_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   autocomplete="off"
                                                   required>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label for="admin_email">Email <span class="req">*</span></label>
                                        <div class="input-wrap">
                                            <i class="fas fa-envelope field-icon"></i>
                                            <input type="email" id="admin_email" name="admin_email"
                                                   placeholder="admin@school.ac.ug"
                                                   value="<?= htmlspecialchars($old['admin_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   required>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid cols-2">
                                    <div class="field">
                                        <label for="admin_password">Password <span class="req">*</span></label>
                                        <div class="input-wrap has-toggle">
                                            <i class="fas fa-lock field-icon"></i>
                                            <input type="password" id="admin_password" name="admin_password"
                                                   placeholder="At least 8 characters"
                                                   minlength="8" autocomplete="new-password" required>
                                            <button type="button" class="toggle-password" data-target="admin_password" aria-label="Show password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <label for="confirm_password">Confirm Password <span class="req">*</span></label>
                                        <div class="input-wrap has-toggle">
                                            <i class="fas fa-lock field-icon"></i>
                                            <input type="password" id="confirm_password" name="confirm_password"
                                                   placeholder="Repeat your password"
                                                   minlength="8" autocomplete="new-password" required>
                                            <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Show password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="button" class="btn-back" id="backToStep1">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="submit" id="submitBtn" class="btn-submit">
                                <i id="btnIcon" class="fas fa-rocket"></i>
                                <span id="btnText">Install NexaT</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-footer">
                Already have an account?
                <a href="<?= rtrim(BASE_URL, '/') . '/login' ?>">Sign in instead</a>
            </div>
        </div>

        <div class="page-footer">
            &copy; <?= date('Y') ?> NexaT · Secure school management
        </div>
    </div>

    <script>
    (function () {
        const rootDomain   = <?= json_encode($rootDomain) ?>;
        const step1        = document.getElementById('step1');
        const step2        = document.getElementById('step2');
        const pill1        = document.getElementById('pillStep1');
        const pill2        = document.getElementById('pillStep2');
        const formError    = document.getElementById('formError');

        const schoolName   = document.getElementById('school_name');
        const shortName    = document.getElementById('school_short_name');
        const errorSchoolName = document.getElementById('errorSchoolName');
        const errorShortName  = document.getElementById('errorShortName');
        const fieldSchoolName = document.getElementById('fieldSchoolName');
        const fieldShortName  = document.getElementById('fieldShortName');

        function showError(msg) {
            formError.querySelector('span').textContent = msg;
            formError.classList.remove('d-none');
            formError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function hideError() {
            formError.classList.add('d-none');
        }

        const validShortName = /^[a-z0-9](?:[a-z0-9-]{0,28}[a-z0-9])?$/;

        function sanitiseShortName(value) {
            return value
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9-]/g, '')
                .replace(/-{2,}/g, '-')
                .replace(/^-+/, '');
        }

        function refreshPreview() {
            const raw = shortName.value;
            const clean = sanitiseShortName(raw);
            if (clean !== raw) {
                shortName.value = clean;
            }

            if (clean === '' || !validShortName.test(clean)) {
                fieldShortName.classList.toggle('has-error', clean !== '');
                errorShortName.classList.toggle('visible', clean !== '');
            } else {
                fieldShortName.classList.remove('has-error');
                errorShortName.classList.remove('visible');
            }
        }

        shortName.addEventListener('input', refreshPreview);

        function showStep2() {
            let ok = true;

            if (schoolName.value.trim() === '') {
                fieldSchoolName.classList.add('has-error');
                errorSchoolName.classList.add('visible');
                ok = false;
            } else {
                fieldSchoolName.classList.remove('has-error');
                errorSchoolName.classList.remove('visible');
            }

            const clean = sanitiseShortName(shortName.value);
            shortName.value = clean;
            if (clean === '' || !validShortName.test(clean)) {
                fieldShortName.classList.add('has-error');
                errorShortName.classList.add('visible');
                ok = false;
            } else {
                fieldShortName.classList.remove('has-error');
                errorShortName.classList.remove('visible');
            }

            if (!ok) {
                showError('Please correct the highlighted fields before continuing.');
                return;
            }

            hideError();
            step1.classList.remove('active');
            step2.classList.add('active');

            pill1.classList.remove('active');
            pill1.classList.add('done');
            pill2.classList.add('active');

            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(function () {
                const first = document.getElementById('admin_first_name');
                if (first) first.focus();
            }, 150);
        }

        function showStep1() {
            hideError();
            step2.classList.remove('active');
            step1.classList.add('active');

            pill2.classList.remove('active');
            pill1.classList.remove('done');
            pill1.classList.add('active');

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.getElementById('toStep2').addEventListener('click', showStep2);
        document.getElementById('backToStep1').addEventListener('click', showStep1);

        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                if (!input || !icon) return;
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
                input.focus();
            });
        });

        const form = document.getElementById('installForm');
        form.addEventListener('submit', function (e) {
            const password = document.getElementById('admin_password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (password.length < 8) {
                e.preventDefault();
                showError('Password must be at least 8 characters long.');
                return;
            }
            if (password !== confirm) {
                e.preventDefault();
                showError('Passwords do not match. Please try again.');
                return;
            }

            hideError();

            const submitBtn = document.getElementById('submitBtn');
            const btnIcon = document.getElementById('btnIcon');
            const btnText = document.getElementById('btnText');

            submitBtn.disabled = true;
            btnIcon.className = 'spinner-border spinner-border-sm';
            btnIcon.setAttribute('role', 'status');
            btnText.textContent = 'Installing…';
        });

        refreshPreview();

        document.querySelectorAll('.alert:not(#formError)').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-6px)';
                setTimeout(function () { alert.remove(); }, 500);
            }, 6000);
        });
    })();
    </script>
</body>
</html>