<?php
/**
 * Database Connection File
 * MySQLi connection for RLI Material Request System
 * XAMPP Default Settings: localhost, root, no password
 */

// Database configuration
// Uses environment variables when deployed (Railway), falls back to XAMPP defaults locally.
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'rli_systems';
$dbPort = getenv('DB_PORT') ?: null;

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
