<?php
// File: /index.php

require_once __DIR__ . '/constants.php';
require_once ROOT_PATH . '/vendor/autoload.php';

// Manually require core files
require_once APP_PATH . '/Core/Environment.php';
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

$dotenv = new \NexaT\Core\Environment();
$dotenv->load(ROOT_PATH . '/.env');

define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');
define('DEBUG_MODE', getenv('APP_DEBUG') === 'true');

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Africa/Kampala');

session_name(getenv('SESSION_NAME') ?: 'nexat_session');
session_start();

use NexaT\Core\Application;

$app = new Application();
$app->boot();

require_once ROOT_PATH . '/routes.php';

$app->run();