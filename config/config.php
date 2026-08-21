<?php
// File: /config/config.php

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'NexaT School Management System',
        'env' => getenv('APP_ENV') ?: 'development',
        'debug' => getenv('APP_DEBUG') === 'true',
        'url' => getenv('APP_URL') ?: 'http://localhost/NexaT',
        'timezone' => getenv('APP_TIMEZONE') ?: 'Africa/Kampala',
        'key' => getenv('APP_KEY'),
    ],
    'session' => [
        'name' => getenv('SESSION_NAME') ?: 'nexat_session',
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?: 120),
        'secure' => getenv('SESSION_SECURE') === 'true',
    ],
    'upload' => [
        'max_size' => 5242880,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    ],
];