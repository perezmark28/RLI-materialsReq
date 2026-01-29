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

// Load request
$stmt = $conn->prepare("
    SELECT mr.*, s.initials as supervisor_initials, s.email as supervisor_email
    FROM material_requests mr
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

// Authorization: viewer can only edit own pending request
if ($role === 'viewer') {
    if ((int)$mr['user_id'] !== (int)$user['id'] || $mr['status'] !== 'pending') {
        http_response_code(403);
        echo "403 Forbidden";
        exit;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requester_name = trim($_POST['requester_name'] ?? '');
    $date_requested = $_POST['date_requested'] ?? '';
    $date_needed = $_POST['date_needed'] ?? '';
    $particulars = trim($_POST['particulars'] ?? '');
    $supervisor_id = ($_POST['supervisor_id'] ?? '') !== '' ? (int)$_POST['supervisor_id'] : null;

    if ($requester_name === '' || $date_requested === '' || $date_needed === '') {
        $error = 'Requester name and dates are required.';
    } else {
        // Admin/Super can edit any; viewer limited already
        $stmt = $conn->prepare("UPDATE material_requests SET requester_name=?, date_requested=?, date_needed=?, particulars=?, supervisor_id=? WHERE id=?");
        $stmt->bind_param("ssssii", $requester_name, $date_requested, $date_needed, $particulars, $supervisor_id, $id);
        if ($stmt->execute()) {
            $success = 'Request updated.';
            // reload
            $mr['requester_name'] = $requester_name;
            $mr['date_requested'] = $date_requested;
            $mr['date_needed'] = $date_needed;
            $mr['particulars'] = $particulars;
            $mr['supervisor_id'] = $supervisor_id;
        } else {
            $error = 'Update failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}
ui_layout_start('Edit Request - RLI', 'requests');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Edit Request #<?php echo (int)$id; ?></h1>
    <p class="text-slate-500 mt-1">Update request details (items are edited on the request form submission flow).</p>
  </div>
  <a href="requests.php" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Back</a>
</div>

<?php if ($error): ?>
  <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="mt-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3 text-green-700 font-semibold"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="md:col-span-2">
    <label for="particulars" class="block text-sm font-semibold text-slate-700 mb-2">Particulars</label>
    <textarea id="particulars" name="particulars" rows="3"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"><?php echo htmlspecialchars($mr['particulars'] ?? ''); ?></textarea>
  </div>

  <div>
    <label for="requester_name" class="block text-sm font-semibold text-slate-700 mb-2">Requested By</label>
    <input id="requester_name" name="requester_name" type="text" required value="<?php echo htmlspecialchars($mr['requester_name']); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>

  <div>
    <label for="date_requested" class="block text-sm font-semibold text-slate-700 mb-2">Date Requested</label>
    <input id="date_requested" name="date_requested" type="date" required value="<?php echo htmlspecialchars($mr['date_requested']); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>

  <div>
    <label for="date_needed" class="block text-sm font-semibold text-slate-700 mb-2">Date Needed</label>
    <input id="date_needed" name="date_needed" type="date" required value="<?php echo htmlspecialchars($mr['date_needed']); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>

  <div>
    <label for="supervisor_id" class="block text-sm font-semibold text-slate-700 mb-2">Supervisor</label>
    <select id="supervisor_id" name="supervisor_id"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      <option value="">Select Supervisor</option>
      <?php
        $sup = $conn->query("SELECT id, initials, email FROM supervisors ORDER BY initials ASC");
        if ($sup) {
          while ($s = $sup->fetch_assoc()) {
            $selected = ((int)$mr['supervisor_id'] === (int)$s['id']) ? 'selected' : '';
            echo '<option value="' . (int)$s['id'] . '" data-email="' . htmlspecialchars($s['email']) . '" ' . $selected . '>' . htmlspecialchars($s['initials']) . '</option>';
          }
        }
      ?>
    </select>
  </div>

  <div>
    <label for="supervisor_email" class="block text-sm font-semibold text-slate-700 mb-2">Supervisor Email</label>
    <input id="supervisor_email" type="email" readonly value="<?php echo htmlspecialchars($mr['supervisor_email'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-600">
  </div>

  <div class="md:col-span-2 flex gap-3 flex-wrap mt-2">
    <button class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Save</button>
    <a class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" href="requests.php">Cancel</a>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sel = document.getElementById('supervisor_id');
  const email = document.getElementById('supervisor_email');
  if (!sel || !email) return;
  sel.addEventListener('change', function() {
    const opt = sel.options[sel.selectedIndex];
    email.value = (opt && opt.dataset && opt.dataset.email) ? opt.dataset.email : '';
  });
});
</script>

<?php ui_layout_end(); ?>

