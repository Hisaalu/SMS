<?php
// File: /config/database.php

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: '',
    'username' => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    'persistent' => false,
];