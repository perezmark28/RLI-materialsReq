<?php
/**
 * Main Front Controller
 * Single entry point for all requests
 */

// Test mode: access with ?_test=1 to see diagnostic info
if (isset($_GET['_test'])) {
    echo "<!DOCTYPE html><html><head><title>MVC Test</title></head><body>";
    echo "<h1>✓ Index.php is accessible!</h1>";
    echo "<p>REQUEST_URI: " . htmlspecialchars($_SERVER['REQUEST_URI']) . "</p>";
    echo "<p>SCRIPT_NAME: " . htmlspecialchars($_SERVER['SCRIPT_NAME']) . "</p>";
    echo "</body></html>";
    exit;
}

// Initialize application
require_once __DIR__ . '/config/bootstrap.php';

// Security headers (improve grade on Security Headers / Snyk scan)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

// Handle errors
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

// Create router
$router = new \App\Core\Router();

// Define routes
// Public routes
$router->get('/', 'Home@index');
$router->get('/login', 'Auth@loginPage');
$router->post('/login', 'Auth@login');
$router->get('/signup', 'Auth@signupPage');
$router->post('/signup', 'Auth@signup');
$router->get('/logout', 'Auth@logout');

// Protected routes
$router->get('/home', 'Home@home');
$router->get('/dashboard', 'Home@dashboard');
$router->get('/profile', 'Home@profile');
$router->post('/profile', 'Home@profile');
$router->get('/statistics', 'Home@statistics');

// Request routes (specific paths before {id} so /requests/print and /requests/create match first)
$router->get('/requests', 'Request@index');
$router->get('/requests/create', 'Request@create');
$router->post('/requests/create', 'Request@store');
$router->get('/requests/print', 'Request@printAll');
$router->get('/requests/{id}/print', 'Request@printOne');
$router->get('/requests/{id}', 'Request@show');
$router->get('/requests/{id}/edit', 'Request@edit');
$router->post('/requests/{id}/edit', 'Request@update');
$router->post('/requests/{id}/delete', 'Request@delete');
$router->post('/requests/{id}/approve', 'Request@approve');
$router->post('/requests/{id}/decline', 'Request@decline');
$router->get('/supervisors/{id}/info', 'Request@supervisorInfo');

// User management (super admin)
$router->get('/users', 'User@index');
$router->get('/users/create', 'User@create');
$router->post('/users/create', 'User@store');
$router->get('/users/{id}/edit', 'User@edit');
$router->post('/users/{id}/edit', 'User@update');
$router->post('/users/{id}/delete', 'User@delete');

// Supplier management (admin/super admin)
$router->get('/suppliers', 'Supplier@index');
$router->get('/suppliers/create', 'Supplier@create');
$router->post('/suppliers/create', 'Supplier@store');
$router->get('/suppliers/{id}/edit', 'Supplier@edit');
$router->post('/suppliers/{id}/edit', 'Supplier@update');
$router->post('/suppliers/{id}/delete', 'Supplier@delete');

// Dispatch the request
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug logging (remove after testing)
if (APP_ENV === 'development') {
    error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);
    error_log("SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
    error_log("PATH before processing: " . $path);
}

// Remove base path if running in subdirectory
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if (!empty($base_path) && strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}
$path = $path ?: '/';

// Debug logging
if (APP_ENV === 'development') {
    error_log("Computed base_path: " . $base_path);
    error_log("Final PATH: " . $path);
}

try {
    $router->dispatch($path);
} catch (\Exception $e) {
    if (APP_ENV === 'development') {
        echo "Error: " . $e->getMessage();
        echo "\n\nTrace:\n" . $e->getTraceAsString();
    } else {
        http_response_code(500);
        echo "500 Internal Server Error";
    }
}

