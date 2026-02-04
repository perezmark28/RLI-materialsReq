<?php
/**
 * Requests List View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Requests - RLI', 'requests');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900"><?php echo ($role ?? '') === 'viewer' ? 'My Requests' : 'All Requests'; ?></h1>
    <p class="text-slate-500 mt-1">Track requests and status updates.</p>
  </div>
  <div class="flex items-center gap-2">
    <?php if (($role ?? '') === 'admin' || ($role ?? '') === 'super_admin'): ?>
      <a href="<?php echo $base; ?>/requests/print" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
        View All (Printable)
      </a>
    <?php endif; ?>
    <a href="<?php echo $base; ?>/requests/create" class="px-4 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95">
      Create Request
    </a>
  </div>
</div>

<div class="mt-6 flex items-end gap-3 flex-wrap">
  <form method="GET" class="flex items-end gap-3 flex-wrap">
    <div>
      <label for="q" class="block text-sm font-semibold text-slate-700 mb-2">Search</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($search ?? ''); ?>"
        placeholder="Search requests..."
        class="min-w-[260px] rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
      <select id="status" name="status"
        class="min-w-[200px] rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
        <option value="">All</option>
        <option value="pending" <?php echo ($status ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
        <option value="approved" <?php echo ($status ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
        <option value="declined" <?php echo ($status ?? '') === 'declined' ? 'selected' : ''; ?>>Declined</option>
      </select>
    </div>
    <button type="submit" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
      Filter
    </button>
  </form>
  <a href="<?php echo $base; ?>/requests" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Reset</a>
</div>

<div class="mt-6 overflow-x-auto rounded-card bg-bgGrey border border-slate-100 p-4">
  <div class="overflow-x-auto rounded-2xl bg-white border border-slate-100">
    <table class="min-w-[1100px] w-full">
      <thead>
        <tr class="text-left text-slate-500 text-sm">
          <th class="py-4 px-4 font-semibold">Requester</th>
          <th class="py-4 px-4 font-semibold">Particulars</th>
          <th class="py-4 px-4 font-semibold">Date Requested</th>
          <th class="py-4 px-4 font-semibold">Date Needed</th>
          <th class="py-4 px-4 font-semibold">Item</th>
          <th class="py-4 px-4 font-semibold">Total</th>
          <th class="py-4 px-4 font-semibold">Supervisor</th>
          <th class="py-4 px-4 font-semibold">Email</th>
          <th class="py-4 px-4 font-semibold">Approval Status</th>
          <th class="py-4 px-4 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="text-slate-900">
        <?php if (empty($requests)): ?>
          <tr class="border-t border-slate-100">
            <td colspan="10" class="py-6 px-4 text-slate-600">No requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $request): ?>
            <?php
              $badge = 'bg-red-100 text-red-700';
              if (($request['status'] ?? '') === 'approved') $badge = 'bg-green-100 text-green-700';
              if (($request['status'] ?? '') === 'declined') $badge = 'bg-slate-200 text-slate-700';
              $particulars = $request['particulars'] ?? '';
              $particularsShort = mb_strlen($particulars) > 50 ? mb_substr($particulars, 0, 50) . '…' : $particulars;
              $itemCount = (int)($request['item_count'] ?? 0);
              $totalAmount = (float)($request['total_amount'] ?? 0);
            ?>
            <tr class="border-t border-slate-100">
              <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($request['requester_name'] ?? ''); ?></td>
              <td class="py-4 px-4 text-slate-700 max-w-[200px]" title="<?php echo htmlspecialchars($particulars); ?>"><?php echo htmlspecialchars($particularsShort); ?></td>
              <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($request['date_requested'] ?? ''); ?></td>
              <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($request['date_needed'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo $itemCount; ?></td>
              <td class="py-4 px-4 font-semibold"><?php echo number_format($totalAmount, 2); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($request['initials'] ?? '—'); ?></td>
              <td class="py-4 px-4 text-slate-600 text-sm"><?php echo htmlspecialchars($request['supervisor_email'] ?? '—'); ?></td>
              <td class="py-4 px-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo $badge; ?>">
                  <?php echo htmlspecialchars(strtoupper($request['status'] ?? '')); ?>
                </span>
              </td>
              <td class="py-4 px-4">
                <?php
                  $roleLower = strtolower(trim($role ?? ''));
                  $isAdmin = ($roleLower === 'admin');
                  $isSuperAdmin = ($roleLower === 'super_admin');
                  $canManage = $isAdmin || $isSuperAdmin;
                  $isPending = ($request['status'] ?? '') === 'pending';
                ?>
                <div class="flex flex-wrap gap-2 justify-end">
                  <a href="<?php echo $base; ?>/requests/<?php echo $request['id']; ?>"
                     class="inline-flex px-3 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95 text-sm whitespace-nowrap">View</a>
                  <?php if (($roleLower ?? '') === 'viewer' && ($request['status'] ?? '') === 'declined'): ?>
                    <button type="button"
                            data-view-remark
                            data-remarks="<?php echo htmlspecialchars($request['decline_remarks'] ?? ''); ?>"
                            class="inline-flex px-3 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold text-sm whitespace-nowrap">
                      View Remark
                    </button>
                  <?php endif; ?>
                  <?php if ($canManage): ?>
                    <?php if ($isPending): ?>
                      <a href="<?php echo $base; ?>/requests/<?php echo $request['id']; ?>/edit"
                         class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800"
                         title="Edit">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M12 20h9" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </a>
                      <button type="button"
                              data-action="approve"
                              data-id="<?php echo (int)$request['id']; ?>"
                              class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-green-600 hover:bg-green-700 text-white"
                              title="Approve">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                      <button type="button"
                              data-action="decline"
                              data-id="<?php echo (int)$request['id']; ?>"
                              class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-600 hover:bg-red-700 text-white"
                              title="Decline">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                      <?php if ($isSuperAdmin): ?>
                        <button type="button"
                                data-action="delete"
                                data-id="<?php echo (int)$request['id']; ?>"
                                title="Delete"
                                class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-red-600 hover:bg-red-700 text-white">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      <?php endif; ?>
                    <?php else: ?>
                      <a href="<?php echo $base; ?>/requests/<?php echo $request['id']; ?>/edit"
                         class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800"
                         title="Edit">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M12 20h9" stroke-linecap="round" stroke-linejoin="round"/>
                          <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </a>
                      <?php if ($isSuperAdmin): ?>
                        <button type="button"
                                data-action="delete"
                                data-id="<?php echo (int)$request['id']; ?>"
                                title="Delete"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-600 hover:bg-red-700 text-white">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      <?php endif; ?>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- View Remark modal (for viewers on declined requests) -->
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
  document.querySelectorAll('[data-view-remark]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const remarks = this.getAttribute('data-remarks') || '';
      const modal = document.getElementById('remarkModal');
      const content = document.getElementById('remarkModalContent');
      if (content) content.textContent = remarks.trim() || 'No remarks provided.';
      if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }
    });
  });
  document.getElementById('remarkModalClose')?.addEventListener('click', function() {
    const modal = document.getElementById('remarkModal');
    if (modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
  });
  document.getElementById('remarkModal')?.addEventListener('click', function(e) {
    if (e.target === this) { this.classList.add('hidden'); this.classList.remove('flex'); }
  });
</script>

<?php if (can_manage_requests()): ?>
<!-- Delete confirmation modal (Super Admin) -->
<div id="deleteConfirmModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4" style="background: rgba(0,0,0,0.4);">
  <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full p-6">
    <h3 class="text-lg font-bold text-slate-900 mb-2">Delete Request</h3>
    <p class="text-slate-600 mb-6">Are you sure you want to delete this request? This action cannot be undone.</p>
    <div class="flex gap-3 justify-end">
      <button type="button" id="deleteConfirmCancel" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Cancel</button>
      <button type="button" id="deleteConfirmOk" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold">Delete</button>
    </div>
  </div>
</div>

<!-- Approve / Decline confirmation modal -->
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
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-action][data-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const action = btn.getAttribute('data-action');
    if (!id || !action) return;

    if (action === 'delete') {
      const modal = document.getElementById('deleteConfirmModal');
      const cancelBtn = document.getElementById('deleteConfirmCancel');
      const okBtn = document.getElementById('deleteConfirmOk');
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        cancelBtn.onclick = null;
        okBtn.onclick = null;
      };

      cancelBtn.onclick = closeModal;
      okBtn.onclick = async () => {
        closeModal();
        try {
          const res = await fetch(`<?php echo $base; ?>/requests/${id}/delete`, { method: 'POST' });
          const data = await res.json();
          if (data.success) {
            window.location.reload();
          } else {
            alert(data.message || 'Failed to delete request.');
          }
        } catch (err) {
          alert('Error: ' + err.message);
        }
      };
      modal.onclick = (ev) => { if (ev.target === modal) closeModal(); };
      return;
    }

    if (action === 'approve' || action === 'decline') {
      openActionModal(action, id);
      return;
    }

    try {
      const res = await fetch(`<?php echo $base; ?>/requests/${id}/${action}`, { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Action failed.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    }
  });

  const actionModal = document.getElementById('actionConfirmModal');
  const actionTitle = document.getElementById('actionConfirmTitle');
  const actionMessage = document.getElementById('actionConfirmMessage');
  const actionCancel = document.getElementById('actionConfirmCancel');
  const actionOk = document.getElementById('actionConfirmOk');
  let actionState = null;

  const remarksWrap = document.getElementById('actionDeclineRemarksWrap');
  const remarksInput = document.getElementById('actionDeclineRemarks');

  function openActionModal(action, requestId) {
    if (!actionModal) return;
    const isApprove = action === 'approve';
    actionState = { action, requestId };
    actionTitle.textContent = isApprove ? 'Approve Request' : 'Decline Request';
    actionMessage.textContent = isApprove
      ? 'Are you sure you want to approve this request?'
      : 'Are you sure you want to decline this request?';
    actionOk.textContent = isApprove ? 'Approve' : 'Decline';
    actionOk.className = `px-4 py-2 rounded-xl text-white font-semibold ${isApprove ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'}`;

    if (remarksWrap) remarksWrap.classList.toggle('hidden', isApprove);
    if (remarksInput) remarksInput.value = '';

    actionModal.classList.remove('hidden');
    actionModal.classList.add('flex');
  }

  function closeActionModal() {
    if (!actionModal) return;
    actionModal.classList.add('hidden');
    actionModal.classList.remove('flex');
    actionState = null;
  }

  async function submitAction() {
    if (!actionState) return;
    const { action, requestId } = actionState;
    const remarks = remarksInput ? remarksInput.value.trim() : '';
    closeActionModal();
    try {
      const opts = { method: 'POST' };
      if (action === 'decline') {
        opts.headers = { 'Content-Type': 'application/json' };
        opts.body = JSON.stringify({ remarks });
      }
      const res = await fetch(`<?php echo $base; ?>/requests/${requestId}/${action}`, opts);
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Action failed.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    }
  }

  actionCancel?.addEventListener('click', closeActionModal);
  actionOk?.addEventListener('click', submitAction);
  actionModal?.addEventListener('click', (ev) => {
    if (ev.target === actionModal) closeActionModal();
  });
</script>
<?php endif; ?>

<?php if ($total_pages > 1): ?>
  <div class="mt-6 flex justify-center gap-2">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?php echo $i; ?>"
         class="px-4 py-2 rounded-xl <?php echo ($page === $i) ? 'bg-ink text-white' : 'bg-white border border-slate-200 text-slate-700'; ?>">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>
  </div>
<?php endif; ?>

<?php ui_layout_end(); ?>
