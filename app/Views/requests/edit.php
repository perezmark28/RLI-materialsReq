<?php
/**
 * Edit Request View
 */
require_once __DIR__ . '/../../../includes/ui.php';

$base = defined('BASE_PATH') ? BASE_PATH : '';
$requestId = (int)($request['id'] ?? 0);
ui_layout_start('Edit Request - RLI', 'requests');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Edit Material Request</h1>
    <p class="text-slate-500 mt-1">Update request details and items. Only pending requests can be edited.</p>
  </div>
  <div class="flex items-center gap-2">
    <a href="<?php echo $base; ?>/requests/<?php echo $requestId; ?>" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
      View Request
    </a>
    <a href="<?php echo $base; ?>/requests" class="px-4 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-800 font-semibold">
      Back
    </a>
  </div>
</div>

<form id="materialRequestForm"
      data-requests-url="<?php echo $base; ?>/requests"
      data-supervisor-url="<?php echo $base; ?>/supervisors"
      data-mode="edit"
      method="POST"
      action="<?php echo $base; ?>/requests/<?php echo $requestId; ?>/edit"
      class="mt-6 space-y-6">
  <div class="rounded-2xl bg-bgGrey p-5">
    <div class="text-sm font-semibold text-slate-700 mb-2">Particulars</div>
    <textarea id="particulars" name="particulars" rows="2" required
      class="w-full text-sm rounded-2xl border border-slate-200 bg-white px-3 py-2 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow min-h-[60px]"
      placeholder="Enter particulars..."><?php echo htmlspecialchars($request['particulars'] ?? ''); ?></textarea>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="requester_name">Requested By</label>
      <input id="requester_name" name="requester_name" type="text" required
        value="<?php echo htmlspecialchars($request['requester_name'] ?? ($user['full_name'] ?? $user['username'] ?? '')); ?>"
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"
        placeholder="Enter requester name">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="date_requested">Date Requested</label>
      <input id="date_requested" name="date_requested" type="date" required
        value="<?php echo htmlspecialchars($request['date_requested'] ?? ''); ?>"
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="date_needed">Date Needed</label>
      <input id="date_needed" name="date_needed" type="date" required
        value="<?php echo htmlspecialchars($request['date_needed'] ?? ''); ?>"
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2" for="supervisor_id">Assign Supervisor</label>
      <select id="supervisor_id" name="supervisor_id" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
        <option value="">Select Supervisor (Admin / Super Admin)</option>
        <?php foreach ($supervisors as $supervisor): ?>
          <option value="<?php echo (int)$supervisor['id']; ?>"
            <?php echo ((int)$request['supervisor_id'] === (int)$supervisor['id']) ? 'selected' : ''; ?>>
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

  <div class="rounded-2xl bg-bgGrey p-5">
    <div class="flex items-center justify-between mb-3">
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
        <tbody id="itemsTableBody" class="text-slate-900">
          <?php if (!empty($items)): ?>
            <?php foreach ($items as $index => $item): ?>
              <?php
                $rowIndex = $index + 1;
                $quantity = (float)($item['quantity'] ?? 0);
                $price = (float)($item['price'] ?? 0);
                $amount = $item['amount'] !== null ? (float)$item['amount'] : ($quantity * $price);
              ?>
              <tr>
                <td class="item-no py-3 px-4 text-slate-600 font-semibold"><?php echo $rowIndex; ?></td>
                <td class="py-3 px-4">
                  <input type="text" name="items[<?php echo $rowIndex; ?>][item_name]" class="item-name w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" required value="<?php echo htmlspecialchars($item['item_name'] ?? ''); ?>">
                </td>
                <td class="py-3 px-4">
                  <textarea name="items[<?php echo $rowIndex; ?>][specs]" class="item-specs w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" rows="2"><?php echo htmlspecialchars($item['specs'] ?? ''); ?></textarea>
                </td>
                <td class="py-3 px-4">
                  <input type="number" name="items[<?php echo $rowIndex; ?>][quantity]" class="item-quantity w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" step="0.01" min="0" required value="<?php echo htmlspecialchars($quantity); ?>">
                </td>
                <td class="py-3 px-4">
                  <input type="text" name="items[<?php echo $rowIndex; ?>][unit]" class="item-unit w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" value="<?php echo htmlspecialchars($item['unit'] ?? ''); ?>">
                </td>
                <td class="py-3 px-4">
                  <input type="number" name="items[<?php echo $rowIndex; ?>][price]" class="item-price w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" step="0.01" min="0" required value="<?php echo htmlspecialchars($price); ?>">
                </td>
                <td class="py-3 px-4">
                  <input type="text" name="items[<?php echo $rowIndex; ?>][amount]" class="item-amount w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 font-semibold" readonly value="<?php echo number_format($amount, 2, '.', ''); ?>">
                </td>
                <td class="py-3 px-4">
                  <input type="url" name="items[<?php echo $rowIndex; ?>][item_link]" class="item-link w-full rounded-xl border border-slate-200 px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500" placeholder="https://..." value="<?php echo htmlspecialchars($item['item_link'] ?? ''); ?>">
                </td>
                <td class="py-3 px-4 text-right">
                  <button type="button" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-600 hover:bg-red-700 text-white" onclick="removeRow(this)" title="Remove row">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="flex items-center gap-3">
    <button id="submitBtn" type="submit" class="px-5 py-3 rounded-2xl bg-ink text-white font-semibold hover:bg-black">
      Save Changes
    </button>
    <a href="<?php echo $base; ?>/requests/<?php echo $requestId; ?>" class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold">
      Cancel
    </a>
  </div>
</form>

<script src="<?php echo $base; ?>/assets/js/main.js?v=<?php echo (int)@filemtime(__DIR__ . '/../../../assets/js/main.js'); ?>"></script>

<?php ui_layout_end(); ?>
