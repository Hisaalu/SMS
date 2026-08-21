<?php
// File: /app/Controllers/InstallController.php

namespace NexaT\Controllers;

use NexaT\Core\Controller;
use NexaT\Core\Database;
use NexaT\Services\SettingsService;
use NexaT\Core\Auth;

class InstallController extends Controller
{
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
            // 1. Ensure tables exist
            $this->ensureTablesExist();
            
            // 2. Create admin user
            $adminId = $this->createAdminUser();
            if (!$adminId) {
                throw new \Exception("Failed to create admin user");
            }
            
            // 3. Assign Super Admin role to the admin user
            $this->assignSuperAdminRole($adminId);
            
            // 4. Save school settings
            $this->saveSchoolSettings();
            
            // 5. Seed default data
            $this->seedDefaultData();
            
            // 6. Mark as installed
            file_put_contents(STORAGE_PATH . '/installed', date('Y-m-d H:i:s'));
            
            // 7. Log the user in
            $user = \NexaT\Models\User::find($adminId);
            if (!$user) {
                throw new \Exception("User not found after creation");
            }
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION[SESSION_USER_KEY] = $user->id;
            session_write_close();
            
            // 8. Show success page
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
        $db = Database::getInstance();
        
        try {
            $db->fetch("SELECT 1 FROM users LIMIT 1");
        } catch (\Exception $e) {
            $this->runMigrations();
        }
    }
    
    private function runMigrations(): void
    {
        $db = Database::getInstance();
        $schema = file_get_contents(DATABASE_PATH . '/sql/schema.sql');
        
        if ($schema === false) {
            throw new \Exception("Schema file not found: " . DATABASE_PATH . '/sql/schema.sql');
        }
        
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $db->execute($statement);
                } catch (\Exception $e) {
                    error_log("Migration warning: " . $e->getMessage());
                }
            }
        }
    }
    
    private function createAdminUser(): int
    {
        $db = Database::getInstance();
        
        $password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        
        // Check if user already exists
        $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$_POST['admin_email']]);
        if ($existing) {
            $db->delete('users', ['email' => $_POST['admin_email']]);
        }
        
        // Get school_id - for now use 1, but you can make it dynamic
        $schoolId = 1;
        
        $id = $db->insert('users', [
            'school_id' => $schoolId,
            'username' => $_POST['admin_username'],
            'email' => $_POST['admin_email'],
            'password' => $password,
            'first_name' => $_POST['admin_first_name'],
            'last_name' => $_POST['admin_last_name'],
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if (!$id) {
            throw new \Exception("Failed to insert user into database");
        }
        
        return $id;
    }
    
    private function assignSuperAdminRole($userId): void
    {
        $db = Database::getInstance();
        
        // Get super_admin role ID
        $role = $db->fetch("SELECT id FROM roles WHERE slug = 'super_admin'");
        
        if (!$role) {
            // Create super_admin role if it doesn't exist
            $roleId = $db->insert('roles', [
                'name' => 'Super Administrator',
                'slug' => 'super_admin',
                'description' => 'Full system access with all permissions',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $role = ['id' => $roleId];
        }
        
        // Check if user already has this role
        $existing = $db->fetch(
            "SELECT id FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $role['id']]
        );
        
        if (!$existing) {
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $role['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            error_log("Super Admin role assigned to user ID: " . $userId);
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
    
    private function seedDefaultData(): void
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
        
        $this->seedGradingSystem();
        $this->seedAcademicStructure();
    }
    
    private function seedGradingSystem(): void
    {
        $db = Database::getInstance();
        
        $existing = $db->fetch("SELECT id FROM grading_systems LIMIT 1");
        if ($existing) {
            return;
        }
        
        $systemId = $db->insert('grading_systems', [
            'name' => 'Secondary School Grading',
            'description' => 'Standard secondary school grading system',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $grades = [
            ['grade' => 'A', 'min' => 80, 'max' => 100, 'points' => 1, 'pass' => 1, 'description' => 'Excellent'],
            ['grade' => 'B', 'min' => 70, 'max' => 79, 'points' => 2, 'pass' => 1, 'description' => 'Very Good'],
            ['grade' => 'C', 'min' => 60, 'max' => 69, 'points' => 3, 'pass' => 1, 'description' => 'Good'],
            ['grade' => 'D', 'min' => 50, 'max' => 59, 'points' => 4, 'pass' => 1, 'description' => 'Average'],
            ['grade' => 'F', 'min' => 0, 'max' => 49, 'points' => 5, 'pass' => 0, 'description' => 'Fail']
        ];
        
        foreach ($grades as $grade) {
            $db->insert('grading_rules', [
                'system_id' => $systemId,
                'grade' => $grade['grade'],
                'min_mark' => $grade['min'],
                'max_mark' => $grade['max'],
                'points' => $grade['points'],
                'pass' => $grade['pass'],
                'description' => $grade['description'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    private function seedAcademicStructure(): void
    {
        $db = Database::getInstance();
        
        $existing = $db->fetch("SELECT id FROM academic_years LIMIT 1");
        if ($existing) {
            return;
        }
        
        $year = date('Y');
        $yearId = $db->insert('academic_years', [
            'name' => $year . ' Academic Year',
            'start_date' => $year . '-01-01',
            'end_date' => $year . '-12-31',
            'is_current' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $terms = [
            ['name' => 'Term 1', 'term_number' => 1, 'start' => $year . '-01-15', 'end' => $year . '-04-15'],
            ['name' => 'Term 2', 'term_number' => 2, 'start' => $year . '-05-01', 'end' => $year . '-08-15'],
            ['name' => 'Term 3', 'term_number' => 3, 'start' => $year . '-09-01', 'end' => $year . '-12-15']
        ];
        
        foreach ($terms as $index => $term) {
            $isCurrent = ($index === 0) ? 1 : 0;
            $db->insert('terms', [
                'academic_year_id' => $yearId,
                'name' => $term['name'],
                'term_number' => $term['term_number'],
                'start_date' => $term['start'],
                'end_date' => $term['end'],
                'is_current' => $isCurrent,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
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
                <hr>
                <p class="text-muted small mb-0">Login credentials were sent to the admin email.</p>
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