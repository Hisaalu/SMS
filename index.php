<?php
// File: /index.php

// 1. Force Error Reporting for Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Load Constants FIRST
require_once __DIR__ . '/constants.php';
require_once ROOT_PATH . '/vendor/autoload.php';

// 3. Load Environment Variables BEFORE starting sessions or setting timezone
require_once APP_PATH . '/Core/Environment.php';
$dotenv = new \NexaT\Core\Environment();
$dotenv->load(ROOT_PATH . '/.env');

define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');
define('DEBUG_MODE', getenv('APP_DEBUG') === 'true');

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Africa/Kampala');

// File: index.php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_name(getenv('SESSION_NAME') ?: 'nexat_session');
    session_start();
}

// 5. Require Core Framework Files
require_once APP_PATH . '/Core/Config.php';
require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/Session.php';
require_once APP_PATH . '/Core/Router.php';
require_once APP_PATH . '/Core/MiddlewareManager.php';
require_once APP_PATH . '/Core/Application.php';
require_once APP_PATH . '/Core/Auth.php';
require_once APP_PATH . '/Core/Controller.php';
require_once APP_PATH . '/Core/View.php';
require_once APP_PATH . '/Core/ErrorHandler.php';
require_once APP_PATH . '/Core/SettingsService.php';
require_once APP_PATH . '/Core/Model.php';

// 6. Boot and Run Application
use NexaT\Core\Application;

$app = new Application();
$app->boot();

require_once ROOT_PATH . '/routes.php';

$app->run();