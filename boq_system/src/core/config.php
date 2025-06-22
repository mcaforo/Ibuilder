<?php
// src/core/config.php

// Database Configuration
define('DB_HOST', 'localhost'); // Or your MySQL host, e.g., 127.0.0.1
define('DB_NAME', 'boq_system_db'); // The name of your database
define('DB_USER', 'root'); // Your MySQL username
define('DB_PASS', ''); // Your MySQL password - **SET THIS FOR YOUR ENVIRONMENT**
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_ROOT', dirname(__DIR__, 2)); // Points to 'boq_system' directory
define('BASE_URL', 'http://localhost/boq_system/public'); // Adjust if your local setup is different

// Error Reporting (Development vs Production)
// For development, show all errors. For production, log errors and don't show them to users.
ini_set('display_errors', 1); // Set to 0 in production
ini_set('display_startup_errors', 1); // Set to 0 in production
error_reporting(E_ALL);

// Start session if not already started (useful for including this config in various files)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
