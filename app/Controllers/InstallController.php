<?php
// File: /app/Controllers/InstallController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Core\Database;
use NexaT\Services\SettingsService;
use NexaT\Services\NotificationService;

class InstallController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        if (file_exists(STORAGE_PATH . '/installed')) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        echo $this->view->render('install/index');
    }

    public function install(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/install');
            exit;
        }

        try {
            $this->ensureTablesExist();

            $schoolId = $this->createSchool();
            $adminId  = $this->createAdminUser($schoolId);

            $this->assignSuperAdminRole($adminId);
            $this->saveSchoolSettings();
            $this->seedDefaultData($schoolId);

            file_put_contents(STORAGE_PATH . '/installed', date('Y-m-d H:i:s'));

            $admin = $this->db->fetch(
                "SELECT id FROM users WHERE id = ? LIMIT 1",
                [$adminId]
            );

            if (!$admin) {
                throw new \Exception("Failed to load freshly created admin user (ID: {$adminId}).");
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION[SESSION_USER_KEY] = (int)$admin['id'];
            session_write_close();

            $schoolName = $_POST['school_name'] ?? 'Your School';
            $this->notifications->notify(
                (int) $adminId,
                $schoolId,
                'Welcome to NexaT!',
                "Your school management system for {$schoolName} has been successfully installed. Start by configuring your school settings and adding staff members.",
                'success',
                BASE_URL . '/dashboard',
                'fas fa-rocket'
            );

            $this->notifications->notify(
                (int) $adminId,
                $schoolId,
                'Installation Complete',
                'The system has been installed with default academic year, terms, and grading system. You can customize these in Settings.',
                'info',
                BASE_URL . '/settings',
                'fas fa-cog'
            );

            $this->showSuccessPage();
            exit;

        } catch (\Exception $e) {
            error_log("Installation error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            $this->view->flash('error', 'Installation failed: ' . $e->getMessage());
            header('Location: ' . BASE_URL . '/install');
            exit;
        }
    }

    private function ensureTablesExist(): void
    {
        try {
            $this->db->fetch("SELECT 1 FROM users LIMIT 1");
        } catch (\Exception $e) {
            $this->runMigrations();
        }
    }

    private function runMigrations(): void
    {
        $schemaPath = DATABASE_PATH . '/sql/schema.sql';
        $schema = file_get_contents($schemaPath);

        if ($schema === false) {
            throw new \Exception("Schema file not found: " . $schemaPath);
        }

        $statements = array_filter(array_map('trim', explode(';', $schema)));

        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }
            try {
                $this->db->execute($statement);
            } catch (\Exception $e) {
                error_log("Migration warning: " . $e->getMessage());
            }
        }
    }

    private function createSchool(): int
    {
        $name = trim($_POST['school_name'] ?? '');
        if ($name === '') {
            throw new \Exception("School name is required.");
        }

        $slug  = $this->makeSlug($name);
        $email = !empty($_POST['school_email']) ? $_POST['school_email'] : null;

        $this->db->execute(
            "DELETE FROM schools WHERE slug = ? OR (email IS NOT NULL AND email = ?)",
            [$slug, $email]
        );

        $schoolId = $this->db->insert('schools', [
            'name'       => $name,
            'slug'       => $slug,
            'email'      => $email,
            'phone'      => !empty($_POST['school_telephone']) ? $_POST['school_telephone'] : null,
            'address'    => !empty($_POST['school_address']) ? $_POST['school_address'] : null,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$schoolId) {
            throw new \Exception("Failed to create school record.");
        }

        return $schoolId;
    }

    private function createAdminUser(int $schoolId): int
    {
        $password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);

        $this->db->execute(
            "DELETE FROM users WHERE email = ?",
            [$_POST['admin_email']]
        );

        $id = $this->db->insert('users', [
            'school_id'  => $schoolId,
            'username'   => $_POST['admin_username'],
            'email'      => $_POST['admin_email'],
            'password'   => $password,
            'first_name' => $_POST['admin_first_name'],
            'last_name'  => $_POST['admin_last_name'],
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$id) {
            throw new \Exception("Failed to insert user into database.");
        }

        $check = $this->db->fetch("SELECT id FROM users WHERE id = ?", [$id]);
        if (!$check) {
            throw new \Exception("User insert returned ID {$id}, but row is not readable.");
        }

        return $id;
    }

    private function assignSuperAdminRole(int $userId): void
    {
        $role = $this->db->fetch("SELECT id FROM roles WHERE slug = 'super_admin'");

        if (!$role) {
            $roleId = $this->db->insert('roles', [
                'name'        => 'Super Administrator',
                'slug'        => 'super_admin',
                'description' => 'Full system access with all permissions',
                'status'      => 'active',
                'is_system'   => 1,
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $role = ['id' => $roleId];
        }

        $existing = $this->db->fetch(
            "SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $role['id']]
        );

        if (!$existing) {
            $this->db->insert('user_roles', [
                'user_id'    => $userId,
                'role_id'    => $role['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function saveSchoolSettings(): void
    {
        $settings = new SettingsService();

        $settings->set('school.name', $_POST['school_name'] ?? 'NexaT School');
        $settings->set('school.short_name', $_POST['school_short_name'] ?? 'NexaT');
        $settings->set('school.motto', $_POST['school_motto'] ?? 'Excellence Through Technology');
        $settings->set('school.telephone', $_POST['school_telephone'] ?? '');
        $settings->set('school.email', $_POST['school_email'] ?? '');
        $settings->set('school.physical_address', $_POST['school_address'] ?? '');
        $settings->set('school.registration_number', $_POST['school_registration'] ?? '');
        $settings->set('school.website', $_POST['school_website'] ?? '');

        $settings->set('currency.name', 'Uganda Shilling');
        $settings->set('currency.code', 'UGX');
        $settings->set('currency.symbol', 'UGX');
        $settings->set('currency.decimal_places', 0);
    }

    private function seedDefaultData(int $schoolId): void
    {
        $settings = new SettingsService();

        $settings->set('theme.primary', '#000000');
        $settings->set('theme.secondary', '#141414');
        $settings->set('theme.accent', '#1D9BF0');
        $settings->set('theme.background', '#FFFFFF');
        $settings->set('theme.surface', '#F7F9F9');
        $settings->set('theme.text', '#0F1419');
        $settings->set('theme.muted', '#536471');
        $settings->set('theme.border', '#EFF3F4');
        $settings->set('theme.success', '#00BA7C');
        $settings->set('theme.warning', '#FFD400');
        $settings->set('theme.danger', '#F4212E');
        $settings->set('theme.dark_mode', false);

        $settings->set('academic.current_year', date('Y'));
        $settings->set('academic.terms', 3);
        $settings->set('academic.pass_mark', 50);
        $settings->set('academic.term_names', json_encode(['Term 1', 'Term 2', 'Term 3']));

        $this->seedGradingSystem($schoolId);
        $this->seedAcademicStructure($schoolId);
    }

    private function seedGradingSystem(int $schoolId): void
    {
        $existing = $this->db->fetch(
            "SELECT id FROM grading_systems WHERE school_id = ? LIMIT 1",
            [$schoolId]
        );
        if ($existing) {
            return;
        }

        $systemId = $this->db->insert('grading_systems', [
            'school_id'   => $schoolId,
            'name'        => 'Secondary School Grading',
            'description' => 'Standard secondary school grading system',
            'is_default'  => 1,
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        if (!$systemId) {
            throw new \Exception("Failed to create default grading system.");
        }

        $grades = [
            ['grade' => 'A', 'min' => 80, 'max' => 100, 'score' => 1, 'pass' => 1, 'description' => 'Excellent'],
            ['grade' => 'B', 'min' => 70, 'max' =>  79, 'score' => 2, 'pass' => 1, 'description' => 'Very Good'],
            ['grade' => 'C', 'min' => 60, 'max' =>  69, 'score' => 3, 'pass' => 1, 'description' => 'Good'],
            ['grade' => 'D', 'min' => 50, 'max' =>  59, 'score' => 4, 'pass' => 1, 'description' => 'Average'],
            ['grade' => 'F', 'min' =>  0, 'max' =>  49, 'score' => 5, 'pass' => 0, 'description' => 'Fail'],
        ];

        foreach ($grades as $grade) {
            $this->db->insert('grading_rules', [
                'system_id'   => $systemId,
                'grade'       => $grade['grade'],
                'min_mark'    => $grade['min'],
                'max_mark'    => $grade['max'],
                'score'       => $grade['score'],
                'pass'        => $grade['pass'],
                'description' => $grade['description'],
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedAcademicStructure(int $schoolId): void
    {
        $existing = $this->db->fetch(
            "SELECT id FROM academic_years WHERE school_id = ? LIMIT 1",
            [$schoolId]
        );
        if ($existing) {
            return;
        }

        $year = date('Y');

        $yearId = $this->db->insert('academic_years', [
            'school_id'  => $schoolId,
            'name'       => $year . ' Academic Year',
            'start_date' => $year . '-01-01',
            'end_date'   => $year . '-12-31',
            'is_current' => 1,
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$yearId) {
            throw new \Exception("Failed to create academic year.");
        }

        $this->seedTerms($yearId, (int)$year);
    }

    private function seedTerms(int $yearId, int $year): void
    {
        try {
            $this->db->fetch("SELECT 1 FROM terms LIMIT 1");
        } catch (\Exception $e) {
            return;
        }

        $terms = [
            ['name' => 'Term 1', 'term_number' => 1, 'start' => $year . '-01-15', 'end' => $year . '-04-15'],
            ['name' => 'Term 2', 'term_number' => 2, 'start' => $year . '-05-01', 'end' => $year . '-08-15'],
            ['name' => 'Term 3', 'term_number' => 3, 'start' => $year . '-09-01', 'end' => $year . '-12-15'],
        ];

        foreach ($terms as $index => $term) {
            $this->db->insert('terms', [
                'academic_year_id' => $yearId,
                'name'             => $term['name'],
                'term_number'      => $term['term_number'],
                'start_date'       => $term['start'],
                'end_date'         => $term['end'],
                'is_current'       => ($index === 0) ? 1 : 0,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function makeSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $base      = $slug !== '' ? $slug : 'school';
        $candidate = $base;
        $i         = 1;

        while ($this->db->fetch("SELECT id FROM schools WHERE slug = ?", [$candidate])) {
            $candidate = $base . '-' . (++$i);
        }

        return $candidate;
    }

    private function showSuccessPage(): void
    {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="3; url=' . BASE_URL . '/dashboard">
            <title>Installation Complete</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
                body { background: #f7f9f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
                .card { max-width: 450px; width: 100%; padding: 2.5rem; text-align: center; background: white; border-radius: 1rem; box-shadow: 0 2px 16px rgba(0,0,0,0.05); border: 1px solid #EFF3F4; }
                .icon { font-size: 4rem; color: #00BA7C; }
                h2 { margin: 1rem 0 0.5rem; }
                .btn-primary { background: #1D9BF0; border: none; padding: 0.6rem 2rem; border-radius: 0.5rem; color: white; text-decoration: none; display: inline-block; }
                .btn-primary:hover { opacity: 0.9; color: white; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <h2>Installation Complete!</h2>
                <p class="text-muted">Your school management system is ready.</p>
                <div class="spinner-border text-primary my-3" role="status"></div>
                <p class="text-muted small">Redirecting to dashboard in 3 seconds...</p>
                <a href="' . BASE_URL . '/dashboard" class="btn-primary">Go to Dashboard Now</a>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = "' . BASE_URL . '/dashboard";
                }, 3000);
            </script>
        </body>
        </html>';
    }
}