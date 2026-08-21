<?php
// File: /routes.php

use NexaT\Core\Router;

$router = Router::getInstance();

// ============================================
// PUBLIC ROUTES
// ============================================

$router->get('/', ['HomeController', 'index']);
$router->get('/login', ['AuthController', 'login']);
$router->post('/login', ['AuthController', 'authenticate']);
$router->get('/logout', ['AuthController', 'logout']);
$router->get('/install', ['InstallController', 'index']);
$router->post('/install', ['InstallController', 'install']);

$router->get('/test-route', function() {
    echo "Test route works! The system is running!";
    exit;
});

// ============================================
// AUTHENTICATED ROUTES
// ============================================

$router->group(['middleware' => ['auth']], function ($router) {
    
    $router->get('/dashboard', ['DashboardController', 'index']);
    
    // User Management - uses permission middleware with parameter
    $router->group(['middleware' => ['permission:users.view']], function ($router) {
        $router->get('/users', ['UserController', 'index']);
        $router->get('/users/create', ['UserController', 'create']);
        $router->post('/users', ['UserController', 'store']);
        $router->get('/users/{id}/edit', ['UserController', 'edit']);
        $router->put('/users/{id}', ['UserController', 'update']);
        $router->delete('/users/{id}', ['UserController', 'delete']);
    });
    
    // Role Management
    $router->group(['middleware' => ['permission:roles.view']], function ($router) {
        $router->get('/roles', ['RoleController', 'index']);
        $router->get('/roles/create', ['RoleController', 'create']);
        $router->post('/roles', ['RoleController', 'store']);
        $router->get('/roles/{id}/edit', ['RoleController', 'edit']);
        $router->post('/roles/{id}', ['RoleController', 'update']);
        $router->post('/users/{id}', ['UserController', 'update']);
        $router->delete('/roles/{id}', ['RoleController', 'delete']);
    });
    
    // Settings
    $router->group(['middleware' => ['permission:settings.view']], function ($router) {
        $router->get('/settings', ['SettingsController', 'index']);
        $router->get('/settings/school', ['SettingsController', 'school']);
        $router->post('/settings/school', ['SettingsController', 'updateSchool']);
        $router->get('/settings/branding', ['SettingsController', 'branding']);
        $router->post('/settings/branding', ['SettingsController', 'updateBranding']);
        $router->get('/settings/appearance', ['SettingsController', 'appearance']);
        $router->post('/settings/appearance', ['SettingsController', 'updateAppearance']);
        $router->post('/settings', ['SettingsController', 'update']);
    });

    // Academic Years
    $router->group(['middleware' => ['auth']], function ($router) {
        $router->get('/academic/years', ['AcademicYearController', 'index']);
        $router->get('/academic/years/create', ['AcademicYearController', 'create']);
        $router->post('/academic/years', ['AcademicYearController', 'store']);
        $router->get('/academic/years/{id}/edit', ['AcademicYearController', 'edit']);
        $router->post('/academic/years/{id}', ['AcademicYearController', 'update']);
        $router->delete('/academic/years/{id}', ['AcademicYearController', 'delete']);

        // Terms
        $router->get('/academic/terms', ['TermController', 'index']);
        $router->get('/academic/terms/create', ['TermController', 'create']);
        $router->post('/academic/terms', ['TermController', 'store']);
        $router->get('/academic/terms/{id}/edit', ['TermController', 'edit']);
        $router->post('/academic/terms/{id}', ['TermController', 'update']);
    });

    $router->group(['middleware' => ['auth']], function ($router) {
        // Classes
        $router->get('/academic/classes', ['ClassController', 'index']);
        $router->get('/academic/classes/create', ['ClassController', 'create']);
        $router->post('/academic/classes', ['ClassController', 'store']); 
        $router->get('/academic/classes/{id}/edit', ['ClassController', 'edit']); 
        $router->post('/academic/classes/{id}', ['ClassController', 'update']);

        // Streams
        $router->get('/academic/streams', ['StreamController', 'index']);
        $router->get('/academic/streams/create', ['StreamController', 'create']);
        $router->post('/academic/streams', ['StreamController', 'store']);

        // Departments
        $router->get('/academic/departments', ['DepartmentController', 'index']);
        $router->get('/academic/departments/create', ['DepartmentController', 'create']);
        $router->post('/academic/departments', ['DepartmentController', 'store']);

        // Subjects
        $router->get('/academic/subjects', ['SubjectController', 'index']);
        $router->get('/academic/subjects/create', ['SubjectController', 'create']);
        $router->post('/academic/subjects', ['SubjectController', 'store']);
    });

    $router->group(['middleware' => ['auth']], function ($router) {
        // Streams
        $router->get('/academic/streams', ['StreamController', 'index']);
        $router->get('/academic/streams/create', ['StreamController', 'create']);
        $router->post('/academic/streams', ['StreamController', 'store']);
        $router->get('/academic/streams/{id}/edit', ['StreamController', 'edit']);
        $router->post('/academic/streams/{id}', ['StreamController', 'update']);
    });

    $router->group(['middleware' => ['auth']], function ($router) {
        // Departments
        $router->get('/academic/departments', ['DepartmentController', 'index']);
        $router->get('/academic/departments/create', ['DepartmentController', 'create']);
        $router->post('/academic/departments', ['DepartmentController', 'store']);
        $router->get('/academic/departments/{id}/edit', ['DepartmentController', 'edit']);
        $router->post('/academic/departments/{id}', ['DepartmentController', 'update']);
    });

    $router->group(['middleware' => ['auth']], function ($router) {
        // Subjects
        $router->get('/academic/subjects', ['SubjectController', 'index']);
        $router->get('/academic/subjects/create', ['SubjectController', 'create']);
        $router->post('/academic/subjects', ['SubjectController', 'store']);
        $router->get('/academic/subjects/{id}/edit', ['SubjectController', 'edit']);
        $router->post('/academic/subjects/{id}', ['SubjectController', 'update']);
    });

    $router->group(['middleware' => ['auth']], function ($router) {
        // PHASE 7: Student Configs
        $router->get('/student-categories', ['StudentCategoryController', 'index']);
        $router->post('/student-categories', ['StudentCategoryController', 'store']);
        $router->get('/student-statuses', ['StudentStatusController', 'index']);
        $router->post('/student-statuses', ['StudentStatusController', 'store']);

        // PHASE 8: Student Management
        $router->get('/students', ['StudentController', 'index']);
        $router->get('/students/create', ['StudentController', 'create']);
        $router->post('/students/store', ['StudentController', 'store']);
        $router->get('/students/show', ['StudentController', 'show']); 
        $router->get('/students/edit', ['StudentController', 'edit']); 
        $router->post('/students/update', ['StudentController', 'update']); 

        // PHASE 9: Staff Configs
        $router->get('/student-categories', ['StudentCategoryController', 'index']);
        $router->get('/student/categories', ['StudentCategoryController', 'index']);
        $router->get('/student-categories/create', ['StudentCategoryController', 'create']);
        $router->get('/student/categories/create', ['StudentCategoryController', 'create']);
        $router->post('/student-categories/store', ['StudentCategoryController', 'store']);
        $router->post('/student/categories/store', ['StudentCategoryController', 'store']);
        $router->get('/student-categories/edit', ['StudentCategoryController', 'edit']);
        $router->post('/student-categories/update', ['StudentCategoryController', 'update']);
        $router->post('/student-categories/delete', ['StudentCategoryController', 'delete']);

        // PHASE 10: Staff Management
        $router->get('/staff', ['StaffController', 'index']);
        $router->get('/staff/create', ['StaffController', 'create']);
        $router->post('/staff/store', ['StaffController', 'store']);
        $router->get('/staff/{id}', ['StaffController', 'show']);

        // PHASE 11: Teacher Assignments
        $router->get('/teacher-assignments', ['TeacherAssignmentController', 'index']);
        $router->post('/teacher-assignments/store', ['TeacherAssignmentController', 'store']);
        $router->post('/teacher-assignments/{id}/terminate', ['TeacherAssignmentController', 'terminate']);
    });
});

// ============================================
// API ROUTES
// ============================================

$router->group(['prefix' => '/api', 'middleware' => ['auth']], function ($router) {
    $router->get('/school-profile', ['ApiController', 'schoolProfile']);
    $router->get('/theme-settings', ['ApiController', 'themeSettings']);
    $router->post('/settings', ['ApiController', 'updateSetting']);
});