<?php
// Session + RBAC helpers

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return isset($_SESSION['user']) && is_array($_SESSION['user']);
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('current_role')) {
    function current_role(): ?string {
        return $_SESSION['user']['role'] ?? null;
    }
}

if (!function_exists('require_login')) {
    function require_login(): void {
        if (!is_logged_in()) {
            $loginPath = (defined('BASE_PATH') && BASE_PATH !== '') ? BASE_PATH . '/login' : '/login';
            header('Location: ' . $loginPath);
            exit;
        }
    }
}

if (!function_exists('require_role')) {
    function require_role(array $allowed_roles): void {
        require_login();
        $role = current_role();
        if (!$role || !in_array($role, $allowed_roles, true)) {
            http_response_code(403);
            echo "403 Forbidden";
            exit;
        }
    }
}

if (!function_exists('can_manage_requests')) {
    function can_manage_requests(): bool {
        $r = current_role();
        return $r === 'admin' || $r === 'super_admin';
    }
}

if (!function_exists('can_manage_users')) {
    function can_manage_users(): bool {
        return current_role() === 'super_admin';
    }
}

if (!function_exists('can_manage_suppliers')) {
    function can_manage_suppliers(): bool {
        $r = current_role();
        return $r === 'admin' || $r === 'super_admin';
    }
}

// Simple flash messages (for redirect flows)
if (!function_exists('flash_set')) {
    function flash_set(string $type, string $message): void {
        $_SESSION['_flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('flash_get')) {
    function flash_get(): ?array {
        if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
            return null;
        }
        $f = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $f;
    }
}

