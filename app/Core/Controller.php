<?php
/**
 * Base Controller Class
 * Provides common functionality for all controllers
 */
namespace App\Core;

class Controller {
    protected $viewPath = __DIR__ . '/../Views/';

    /**
     * Base constructor — kept empty so child controllers can call parent::__construct()
     */
    public function __construct() {
        // intentionally empty
    }

    /**
     * Render a view with data
     */
    protected function view($name, $data = []) {
        $file = $this->viewPath . $name . '.php';
        
        if (!file_exists($file)) {
            throw new \Exception("View not found: " . $file);
        }

        extract($data);
        include $file;
    }

    /**
     * Render a view and return as string
     */
    protected function renderView($name, $data = []) {
        ob_start();
        $this->view($name, $data);
        $content = ob_get_clean();
        return $content;
    }

    /**
     * JSON response
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect($url) {
        // If running in a subdirectory, keep redirects inside BASE_PATH
        if (defined('BASE_PATH') && strpos($url, '/') === 0) {
            $url = (BASE_PATH !== '' ? BASE_PATH : '') . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Get request data (GET/POST)
     */
    protected function request($key = null, $default = null) {
        $data = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    /**
     * Get POST data only
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }

        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data only
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }

        return $_GET[$key] ?? $default;
    }
}
