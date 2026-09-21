<?php 
// File: /app/Views/layouts/_sidebar_menu.php

    $currentView = $view ?? ''; 
    $menuGroups = [
        [
            'type' => 'single',
            'title' => 'Dashboard',
            'icon' => 'fas fa-chart-pie',
            'url' => '/dashboard',
            'active' => ($currentView === 'dashboard/index' || $currentView === 'dashboard')
        ],
        [
            'type' => 'group',
            'id' => 'menuAcademic',
            'title' => 'Academics',
            'icon' => 'fas fa-university',
            'routes' => ['academic/years', 'academic/terms', 'academic/classes', 'academic/streams', 'academic/departments', 'academic/subjects'],
            'items' => [
                ['title' => 'Academic Years', 'icon' => 'fas fa-calendar-alt', 'url' => '/academic/years', 'route' => 'academic/years'],
                ['title' => 'Terms', 'icon' => 'fas fa-calendar-week', 'url' => '/academic/terms', 'route' => 'academic/terms'],
                ['title' => 'Classes', 'icon' => 'fas fa-chalkboard', 'url' => '/academic/classes', 'route' => 'academic/classes'],
                ['title' => 'Streams', 'icon' => 'fas fa-layer-group', 'url' => '/academic/streams', 'route' => 'academic/streams'],
                ['title' => 'Departments', 'icon' => 'fas fa-building', 'url' => '/academic/departments', 'route' => 'academic/departments'],
                ['title' => 'Subjects', 'icon' => 'fas fa-book', 'url' => '/academic/subjects', 'route' => 'academic/subjects'],
            ]
        ],
        [
            'type' => 'group',
            'id' => 'menuStudents',
            'title' => 'Students',
            'icon' => 'fas fa-user-graduate',
            'routes' => ['students', 'student/show', 'student/edit', 'student/create', 'enroll', 'student/categories', 'students/categories'],
            'items' => [
                ['title' => 'Student Directory', 'icon' => 'fas fa-list', 'url' => '/students', 'check' => fn($v) => (str_contains($v, 'students') || str_contains($v, 'student/show') || str_contains($v, 'student/edit') || str_contains($v, 'student/create')) && !str_contains($v, 'categories') && !str_contains($v, 'enroll')],
                ['title' => 'Enrollments', 'icon' => 'fas fa-user-plus', 'url' => '/student/enrollments', 'route' => 'enroll'],
                ['title' => 'Categories', 'icon' => 'fas fa-tags', 'url' => '/student/categories', 'check' => fn($v) => str_contains($v, 'categories') && str_contains($v, 'student')],
            ]
        ],
        [
            'type' => 'group',
            'id' => 'menuStaff',
            'title' => 'Staff',
            'icon' => 'fas fa-users-cog',
            'routes' => ['staff'],
            'items' => [
                ['title' => 'Staff Directory', 'icon' => 'fas fa-address-book', 'url' => '/staff', 'check' => fn($v) => str_contains($v, 'staff') && !str_contains($v, 'categories') && !str_contains($v, 'statuses')],
                ['title' => 'Staff Categories', 'icon' => 'fas fa-tags', 'url' => '/staff/categories', 'route' => 'staff/categories'],
                ['title' => 'Staff Statuses', 'icon' => 'fas fa-toggle-on', 'url' => '/staff/statuses', 'route' => 'staff/statuses'],
            ]
        ],
        [
            'type' => 'group',
            'id' => 'menuAttendance',
            'title' => 'Attendance',
            'icon' => 'fas fa-calendar-check',
            'routes' => ['attendance'],
            'items' => [
                ['title' => 'Take Attendance', 'icon' => 'fas fa-check-circle', 'url' => '/attendance', 'check' => fn($v) => str_contains($v, 'attendance') && !str_contains($v, 'statuses') && !str_contains($v, 'sessions')],
                ['title' => 'Sessions', 'icon' => 'fas fa-clock', 'url' => '/attendance/sessions', 'route' => 'attendance/sessions'],
                ['title' => 'Statuses', 'icon' => 'fas fa-list-check', 'url' => '/attendance/statuses', 'route' => 'attendance/statuses'],
            ]
        ],
        [
            'type' => 'group',
            'id' => 'menuExaminations',
            'title' => 'Examinations',
            'icon' => 'fas fa-file-signature',
            'routes' => ['examination', 'grading', 'assessment', 'promotion', 'marks', 'results'],
            'items' => [
                ['title' => 'Marks Entry', 'icon' => 'fas fa-edit', 'url' => '/marks/entry', 'route' => 'marks'],
                ['title' => 'Class Results', 'icon' => 'fas fa-chart-line', 'url' => '/results/class', 'route' => 'results'],
                ['title' => 'Exam Schedules', 'icon' => 'fas fa-list-alt', 'url' => '/examinations', 'check' => fn($v) => str_contains($v, 'examinations') && !str_contains($v, 'grading') && !str_contains($v, 'assessment') && !str_contains($v, 'promotion') && !str_contains($v, 'marks') && !str_contains($v, 'results')],
                ['title' => 'Grading Systems', 'icon' => 'fas fa-percent', 'url' => '/grading/systems', 'route' => 'grading'],
                ['title' => 'Assessment Types', 'icon' => 'fas fa-tasks', 'url' => '/assessment/types', 'route' => 'assessment'],
                ['title' => 'Promotion Rules', 'icon' => 'fas fa-arrow-up', 'url' => '/promotion/rules', 'route' => 'promotion'],
            ]
        ],
        [
            'type' => 'group',
            'id' => 'menuFinance',
            'title' => 'Finance',
            'icon' => 'fas fa-wallet',
            'routes' => ['fees', 'payments', 'billing'],
            'items' => [
                ['title' => 'Fee Structures', 'icon' => 'fas fa-money-check-alt', 'url' => '/fees', 'route' => 'fees'],
                ['title' => 'Payments', 'icon' => 'fas fa-receipt', 'url' => '/payments', 'route' => 'payments'],
                ['title' => 'Student Billing', 'icon' => 'fas fa-file-invoice', 'url' => '/billing', 'route' => 'billing'],
            ]
        ],
        [
            'type' => 'group',
            'id' => 'menuReports',
            'title' => 'Reports',
            'icon' => 'fas fa-chart-bar',
            'routes' => ['reports'],
            'items' => [
                ['title' => 'Academic Dashboard', 'icon' => 'fas fa-chart-pie', 'url' => '/reports/academic', 'check' => fn($v) => $v === 'reports/academic/dashboard' || $v === 'reports/academic' || $v === 'reports/academic/index'],
                ['title' => 'Report Cards', 'icon' => 'fas fa-file-alt', 'url' => '/reports/academic/report-cards', 'check' => fn($v) => (str_contains($v, 'report-card') || str_contains($v, 'report_card')) && !str_contains($v, 'batch')],
                ['title' => 'Batch Report Cards', 'icon' => 'fas fa-print', 'url' => '/reports/academic/batch-report-cards', 'check' => fn($v) => str_contains($v, 'batch')],
                ['title' => 'Class Analysis', 'icon' => 'fas fa-users', 'url' => '/reports/academic/class-analysis', 'check' => fn($v) => str_contains($v, 'class-analysis') || str_contains($v, 'class_analysis') || str_contains($v, 'classAnalysis')],
                ['title' => 'Subject Analysis', 'icon' => 'fas fa-book', 'url' => '/reports/academic/subject-analysis', 'check' => fn($v) => str_contains($v, 'subject-analysis') || str_contains($v, 'subject_analysis') || str_contains($v, 'subjectAnalysis')],
                ['title' => 'Financial Reports', 'icon' => 'fas fa-coins', 'url' => '/reports/finance', 'check' => fn($v) => str_contains($v, 'reports/finance')],
                ['title' => 'Attendance Reports', 'icon' => 'fas fa-user-clock', 'url' => '/reports/attendance', 'check' => fn($v) => str_contains($v, 'reports/attendance')],
            ]
        ],
        [
            'type' => 'group',
            'id' => 'menuAdmin',
            'title' => 'Administration',
            'icon' => 'fas fa-sliders-h',
            'routes' => ['users', 'settings', 'audit'],
            'items' => [
                ['title' => 'Users & Roles', 'icon' => 'fas fa-users-cog', 'url' => '/users', 'route' => 'users'],
                ['title' => 'System Settings', 'icon' => 'fas fa-cog', 'url' => '/settings', 'route' => 'settings'],
                ['title' => 'Audit Logs', 'icon' => 'fas fa-history', 'url' => '/audit', 'route' => 'audit'],
            ]
        ]
    ];
?>

<ul class="nav flex-column px-2">
    <?php foreach ($menuGroups as $group): ?>
        <li class="nav-item mb-1">
            <?php if ($group['type'] === 'single'): ?>
                <a class="nav-link rounded-2 page-navigation <?= $group['active'] ? 'active' : '' ?>" href="<?= BASE_URL . $group['url'] ?>">
                    <i class="<?= $group['icon'] ?> me-2"></i> <span><?= $group['title'] ?></span>
                </a>
            <?php else: 
                $isActiveGroup = false;
                foreach ($group['routes'] as $route) {
                    if (str_contains($currentView, $route)) {
                        $isActiveGroup = true;
                        break;
                    }
                }
            ?>
                <a class="nav-link rounded-2 d-flex align-items-center justify-content-between <?= $isActiveGroup ? 'text-primary fw-bold' : 'text-dark' ?>" 
                   data-bs-toggle="collapse" 
                   href="#<?= $group['id'] ?>" 
                   role="button" 
                   aria-expanded="<?= $isActiveGroup ? 'true' : 'false' ?>">
                    <span><i class="<?= $group['icon'] ?> me-2"></i> <?= $group['title'] ?></span>
                    <i class="fas fa-chevron-down fs-7 transition-icon"></i>
                </a>
                
                <div class="collapse <?= $isActiveGroup ? 'show' : '' ?>" id="<?= $group['id'] ?>">
                    <ul class="nav flex-column ps-3 mt-1 small">
                        <?php foreach ($group['items'] as $item): 
                            $isItemActive = false;
                            if (isset($item['check'])) {
                                $isItemActive = $item['check']($currentView);
                            } elseif (isset($item['route'])) {
                                $isItemActive = str_contains($currentView, $item['route']);
                            }
                        ?>
                            <li class="nav-item">
                                <a class="nav-link py-1 rounded-2 page-navigation <?= $isItemActive ? 'active' : '' ?>" href="<?= BASE_URL . $item['url'] ?>">
                                    <i class="<?= $item['icon'] ?> me-2"></i> <?= $item['title'] ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mobileSidebar = document.getElementById('mobileSidebar');
    if (!mobileSidebar) return;

    const collapseLinks = mobileSidebar.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');
    collapseLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.stopPropagation();
        }, false);
    });

    const pageLinks = mobileSidebar.querySelectorAll('.page-navigation');
    pageLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            const instance = bootstrap.Offcanvas.getInstance(mobileSidebar) || bootstrap.Offcanvas.getOrCreateInstance(mobileSidebar);
            if (instance) instance.hide();
        });
    });
});
</script>