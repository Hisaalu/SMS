<?php
// File: /constants.php

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__FILE__));
defined('APP_PATH') or define('APP_PATH', ROOT_PATH . DS . 'app');
defined('CONFIG_PATH') or define('CONFIG_PATH', ROOT_PATH . DS . 'config');
defined('PUBLIC_PATH') or define('PUBLIC_PATH', ROOT_PATH . DS . 'public');
defined('STORAGE_PATH') or define('STORAGE_PATH', ROOT_PATH . DS . 'storage');
defined('DATABASE_PATH') or define('DATABASE_PATH', ROOT_PATH . DS . 'database');
defined('VIEWS_PATH') or define('VIEWS_PATH', APP_PATH . DS . 'Views');

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname($scriptName);
$basePath = $basePath === '/' || $basePath === '\\' ? '' : $basePath;

defined('BASE_URL') or define('BASE_URL', rtrim($basePath, '/'));
defined('BASE_PATH') or define('BASE_PATH', BASE_URL . '/');

defined('ENV_DEVELOPMENT') or define('ENV_DEVELOPMENT', 'development');
defined('ENV_PRODUCTION') or define('ENV_PRODUCTION', 'production');
defined('ENV_TESTING') or define('ENV_TESTING', 'testing');

defined('HTTP_OK') or define('HTTP_OK', 200);
defined('HTTP_CREATED') or define('HTTP_CREATED', 201);
defined('HTTP_BAD_REQUEST') or define('HTTP_BAD_REQUEST', 400);
defined('HTTP_UNAUTHORIZED') or define('HTTP_UNAUTHORIZED', 401);
defined('HTTP_FORBIDDEN') or define('HTTP_FORBIDDEN', 403);
defined('HTTP_NOT_FOUND') or define('HTTP_NOT_FOUND', 404);
defined('HTTP_METHOD_NOT_ALLOWED') or define('HTTP_METHOD_NOT_ALLOWED', 405);
defined('HTTP_UNPROCESSABLE_ENTITY') or define('HTTP_UNPROCESSABLE_ENTITY', 422);
defined('HTTP_INTERNAL_ERROR') or define('HTTP_INTERNAL_ERROR', 500);

defined('BCRYPT_COST') or define('BCRYPT_COST', 12);
defined('CSRF_TOKEN_NAME') or define('CSRF_TOKEN_NAME', 'csrf_token');

if (!defined('SESSION_USER_KEY')) {
    define('SESSION_USER_KEY', 'user_id'); 
}
if (!defined('SESSION_ROLE_KEY')) {
    define('SESSION_ROLE_KEY', 'role_id');
}

defined('MAX_UPLOAD_SIZE') or define('MAX_UPLOAD_SIZE', 5242880);
defined('DEFAULT_PAGE_SIZE') or define('DEFAULT_PAGE_SIZE', 20);
defined('MAX_PAGE_SIZE') or define('MAX_PAGE_SIZE', 100);

if (!is_dir(STORAGE_PATH)) {
    mkdir(STORAGE_PATH, 0755, true);
}