<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';

require_role(['admin','super_admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($id <= 0 || !in_array($action, ['approve','decline'], true)) {
    http_response_code(400);
    echo "Bad Request";
    exit;
}

$new_status = $action === 'approve' ? 'approved' : 'declined';
$user_id = (int)($_SESSION['user']['id'] ?? 0);

$conn->begin_transaction();
try {
    // Update request (no delete allowed here)
    $stmt = $conn->prepare("UPDATE material_requests SET status=?, approved_by=?, approved_at=NOW() WHERE id=? AND status='pending'");
    if ($stmt === false) {
        throw new Exception("Prepare failed (update material_requests): " . $conn->error);
    }
    $stmt->bind_param("sii", $new_status, $user_id, $id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected !== 1) {
        $conn->rollback();
        flash_set('warning', 'No changes made. This request may have already been processed (not pending).');
    } else {
        // History (only if update succeeded)
        $h = $conn->prepare("INSERT INTO request_status_history (request_id, status, changed_by, remarks) VALUES (?, ?, ?, '')");
        if ($h !== false) {
            $h->bind_param("isi", $id, $new_status, $user_id);
            $h->execute();
            $h->close();
        }

        $conn->commit();
        if ($h === false) {
            flash_set('warning', 'Request updated, but history log was not saved. Fix: create table `request_status_history`. MySQL: ' . $conn->error);
        } else {
            flash_set('success', $new_status === 'approved' ? 'Request approved successfully.' : 'Request declined successfully.');
        }
    }
} catch (Exception $e) {
    $conn->rollback();
    flash_set('error', 'Action failed: ' . $e->getMessage());
}

header('Location: requests.php');
exit;

