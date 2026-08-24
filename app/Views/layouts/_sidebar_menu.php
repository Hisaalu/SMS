<!-- File: /app/Views/layouts/_sidebar_menu.php -->
<?php 
    $currentView = $view ?? ''; 
    
    // Ensure branding settings are accessible if instantiated inside sidebar context
    if (!isset($settingsService)) {
        $settingsService = new \NexaT\Core\SettingsService();
    }
    
    $sidebarLogo     = $settingsService->get('branding.logo', '');
    $sidebarLogoPath = !empty($sidebarLogo) ? ROOT_PATH . '/public/' . ltrim($sidebarLogo, '/') : '';
    if (!empty($sidebarLogo) && !file_exists($sidebarLogoPath)) {
        $sidebarLogoPath = ROOT_PATH . '/' . ltrim($sidebarLogo, '/');
    }
?>

<ul class="nav flex-column">
    <!-- Dashboard -->
    <li class="nav-item">
        <a class="nav-link <?= $currentView === 'dashboard/index' ? 'active' : '' ?>" href="<?= BASE_URL . '/dashboard' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
    </li>
    
    <!-- Academic Management -->
    <li class="nav-section">Academics</li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'academic/years') ? 'active' : '' ?>" href="<?= BASE_URL . '/academic/years' ?>">
            <i class="fas fa-calendar-alt"></i> Academic Years
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'academic/terms') ? 'active' : '' ?>" href="<?= BASE_URL . '/academic/terms' ?>">
            <i class="fas fa-calendar-week"></i> Terms
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'academic/classes') ? 'active' : '' ?>" href="<?= BASE_URL . '/academic/classes' ?>">
            <i class="fas fa-chalkboard"></i> Classes
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'academic/streams') ? 'active' : '' ?>" href="<?= BASE_URL . '/academic/streams' ?>">
            <i class="fas fa-layer-group"></i> Streams
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'academic/departments') ? 'active' : '' ?>" href="<?= BASE_URL . '/academic/departments' ?>">
            <i class="fas fa-building"></i> Departments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'academic/subjects') ? 'active' : '' ?>" href="<?= BASE_URL . '/academic/subjects' ?>">
            <i class="fas fa-book"></i> Subjects
        </a>
    </li>

    <!-- Student Management -->
    <li class="nav-section">Students</li>
    <li class="nav-item">
        <a class="nav-link <?= (str_contains($currentView, 'students') || str_contains($currentView, 'student/show') || str_contains($currentView, 'student/edit') || str_contains($currentView, 'student/create')) && !str_contains($currentView, 'students/categories') && !str_contains($currentView, 'student/categories') && !str_contains($currentView, 'enroll') ? 'active' : '' ?>" href="<?= BASE_URL . '/students' ?>">
            <i class="fas fa-user-graduate"></i> Student Management
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'enroll') ? 'active' : '' ?>" href="<?= BASE_URL . '/student/enrollments' ?>">
            <i class="fas fa-user-plus"></i> Student Enrollment
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'students/categories') || str_contains($currentView, 'student/categories') ? 'active' : '' ?>" href="<?= BASE_URL . '/student/categories' ?>">
            <i class="fas fa-tags"></i> Student Categories
        </a>
    </li>

    <!-- Staff Management -->
    <li class="nav-section">Staff Management</li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'staff') && !str_contains($currentView, 'staff/categories') && !str_contains($currentView, 'staff/statuses') ? 'active' : '' ?>" href="<?= BASE_URL . '/staff' ?>">
            <i class="fas fa-users"></i> Staff Directory
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'staff/categories') ? 'active' : '' ?>" href="<?= BASE_URL . '/staff/categories' ?>">
            <i class="fas fa-tags"></i> Staff Categories
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'staff/statuses') ? 'active' : '' ?>" href="<?= BASE_URL . '/staff/statuses' ?>">
            <i class="fas fa-toggle-on"></i> Staff Statuses
        </a>
    </li>

    <!-- Attendance -->
    <li class="nav-section">Attendance</li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'attendance') && !str_contains($currentView, 'attendance/statuses') && !str_contains($currentView, 'attendance/sessions') && !str_contains($currentView, 'reports/attendance') ? 'active' : '' ?>" href="<?= BASE_URL . '/attendance' ?>">
            <i class="fas fa-calendar-check"></i> Attendance
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'attendance/sessions') || str_contains($currentView, 'attendance/session') ? 'active' : '' ?>" href="<?= BASE_URL . '/attendance/sessions' ?>">
            <i class="fas fa-clock"></i> Attendance Sessions
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'attendance/statuses') || str_contains($currentView, 'attendance/status') ? 'active' : '' ?>" href="<?= BASE_URL . '/attendance/statuses' ?>">
            <i class="fas fa-list-check"></i> Attendance Statuses
        </a>
    </li>

    <!-- Examinations -->
    <li class="nav-section">Examinations</li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'examinations') ? 'active' : '' ?>" href="<?= BASE_URL . '/examinations' ?>">
            <i class="fas fa-file-signature"></i> Examinations
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'grading') ? 'active' : '' ?>" href="<?= BASE_URL . '/grading/systems' ?>">
            <i class="fas fa-percent"></i> Grading Systems
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'assessment/types') ? 'active' : '' ?>" href="<?= BASE_URL . '/assessment/types' ?>">
            <i class="fas fa-tasks"></i> Assessment Types
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'promotion') ? 'active' : '' ?>" href="<?= BASE_URL . '/promotion/rules' ?>">
            <i class="fas fa-arrow-up"></i> Promotion Rules
        </a>
    </li>

    <!-- Finance -->
    <li class="nav-section">Finance</li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'fees') ? 'active' : '' ?>" href="<?= BASE_URL . '/fees' ?>">
            <i class="fas fa-wallet"></i> Fee Management
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'payments') ? 'active' : '' ?>" href="<?= BASE_URL . '/payments' ?>">
            <i class="fas fa-receipt"></i> Payments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'billing') ? 'active' : '' ?>" href="<?= BASE_URL . '/billing' ?>">
            <i class="fas fa-file-invoice"></i> Student Billing
        </a>
    </li>

    <!-- Reports -->
    <li class="nav-section">Reports</li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'reports/academic') ? 'active' : '' ?>" href="<?= BASE_URL . '/reports/academic' ?>">
            <i class="fas fa-chart-bar"></i> Academic Reports
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'reports/finance') ? 'active' : '' ?>" href="<?= BASE_URL . '/reports/finance' ?>">
            <i class="fas fa-coins"></i> Financial Reports
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'reports/attendance') ? 'active' : '' ?>" href="<?= BASE_URL . '/reports/attendance' ?>">
            <i class="fas fa-user-clock"></i> Attendance Reports
        </a>
    </li>

    <!-- Administration -->
    <li class="nav-section">Administration</li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'users') ? 'active' : '' ?>" href="<?= BASE_URL . '/users' ?>">
            <i class="fas fa-users-cog"></i> Users & Roles
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'settings') ? 'active' : '' ?>" href="<?= BASE_URL . '/settings' ?>">
            <i class="fas fa-sliders-h"></i> System Settings
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= str_contains($currentView, 'audit') ? 'active' : '' ?>" href="<?= BASE_URL . '/audit' ?>">
            <i class="fas fa-history"></i> Audit Logs
        </a>
    </li>
</ul>