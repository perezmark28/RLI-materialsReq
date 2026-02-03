<?php
/**
 * Request detail view (Tailwind layout with sidebar)
 * Expects: $request, $items, $user, $role
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Request Details - RLHI', 'requests');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Request #<?php echo htmlspecialchars($request['id'] ?? ''); ?></h1>
    <p class="text-slate-500 mt-1">View full request details.</p>
  </div>
  <div class="flex items-center gap-2">
    <?php if (can_manage_requests() && ($request['status'] ?? '') === 'pending'): ?>
      <button type="button"
              data-action="approve"
              data-id="<?php echo (int)$request['id']; ?>"
              class="px-4 py-2 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700">
        Approve
      </button>
      <button type="button"
              data-action="decline"
              data-id="<?php echo (int)$request['id']; ?>"
              class="px-4 py-2 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700">
        Decline
      </button>
    <?php endif; ?>
    <a href="<?php echo $base; ?>/requests" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
      Back to Requests
    </a>
  </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Requester</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars($request['requester_name'] ?? ''); ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Status</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars(strtoupper($request['status'] ?? '')); ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Date Requested</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars($request['date_requested'] ?? ''); ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Date Needed</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars($request['date_needed'] ?? ''); ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4 md:col-span-2">
    <div class="text-xs uppercase text-slate-500 font-semibold">Supervisor</div>
    <div class="text-slate-900 font-semibold mt-1">
      <?php echo htmlspecialchars($request['supervisor_name'] ?? $request['supervisor_id'] ?? ''); ?>
    </div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4 md:col-span-2">
    <div class="text-xs uppercase text-slate-500 font-semibold">Particulars</div>
    <div class="text-slate-900 mt-1"><?php echo nl2br(htmlspecialchars($request['particulars'] ?? '')); ?></div>
  </div>
</div>

<div class="mt-6 rounded-2xl border border-slate-100 bg-bgGrey p-4">
  <div class="text-sm font-semibold text-slate-700 mb-3">Items</div>
  <div class="overflow-x-auto rounded-2xl bg-white border border-slate-100">
    <table class="min-w-[900px] w-full">
      <thead>
        <tr class="text-left text-slate-500 text-sm">
          <th class="py-4 px-4 font-semibold">No.</th>
          <th class="py-4 px-4 font-semibold">Item Name</th>
          <th class="py-4 px-4 font-semibold">Specs</th>
          <th class="py-4 px-4 font-semibold">Quantity</th>
          <th class="py-4 px-4 font-semibold">Unit</th>
          <th class="py-4 px-4 font-semibold">Price</th>
          <th class="py-4 px-4 font-semibold">Amount</th>
        </tr>
      </thead>
      <tbody class="text-slate-900">
        <?php if (!empty($items)): ?>
          <?php foreach ($items as $i => $it): ?>
            <tr class="border-t border-slate-100">
              <td class="py-4 px-4"><?php echo htmlspecialchars($it['item_no'] ?? ($i + 1)); ?></td>
              <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($it['item_name'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($it['specs'] ?? ''); ?></td>
              <td class="py-4 px-4 text-right"><?php echo htmlspecialchars($it['quantity'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($it['unit'] ?? ''); ?></td>
              <td class="py-4 px-4 text-right"><?php echo htmlspecialchars($it['price'] ?? ''); ?></td>
              <td class="py-4 px-4 text-right"><?php echo htmlspecialchars($it['amount'] ?? ''); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr class="border-t border-slate-100">
            <td colspan="7" class="py-6 px-4 text-slate-600">No items found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (can_manage_requests()): ?>
<script>
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action][data-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const action = btn.getAttribute('data-action');
    if (!id || !action) return;

    const verb = action === 'approve' ? 'approve' : 'decline';
    if (!confirm(`Are you sure you want to ${verb} this request?`)) return;

    try {
      const res = await fetch(`<?php echo $base; ?>/requests/${id}/${action}`, { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        window.location.href = '<?php echo $base; ?>/requests';
      } else {
        alert(data.message || 'Action failed.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    }
  });
</script>
<?php endif; ?>

<?php ui_layout_end(); ?>