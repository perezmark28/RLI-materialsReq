<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_role(['super_admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo "Bad Request"; exit; }

// Prevent deleting yourself
if ($id === (int)($_SESSION['user']['id'] ?? 0)) {
    header('Location: users.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header('Location: users.php');
exit;

