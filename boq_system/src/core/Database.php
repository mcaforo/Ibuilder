<?php
// src/core/Database.php

// Ensure config is loaded. If this class is autoloaded, config might need explicit inclusion.
// For direct script execution (like in auth scripts), require_once is fine.
// If using an autoloader, ensure config is loaded early in your application bootstrap.
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} elseif (file_exists(dirname(__DIR__, 2) . '/src/core/config.php')) {
    // Fallback if called from a different path, e.g. public directory script
    require_once dirname(__DIR__, 2) . '/src/core/config.php';
} else {
    die("Configuration file not found. Critical error.");
}


class Database {
    private static $instance = null;
    private $conn;

    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;

    // Private constructor so nobody else can instantiate it
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch be an associative array
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // In a real application, log this error and show a generic message
            // For development, it's okay to show the error, but ensure DB_PASS is not exposed if this message is public
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check configuration and ensure the database server is running. Specific error: " . $e->getMessage());
        }
    }

    // The singleton method
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get the PDO connection object
    public function getConnection() {
        return $this->conn;
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserialization of the instance
    public function __wakeup() {}
}

/*
// Example Usage:
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Example query
    $stmt = $conn->query("SELECT setting_key, setting_value FROM settings LIMIT 1");
    $setting = $stmt->fetch();
    if ($setting) {
        echo "Successfully connected and fetched a setting: " . htmlspecialchars($setting['setting_key']);
    } else {
        echo "Successfully connected, but no settings found or table is empty.";
    }

} catch (PDOException $e) {
    echo "Database operation failed: " . $e->getMessage();
}
*/
?>
