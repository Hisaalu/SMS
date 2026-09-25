<?php
// File: /constants.php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . DS . 'app');
define('CONFIG_PATH', ROOT_PATH . DS . 'config');
define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
define('STORAGE_PATH', ROOT_PATH . DS . 'storage');
define('DATABASE_PATH', ROOT_PATH . DS . 'database');
define('VIEWS_PATH', APP_PATH . DS . 'Views');

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
$basePath = $basePath === '/' || $basePath === '\\' ? '' : $basePath;
define('BASE_URL', rtrim($basePath, '/'));
define('BASE_PATH', BASE_URL . '/');

define('ENV_DEVELOPMENT', 'development');
define('ENV_PRODUCTION', 'production');
define('ENV_TESTING', 'testing');

define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_INTERNAL_ERROR', 500);

define('BCRYPT_COST', 12);
define('CSRF_TOKEN_NAME', 'csrf_token');
if (!defined('SESSION_USER_KEY')) {
    define('SESSION_USER_KEY', 'user_id'); 
}
if (!defined('SESSION_ROLE_KEY')) {
    define('SESSION_ROLE_KEY', 'role_id');
}

define('MAX_UPLOAD_SIZE', 5242880);
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}