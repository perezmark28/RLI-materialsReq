<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_role(['admin','super_admin']);

$user = current_user();
$role = current_role();

$stats = [
  'pending' => 0,
  'approved' => 0,
  'declined' => 0,
  'total_amount' => 0.0,
];

if ($role === 'admin') {
  $username = strtoupper(trim($user['username'] ?? ''));
  $supId = null;
  if ($username !== '') {
    $st = $conn->prepare("SELECT id FROM supervisors WHERE UPPER(initials) = ? LIMIT 1");
    if ($st) {
      $st->bind_param("s", $username);
      $st->execute();
      $res = $st->get_result();
      if ($res && ($row = $res->fetch_assoc())) {
        $supId = (int)$row['id'];
      }
      $st->close();
    }
  }

  if ($supId !== null) {
    // Counts by status for this admin's assigned requests
    $stmt1 = $conn->prepare("SELECT status, COUNT(*) c FROM material_requests WHERE supervisor_id = ? GROUP BY status");
    if ($stmt1) {
      $stmt1->bind_param("i", $supId);
      $stmt1->execute();
      $res1 = $stmt1->get_result();
      if ($res1) {
        while ($r = $res1->fetch_assoc()) {
          $stats[$r['status']] = (int)$r['c'];
        }
      }
      $stmt1->close();
    }

    // Total amount for this admin's assigned requests
    $stmt2 = $conn->prepare("
      SELECT COALESCE(SUM(ri.amount),0) total_amount
      FROM request_items ri
      JOIN material_requests mr ON mr.id = ri.request_id
      WHERE mr.supervisor_id = ?
    ");
    if ($stmt2) {
      $stmt2->bind_param("i", $supId);
      $stmt2->execute();
      $res2 = $stmt2->get_result();
      if ($res2) {
        $stats['total_amount'] = (float)($res2->fetch_assoc()['total_amount'] ?? 0);
      }
      $stmt2->close();
    }
  }
} else {
  // super_admin: all requests
  $q1 = $conn->query("SELECT status, COUNT(*) c FROM material_requests GROUP BY status");
  if ($q1) {
    while ($r = $q1->fetch_assoc()) {
      $stats[$r['status']] = (int)$r['c'];
    }
  }

  $q2 = $conn->query("SELECT COALESCE(SUM(ri.amount),0) total_amount FROM request_items ri");
  if ($q2) {
    $stats['total_amount'] = (float)($q2->fetch_assoc()['total_amount'] ?? 0);
  }
}

ui_layout_start('Statistics - RLI', 'statistics');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Statistics</h1>
    <p class="text-slate-500 mt-1">High-level totals across all requests.</p>
  </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Pending</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo (int)$stats['pending']; ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Approved</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo (int)$stats['approved']; ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Declined</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo (int)$stats['declined']; ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Total Amount (All Items)</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo number_format((float)$stats['total_amount'], 2); ?></div>
  </div>
</div>

<?php ui_layout_end(); ?>

