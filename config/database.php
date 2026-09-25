<?php
// File: /config/database.php

$sslCert = __DIR__ . '/isrgrootx1.pem';

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: '',
    'username' => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    'ssl_ca' => (getenv('DB_HOST') && getenv('DB_HOST') !== '127.0.0.1' && file_exists($sslCert)) ? $sslCert : null,
    
    'persistent' => false,
];