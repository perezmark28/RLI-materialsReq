<?php
/**
 * Helper Functions
 * Authentication and common utilities
 */

/**
 * Sanitize value as integer (SQL injection safe for IDs)
 */
function sanitize_int($value, $default = 0): int {
    return is_numeric($value) ? (int) $value : $default;
}

/**
 * Sanitize string for safe use (max length, trim)
 */
function sanitize_string($value, int $maxLength = 1000): string {
    $trimmed = trim((string) ($value ?? ''));
    return mb_strlen($trimmed) > $maxLength ? mb_substr($trimmed, 0, $maxLength) : $trimmed;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function current_role(): ?string {
    return $_SESSION['user']['role'] ?? null;
}

function current_user_id(): ?int {
    return $_SESSION['user']['id'] ?? null;
}

function require_login(): void {
    if (!is_logged_in()) {
        // Redirect using BASE_PATH so subdirectory installations work correctly
        $loginPath = (defined('BASE_PATH') && BASE_PATH !== '') ? BASE_PATH . '/login' : '/login';
        header('Location: ' . $loginPath);
        exit;
    }
}

function require_role(array $allowed_roles): void {
    require_login();
    $role = current_role();
    if (!$role || !in_array($role, $allowed_roles, true)) {
        http_response_code(403);
        echo "403 Forbidden";
        exit;
    }
}

function can_manage_requests(): bool {
    $r = current_role();
    return $r === 'admin' || $r === 'super_admin';
}

function can_manage_users(): bool {
    return current_role() === 'super_admin';
}

function can_manage_suppliers(): bool {
    $r = current_role();
    return $r === 'admin' || $r === 'super_admin';
}

function login_user($user_data) {
    $_SESSION['user'] = $user_data;
}

function logout_user() {
    session_destroy();
}

function set_flash($key, $value) {
    $_SESSION['flash'][$key] = $value;
}

function get_flash($key) {
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function has_flash($key) {
    return isset($_SESSION['flash'][$key]);
}
