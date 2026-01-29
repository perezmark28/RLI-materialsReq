<?php
/**
 * One-time account setup script for RLI RBAC system.
 *
 * Creates/updates:
 *  - Admin: mts / admin123
 *  - Admin: pjj / admin123
 *  - Admin: alu / admin123
 *  - Super Admin: apl / superadmin123
 *
 * IMPORTANT: Run once, then delete this file.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db_connect.php';

function getRoleId(mysqli $conn, string $roleName): int {
    $stmt = $conn->prepare("SELECT id FROM roles WHERE role_name=? LIMIT 1");
    $stmt->bind_param("s", $roleName);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$row) {
        throw new Exception("Role not found: " . $roleName . ". Import schema.sql first.");
    }
    return (int)$row['id'];
}

function upsertUser(mysqli $conn, string $username, string $password, string $fullName, string $email, int $roleId): void {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // If exists, update; else insert
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $existing = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($existing) {
        $id = (int)$existing['id'];
        $u = $conn->prepare("UPDATE users SET password_hash=?, full_name=?, email=?, role_id=?, status='active' WHERE id=?");
        $u->bind_param("sssii", $hash, $fullName, $email, $roleId, $id);
        if (!$u->execute()) {
            throw new Exception("Failed updating user {$username}: " . $u->error);
        }
        $u->close();
    } else {
        $i = $conn->prepare("INSERT INTO users (username, password_hash, full_name, email, role_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $i->bind_param("ssssi", $username, $hash, $fullName, $email, $roleId);
        if (!$i->execute()) {
            throw new Exception("Failed creating user {$username}: " . $i->error);
        }
        $i->close();
    }
}

try {
    // Ensure roles exist (schema.sql already inserts them, but this is safe)
    $conn->query("INSERT INTO roles (role_name) VALUES ('viewer'),('admin'),('super_admin') ON DUPLICATE KEY UPDATE role_name=VALUES(role_name)");

    $adminRoleId = getRoleId($conn, 'admin');
    $superAdminRoleId = getRoleId($conn, 'super_admin');

    // Admins
    upsertUser($conn, 'mts', 'admin123', 'MTS Admin', 'mts.admin@lic.ph', $adminRoleId);
    upsertUser($conn, 'pjj', 'admin123', 'PJJ Admin', 'pjj.admin@lic.ph', $adminRoleId);
    upsertUser($conn, 'alu', 'admin123', 'ALU Admin', 'alu.admin@lic.ph', $adminRoleId);

    // Super Admin
    upsertUser($conn, 'apl', 'superadmin123', 'APL Super Admin', 'apl.admin@lic.ph', $superAdminRoleId);

    header('Content-Type: text/plain; charset=utf-8');
    echo "Accounts created/updated successfully.\n\n";
    echo "Admin accounts:\n";
    echo " - mts / admin123\n";
    echo " - pjj / admin123\n";
    echo " - alu / admin123\n\n";
    echo "Super Admin account:\n";
    echo " - apl / superadmin123\n\n";
    echo "IMPORTANT: Delete setup_accounts.php now.\n";
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: " . $e->getMessage() . "\n";
}

