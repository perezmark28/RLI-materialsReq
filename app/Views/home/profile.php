<?php
/**
 * Profile View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Profile - RLHI', 'profile');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Profile</h1>
    <p class="text-slate-500 mt-1">Your account information.</p>
  </div>
  <a href="<?php echo $base; ?>/home" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
    Back to Home
  </a>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Full Name</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Username</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars($user['username'] ?? ''); ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Email</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-4">
    <div class="text-xs uppercase text-slate-500 font-semibold">Role</div>
    <div class="text-slate-900 font-semibold mt-1"><?php echo htmlspecialchars($user['role'] ?? ''); ?></div>
  </div>
</div>

<div class="mt-6 rounded-2xl border border-slate-100 bg-bgGrey p-5">
  <h2 class="text-lg font-bold text-slate-900 mb-4">Change Password</h2>
  <form id="passwordForm" method="POST" action="<?php echo $base; ?>/profile" class="space-y-4">
    <div>
      <label for="current_password" class="block text-sm font-semibold text-slate-700 mb-2">Current Password</label>
      <input id="current_password" name="current_password" type="password" required
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label for="new_password" class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
        <input id="new_password" name="new_password" type="password" required
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">Confirm New Password</label>
        <input id="confirm_password" name="confirm_password" type="password" required
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
    </div>
    <div class="flex items-center gap-3">
      <button type="submit" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">
        Update Password
      </button>
      <span id="passwordMessage" class="text-sm font-semibold"></span>
    </div>
  </form>
</div>

<script>
  document.getElementById('passwordForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const msg = document.getElementById('passwordMessage');
    const submitBtn = form.querySelector('button[type="submit"]');
    msg.textContent = '';
    msg.className = 'text-sm font-semibold';
    submitBtn.disabled = true;
    submitBtn.textContent = 'Updating...';

    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) {
        msg.textContent = data.message || 'Password updated.';
        msg.classList.add('text-green-600');
        form.reset();
      } else {
        msg.textContent = data.message || 'Failed to update password.';
        msg.classList.add('text-red-600');
      }
    } catch (err) {
      msg.textContent = 'Error: ' + err.message;
      msg.classList.add('text-red-600');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Update Password';
    }
  });
</script>

<?php ui_layout_end(); ?>
