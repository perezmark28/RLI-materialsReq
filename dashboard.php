<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_role(['admin','super_admin']);

$user = current_user();
$role = current_role();

// Simple real-data summary for dashboard
// - Admin: only for requests assigned to their supervisor (based on username -> supervisor.initials)
// - Super Admin: all requests
$summary = [
  'pending' => 0,
  'approved' => 0,
  'declined' => 0,
  'total' => 0,
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
    $stmt = $conn->prepare("SELECT status, COUNT(*) c FROM material_requests WHERE supervisor_id = ? GROUP BY status");
    if ($stmt) {
      $stmt->bind_param("i", $supId);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res) {
        while ($r = $res->fetch_assoc()) {
          $summary[$r['status']] = (int)$r['c'];
          $summary['total'] += (int)$r['c'];
        }
      }
      $stmt->close();
    }
  }
} else {
  // super_admin: all requests
  $q = $conn->query("SELECT status, COUNT(*) c FROM material_requests GROUP BY status");
  if ($q) {
    while ($r = $q->fetch_assoc()) {
      $summary[$r['status']] = (int)$r['c'];
      $summary['total'] += (int)$r['c'];
    }
  }
}

ui_layout_start('Dashboard - RLI', 'dashboard');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-slate-500 mt-1">Quick overview of material request statuses.</p>
  </div>
  <a href="requests.php" class="px-4 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95">Go to Requests</a>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Total</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo (int)$summary['total']; ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Pending</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo (int)$summary['pending']; ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Approved</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo (int)$summary['approved']; ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-5">
    <div class="text-slate-500 text-sm">Declined</div>
    <div class="text-2xl font-bold text-slate-900 mt-1"><?php echo (int)$summary['declined']; ?></div>
  </div>
</div>

<?php ui_layout_end(); ?>

