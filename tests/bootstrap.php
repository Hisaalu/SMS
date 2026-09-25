<?php
//File: tests/bootstrap.php

putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');

require_once __DIR__ . '/../constants.php';

require_once ROOT_PATH . '/vendor/autoload.php';

$dotenv = new NexaT\Core\Environment();
$dotenv->load(ROOT_PATH . '/.env');

$db = NexaT\Core\Database::getInstance();

try {
    $db->execute("CREATE DATABASE IF NOT EXISTS `nexat_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (\Exception $e) {
}

$schema = file_get_contents(DATABASE_PATH . '/sql/schema.sql');
foreach (explode(';', $schema) as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        try {
            $db->execute($statement);
        } catch (\Exception $e) {
        }
    }
}