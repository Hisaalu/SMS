<!-- File: /app/Views/auth/login.php -->
<?php
    $settingsService = new \NexaT\Core\SettingsService();
    $customFavicon   = $settingsService->get('branding.favicon', '');
    $customLogo      = $settingsService->get('branding.logo', '');
    $dbSchoolName    = $settingsService->get('school.name', $settingsService->get('branding.school_name', 'NexaT School'));

    $theme      = $settingsService->getTheme();
    $accent     = $theme['accent']     ?? '#1D9BF0';
    $accentRgb  = (new \NexaT\Core\ThemeService($settingsService))->hexToRgb($accent);
    $textColor  = $theme['text']       ?? '#0F1419';
    $mutedColor = $theme['muted']      ?? '#6c757d';
    $borderCol  = $theme['border']     ?? '#EFF3F4';
    $bgColor    = $theme['background'] ?? '#f7f9f9';
    $surfaceCol = $theme['surface']    ?? '#ffffff';
    $radius     = ($theme['border_radius'] ?? 0.5) . 'rem';

    $resolveDiskPath = function(?string $relativePath): ?string {
        if (empty($relativePath)) return null;
        $relativePath = ltrim($relativePath, '/');

        $possiblePaths = [
            ROOT_PATH . '/public/' . $relativePath,
            ROOT_PATH . '/' . $relativePath,
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    };

    $resolveUrl = function(string $relativePath): string {
        $cleanPath = ltrim($relativePath, '/');
        if (file_exists(ROOT_PATH . '/public/' . $cleanPath) && !str_contains(BASE_URL, '/public')) {
            return rtrim(BASE_URL, '/') . '/public/' . $cleanPath;
        }
        return rtrim(BASE_URL, '/') . '/' . $cleanPath;
    };

    $faviconDiskPath = $resolveDiskPath($customFavicon);
    $logoDiskPath    = $resolveDiskPath($customLogo);
    $displaySchoolName = $schoolName ?? $dbSchoolName;

    $csrfToken = '';
    if (function_exists('csrf_token')) {
        $csrfToken = csrf_token();
    } elseif (isset($_SESSION['csrf_token'])) {
        $csrfToken = $_SESSION['csrf_token'];
    } else {
        $csrfToken = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $csrfToken;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In · <?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?></title>

    <?php if ($faviconDiskPath): ?>
        <link rel="icon" href="<?= $resolveUrl($customFavicon) ?>" type="image/x-icon">
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
            --success-bg:   #ECFDF5;
            --success-bd:   #A7F3D0;
            --success-tx:   #065F46;
            --error-bg:     #FEF2F2;
            --error-bd:     #FECACA;
            --error-tx:     #991B1B;
            --page-cl:      #f0f3f7;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { height: 100%; }

        body {
            background: var(--page-cl);
            color: var(--text-color);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1.5rem 1rem;
            -webkit-font-smoothing: antialiased;
        }

        .auth-shell {
            width: 100%;
            max-width: 420px;
        }

        .brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }
        .brand .logo-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 130px;
            height: 130px;
            border-radius: 26px;
            background: rgba(var(--accent-rgb), 0.1);
            color: var(--accent-color);
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        .brand img.logo-img {
            max-width: 150px;
            max-height: 150px;
            width: auto;
            height: auto;
            object-fit: contain;
            margin-bottom: 1rem;
        }
        .brand h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 0.25rem;
            letter-spacing: -0.01em;
            color: var(--text-color);
            line-height: 1.25;
        }
        .brand .tagline {
            color: var(--muted-color);
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid #EFF3F4;
            border-radius: calc(var(--radius-base) * 1.5);
            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.04),
                0 12px 32px -12px rgba(15, 23, 42, 0.12);
            padding: 2rem;
        }

        .auth-card h2 {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 0 0 1.5rem;
            color: var(--text-color);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            text-align: center;
        }

        .field { margin-bottom: 1.1rem; }

        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.4rem;
            letter-spacing: 0.01em;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap .field-icon {
            position: absolute;
            left: 0.85rem;
            color: var(--muted-color);
            font-size: 0.85rem;
            pointer-events: none;
            transition: color 0.15s ease;
        }

        .input-wrap input {
            width: 100%;
            padding: 0.7rem 0.9rem 0.7rem 2.4rem;
            font-size: 0.9rem;
            color: var(--text-color);
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: var(--radius-base);
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .input-wrap input::placeholder {
            color: #9CA3AF;
        }

        .input-wrap input:hover {
            border-color: #B8C0CC;
        }

        .input-wrap input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.25);
        }

        .input-wrap:focus-within .field-icon {
            color: var(--accent-color);
        }

        .input-wrap.has-toggle input {
            padding-right: 2.7rem;
        }

        .toggle-password {
            position: absolute;
            right: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
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

        .links-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin: -0.25rem 0 1.25rem;
            flex-wrap: wrap;
        }

        a.link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.82rem;
        }
        a.link:hover { text-decoration: underline; }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.92rem;
            font-weight: 600;
            color: #ffffff;
            background: var(--accent-color);
            border: none;
            border-radius: var(--radius-base);
            cursor: pointer;
            transition: filter 0.15s ease, transform 0.05s ease, box-shadow 0.15s ease;
            box-shadow: 0 1px 2px rgba(var(--accent-rgb), 0.25), 0 8px 20px -8px rgba(var(--accent-rgb), 0.5);
        }
        .btn-primary:hover { filter: brightness(0.95); }
        .btn-primary:active { transform: translateY(1px); }
        .btn-primary:focus-visible {
            outline: 2px solid var(--accent-color);
            outline-offset: 2px;
        }
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            filter: none;
        }
        .btn-primary * { color: #ffffff !important; }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            padding: 0.75rem 0.9rem;
            font-size: 0.85rem;
            border-radius: var(--radius-base);
            margin-bottom: 1rem;
            border: 1px solid transparent;
            line-height: 1.4;
        }
        .alert i { margin-top: 0.15rem; flex-shrink: 0; }
        .alert-danger  { background: var(--error-bg);   border-color: var(--error-bd);   color: var(--error-tx); }
        .alert-success { background: var(--success-bg); border-color: var(--success-bd); color: var(--success-tx); }

        .page-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.72rem;
            color: var(--muted-color);
        }

        @media (max-width: 480px) {
            .auth-card { padding: 1.5rem; }
            .brand h1 { font-size: 1.2rem; }
            .brand img.logo-img { max-width: 110px; max-height: 110px; }
            .brand .logo-mark { width: 100px; height: 100px; font-size: 2.75rem; }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="brand">
            <?php if ($logoDiskPath): ?>
                <img class="logo-img" src="<?= $resolveUrl($customLogo) ?>" alt="<?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <div class="logo-mark">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            <?php endif; ?>

            <h1><?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="tagline">School Management System</div>
        </div>

        <div class="auth-card">
            <h2>Login to Your Account</h2>

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

            <form id="loginForm" method="POST" action="<?= rtrim(BASE_URL, '/') . '/login' ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="field">
                    <div class="input-wrap">
                        <i class="fas fa-user field-icon"></i>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            placeholder="Email or Username"
                            value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            autocomplete="username"
                            required>
                    </div>
                </div>

                <div class="field">
                    <div class="input-wrap has-toggle">
                        <i class="fas fa-lock field-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Password"
                            autocomplete="current-password"
                            required>
                        <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="links-row">
                    <a href="<?= rtrim(BASE_URL, '/') . '/install' ?>" class="link">Create Account</a>
                    <a href="<?= rtrim(BASE_URL, '/') . '/forgot-password' ?>" class="link">Forgot password?</a>
                </div>

                <button type="submit" id="submitBtn" class="btn-primary">
                    <i id="btnIcon" class="fas fa-sign-in-alt"></i>
                    <span id="btnText">Sign In</span>
                </button>
            </form>
        </div>

        <div class="page-footer">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?> · All rights reserved
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('.alert').forEach(function (alert) {
                setTimeout(function () {
                    alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-6px)';
                    setTimeout(function () { alert.remove(); }, 500);
                }, 6000);
            });

            const toggleBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (toggleBtn && passwordInput && toggleIcon) {
                toggleBtn.addEventListener('click', function () {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    toggleIcon.classList.toggle('fa-eye', !isPassword);
                    toggleIcon.classList.toggle('fa-eye-slash', isPassword);
                    passwordInput.focus();
                });
            }

            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function () {
                    const submitBtn = document.getElementById('submitBtn');
                    const btnIcon = document.getElementById('btnIcon');
                    const btnText = document.getElementById('btnText');

                    if (submitBtn) {
                        submitBtn.disabled = true;
                        btnIcon.className = 'spinner-border spinner-border-sm';
                        btnIcon.setAttribute('role', 'status');
                        btnText.textContent = 'Signing in…';
                    }
                });
            }
        })();
    </script>
</body>
</html>