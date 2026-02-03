<?php
/**
 * Users List View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('User Accounts - RLHI', 'users');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">User Accounts</h1>
    <p class="text-slate-500 mt-1">Manage system users.</p>
  </div>
  <a href="<?php echo $base; ?>/users/create" class="px-4 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95">
    Add User
  </a>
</div>

<div class="mt-6 overflow-x-auto rounded-card bg-bgGrey border border-slate-100 p-4">
  <div class="overflow-x-auto rounded-2xl bg-white border border-slate-100">
    <table class="min-w-[1000px] w-full">
      <thead>
        <tr class="text-left text-slate-500 text-sm">
          <th class="py-4 px-4 font-semibold">Username</th>
          <th class="py-4 px-4 font-semibold">Full Name</th>
          <th class="py-4 px-4 font-semibold">Email</th>
          <th class="py-4 px-4 font-semibold">Role</th>
          <th class="py-4 px-4 font-semibold">Status</th>
          <th class="py-4 px-4 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="text-slate-900">
        <?php if (empty($users)): ?>
          <tr class="border-t border-slate-100">
            <td colspan="6" class="py-6 px-4 text-slate-600">No users found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $u): ?>
            <tr class="border-t border-slate-100">
              <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($u['username'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($u['full_name'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($u['role_name'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($u['status'] ?? ''); ?></td>
              <td class="py-4 px-4 text-right">
                <a href="<?php echo $base; ?>/users/<?php echo (int)$u['id']; ?>/edit"
                   class="px-3 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold mr-2">Edit</a>
                <button type="button"
                        data-user-id="<?php echo (int)$u['id']; ?>"
                        class="px-3 py-2 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700">
                  Delete
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

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

<script>
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-user-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-user-id');
    if (!id) return;
    if (!confirm('Delete this user?')) return;

    try {
      const res = await fetch('<?php echo $base; ?>/users/' + id + '/delete', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Failed to delete user.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    }
  });
</script>

<?php ui_layout_end(); ?>
