<?php
/**
 * Bootstrap/Configuration File
 * Sets up autoloading and application configuration
 */

// Define app root (parent directory of config/)
define('APP_ROOT', dirname(__DIR__));
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// Base path for URLs (useful when running inside a subdirectory)
// e.g. if SCRIPT_NAME is /RLI-materialsReq/index.php then BASE_PATH -> /RLI-materialsReq
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
define('BASE_PATH', $scriptDir === '/' ? '' : $scriptDir);

// Autoloader
spl_autoload_register(function ($class) {
    // Namespace prefix
    $prefix = 'App\\';
    $config_prefix = 'Config\\';

    // Check App namespace
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = APP_ROOT . '/app/' . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }

    // Check Config namespace
    if (strpos($class, $config_prefix) === 0) {
        $relative_class = substr($class, strlen($config_prefix));
        $file = APP_ROOT . '/config/' . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Load helpers
require_once APP_ROOT . '/config/helpers.php';
require_once APP_ROOT . '/config/sms.php';
