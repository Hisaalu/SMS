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
    <title>Install · NexaT School</title>

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
            --border-color: <?= htmlspecialchars($borderCol, ENT_QUOTES, 'UTF-8') ?>;
            --bg-color:     <?= htmlspecialchars($bgColor, ENT_QUOTES, 'UTF-8') ?>;
            --surface-color:<?= htmlspecialchars($surfaceCol, ENT_QUOTES, 'UTF-8') ?>;
            --radius-base:  <?= $radius ?>;

            --input-bg:     #ffffff;
            --input-border: #D0D7DE;
            --error-bg:     #FEF2F2;
            --error-bd:     #FECACA;
            --error-tx:     #991B1B;
            --success-bg:   #ECFDF5;
            --success-bd:   #A7F3D0;
            --success-tx:   #065F46;
            --page-cl:      #f0f3f7;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            background: var(--page-cl);
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 3rem 1rem;
            -webkit-font-smoothing: antialiased;
        }

        .install-shell {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }

        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand .logo-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 96px;
            height: 96px;
            border-radius: 22px;
            background: rgba(var(--accent-rgb), 0.1);
            color: var(--accent-color);
            font-size: 2.5rem;
            margin-bottom: 0.9rem;
        }
        .brand img.logo-img {
            display: block;
            max-width: 130px;
            max-height: 130px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin: 0 auto 0.9rem;
        }
        .brand h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0 0 0.3rem;
            letter-spacing: -0.015em;
            color: var(--text-color);
            line-height: 1.2;
        }
        .brand .tagline {
            color: var(--muted-color);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            margin: 0;
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

        .install-card-header {
            padding: 1.5rem 2rem 1.25rem;
            border-bottom: 1px solid #EFF3F4;
            background: linear-gradient(180deg, rgba(var(--accent-rgb), 0.05), transparent);
        }
        .install-card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 0.3rem;
            color: var(--text-color);
            letter-spacing: -0.005em;
        }
        .install-card-header p {
            color: var(--muted-color);
            font-size: 0.82rem;
            margin: 0;
            line-height: 1.45;
        }

        .install-card-body {
            padding: 1.75rem 2rem 2.25rem;
        }

        .section { margin-bottom: 1.75rem; }
        .section:last-of-type { margin-bottom: 0; }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 1rem;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(var(--accent-rgb), 0.1);
            color: var(--accent-color);
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .section-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
            letter-spacing: -0.005em;
        }

        .grid { display: grid; gap: 0.9rem; }
        .grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.4rem;
            letter-spacing: 0.005em;
        }
        .field label .req {
            color: #DC2626;
            margin-left: 2px;
        }
        .field .hint {
            display: block;
            font-size: 0.7rem;
            color: var(--muted-color);
            margin-top: 0.35rem;
            line-height: 1.4;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrap .field-icon {
            position: absolute;
            left: 0.8rem;
            color: var(--muted-color);
            font-size: 0.8rem;
            pointer-events: none;
            transition: color 0.15s ease;
        }
        .input-wrap input {
            width: 100%;
            padding: 0.6rem 0.8rem 0.6rem 2.25rem;
            font-size: 0.86rem;
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

        .input-wrap.has-toggle input { padding-right: 2.5rem; }
        .toggle-password {
            position: absolute;
            right: 0.6rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
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

        .section-divider {
            height: 1px;
            background: #EFF3F4;
            margin: 1.5rem 0 1.75rem;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.8rem 0.95rem;
            font-size: 0.85rem;
            border-radius: var(--radius-base);
            margin-bottom: 1.25rem;
            border: 1px solid transparent;
            line-height: 1.45;
        }
        .alert i { margin-top: 0.15rem; flex-shrink: 0; }
        .alert-danger  { background: var(--error-bg);   border-color: var(--error-bd);   color: var(--error-tx); }
        .alert-success { background: var(--success-bg); border-color: var(--success-bd); color: var(--success-tx); }
        .alert.d-none  { display: none; }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 0.92rem;
            font-weight: 600;
            color: #ffffff;
            background: var(--accent-color);
            border: none;
            border-radius: var(--radius-base);
            cursor: pointer;
            transition: filter 0.15s ease, transform 0.05s ease, box-shadow 0.15s ease;
            box-shadow: 0 1px 2px rgba(var(--accent-rgb), 0.25), 0 8px 20px -8px rgba(var(--accent-rgb), 0.5);
            margin-top: 1.75rem;
        }
        .btn-primary:hover { filter: brightness(0.95); }
        .btn-primary:active { transform: translateY(1px); }
        .btn-primary:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
        }
        .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; filter: none; }
        .btn-primary * { color: #ffffff !important; }

        .card-footer {
            padding: 1.15rem 2rem 1.5rem;
            text-align: center;
            font-size: 0.85rem;
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
            margin-top: 1.75rem;
            font-size: 0.72rem;
            color: var(--muted-color);
        }

        @media (max-width: 576px) {
            body { padding: 2rem 1rem; }
            .install-card-header,
            .install-card-body,
            .card-footer { padding-left: 1.25rem; padding-right: 1.25rem; }
            .grid.cols-2 { grid-template-columns: 1fr; }
            .brand h1 { font-size: 1.2rem; }
            .brand img.logo-img { max-width: 100px; max-height: 100px; }
            .brand .logo-mark { width: 84px; height: 84px; font-size: 2.1rem; border-radius: 20px; }
        }
    </style>
</head>
<body>
    <?php $old = $old ?? []; ?>
    <div class="install-shell">
        <div class="brand">
            <?php if ($logoUrl): ?>
                <img class="logo-img"
                     src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>"
                     alt="NexaT School">
            <?php else: ?>
                <div class="logo-mark">
                    <i class="fas fa-school"></i>
                </div>
            <?php endif; ?>

            <h1>NexaT School</h1>
            <div class="tagline">Set up your school</div>
        </div>

        <div class="install-card">
            <div class="install-card-header">
                <h2>Installation</h2>
                <p>Fill in the details below. Fields marked <span style="color:#DC2626;">*</span> are required.</p>
            </div>

            <div class="install-card-body">
                <div id="passwordError" class="alert alert-danger d-none" role="alert">
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

                    <div class="section">
                        <div class="section-header">
                            <span class="section-icon"><i class="fas fa-school"></i></span>
                            <h3 class="section-title">School details</h3>
                        </div>

                        <div class="grid">
                            <div class="field">
                                <label for="school_name">School name <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <i class="fas fa-building field-icon"></i>
                                    <input type="text" id="school_name" name="school_name"
                                           placeholder="e.g. Nyakasura School"
                                           value="<?= htmlspecialchars($old['school_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                           required>
                                </div>
                            </div>

                            <div class="grid cols-2">
                                <div class="field">
                                    <label for="school_short_name">Short name</label>
                                    <div class="input-wrap">
                                        <i class="fas fa-tag field-icon"></i>
                                        <input type="text" id="school_short_name" name="school_short_name"
                                               placeholder="e.g. NYAK"
                                               value="<?= htmlspecialchars($old['school_short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="school_motto">Motto</label>
                                    <div class="input-wrap">
                                        <i class="fas fa-quote-left field-icon"></i>
                                        <input type="text" id="school_motto" name="school_motto"
                                               placeholder="e.g. Excellence Through Technology"
                                               value="<?= htmlspecialchars($old['school_motto'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="grid cols-2">
                                <div class="field">
                                    <label for="school_telephone">Phone</label>
                                    <div class="input-wrap">
                                        <i class="fas fa-phone field-icon"></i>
                                        <input type="text" id="school_telephone" name="school_telephone"
                                               placeholder="+256 700 000 000"
                                               value="<?= htmlspecialchars($old['school_telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="school_email">School email</label>
                                    <div class="input-wrap">
                                        <i class="fas fa-envelope field-icon"></i>
                                        <input type="email" id="school_email" name="school_email"
                                               placeholder="info@school.ac.ug"
                                               value="<?= htmlspecialchars($old['school_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="field">
                                <label for="school_address">Physical address</label>
                                <div class="input-wrap">
                                    <i class="fas fa-map-marker-alt field-icon"></i>
                                    <input type="text" id="school_address" name="school_address"
                                           placeholder="e.g. P.O. Box 123, Kampala"
                                           value="<?= htmlspecialchars($old['school_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <div class="section">
                        <div class="section-header">
                            <span class="section-icon"><i class="fas fa-user-shield"></i></span>
                            <h3 class="section-title">Administrator account</h3>
                        </div>

                        <div class="grid">
                            <div class="grid cols-2">
                                <div class="field">
                                    <label for="admin_first_name">First name <span class="req">*</span></label>
                                    <div class="input-wrap">
                                        <i class="fas fa-user field-icon"></i>
                                        <input type="text" id="admin_first_name" name="admin_first_name"
                                               placeholder="John"
                                               value="<?= htmlspecialchars($old['admin_first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                               required>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="admin_last_name">Last name <span class="req">*</span></label>
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
                                               minlength="8" required>
                                        <button type="button" class="toggle-password" data-target="admin_password" aria-label="Show password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="field">
                                    <label for="confirm_password">Confirm password <span class="req">*</span></label>
                                    <div class="input-wrap has-toggle">
                                        <i class="fas fa-lock field-icon"></i>
                                        <input type="password" id="confirm_password" name="confirm_password"
                                               placeholder="Repeat your password"
                                               minlength="8" required>
                                        <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Show password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="btn-primary">
                        <i id="btnIcon" class="fas fa-rocket"></i>
                        <span id="btnText">Install NexaT</span>
                    </button>
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
            document.querySelectorAll('.alert:not(#passwordError)').forEach(function (alert) {
                setTimeout(function () {
                    alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-6px)';
                    setTimeout(function () { alert.remove(); }, 500);
                }, 6000);
            });

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
            const errorDiv = document.getElementById('passwordError');

            if (form) {
                form.addEventListener('submit', function (e) {
                    const password = document.getElementById('admin_password').value;
                    const confirm = document.getElementById('confirm_password').value;

                    if (password !== confirm) {
                        e.preventDefault();
                        errorDiv.querySelector('span').textContent = 'Passwords do not match. Please try again.';
                        errorDiv.classList.remove('d-none');
                        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    errorDiv.classList.add('d-none');

                    const submitBtn = document.getElementById('submitBtn');
                    const btnIcon = document.getElementById('btnIcon');
                    const btnText = document.getElementById('btnText');

                    submitBtn.disabled = true;
                    btnIcon.className = 'spinner-border spinner-border-sm';
                    btnIcon.setAttribute('role', 'status');
                    btnText.textContent = 'Installing…';
                });
            }
        })();
    </script>
</body>
</html>