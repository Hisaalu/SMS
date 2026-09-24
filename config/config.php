<?php
// File: /config/config.php

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'NexaT',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
        'url' => getenv('APP_URL') ?: 'http://localhost/NexaT',
        'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Kampala',
        'key' => getenv('APP_KEY'),
    ],
    'session' => [
        'name' => getenv('SESSION_NAME') ?: 'nexat_session',
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 120),
        'secure' => filter_var(getenv('SESSION_SECURE'), FILTER_VALIDATE_BOOLEAN),
    ],
    'upload' => [
        'max_size' => 5242880,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    ],
];