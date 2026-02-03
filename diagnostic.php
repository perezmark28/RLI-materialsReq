<?php
/**
 * Diagnostic Script to Test MVC Setup
 * Access this file directly to diagnose issues
 * Then delete it after testing
 */

echo "=== RLI Material Request System - Diagnostic Report ===\n\n";

// Check bootstrap
echo "1. Bootstrap Configuration:\n";
require_once __DIR__ . '/config/bootstrap.php';
echo "   ✓ Bootstrap loaded\n";
echo "   - APP_ROOT: " . APP_ROOT . "\n";
echo "   - APP_ENV: " . APP_ENV . "\n\n";

// Check file locations
echo "2. File Existence Check:\n";
$files_to_check = [
    'config/Database.php' => APP_ROOT . '/config/Database.php',
    'config/helpers.php' => APP_ROOT . '/config/helpers.php',
    'config/sms.php' => APP_ROOT . '/config/sms.php',
    'app/Core/Model.php' => APP_ROOT . '/app/Core/Model.php',
    'app/Core/Controller.php' => APP_ROOT . '/app/Core/Controller.php',
    'app/Core/Router.php' => APP_ROOT . '/app/Core/Router.php',
];

foreach ($files_to_check as $name => $path) {
    $exists = file_exists($path) ? '✓' : '✗';
    echo "   $exists $name\n";
}

echo "\n3. Helper Functions Check:\n";
if (function_exists('is_logged_in')) {
    echo "   ✓ is_logged_in() defined\n";
} else {
    echo "   ✗ is_logged_in() NOT defined\n";
}

if (function_exists('current_user')) {
    echo "   ✓ current_user() defined\n";
} else {
    echo "   ✗ current_user() NOT defined\n";
}

echo "\n4. Controllers Check:\n";
$controllers = [
    'App\Controllers\HomeController',
    'App\Controllers\AuthController',
    'App\Controllers\RequestController',
    'App\Controllers\UserController',
    'App\Controllers\SupplierController',
];

foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "   ✓ $controller\n";
    } else {
        echo "   ✗ $controller NOT FOUND\n";
    }
}

echo "\n5. Database Connection Check:\n";
try {
    $db = \Config\Database::getInstance();
    $conn = $db->getConnection();
    if ($conn && !$conn->connect_error) {
        echo "   ✓ Database connection successful\n";
    } else {
        echo "   ✗ Database connection failed: " . $conn->connect_error . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

echo "\n6. Router Test:\n";
$router = new \App\Core\Router();
echo "   ✓ Router instantiated\n";

echo "\n7. Server Configuration:\n";
echo "   - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "   - SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "   - PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "   - REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";

echo "\n8. Apache Modules:\n";
if (extension_loaded('mod_rewrite')) {
    echo "   ✓ mod_rewrite available\n";
} else {
    echo "   ? mod_rewrite status unknown (check Apache directly)\n";
}

echo "\n=== All Checks Complete ===\n";
echo "\nIf you see any ✗ marks, there's a configuration issue to fix.\n";
echo "After reviewing, you can delete this file: diagnostic.php\n";
?>
