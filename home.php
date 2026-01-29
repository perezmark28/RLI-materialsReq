<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui.php';
require_login();
ui_layout_start('Home - RLI', 'home');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Home</h1>
    <p class="text-slate-500 mt-1">Welcome back, <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>.</p>
  </div>
  <div class="text-sm text-slate-500">
    Role: <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($_SESSION['user']['role']); ?></span>
  </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <a href="create_request.php" class="block rounded-2xl border border-slate-100 bg-bgGrey p-5 hover:bg-slate-100 transition">
    <div class="text-slate-900 font-semibold">Create Request</div>
    <div class="text-slate-500 text-sm mt-1">Submit a new material request.</div>
  </a>
  <a href="requests.php" class="block rounded-2xl border border-slate-100 bg-bgGrey p-5 hover:bg-slate-100 transition">
    <div class="text-slate-900 font-semibold"><?php echo current_role()==='viewer'?'My Requests':'All Requests'; ?></div>
    <div class="text-slate-500 text-sm mt-1">Review and manage request records.</div>
  </a>

  <?php if (current_role() !== 'viewer'): ?>
    <a href="suppliers.php" class="block rounded-2xl border border-slate-100 bg-bgGrey p-5 hover:bg-slate-100 transition">
      <div class="text-slate-900 font-semibold">Suppliers</div>
      <div class="text-slate-500 text-sm mt-1">Manage suppliers list.</div>
    </a>
    <a href="statistics.php" class="block rounded-2xl border border-slate-100 bg-bgGrey p-5 hover:bg-slate-100 transition">
      <div class="text-slate-900 font-semibold">Statistics</div>
      <div class="text-slate-500 text-sm mt-1">View totals by status and trends.</div>
    </a>
  <?php endif; ?>

  <?php if (current_role() === 'super_admin'): ?>
    <a href="users.php" class="block rounded-2xl border border-slate-100 bg-bgGrey p-5 hover:bg-slate-100 transition">
      <div class="text-slate-900 font-semibold">User Accounts</div>
      <div class="text-slate-500 text-sm mt-1">Create and manage users and admins.</div>
    </a>
  <?php endif; ?>

  <a href="profile.php" class="block rounded-2xl border border-slate-100 bg-bgGrey p-5 hover:bg-slate-100 transition">
    <div class="text-slate-900 font-semibold">Profile</div>
    <div class="text-slate-500 text-sm mt-1">Update your details and password.</div>
  </a>
</div>

<?php ui_layout_end(); ?>

