<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
ob_start();

$out = ['success' => false];

require_once __DIR__ . '/includes/auth.php';
if (!is_logged_in()) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    require_once __DIR__ . '/includes/db_connect.php';
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Bad Request']);
    exit;
}

$stmt = $conn->prepare("SELECT initials, email, mobile FROM supervisors WHERE id = ? LIMIT 1");
if (!$stmt) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();
$conn->close();

if (!$row) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Not Found']);
    exit;
}

ob_clean();
echo json_encode([
    'success' => true,
    'initials' => $row['initials'],
    'email' => $row['email'],
    'mobile' => $row['mobile'],
]);
exit;

