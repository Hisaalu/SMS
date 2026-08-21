<?php
// File: /assign-super-admin.php

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = new \NexaT\Core\Environment();
$dotenv->load(ROOT_PATH . '/.env');

use NexaT\Core\Database;

$db = Database::getInstance();

echo "<h1>Assign Super Admin Role</h1>";

// Get the user
$username = 'hisaalunelson'; // Your username
$user = $db->fetch("SELECT * FROM users WHERE username = ?", [$username]);

if (!$user) {
    echo "<p style='color: red;'>User not found: " . $username . "</p>";
    exit;
}

echo "<p>User found: " . $user['username'] . " (ID: " . $user['id'] . ")</p>";

// Get super_admin role
$superAdmin = $db->fetch("SELECT * FROM roles WHERE slug = 'super_admin'");

if (!$superAdmin) {
    echo "<p style='color: red;'>Super Admin role not found!</p>";
    exit;
}

echo "<p>Super Admin role found: ID " . $superAdmin['id'] . "</p>";

// Check if user already has super_admin role
$existing = $db->fetch("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", [
    $user['id'],
    $superAdmin['id']
]);

if ($existing) {
    echo "<p style='color: green;'>✅ User already has Super Admin role!</p>";
} else {
    // Assign super_admin role
    $result = $db->insert('user_roles', [
        'user_id' => $user['id'],
        'role_id' => $superAdmin['id'],
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Super Admin role assigned to user: " . $user['username'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to assign Super Admin role</p>";
    }
}

// Verify the assignment
echo "<h2>Verification</h2>";
$userRoles = $db->fetchAll("SELECT r.* FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?", [$user['id']]);

echo "<p>User " . $user['username'] . " has the following roles:</p>";
echo "<ul>";
foreach ($userRoles as $role) {
    echo "<li>" . $role['name'] . " (" . $role['slug'] . ")</li>";
}
echo "</ul>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Go to: <a href='/NexaT/login'>Login Page</a></li>";
echo "<li>Login with your credentials</li>";
echo "<li>Go to: <a href='/NexaT/users'>Users Page</a> - Should work now</li>";
echo "</ol>";