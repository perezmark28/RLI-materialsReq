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
    <?php if (($role ?? '') === 'viewer' && ($request['status'] ?? '') === 'declined'): ?>
      <button type="button" id="viewRemarkBtn" data-remarks="<?php echo htmlspecialchars($request['decline_remarks'] ?? ''); ?>"
              class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold">
        View Remark
      </button>
    <?php endif; ?>
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
    <a href="<?php echo $base; ?>/requests/<?php echo (int)($request['id'] ?? 0); ?>/print" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">
      Print
    </a>
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
          <?php if (can_manage_requests()): ?>
          <th class="py-4 px-4 font-semibold">Link</th>
          <?php endif; ?>
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
              <?php if (can_manage_requests()): ?>
              <td class="py-4 px-4">
                <?php $link = trim($it['item_link'] ?? ''); ?>
                <?php if (!empty($link)): ?>
                  <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" rel="noopener noreferrer"
                     class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Check Link
                  </a>
                <?php else: ?>
                  <span class="text-slate-400 text-sm">—</span>
                <?php endif; ?>
              </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr class="border-t border-slate-100">
            <td colspan="<?php echo can_manage_requests() ? 8 : 7; ?>" class="py-6 px-4 text-slate-600">No items found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (($role ?? '') === 'viewer' && ($request['status'] ?? '') === 'declined'): ?>
<!-- View Remark modal for declined request -->
<div id="remarkModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.4);">
  <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6">
    <h3 class="text-lg font-bold text-slate-900 mb-2">Decline Remark</h3>
    <p id="remarkModalContent" class="text-slate-600 mb-6 min-h-[60px]"></p>
    <div class="flex justify-end">
      <button type="button" id="remarkModalClose" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Close</button>
    </div>
  </div>
</div>
<script>
  document.getElementById('viewRemarkBtn')?.addEventListener('click', function() {
    const remarks = this.getAttribute('data-remarks') || '';
    const modal = document.getElementById('remarkModal');
    const content = document.getElementById('remarkModalContent');
    if (content) content.textContent = remarks.trim() || 'No remarks provided.';
    if (modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); }
  });
  document.getElementById('remarkModalClose')?.addEventListener('click', function() {
    const modal = document.getElementById('remarkModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
  });
  document.getElementById('remarkModal')?.addEventListener('click', function(e) {
    if (e.target === this) { this.classList.add('hidden'); this.classList.remove('flex'); }
  });
</script>
<?php endif; ?>

<?php if (can_manage_requests()): ?>
<!-- Approve/Decline modals for admin/super_admin -->
<div id="actionConfirmModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.4);">
  <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6">
    <h3 id="actionConfirmTitle" class="text-lg font-bold text-slate-900 mb-2">Confirm Action</h3>
    <p id="actionConfirmMessage" class="text-slate-600 mb-4">Are you sure?</p>
    <div id="actionDeclineRemarksWrap" class="hidden mb-4">
      <label for="actionDeclineRemarks" class="block text-sm font-semibold text-slate-700 mb-2">Remarks (optional)</label>
      <textarea id="actionDeclineRemarks" rows="3" placeholder="Add a message for the requester..."
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow text-slate-900"></textarea>
    </div>
    <div class="flex gap-3 justify-end">
      <button type="button" id="actionConfirmCancel" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Cancel</button>
      <button type="button" id="actionConfirmOk" class="px-4 py-2 rounded-xl text-white font-semibold">Confirm</button>
    </div>
  </div>
</div>
<script>
  const actionModal = document.getElementById('actionConfirmModal');
  const actionTitle = document.getElementById('actionConfirmTitle');
  const actionMessage = document.getElementById('actionConfirmMessage');
  const remarksWrap = document.getElementById('actionDeclineRemarksWrap');
  const remarksInput = document.getElementById('actionDeclineRemarks');
  const actionCancel = document.getElementById('actionConfirmCancel');
  const actionOk = document.getElementById('actionConfirmOk');

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action][data-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const action = btn.getAttribute('data-action');
    if (!id || !action) return;

    if (action === 'approve' || action === 'decline') {
      const isApprove = action === 'approve';
      actionTitle.textContent = isApprove ? 'Approve Request' : 'Decline Request';
      actionMessage.textContent = isApprove ? 'Are you sure you want to approve this request?' : 'Are you sure you want to decline this request?';
      actionOk.textContent = isApprove ? 'Approve' : 'Decline';
      actionOk.className = 'px-4 py-2 rounded-xl text-white font-semibold ' + (isApprove ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700');
      if (remarksWrap) remarksWrap.classList.toggle('hidden', isApprove);
      if (remarksInput) remarksInput.value = '';
      actionModal.dataset.actionId = id;
      actionModal.dataset.action = action;
      actionModal.classList.remove('hidden');
      actionModal.classList.add('flex');
      return;
    }
  });

  actionCancel?.addEventListener('click', () => { actionModal.classList.add('hidden'); actionModal.classList.remove('flex'); });
  actionOk?.addEventListener('click', async () => {
    const id = actionModal.dataset.actionId;
    const action = actionModal.dataset.action;
    if (!id || !action) return;
    actionModal.classList.add('hidden');
    actionModal.classList.remove('flex');
    const opts = { method: 'POST' };
    if (action === 'decline') {
      opts.headers = { 'Content-Type': 'application/json' };
      opts.body = JSON.stringify({ remarks: remarksInput?.value?.trim() || '' });
    }
    try {
      const res = await fetch(`<?php echo $base; ?>/requests/${id}/${action}`, opts);
      const data = await res.json();
      if (data.success) window.location.href = '<?php echo $base; ?>/requests';
      else alert(data.message || 'Action failed.');
    } catch (err) { alert('Error: ' + err.message); }
  });
  actionModal?.addEventListener('click', (e) => { if (e.target === actionModal) { actionModal.classList.add('hidden'); actionModal.classList.remove('flex'); } });
</script>
<?php endif; ?>

<?php ui_layout_end(); ?>