<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_login();

$user = current_user();
$role = current_role();

// For Admins: restrict view to requests assigned to their supervisor (based on username -> supervisors.initials).
// Super Admin sees all requests.
$supervisor_filter_id = null;
if ($role === 'admin') {
    $username = strtoupper(trim($user['username'] ?? ''));
    if ($username !== '') {
        $supStmt = $conn->prepare("SELECT id FROM supervisors WHERE UPPER(initials) = ? LIMIT 1");
        if ($supStmt) {
            $supStmt->bind_param("s", $username);
            $supStmt->execute();
            $supRes = $supStmt->get_result();
            if ($supRes && ($supRow = $supRes->fetch_assoc())) {
                $supervisor_filter_id = (int)$supRow['id'];
            }
            $supStmt->close();
        }
    }
}

// Filters
$status_filter = trim($_GET['status'] ?? '');
$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];
$types = '';

if ($role === 'viewer') {
    $where[] = "mr.user_id = ?";
    $types .= "i";
    $params[] = (int)$user['id'];
} elseif ($role === 'admin' && $supervisor_filter_id !== null) {
    // Admin sees only requests mapped to their supervisor initials
    $where[] = "mr.supervisor_id = ?";
    $types .= "i";
    $params[] = $supervisor_filter_id;
}

if (in_array($status_filter, ['pending','approved','declined'], true)) {
    $where[] = "mr.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

if ($q !== '') {
    $like = '%' . $q . '%';
    $where[] = "(mr.requester_name LIKE ? OR mr.particulars LIKE ? OR u.username LIKE ? OR s.initials LIKE ? OR s.email LIKE ? OR mr.id = ?)";
    $types .= "sssss" . "i";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = ctype_digit($q) ? (int)$q : 0;
}

$where_sql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// Request-level rows (earlier layout)
$sql = "
SELECT
  mr.id,
  mr.requester_name,
  mr.date_requested,
  mr.date_needed,
  mr.particulars,
  mr.status,
  mr.created_at,
  mr.user_id,
  u.username AS owner_username,
  s.initials AS supervisor_initials,
  s.email AS supervisor_email,
  COUNT(ri.id) AS items_count,
  COALESCE(SUM(ri.amount),0) AS total_amount
FROM material_requests mr
LEFT JOIN request_items ri ON mr.id = ri.request_id
LEFT JOIN users u ON mr.user_id = u.id
LEFT JOIN supervisors s ON mr.supervisor_id = s.id
$where_sql
GROUP BY mr.id
ORDER BY mr.id DESC
";

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

ui_layout_start('Requests - RLI', 'requests');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?php echo $role === 'viewer' ? 'My Requests' : 'All Requests'; ?></h1>
    <p class="text-slate-500 mt-1">Track requests and status updates.</p>
  </div>
  <a href="create_request.php" class="px-4 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95">Create Request</a>
</div>

<div class="mt-6 flex items-end gap-3 flex-wrap">
  <form method="GET" action="requests.php" class="flex items-end gap-3 flex-wrap">
    <div>
      <label for="q" class="block text-sm font-semibold text-slate-700 mb-2">Search</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($q); ?>"
        placeholder="Search ID, requester, supervisor, item..."
        class="min-w-[280px] rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
      <select id="status" name="status"
        class="min-w-[220px] rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
        <option value="">All</option>
        <option value="pending" <?php echo $status_filter==='pending'?'selected':''; ?>>Pending</option>
        <option value="approved" <?php echo $status_filter==='approved'?'selected':''; ?>>Approved</option>
        <option value="declined" <?php echo $status_filter==='declined'?'selected':''; ?>>Declined</option>
      </select>
    </div>
    <button class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" type="submit">Filter</button>
  </form>
  <a href="requests.php" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Reset</a>
</div>

<div class="mt-6 overflow-x-auto rounded-card bg-bgGrey border border-slate-100 p-4">
  <div class="overflow-x-auto rounded-2xl bg-white border border-slate-100">
      <table class="min-w-[1200px] w-full">
      <thead>
        <tr class="text-left text-slate-500 text-sm">
            <th class="py-4 px-4 font-semibold">ID</th>
            <?php if ($role !== 'viewer'): ?><th class="py-4 px-4 font-semibold">Owner</th><?php endif; ?>
            <th class="py-4 px-4 font-semibold">Requester</th>
            <th class="py-4 px-4 font-semibold">Date Requested</th>
            <th class="py-4 px-4 font-semibold">Date Needed</th>
            <th class="py-4 px-4 font-semibold">Supervisor</th>
            <th class="py-4 px-4 font-semibold">Email</th>
            <th class="py-4 px-4 font-semibold">Items</th>
            <th class="py-4 px-4 font-semibold">Total</th>
            <th class="py-4 px-4 font-semibold">Approval Status</th>
            <th class="py-4 px-4 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="text-slate-900">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php
              $can_edit = false;
              if ($role === 'viewer') {
                $can_edit = ((int)$row['user_id'] === (int)$user['id']) && ($row['status'] === 'pending');
              } else {
                $can_edit = true;
              }
              $badge = 'bg-red-100 text-red-700';
              if ($row['status'] === 'approved') $badge = 'bg-green-100 text-green-700';
              if ($row['status'] === 'declined') $badge = 'bg-slate-200 text-slate-700';
            ?>
            <tr class="border-t border-slate-100">
              <td class="py-4 px-4 font-semibold text-slate-800">#<?php echo (int)$row['id']; ?></td>
              <?php if ($role !== 'viewer'): ?><td class="py-4 px-4 text-slate-700"><?php echo htmlspecialchars($row['owner_username'] ?? ''); ?></td><?php endif; ?>
              <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($row['requester_name']); ?></td>
              <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($row['date_requested']); ?></td>
              <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($row['date_needed']); ?></td>
              <td class="py-4 px-4 text-slate-700"><?php echo htmlspecialchars($row['supervisor_initials'] ?? ''); ?></td>
              <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($row['supervisor_email'] ?? ''); ?></td>
              <td class="py-4 px-4 text-center text-slate-700 font-semibold"><?php echo (int)$row['items_count']; ?></td>
              <td class="py-4 px-4 font-semibold"><?php echo number_format((float)$row['total_amount'], 2); ?></td>
              <td class="py-4 px-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo $badge; ?>">
                  <?php echo htmlspecialchars(strtoupper($row['status'])); ?>
                </span>
              </td>
              <td class="py-4 px-4 text-right whitespace-nowrap">
                <a class="px-3 py-2 mr-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95" href="request_view.php?id=<?php echo (int)$row['id']; ?>">View</a>
                <?php if ($can_edit): ?>
                  <a class="px-3 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" href="request_edit.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                <?php endif; ?>

                <?php if (can_manage_requests() && $row['status'] === 'pending'): ?>
                  <form method="POST" action="request_action.php" class="inline">
                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                    <input type="hidden" name="action" value="approve">
                    <button type="submit" class="px-3 py-2 rounded-xl bg-green-600 text-white font-semibold hover:opacity-95">Accept</button>
                  </form>
                  <form method="POST" action="request_action.php" class="inline">
                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                    <input type="hidden" name="action" value="decline">
                    <button type="submit" class="px-3 py-2 rounded-xl bg-red-600 text-white font-semibold hover:opacity-95">Decline</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr class="border-t border-slate-100">
            <td class="py-6 px-4 text-slate-600" colspan="<?php echo $role !== 'viewer' ? '11' : '10'; ?>">No requests found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php ui_layout_end(); ?>

