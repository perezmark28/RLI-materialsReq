<?php
/**
 * One-time script: create Super Admin account only.
 * Creates/updates: apl / superadmin123
 *
 * Run once in browser, then delete this file.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db_connect.php';

$username = 'apl';
$password = 'superadmin123';
$fullName = 'APL Super Admin';
$email = 'apl.admin@lic.ph';

try {
    // Ensure super_admin role exists
    $conn->query("INSERT INTO roles (role_name) VALUES ('viewer'),('admin'),('super_admin') ON DUPLICATE KEY UPDATE role_name=VALUES(role_name)");

    $stmt = $conn->prepare("SELECT id FROM roles WHERE role_name='super_admin' LIMIT 1");
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    if (!$row) {
        throw new Exception("Role 'super_admin' not found. Import schema.sql first.");
    }
    $roleId = (int)$row['id'];

    $hash = password_hash($password, PASSWORD_DEFAULT);

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
            throw new Exception("Failed updating super admin: " . $u->error);
        }
        $u->close();
        $msg = "Super Admin account updated.";
    } else {
        $i = $conn->prepare("INSERT INTO users (username, password_hash, full_name, email, role_id, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $i->bind_param("ssssi", $username, $hash, $fullName, $email, $roleId);
        if (!$i->execute()) {
            throw new Exception("Failed creating super admin: " . $i->error);
        }
        $i->close();
        $msg = "Super Admin account created.";
    }

    header('Content-Type: text/plain; charset=utf-8');
    echo "Success.\n\n";
    echo $msg . "\n\n";
    echo "Super Admin login:\n";
    echo "  Username: apl\n";
    echo "  Password: superadmin123\n\n";
    echo "IMPORTANT: Delete create_superadmin.php after use.\n";
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: " . $e->getMessage() . "\n";
}
