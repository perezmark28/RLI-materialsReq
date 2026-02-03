<?php
/**
 * User Edit View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Edit User - RLHI', 'users');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Edit User</h1>
    <p class="text-slate-500 mt-1">Update user information.</p>
  </div>
  <a href="<?php echo $base; ?>/users" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
    Back to Users
  </a>
</div>

<form id="userForm" method="POST" action="<?php echo $base; ?>/users/<?php echo (int)$user_data['id']; ?>/edit" class="mt-6 space-y-4">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
      <input type="text" readonly
        value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>"
        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700">
    </div>
    <div>
      <label class="block text-sm font-semibold text-slate-700 mb-2">Role</label>
      <input type="text" readonly
        value="<?php echo htmlspecialchars($user_data['role_name'] ?? ''); ?>"
        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700">
    </div>
  </div>

  <div>
    <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
    <input id="full_name" name="full_name" type="text" required
      value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
    <input id="email" name="email" type="email" required
      value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
    <select id="status" name="status"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      <option value="active" <?php echo (($user_data['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
      <option value="inactive" <?php echo (($user_data['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
    </select>
  </div>
  <div class="flex gap-3">
    <button type="submit" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">Save Changes</button>
    <a href="<?php echo $base; ?>/users" class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold">Cancel</a>
  </div>
</form>

<script>
  document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) {
        window.location.href = '<?php echo $base; ?>/users';
      } else {
        alert(data.message || 'Failed to update user.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Save Changes';
    }
  });
</script>

<?php ui_layout_end(); ?>
