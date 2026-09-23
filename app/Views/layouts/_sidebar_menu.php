<?php 
// File: /app/Views/layouts/_sidebar_menu.php
$currentPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
$basePath    = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

$isExactActive = function(string $itemUrl) use ($currentPath, $basePath): bool {
    $itemPath = rtrim(parse_url($itemUrl, PHP_URL_PATH) ?? $itemUrl, '/');
    $fullPath = rtrim($basePath . $itemPath, '/');
    return $currentPath === $fullPath;
};

$menuSections = [
    [
        'section_title' => null,
        'items' => [
            [
                'type'  => 'single',
                'title' => 'Dashboard',
                'icon'  => 'fas fa-chart-pie',
                'url'   => '/dashboard',
            ],
        ],
    ],
    [
        'section_title' => 'Academics & Examinations',
        'items' => [
            [
                'type'  => 'group',
                'id'    => 'menuAcademic',
                'title' => 'Academics Management',
                'icon'  => 'fas fa-university',
                'subitems' => [
                    ['title' => 'Academic Years', 'icon' => 'fas fa-calendar-alt',  'url' => '/academic/years'],
                    ['title' => 'Terms',          'icon' => 'fas fa-calendar-week', 'url' => '/academic/terms'],
                    ['title' => 'Classes',        'icon' => 'fas fa-chalkboard',    'url' => '/academic/classes'],
                    ['title' => 'Streams',        'icon' => 'fas fa-layer-group',   'url' => '/academic/streams'],
                    ['title' => 'Departments',    'icon' => 'fas fa-building',      'url' => '/academic/departments'],
                    ['title' => 'Subjects',       'icon' => 'fas fa-book',          'url' => '/academic/subjects'],
                ],
            ],
            [
                'type'  => 'group',
                'id'    => 'menuExaminations',
                'title' => 'Examinations',
                'icon'  => 'fas fa-file-signature',
                'subitems' => [
                    ['title' => 'Marks Entry',       'icon' => 'fas fa-edit',       'url' => '/marks/entry'],
                    ['title' => 'Class Results',     'icon' => 'fas fa-chart-line', 'url' => '/results/class'],
                    ['title' => 'Exam Schedules',    'icon' => 'fas fa-list-alt',   'url' => '/examinations'],
                    ['title' => 'Grading Systems',   'icon' => 'fas fa-percent',    'url' => '/grading/systems'],
                    ['title' => 'Assessment Types',  'icon' => 'fas fa-tasks',      'url' => '/assessment/types'],
                    ['title' => 'Promotion Rules',   'icon' => 'fas fa-arrow-up',   'url' => '/promotion/rules'],
                ],
            ],
        ],
    ],
    [
        'section_title' => 'Students & Attendance',
        'items' => [
            [
                'type'  => 'group',
                'id'    => 'menuStudents',
                'title' => 'Students',
                'icon'  => 'fas fa-user-graduate',
                'subitems' => [
                    ['title' => 'Student Directory', 'icon' => 'fas fa-list',      'url' => '/students'],
                    ['title' => 'Enrollments',       'icon' => 'fas fa-user-plus', 'url' => '/student/enrollments'],
                    ['title' => 'Categories',        'icon' => 'fas fa-tags',      'url' => '/student/categories'],
                ],
            ],
            [
                'type'  => 'group',
                'id'    => 'menuAttendance',
                'title' => 'Attendance',
                'icon'  => 'fas fa-calendar-check',
                'subitems' => [
                    ['title' => 'Take Attendance', 'icon' => 'fas fa-check-circle', 'url' => '/attendance'],
                    ['title' => 'Sessions',        'icon' => 'fas fa-clock',        'url' => '/attendance/sessions'],
                    ['title' => 'Statuses',        'icon' => 'fas fa-list-check',   'url' => '/attendance/statuses'],
                ],
            ],
        ],
    ],
    [
        'section_title' => 'Staff & Finance',
        'items' => [
            [
                'type'  => 'group',
                'id'    => 'menuStaff',
                'title' => 'Staff',
                'icon'  => 'fas fa-users-cog',
                'subitems' => [
                    ['title' => 'Staff Directory', 'icon' => 'fas fa-address-book', 'url' => '/staff'],
                    ['title' => 'Staff Categories', 'icon' => 'fas fa-tags',         'url' => '/staff/categories'],
                    ['title' => 'Staff Statuses',  'icon' => 'fas fa-toggle-on',    'url' => '/staff/statuses'],
                ],
            ],
            [
                'type'  => 'group',
                'id'    => 'menuFinance',
                'title' => 'Finance',
                'icon'  => 'fas fa-wallet',
                'subitems' => [
                    ['title' => 'Fee Structures',  'icon' => 'fas fa-money-check-alt', 'url' => '/fees'],
                    ['title' => 'Payments',        'icon' => 'fas fa-receipt',         'url' => '/payments'],
                    ['title' => 'Student Billing', 'icon' => 'fas fa-file-invoice',    'url' => '/billing'],
                ],
            ],
        ],
    ],
    [
        'section_title' => 'Insights & System',
        'items' => [
            [
                'type'  => 'group',
                'id'    => 'menuReports',
                'title' => 'Reports',
                'icon'  => 'fas fa-chart-bar',
                'subitems' => [
                    ['title' => 'Academic Dashboard',   'icon' => 'fas fa-chart-pie', 'url' => '/reports/academic'],
                    ['title' => 'Report Cards',         'icon' => 'fas fa-file-alt',  'url' => '/reports/academic/report-cards'],
                    ['title' => 'Batch Report Cards',   'icon' => 'fas fa-print',     'url' => '/reports/academic/batch-report-cards'],
                    ['title' => 'Class Analysis',       'icon' => 'fas fa-users',     'url' => '/reports/academic/class-analysis'],
                    ['title' => 'Subject Analysis',     'icon' => 'fas fa-book',      'url' => '/reports/academic/subject-analysis'],
                    ['title' => 'Financial Reports',    'icon' => 'fas fa-coins',     'url' => '/reports/finance'],
                    ['title' => 'Attendance Reports',   'icon' => 'fas fa-user-clock', 'url' => '/reports/attendance'],
                ],
            ],
            [
                'type'  => 'group',
                'id'    => 'menuAdmin',
                'title' => 'Administration',
                'icon'  => 'fas fa-sliders-h',
                'subitems' => [
                    ['title' => 'Users & Roles',   'icon' => 'fas fa-users-cog', 'url' => '/users'],
                    ['title' => 'System Settings', 'icon' => 'fas fa-cog',        'url' => '/settings'],
                    ['title' => 'Audit Logs',      'icon' => 'fas fa-history',    'url' => '/audit'],
                ],
            ],
        ],
    ],
];
?>
<div class="sidebar-menu-wrapper py-2" id="sidebarMenuWrapper">
    <?php foreach ($menuSections as $section): ?>
        <?php if (!empty($section['section_title'])): ?>
            <div class="sidebar-section-title"><?= htmlspecialchars($section['section_title']) ?></div>
        <?php endif; ?>

        <ul class="nav flex-column px-2 mb-2">
            <?php foreach ($section['items'] as $group):
                $groupActive = false;
                if (($group['type'] ?? '') === 'group' && !empty($group['subitems'])) {
                    foreach ($group['subitems'] as $sub) {
                        if ($isExactActive($sub['url'])) {
                            $groupActive = true;
                            break;
                        }
                    }
                }
            ?>
                <li class="nav-item mb-1">
                    <?php if (($group['type'] ?? '') === 'single'):
                        $isSingleActive = $isExactActive($group['url']);
                    ?>
                        <a class="nav-link rounded-2 page-navigation <?= $isSingleActive ? 'active' : '' ?>"
                           href="<?= BASE_URL . $group['url'] ?>">
                            <i class="<?= $group['icon'] ?> menu-icon"></i>
                            <span><?= $group['title'] ?></span>
                        </a>
                    <?php else: ?>
                        <a class="nav-link rounded-2 d-flex align-items-center justify-content-between sidebar-dropdown-toggle <?= $groupActive ? 'active-group fw-semibold' : '' ?>"
                           data-bs-toggle="collapse"
                           href="#<?= $group['id'] ?>"
                           role="button"
                           aria-expanded="<?= $groupActive ? 'true' : 'false' ?>"
                           aria-controls="<?= $group['id'] ?>">
                            <span class="d-flex align-items-center">
                                <i class="<?= $group['icon'] ?> menu-icon"></i>
                                <span><?= $group['title'] ?></span>
                            </span>
                            <i class="fas fa-chevron-right chevron-icon fs-8"></i>
                        </a>

                        <div class="collapse <?= $groupActive ? 'show' : '' ?>" id="<?= $group['id'] ?>">
                            <ul class="nav flex-column sub-menu-list ps-3 mt-1">
                                <?php foreach ($group['subitems'] as $item):
                                    $isItemActive = $isExactActive($item['url']);
                                ?>
                                    <li class="nav-item">
                                        <a class="nav-link py-1.5 rounded-2 page-navigation sub-link <?= $isItemActive ? 'active' : '' ?>"
                                           href="<?= BASE_URL . $item['url'] ?>">
                                            <i class="<?= $item['icon'] ?> sub-menu-icon"></i>
                                            <span><?= $item['title'] ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</div>