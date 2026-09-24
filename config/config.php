<?php
// File: /config/config.php

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: '',
        'env' => getenv('APP_ENV') ?: '',
        'debug' => getenv('APP_DEBUG') === '',
        'url' => getenv('APP_URL') ?: '',
        'timezone' => getenv('APP_TIMEZONE') ?: '',
        'key' => getenv('APP_KEY'),
    ],
    'session' => [
        'name' => getenv('SESSION_NAME') ?: '',
        'lifetime' => (int)(getenv('SESSION_LIFETIME') ?:''),
        'secure' => getenv('SESSION_SECURE') === '',
    ],
    'upload' => [
        'max_size' => 5242880,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
    ],
];