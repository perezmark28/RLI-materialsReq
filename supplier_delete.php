<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';

// Only Super Admin can delete (extra safety)
require_role(['super_admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo "Bad Request";
    exit;
}

$stmt = $conn->prepare("DELETE FROM suppliers WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header('Location: suppliers.php');
exit;

