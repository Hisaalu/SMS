<!-- File: app/Views/install/index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NexaT Install</title>
    
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
            padding: 1rem 0.75rem;
        }
        .install-wrapper { 
            text-align: center; 
            max-width: 480px; 
            width: 100%; 
        }
        .logo { 
            margin-bottom: 1.25rem; 
        }
        .logo img { 
            max-width: 70px; 
            height: auto; 
            margin-bottom: 0.25rem; 
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
        .card { 
            padding: 1.75rem; 
            border-radius: 0.5rem; 
            background: white; 
            box-shadow: 0 2px 16px rgba(0,0,0,0.05); 
            border: 1px solid #EFF3F4; 
            text-align: left; 
        }
        .form-control { 
            padding: 0.4rem 0.6rem; 
            font-size: 0.85rem; 
            border-radius: 0.4rem; 
            border: 1px solid #EFF3F4; 
        }
        .form-control:focus { 
            border-color: #1D9BF0; 
            box-shadow: 0 0 0 3px rgba(29,155,240,0.1); 
        }
        .form-label { 
            font-size: 0.8rem; 
            font-weight: 500; 
            margin-bottom: 0.15rem; 
        }
        .row.g-1 { 
            --bs-gutter-x: 0.4rem; 
        }
        .password-group {
            position: relative;
        }
        .password-group .form-control {
            padding-right: 2.2rem;
        }
        .toggle-password {
            position: absolute;
            right: 0.6rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            border: none;
            background: none;
            padding: 0;
        }
        .btn-primary { 
            padding: 0.5rem; 
            font-size: 0.9rem; 
            font-weight: 600; 
            border-radius: 0.4rem; 
            width: 100%; 
            background: #1D9BF0; 
            border: none; 
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary:hover { 
            opacity: 0.9; 
            background: #1D9BF0; 
            color: #ffffff;
        }
        .btn-primary * {
            color: #ffffff !important;
        }
        .mb-1 { 
            margin-bottom: 0.5rem !important; 
        }
        .section-title { 
            font-size: 0.85rem; 
            font-weight: 600; 
            margin: 0.75rem 0 0.5rem; 
        }
        hr { 
            margin: 0.75rem 0; 
            border-color: #EFF3F4; 
        }
        .alert { 
            padding: 0.5rem 0.75rem; 
            font-size: 0.85rem; 
            border-radius: 0.4rem; 
            transition: opacity 0.5s ease;
        }
        .footer { 
            margin-top: 1rem; 
            font-size: 0.65rem; 
            color: #6c757d; 
        }

        @media (max-width: 576px) {
            .card {
                padding: 1.25rem;
            }
            .logo h1 {
                font-size: 1.35rem;
            }
            .col-sm-6 {
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <?php $old = $old ?? []; ?>
    <div class="install-wrapper">
        <div class="logo">
            <?php 
                $logoPath = 'public/assets/images/logo.png';
                $logoFullPath = PUBLIC_PATH . '/assets/images/logo.png';
                if (file_exists($logoFullPath)): 
            ?>
                <img src="<?= $logoPath ?>" alt="NexaT School">
            <?php else: ?>
                <div style="font-size: 2rem; color: #1D9BF0;">
                    <i class="fas fa-school"></i>
                </div>
            <?php endif; ?>

            <h1>NexaT School</h1>
            <div class="sub">Installation</div>
        </div>
        
        <div class="card">
            <div id="passwordError" class="alert alert-danger mb-1 d-none"></div>

            <?php if ($flash = $this->getFlash('error')): ?>
                <div class="alert alert-danger mb-1" role="alert"><?= $flash ?></div>
            <?php endif; ?>

            <?php if ($flash = $this->getFlash('success')): ?>
                <div class="alert alert-success mb-1" role="alert"><?= $flash ?></div>
            <?php endif; ?>
            
            <form id="installForm" method="POST" action="<?= BASE_URL . '/install' ?>">
                <div class="section-title">School</div>
                <div class="mb-1">
                    <label class="form-label">School Name</label>
                    <input type="text" class="form-control" name="school_name" value="<?= htmlspecialchars($old['school_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="row g-1">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Short</label>
                        <input type="text" class="form-control" name="school_short_name" value="<?= htmlspecialchars($old['school_short_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Motto</label>
                        <input type="text" class="form-control" name="school_motto" value="<?= htmlspecialchars($old['school_motto'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="row g-1">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="school_telephone" value="<?= htmlspecialchars($old['school_telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="school_email" value="<?= htmlspecialchars($old['school_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="mb-1">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="school_address" value="<?= htmlspecialchars($old['school_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                
                <hr>
                
                <div class="section-title">Admin</div>
                <div class="row g-1">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="admin_first_name" value="<?= htmlspecialchars($old['admin_first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="admin_last_name" value="<?= htmlspecialchars($old['admin_last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="row g-1">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="admin_username" value="<?= htmlspecialchars($old['admin_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="admin_email" value="<?= htmlspecialchars($old['admin_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="row g-1 mb-1">
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Password</label>
                        <div class="password-group">
                            <input type="password" class="form-control" id="admin_password" name="admin_password" minlength="8" required>
                            <button type="button" class="toggle-password" data-target="admin_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label">Confirm Password</label>
                        <div class="password-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="submit" id="submitBtn" class="btn-primary mt-2">
                    <i id="btnIcon" class="fas fa-rocket me-1"></i>
                    <span id="btnText">Install</span>
                </button>
            </form>
        </div>
        
        <div class="footer">&copy; <?= date('Y') ?> NexaT</div>
    </div>

    <script>
        // Auto-dismiss flash messages after 4 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert:not(#passwordError)');
            alerts.forEach(function (alert) {
                setTimeout(function () {
                    alert.style.opacity = '0';
                    setTimeout(function () {
                        alert.remove();
                    }, 500);
                }, 4000);
            });
        });

        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        });

        document.getElementById('installForm').addEventListener('submit', function (e) {
            const password = document.getElementById('admin_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorDiv = document.getElementById('passwordError');

            if (password !== confirmPassword) {
                e.preventDefault();
                errorDiv.textContent = 'Passwords do not match.';
                errorDiv.classList.remove('d-none');
                return;
            }

            errorDiv.classList.add('d-none');

            const submitBtn = document.getElementById('submitBtn');
            const btnIcon = document.getElementById('btnIcon');
            const btnText = document.getElementById('btnText');

            submitBtn.disabled = true;
            btnIcon.className = 'spinner-border spinner-border-sm me-2';
            btnIcon.setAttribute('role', 'status');
            btnText.textContent = 'Installing...';
        });
    </script>
</body>
</html>