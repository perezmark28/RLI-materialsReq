<?php
/**
 * Router Class
 * Handles URL routing to appropriate controllers and actions
 */
namespace App\Core;

class Router {
    private $routes = [];
    private $currentRoute = null;

    /**
     * Register a route
     */
    public function add($method, $path, $callback) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $this->normalizePath($path),
            'callback' => $callback
        ];
    }

    /**
     * Register a GET route
     */
    public function get($path, $callback) {
        $this->add('GET', $path, $callback);
    }

    /**
     * Register a POST route
     */
    public function post($path, $callback) {
        $this->add('POST', $path, $callback);
    }

    /**
     * Match and dispatch request
     */
    public function dispatch($path, $method = null) {
        $method = $method ?? $_SERVER['REQUEST_METHOD'];
        $path = $this->normalizePath($path);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $routePattern = $this->convertPathToPattern($route['path']);
            if (preg_match($routePattern, $path, $matches)) {
                $this->currentRoute = $route;
                return $this->executeCallback($route['callback'], $matches);
            }
        }

        return $this->notFound();
    }

    /**
     * Normalize path
     */
    private function normalizePath($path) {
        $path = trim($path, '/');
        $path = '/' . $path;
        return $path;
    }

    /**
     * Convert path to regex pattern
     */
    private function convertPathToPattern($path) {
        $pattern = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Execute route callback
     */
    private function executeCallback($callback, $matches = []) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)));
        }

        if (is_string($callback)) {
            list($controller, $action) = explode('@', $callback);
            return $this->executeController($controller, $action, $matches);
        }

        throw new \Exception("Invalid callback");
    }

    /**
     * Execute controller action
     */
    private function executeController($controller, $action, $matches) {
        $class = 'App\\Controllers\\' . $controller . 'Controller';

        if (!class_exists($class)) {
            throw new \Exception("Controller not found: " . $class);
        }

        $controllerInstance = new $class();

        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("Action not found: " . $action);
        }

        $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
        return call_user_func_array([$controllerInstance, $action], $params);
    }

    /**
     * 404 Not Found
     */
    private function notFound() {
        http_response_code(404);
        echo "404 Not Found";
        exit;
    }
}
