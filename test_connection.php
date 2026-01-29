<?php
/**
 * Test Database Connection
 * Use this to verify database connection is working
 */

header('Content-Type: text/plain');

echo "Testing Database Connection...\n\n";

// Test connection
try {
    require_once 'includes/db_connect.php';
    echo "✓ Database connection successful!\n";
    echo "Database: " . DB_NAME . "\n";
    echo "Host: " . DB_HOST . "\n\n";
    
    // Check if tables exist
    $tables = ['material_requests', 'request_items'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' does NOT exist\n";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure MySQL is running in XAMPP\n";
    echo "2. Check if database 'rli_systems' exists\n";
    echo "3. Verify credentials in includes/db_connect.php\n";
}
?>
