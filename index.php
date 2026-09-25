<?php
// File: /index.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$rootDir = __DIR__;
if (basename($rootDir) === 'public') {
    $rootDir = dirname($rootDir);
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $rootDir);
}

require_once ROOT_PATH . '/constants.php';
require_once ROOT_PATH . '/vendor/autoload.php';

require_once APP_PATH . '/Core/Environment.php';
$dotenv = new \NexaT\Core\Environment();
$dotenv->load(ROOT_PATH . '/.env');

define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');
define('DEBUG_MODE', getenv('APP_DEBUG') === 'true');
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'Africa/Kampala');

require_once APP_PATH . '/Core/Config.php';
\NexaT\Core\Config::get('database');

require_once APP_PATH . '/Core/Database.php';
require_once APP_PATH . '/Core/DatabaseSessionHandler.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name(getenv('SESSION_NAME') ?: 'nexat_session');

    try {
        $handler = new \NexaT\Core\DatabaseSessionHandler(
            \NexaT\Core\Database::getInstance(),
            86400 * 7
        );
        session_set_save_handler($handler, true);
    } catch (\Throwable $e) {
        error_log('[SessionHandler] DB handler unavailable, falling back to files: ' . $e->getMessage());
    }

    session_start();
}

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

if (defined('MY_DEBUG_SCRIPT')) {
    return;
}

use NexaT\Core\Application;

$app = new Application();
$app->boot();

require_once ROOT_PATH . '/routes.php';

$app->run();