<?php
// File: /config/database.php

return [
    'host' => getenv('DB_HOST') ?: '',
    'port' => getenv('DB_PORT') ?: '',
    'database' => getenv('DB_NAME') ?: '',
    'username' => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => getenv('DB_CHARSET') ?: '',
    'persistent' => false,
];