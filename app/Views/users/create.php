<?php
/**
 * User Create View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Add User - RLHI', 'users');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Add User</h1>
    <p class="text-slate-500 mt-1">Create a new user account.</p>
  </div>
  <a href="<?php echo $base; ?>/users" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
    Back to Users
  </a>
</div>

<form id="userForm" method="POST" action="<?php echo $base; ?>/users/create" class="mt-6 space-y-4">
  <div>
    <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
    <input id="full_name" name="full_name" type="text" required
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
      <input id="email" name="email" type="email" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
      <input id="username" name="username" type="text" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
      <input id="password" name="password" type="password" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password</label>
      <input id="confirm_password" name="confirm_password" type="password" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
  </div>
  <div>
    <label for="role" class="block text-sm font-semibold text-slate-700 mb-2">Role</label>
    <select id="role" name="role"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      <option value="viewer">Viewer</option>
      <option value="admin">Admin</option>
      <option value="super_admin">Super Admin</option>
    </select>
  </div>
  <div class="flex gap-3">
    <button type="submit" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">Create User</button>
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
        alert(data.message || 'Failed to create user.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Create User';
    }
  });
</script>

<?php ui_layout_end(); ?>
