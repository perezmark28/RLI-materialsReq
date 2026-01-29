<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_login();

$role = current_role();
$user = current_user();
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo "Bad Request";
    exit;
}

// Load request header
$stmt = $conn->prepare("
  SELECT mr.*, u.username AS owner_username, s.initials AS supervisor_initials, s.email AS supervisor_email
  FROM material_requests mr
  LEFT JOIN users u ON mr.user_id = u.id
  LEFT JOIN supervisors s ON mr.supervisor_id = s.id
  WHERE mr.id = ?
  LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$mr = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$mr) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

// Authorization:
// - viewer: can only see own request
// - admin: can only see requests mapped to their supervisor (based on username -> supervisor.initials)
// - super_admin: can view all requests
if ($role === 'viewer' && (int)$mr['user_id'] !== (int)$user['id']) {
    http_response_code(403);
    echo "403 Forbidden";
    exit;
}

if ($role === 'admin') {
    $username = strtoupper(trim($user['username'] ?? ''));
    if ($username !== '') {
        $supStmt = $conn->prepare("SELECT id FROM supervisors WHERE UPPER(initials) = ? LIMIT 1");
        if ($supStmt) {
            $supStmt->bind_param("s", $username);
            $supStmt->execute();
            $supRes = $supStmt->get_result();
            $supId = null;
            if ($supRes && ($supRow = $supRes->fetch_assoc())) {
                $supId = (int)$supRow['id'];
            }
            $supStmt->close();
            if ($supId !== null && (int)$mr['supervisor_id'] !== $supId) {
                http_response_code(403);
                echo "403 Forbidden";
                exit;
            }
        }
    }
}

// Load items
$items_stmt = $conn->prepare("
  SELECT item_no, item_name, specs, quantity, unit, price, amount, item_link
  FROM request_items
  WHERE request_id = ?
  ORDER BY item_no ASC
");
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items_res = $items_stmt->get_result();
$items = [];
if ($items_res) {
    while ($r = $items_res->fetch_assoc()) $items[] = $r;
}
$items_stmt->close();

ui_layout_start('Request Details - RLI', 'requests');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Request Details #<?php echo (int)$mr['id']; ?></h1>
    <p class="text-slate-500 mt-1">View all information for this material request.</p>
  </div>
  <a href="requests.php" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Back</a>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase">No.</div>
    <div class="mt-1 text-lg font-bold text-slate-900">#<?php echo (int)$mr['id']; ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase">Requester</div>
    <div class="mt-1 text-lg font-semibold text-slate-900"><?php echo htmlspecialchars($mr['requester_name']); ?></div>
    <?php if (!empty($mr['owner_username'])): ?>
      <div class="text-xs text-slate-500 mt-1">Owner: <?php echo htmlspecialchars($mr['owner_username']); ?></div>
    <?php endif; ?>
  </div>
  <div class="md:col-span-2 rounded-2xl bg-bgGrey border border-slate-100 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase">Particulars</div>
    <div class="mt-1 text-slate-800"><?php echo nl2br(htmlspecialchars($mr['particulars'] ?? '')); ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase">Date Requested</div>
    <div class="mt-1 text-slate-800"><?php echo htmlspecialchars($mr['date_requested']); ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase">Date Needed</div>
    <div class="mt-1 text-slate-800"><?php echo htmlspecialchars($mr['date_needed']); ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase">Approved By Supervisor</div>
    <div class="mt-1 text-slate-800"><?php echo htmlspecialchars($mr['supervisor_initials'] ?? '—'); ?></div>
    <div class="text-xs text-slate-500 mt-1"><?php echo htmlspecialchars($mr['supervisor_email'] ?? ''); ?></div>
  </div>
  <div class="rounded-2xl bg-bgGrey border border-slate-100 p-4">
    <div class="text-xs font-semibold text-slate-500 uppercase">Approval Status</div>
    <?php
      $badge = 'bg-red-100 text-red-700';
      if ($mr['status'] === 'approved') $badge = 'bg-green-100 text-green-700';
      if ($mr['status'] === 'declined') $badge = 'bg-slate-200 text-slate-700';
    ?>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mt-1 <?php echo $badge; ?>">
      <?php echo htmlspecialchars(strtoupper($mr['status'])); ?>
    </span>
  </div>
</div>

<div class="mt-8 rounded-card bg-bgGrey border border-slate-100 p-5">
  <h2 class="text-lg font-bold text-slate-900 mb-3">Item Details</h2>
  <div class="overflow-x-auto rounded-2xl bg-white border border-slate-100">
    <table class="min-w-[1200px] w-full">
      <thead>
        <tr class="text-left text-slate-500 text-sm">
          <th class="py-3 px-4 font-semibold">No.</th>
          <th class="py-3 px-4 font-semibold">Item Name</th>
          <th class="py-3 px-4 font-semibold">Specs</th>
          <th class="py-3 px-4 font-semibold">Quantity</th>
          <th class="py-3 px-4 font-semibold">Unit</th>
          <th class="py-3 px-4 font-semibold">Price</th>
          <th class="py-3 px-4 font-semibold">Amount</th>
        </tr>
      </thead>
      <tbody class="text-slate-900">
        <?php if (count($items) > 0): ?>
          <?php foreach ($items as $it): ?>
            <tr class="border-t border-slate-100">
              <td class="py-3 px-4 text-slate-700 font-semibold"><?php echo (int)$it['item_no']; ?></td>
              <td class="py-3 px-4 text-slate-900 font-semibold"><?php echo htmlspecialchars($it['item_name']); ?></td>
              <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($it['specs'] ?? ''); ?></td>
              <td class="py-3 px-4 text-slate-700 font-semibold"><?php echo htmlspecialchars($it['quantity']); ?></td>
              <td class="py-3 px-4 text-slate-600"><?php echo htmlspecialchars($it['unit'] ?? ''); ?></td>
              <td class="py-3 px-4 font-semibold"><?php echo number_format((float)$it['price'], 2); ?></td>
              <td class="py-3 px-4 font-semibold"><?php echo number_format((float)$it['amount'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr class="border-t border-slate-100">
            <td class="py-4 px-4 text-slate-600" colspan="7">No items found for this request.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php ui_layout_end(); ?>

