<?php

// Set up test environment
putenv('APP_ENV=testing');
putenv('APP_DEBUG=true');

// Load constants
require_once __DIR__ . '/../constants.php';

// Load autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Load environment
$dotenv = new NexaT\Core\Environment();
$dotenv->load(ROOT_PATH . '/.env');

// Set up test database if needed
$db = NexaT\Core\Database::getInstance();

// Create test database if it doesn't exist
try {
    $db->execute("CREATE DATABASE IF NOT EXISTS `nexat_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (\Exception $e) {
    // Database may already exist
}

// Run migrations for test database
$schema = file_get_contents(DATABASE_PATH . '/sql/schema.sql');
foreach (explode(';', $schema) as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        try {
            $db->execute($statement);
        } catch (\Exception $e) {
            // Table may already exist
        }
    }
}