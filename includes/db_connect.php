<?php
/**
 * Database Connection File
 * MySQLi connection for RLI Material Request System
 * XAMPP Default Settings: localhost, root, no password
 */

// Database configuration
// Uses environment variables when deployed (Railway), falls back to XAMPP defaults locally.
// Read from getenv() and $_ENV (Railway may set one or the other).
$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$dbUser = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
$dbPass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '');
// If Railway linked MySQL, it may expose MYSQL_* vars on this service — use them when DB_* are empty
if ($dbPass === '' && (getenv('MYSQL_ROOT_PASSWORD') !== false || isset($_ENV['MYSQL_ROOT_PASSWORD']))) {
    $dbPass = (string)(getenv('MYSQL_ROOT_PASSWORD') ?: ($_ENV['MYSQL_ROOT_PASSWORD'] ?? ''));
}
if ($dbHost === 'localhost' && (getenv('MYSQLHOST') !== false || isset($_ENV['MYSQLHOST']))) {
    $dbHost = (string)(getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? $dbHost));
}
if ($dbUser === 'root' && (getenv('MYSQLUSER') !== false || isset($_ENV['MYSQLUSER']))) {
    $dbUser = (string)(getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? $dbUser));
}
$dbName = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'rli_systems');
$dbPort = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? null);
if ($dbPort === null || $dbPort === '') {
    $dbPort = getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? null);
}
if ($dbPort === null || $dbPort === '') {
    $dbPort = null;
} else {
    $dbPort = (string)$dbPort;
}

// Create connection
try {
    if ($dbPort !== null && $dbPort !== '') {
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
    } else {
        $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    }
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4 for proper character encoding
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Don't use die() as it outputs HTML - throw exception instead
    throw new Exception("Database connection error: " . $e->getMessage());
}
?>
