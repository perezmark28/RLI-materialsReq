<?php
// Session + RBAC helpers

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

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: login.php');
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

// Simple flash messages (for redirect flows)
function flash_set(string $type, string $message): void {
    $_SESSION['_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function flash_get(): ?array {
    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        return null;
    }
    $f = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return $f;
}

