<?php
/**
 * Create Request View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';

$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Create Request - RLI', 'create');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Material Request Form</h1>
    <p class="text-slate-500 mt-1">Fill in the details and add your requested items.</p>
  </div>
  <a href="<?php echo $base; ?>/requests" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
    View Requests
  </a>
</div>

<form id="materialRequestForm"
      data-requests-url="<?php echo $base; ?>/requests"
      data-supervisor-url="<?php echo $base; ?>/supervisors"
      data-mode="create"
      method="POST"
      action="<?php echo $base; ?>/requests/create"
      class="mt-6 space-y-4">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="particulars">Particulars</label>
      <textarea id="particulars" name="particulars" rows="2" required
        class="w-full text-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow min-h-[60px]"
        placeholder="Enter particulars..."></textarea>
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="requester_name">Requested By</label>
      <input id="requester_name" name="requester_name" type="text" required
        value="<?php echo htmlspecialchars($user['full_name'] ?? $user['username'] ?? ''); ?>"
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"
        placeholder="Enter requester name">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="date_requested">Date Requested</label>
      <input id="date_requested" name="date_requested" type="date" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="date_needed">Date Needed</label>
      <input id="date_needed" name="date_needed" type="date" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="supervisor_id">Assign Supervisor</label>
      <select id="supervisor_id" name="supervisor_id" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
        <option value="">Select Supervisor (Admin / Super Admin)</option>
        <?php foreach ($supervisors as $supervisor): ?>
          <option value="<?php echo (int)$supervisor['id']; ?>">
            <?php echo htmlspecialchars($supervisor['initials'] . ' - ' . $supervisor['email']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="supervisor_email">Supervisor Email</label>
      <input id="supervisor_email" type="text" readonly
        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700"
        placeholder="Email will appear automatically">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="supervisor_mobile">Supervisor Mobile</label>
      <input id="supervisor_mobile" type="text" readonly
        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700"
        placeholder="Mobile number will appear automatically">
    </div>
  </div>

  <div class="rounded-2xl bg-bgGrey p-4">
    <div class="flex items-center justify-between mb-2">
      <div class="text-sm font-semibold text-slate-700">Request Items</div>
      <button type="button" id="addRowBtn" class="px-4 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95">
        Add New Row
      </button>
    </div>
    <div class="overflow-x-auto rounded-2xl bg-white border border-slate-100">
      <table class="min-w-[1200px] w-full">
        <thead>
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

  <div class="flex items-center gap-3">
    <button id="submitBtn" type="submit" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">
      Submit Request
    </button>
    <button type="button" id="resetFormBtn" class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold">
      Reset Form
    </button>
  </div>
</form>

<script src="<?php echo $base; ?>/assets/js/main.js?v=<?php echo (int)@filemtime(__DIR__ . '/../../../assets/js/main.js'); ?>"></script>

<?php ui_layout_end(); ?>
