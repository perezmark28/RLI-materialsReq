<?php
/**
 * One-time script: Update supervisor mobile numbers (PJJ and ALU to +639178187240).
 * Run once by visiting: your-site.com/update-supervisor-mobiles?run=1
 * DELETE this file after use for security.
 */
require_once __DIR__ . '/config/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['run']) || $_GET['run'] !== '1') {
    echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem;">';
    echo '<h1>Update supervisor mobiles</h1>';
    echo '<p>To run the update, visit: <strong>' . htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') . (strpos($_SERVER['REQUEST_URI'] ?? '', '?') !== false ? '&' : '?') . 'run=1</strong></p>';
    echo '</body></html>';
    exit;
}

$conn = \Config\Database::getInstance()->getConnection();

$mobile = '+639178187240';
$initials = ['PJJ', 'ALU'];

$stmt = $conn->prepare('UPDATE supervisors SET mobile = ? WHERE initials = ?');
$stmt->bind_param('ss', $mobile, $initial);

$updated = [];
foreach ($initials as $initial) {
    $stmt->execute();
    $updated[$initial] = $conn->affected_rows;
}
$stmt->close();

// Show current state
$result = $conn->query('SELECT initials, email, mobile FROM supervisors ORDER BY initials');
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$result->close();

echo '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem;">';
echo '<h1>Supervisor mobiles updated</h1>';
echo '<p>PJJ rows updated: ' . (int)($updated['PJJ'] ?? 0) . ', ALU rows updated: ' . (int)($updated['ALU'] ?? 0) . '</p>';
echo '<table border="1" cellpadding="8"><tr><th>Initials</th><th>Email</th><th>Mobile</th></tr>';
foreach ($rows as $r) {
    echo '<tr><td>' . htmlspecialchars($r['initials']) . '</td><td>' . htmlspecialchars($r['email']) . '</td><td>' . htmlspecialchars($r['mobile'] ?? '') . '</td></tr>';
}
echo '</table>';
echo '<p><strong>Delete this file (update_supervisor_mobiles_once.php) after use.</strong></p>';
echo '</body></html>';
