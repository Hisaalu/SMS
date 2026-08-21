<!-- File: /app/Views/auth/login.php -->
<?php
    // Instantiate settings service directly to fetch database settings
    $settingsService = new \NexaT\Core\SettingsService();
    $customFavicon   = $settingsService->get('branding.favicon', '');
    $customLogo      = $settingsService->get('branding.logo', '');
    $dbSchoolName    = $settingsService->get('school.name', $settingsService->get('branding.school_name', 'NexaT School'));

    // Helper to locate absolute file path on disk regardless of public folder setup
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

    // Helper to build web-accessible URL
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?></title>

    <?php if ($faviconDiskPath): ?>
        <link rel="icon" href="<?= $resolveUrl($customFavicon) ?>" type="image/x-icon">
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            background: #f7f9f9; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0; 
            padding: 1rem;
        }
        .login-wrapper { 
            text-align: center; 
            max-width: 360px; 
            width: 100%; 
        }
        .logo { 
            margin-bottom: 1.5rem; 
        }
        .logo img { 
            max-width: 100px; 
            max-height: 100px;
            width: auto;
            height: auto; 
            margin-bottom: 0.5rem; 
            object-fit: contain;
        }
        .logo h1 { 
            font-size: 1.5rem; 
            font-weight: 700; 
            margin: 0; 
        }
        .logo .sub { 
            color: #6c757d; 
            font-size: 0.85rem; 
        }
        .login-card { 
            padding: 2rem; 
            background: white; 
            border-radius: 0.5rem; 
            box-shadow: 0 2px 16px rgba(0,0,0,0.05); 
            border: 1px solid #EFF3F4; 
        }
        .form-control { 
            padding: 0.45rem 0.75rem; 
            font-size: 0.9rem; 
            border-radius: 0.5rem; 
            border: 1px solid #EFF3F4; 
        }
        .form-control:focus { 
            border-color: #1D9BF0; 
            box-shadow: 0 0 0 3px rgba(29,155,240,0.1); 
        }
        .form-label { 
            font-size: 0.85rem; 
            font-weight: 500; 
            margin-bottom: 0.2rem; 
            text-align: left; 
            display: block; 
        }
        .password-group {
            position: relative;
        }
        .password-group .form-control {
            padding-right: 2.5rem;
        }
        .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            border: none;
            background: none;
            padding: 0;
        }
        .btn-login { 
            background: #1D9BF0; 
            color: #ffffff; 
            border: none; 
            border-radius: 0.5rem; 
            padding: 0.5rem; 
            font-weight: 600; 
            width: 100%; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-login:hover { 
            opacity: 0.9; 
            color: #ffffff; 
        }
        .btn-login * {
            color: #ffffff !important;
        }
        .mb-2 { 
            margin-bottom: 0.75rem !important; 
        }
        .alert { 
            padding: 0.5rem 0.75rem; 
            font-size: 0.85rem; 
            border-radius: 0.4rem; 
            transition: opacity 0.5s ease;
        }
        .footer { 
            margin-top: 1rem; 
            font-size: 0.7rem; 
            color: #6c757d; 
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem;
            }
            .logo h1 {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="logo">
            <?php if ($logoDiskPath): ?>
                <img src="<?= $resolveUrl($customLogo) ?>" alt="<?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?>">
            <?php else: ?>
                <div style="font-size: 2.5rem; color: #1D9BF0; margin-bottom: 0.5rem;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
            <?php endif; ?>

            <h1><?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="sub">Sign in to continue</div>
        </div>
        
        <div class="login-card">
            <?php if ($flash = $this->getFlash('error')): ?>
                <div class="alert alert-danger mb-2" role="alert">
                    <?= $flash ?>
                </div>
            <?php endif; ?>

            <?php if ($flash = $this->getFlash('success')): ?>
                <div class="alert alert-success mb-2" role="alert">
                    <?= $flash ?>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST" action="<?= rtrim(BASE_URL, '/') . '/login' ?>">
                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                </div>

                <div class="mb-2">
                    <label class="form-label">Password</label>
                    <div class="password-group">
                        <input type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            value="<?= htmlspecialchars($password ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                            required>
                        <button type="button" class="toggle-password" id="togglePasswordBtn">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="btn-login">
                    <i id="btnIcon" class="fas fa-sign-in-alt me-2"></i>
                    <span id="btnText">Sign In</span>
                </button>
            </form>
        </div>
        
        <div class="footer">&copy; <?= date('Y') ?> <?= htmlspecialchars($displaySchoolName, ENT_QUOTES, 'UTF-8') ?></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function (alert) {
                setTimeout(function () {
                    alert.style.opacity = '0';
                    setTimeout(function () {
                        alert.remove();
                    }, 500);
                }, 4000);
            });
        });

        const togglePasswordBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (togglePasswordBtn && passwordInput) {
            togglePasswordBtn.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                toggleIcon.classList.toggle('fa-eye');
                toggleIcon.classList.toggle('fa-eye-slash');
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
                    btnIcon.className = 'spinner-border spinner-border-sm me-2';
                    btnIcon.setAttribute('role', 'status');
                    btnText.textContent = 'Signing in...';
                }
            });
        }
    </script>
</body>
</html>