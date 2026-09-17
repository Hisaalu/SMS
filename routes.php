<?php

use NexaT\Core\Router;

$router = Router::getInstance();

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

$router->group(['middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', ['DashboardController', 'index']);
    
    $router->group(['middleware' => ['permission:users.view']], function ($router) {
        $router->get('/users', ['UserController', 'index']);
        $router->get('/users/create', ['UserController', 'create']);
        $router->post('/users', ['UserController', 'store']);
        $router->get('/users/{id}/edit', ['UserController', 'edit']);
        $router->put('/users/{id}', ['UserController', 'update']);
        $router->delete('/users/{id}', ['UserController', 'delete']);
    });
    
    $router->group(['middleware' => ['permission:roles.view']], function ($router) {
        $router->get('/roles', ['RoleController', 'index']);
        $router->get('/roles/create', ['RoleController', 'create']);
        $router->post('/roles', ['RoleController', 'store']);
        $router->get('/roles/{id}/edit', ['RoleController', 'edit']);
        $router->post('/roles/{id}', ['RoleController', 'update']);
        $router->post('/users/{id}', ['UserController', 'update']);
        $router->delete('/roles/{id}', ['RoleController', 'delete']);
    });
    
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

    $router->get('/academic/years', ['AcademicYearController', 'index']);
    $router->get('/academic/years/create', ['AcademicYearController', 'create']);
    $router->post('/academic/years', ['AcademicYearController', 'store']);
    $router->get('/academic/years/{id}/edit', ['AcademicYearController', 'edit']);
    $router->post('/academic/years/{id}', ['AcademicYearController', 'update']);
    $router->delete('/academic/years/{id}', ['AcademicYearController', 'delete']);

    $router->get('/academic/terms', ['TermController', 'index']);
    $router->get('/academic/terms/create', ['TermController', 'create']);
    $router->post('/academic/terms', ['TermController', 'store']);
    $router->get('/academic/terms/{id}/edit', ['TermController', 'edit']);
    $router->post('/academic/terms/{id}', ['TermController', 'update']);

    $router->get('/academic/classes', ['ClassController', 'index']);
    $router->get('/academic/classes/create', ['ClassController', 'create']);
    $router->post('/academic/classes', ['ClassController', 'store']); 
    $router->get('/academic/classes/{id}/edit', ['ClassController', 'edit']); 
    $router->post('/academic/classes/{id}', ['ClassController', 'update']);

    $router->get('/academic/streams', ['StreamController', 'index']);
    $router->get('/academic/streams/create', ['StreamController', 'create']);
    $router->post('/academic/streams', ['StreamController', 'store']);
    $router->get('/academic/streams/{id}/edit', ['StreamController', 'edit']);
    $router->post('/academic/streams/{id}', ['StreamController', 'update']);

    $router->get('/academic/departments', ['DepartmentController', 'index']);
    $router->get('/academic/departments/create', ['DepartmentController', 'create']);
    $router->post('/academic/departments', ['DepartmentController', 'store']);
    $router->get('/academic/departments/{id}/edit', ['DepartmentController', 'edit']);
    $router->post('/academic/departments/{id}', ['DepartmentController', 'update']);

    $router->get('/academic/subjects', ['SubjectController', 'index']);
    $router->get('/academic/subjects/create', ['SubjectController', 'create']);
    $router->post('/academic/subjects', ['SubjectController', 'store']);
    $router->get('/academic/subjects/{id}/edit', ['SubjectController', 'edit']);
    $router->post('/academic/subjects/{id}', ['SubjectController', 'update']);

    $router->get('/student-categories', ['StudentCategoryController', 'index']);
    $router->get('/student/categories', ['StudentCategoryController', 'index']);
    $router->get('/student-categories/create', ['StudentCategoryController', 'create']);
    $router->get('/student/categories/create', ['StudentCategoryController', 'create']);
    $router->post('/student-categories', ['StudentCategoryController', 'store']);
    $router->post('/student-categories/store', ['StudentCategoryController', 'store']);
    $router->post('/student/categories/store', ['StudentCategoryController', 'store']);
    $router->get('/student-categories/edit', ['StudentCategoryController', 'edit']);
    $router->post('/student-categories/update', ['StudentCategoryController', 'update']);
    $router->post('/student-categories/delete', ['StudentCategoryController', 'delete']);

    $router->get('/student-statuses', ['StudentStatusController', 'index']);
    $router->post('/student-statuses', ['StudentStatusController', 'store']);

    $router->get('/students', ['StudentController', 'index']);
    $router->get('/student/create', ['StudentController', 'create']);
    $router->post('/students/store', ['StudentController', 'store']);
    $router->get('/students/show', ['StudentController', 'show']); 
    $router->get('/students/edit', ['StudentController', 'edit']); 
    $router->post('/students/update', ['StudentController', 'update']); 
    $router->get('/students/delete', ['StudentController', 'delete']);
    $router->get('/students/enrollments', ['StudentController', 'enrollments']);
    $router->post('/students/enrollments/store', ['StudentController', 'storeEnrollment']);

    $router->get('/student/show', ['StudentController', 'show']); 
    $router->get('/student/edit', ['StudentController', 'edit']); 
    $router->post('/student/update', ['StudentController', 'update']); 
    $router->get('/student/enrollments', ['StudentController', 'enrollments']);
    $router->post('/student/enrollments/store', ['StudentController', 'storeEnrollment']);

    $router->get('/staff', ['StaffController', 'index']);
    $router->get('/staff/create', ['StaffController', 'create']);
    $router->post('/staff/store', ['StaffController', 'store']);
    $router->get('/staff/show', ['StaffController', 'show']);
    $router->get('/staff/edit', ['StaffController', 'edit']);
    $router->post('/staff/update', ['StaffController', 'update']);
    $router->post('/staff/change-status', ['StaffController', 'changeStatus']);

    $router->get('/staff/categories', ['StaffCategoryController', 'index']);
    $router->post('/staff/categories/store', ['StaffCategoryController', 'store']);
    $router->post('/staff/categories/update', ['StaffCategoryController', 'update']);

    $router->get('/staff/statuses', ['StaffStatusController', 'index']);
    $router->post('/staff/statuses/store', ['StaffStatusController', 'store']);
    $router->post('/staff/statuses/update', ['StaffStatusController', 'update']);
    $router->post('/staff/statuses/delete', ['StaffStatusController', 'delete']);

    $router->get('/teacher-assignments', ['TeacherAssignmentController', 'index']);
    $router->post('/teacher-assignments/store', ['TeacherAssignmentController', 'store']);
    $router->post('/teacher-assignments/{id}/terminate', ['TeacherAssignmentController', 'terminate']);

    $router->get('/attendance', [AttendanceController::class, 'index']);
    $router->get('/attendance/take', [AttendanceController::class, 'take']);
    $router->post('/attendance/save', [AttendanceController::class, 'save']);
    $router->get('/attendance/view/{id}', [AttendanceController::class, 'view']);
    $router->get('/attendance/edit/{id}', [AttendanceController::class, 'edit']);

    $router->get('/attendance/statuses', ['AttendanceStatusController', 'index']);
    $router->get('/attendance/statuses/create', ['AttendanceStatusController', 'create']);
    $router->post('/attendance/statuses/store', ['AttendanceStatusController', 'store']);
    $router->get('/attendance/statuses/{id}/edit', ['AttendanceStatusController', 'edit']);
    $router->post('/attendance/statuses/{id}/update', ['AttendanceStatusController', 'update']);
    $router->delete('/attendance/statuses/{id}', ['AttendanceStatusController', 'delete']);

    $router->get('/attendance/sessions', ['AttendanceSessionController', 'index']);
    $router->get('/attendance/sessions/create', ['AttendanceSessionController', 'create']);
    $router->post('/attendance/sessions/store', ['AttendanceSessionController', 'store']);
    $router->get('/attendance/sessions/{id}/edit', ['AttendanceSessionController', 'edit']);
    $router->post('/attendance/sessions/{id}/update', ['AttendanceSessionController', 'update']);
    $router->delete('/attendance/sessions/{id}', ['AttendanceSessionController', 'delete']);

    $router->get('/grading/systems', ['GradingSystemController', 'index']);
    $router->get('/grading/systems/create', ['GradingSystemController', 'create']);
    $router->post('/grading/systems', ['GradingSystemController', 'store']);
    $router->get('/grading/systems/{id}/edit', ['GradingSystemController', 'edit']);
    $router->post('/grading/systems/{id}', ['GradingSystemController', 'update']);
    $router->delete('/grading/systems/{id}', ['GradingSystemController', 'delete']);

    $router->get('/grading/systems/{id}/rules', ['GradingRuleController', 'index']);
    $router->post('/grading/systems/{id}/rules', ['GradingRuleController', 'store']);
    $router->post('/grading/systems/rules/{id}', ['GradingRuleController', 'update']);
    $router->delete('/grading/systems/rules/{id}', ['GradingRuleController', 'delete']);

    $router->get('/assessment/types', ['AssessmentTypeController', 'index']);
    $router->get('/assessment/types/create', ['AssessmentTypeController', 'create']);
    $router->post('/assessment/types', ['AssessmentTypeController', 'store']);
    $router->get('/assessment/types/{id}/edit', ['AssessmentTypeController', 'edit']);
    $router->post('/assessment/types/{id}', ['AssessmentTypeController', 'update']);
    $router->delete('/assessment/types/{id}', ['AssessmentTypeController', 'delete']);

    $router->get('/examinations', ['ExaminationController', 'index']);
    $router->get('/examinations/create', ['ExaminationController', 'create']);
    $router->post('/examinations', ['ExaminationController', 'store']);
    $router->get('/examinations/{id}/edit', ['ExaminationController', 'edit']);
    $router->post('/examinations/{id}/update', ['ExaminationController', 'update']);
    $router->delete('/examinations/{id}', ['ExaminationController', 'delete']);

    $router->get('/marks/entry', ['MarkController', 'entrySelector']);
    $router->get('/marks/entry/{examinationId}/{subjectId}', ['MarkController', 'entry']);
    $router->post('/marks/save', ['MarkController', 'save']);
    $router->post('/marks/bulk-save', ['MarkController', 'bulkSave']);

    $router->get('/results/class', ['ResultController', 'classResults']);
    $router->get('/results/class/{examinationId}', ['ResultController', 'classResults']);
    $router->get('/results/student/{studentId}', ['ResultController', 'studentResults']);
    $router->post('/results/publish/{examinationId}', ['ResultController', 'publish']);

    $router->get('/promotion/rules', ['PromotionRuleController', 'index']);
    $router->get('/promotion/rules/create', ['PromotionRuleController', 'create']);
    $router->post('/promotion/rules', ['PromotionRuleController', 'store']);
    $router->get('/promotion/rules/{id}/edit', ['PromotionRuleController', 'edit']);
    $router->post('/promotion/rules/{id}', ['PromotionRuleController', 'update']);
    $router->delete('/promotion/rules/{id}', ['PromotionRuleController', 'delete']);

    $router->get('/promotion/students', ['PromotionController', 'index']);
    $router->post('/promotion/students', ['PromotionController', 'promote']);
});

$router->group(['prefix' => '/api', 'middleware' => ['auth']], function ($router) {
    $router->get('/school-profile', ['ApiController', 'schoolProfile']);
    $router->get('/theme-settings', ['ApiController', 'themeSettings']);
    $router->post('/settings', ['ApiController', 'updateSetting']);
});

$router->group(['prefix' => '/api', 'middleware' => ['auth']], function ($router) {
    $router->get('/school-profile', ['ApiController', 'schoolProfile']);
    $router->get('/theme-settings', ['ApiController', 'themeSettings']);
    $router->post('/settings', ['ApiController', 'updateSetting']);
    $router->get('/streams-by-class', ['ResultController', 'getStreamsByClass']);
});

$router->get('/reports/academic', ['AcademicReportController', 'index']);
$router->get('/reports/academic/report-cards', ['AcademicReportController', 'reportCards']);
$router->get('/reports/academic/report-card/view', ['AcademicReportController', 'generateReportCard']);
$router->get('/reports/academic/subject-analysis', ['AcademicReportController', 'subjectAnalysis']);
$router->get('/reports/academic/class-analysis', ['AcademicReportController', 'classAnalysis']);

$router->get('/reports/academic/batch-report-cards', ['AcademicReportController', 'batchReportCards']);
$router->get('/reports/academic/batch-report-cards/view', ['AcademicReportController', 'generateBatchReportCards']);