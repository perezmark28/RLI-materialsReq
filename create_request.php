<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_login();

// Load available supervisors that are backed by an Admin/Super Admin user account.
// We assume supervisor.initials matches the UPPER(username) of the user.
$supervisors = [];
$sqlSup = "
  SELECT s.id, s.initials, s.email
  FROM supervisors s
  JOIN users u ON UPPER(u.username) = s.initials
  JOIN roles r ON r.id = u.role_id
  WHERE r.role_name IN ('admin','super_admin')
  ORDER BY s.initials
";
if ($stmtSup = $conn->prepare($sqlSup)) {
    $stmtSup->execute();
    $resSup = $stmtSup->get_result();
    if ($resSup) {
        while ($row = $resSup->fetch_assoc()) {
            $supervisors[] = $row;
        }
    }
    $stmtSup->close();
}

ui_layout_start('Create Request - RLI', 'create');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Material Request Form</h1>
    <p class="text-slate-500 mt-1">Fill in the details and add your requested items.</p>
  </div>
  <a href="requests.php" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">View Requests</a>
</div>

<form id="materialRequestForm" method="POST" action="save_request.php" class="mt-6 space-y-6">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
      <label for="particulars" class="block text-sm font-semibold text-slate-700 mb-2">Particulars</label>
      <textarea id="particulars" name="particulars" rows="3" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"
        placeholder="Enter particulars..."></textarea>
    </div>

    <div>
      <label for="requester_name" class="block text-sm font-semibold text-slate-700 mb-2">Requested By</label>
      <input type="text" id="requester_name" name="requester_name" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"
        placeholder="Enter requester name">
    </div>

    <div>
      <label for="date_requested" class="block text-sm font-semibold text-slate-700 mb-2">Date Requested</label>
      <input type="date" id="date_requested" name="date_requested" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>

    <div>
      <label for="date_needed" class="block text-sm font-semibold text-slate-700 mb-2">Date Needed</label>
      <input type="date" id="date_needed" name="date_needed" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>

    <div>
      <label for="supervisor_id" class="block text-sm font-semibold text-slate-700 mb-2">Assign Supervisor</label>
      <select id="supervisor_id" name="supervisor_id" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
        <option value="">Select Supervisor (Admin / Super Admin)</option>
        <?php foreach ($supervisors as $sup): ?>
          <option
            value="<?php echo (int)$sup['id']; ?>"
            data-email="<?php echo htmlspecialchars($sup['email']); ?>">
            <?php echo htmlspecialchars($sup['initials']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label for="supervisor_email" class="block text-sm font-semibold text-slate-700 mb-2">Supervisor Email</label>
      <input type="email" id="supervisor_email" name="supervisor_email" readonly
        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-600"
        placeholder="Email will appear automatically">
    </div>

    <div>
      <label for="supervisor_mobile" class="block text-sm font-semibold text-slate-700 mb-2">Supervisor Mobile</label>
      <input type="text" id="supervisor_mobile" name="supervisor_mobile" readonly
        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-600"
        placeholder="Mobile number will appear automatically">
    </div>
  </div>

  <div class="rounded-card border border-slate-100 bg-bgGrey p-5">
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <h2 class="text-lg font-bold text-slate-900">Request Items</h2>
      <button type="button" id="addRowBtn"
        class="px-4 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95">
        Add New Row
      </button>
    </div>

    <div class="mt-4 overflow-x-auto rounded-2xl bg-white border border-slate-100">
      <table id="itemsTable" class="min-w-[1100px] w-full">
        <thead class="bg-white">
          <tr class="text-left text-slate-500 text-sm">
            <th class="py-4 px-4 font-semibold">No.</th>
            <th class="py-4 px-4 font-semibold">Item Name</th>
            <th class="py-4 px-4 font-semibold">Specs</th>
            <th class="py-4 px-4 font-semibold">Quantity</th>
            <th class="py-4 px-4 font-semibold">Unit</th>
            <th class="py-4 px-4 font-semibold">Price</th>
            <th class="py-4 px-4 font-semibold">Amount</th>
            <th class="py-4 px-4 font-semibold">Link</th>
            <th class="py-4 px-4 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody id="itemsTableBody" class="text-slate-900"></tbody>
      </table>
    </div>
  </div>

  <div class="flex gap-3 flex-wrap">
    <button type="submit" id="submitBtn" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">
      Submit Request
    </button>
    <button type="reset" id="resetBtn" class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
      Reset Form
    </button>
  </div>
</form>

<script src="assets/js/main.js?v=<?php echo (int)@filemtime(__DIR__ . '/assets/js/main.js'); ?>"></script>

<?php ui_layout_end(); ?>

