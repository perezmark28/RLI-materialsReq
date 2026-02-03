<?php
/**
 * Login View (public shell)
 */
require_once __DIR__ . '/../../../includes/ui_public.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_public_head('Login - RLHI');
ui_public_shell_start();
?>

<div class="flex items-start justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Login</h1>
    <p class="text-slate-600 mt-2">Enter your username and password.</p>
  </div>
  <a href="<?php echo $base; ?>/" class="text-sm font-semibold text-slate-700 hover:text-black">Back</a>
</div>

<?php if (isset($error) && !empty($error)): ?>
  <div class="rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold mb-4">
    <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<form method="POST" action="<?php echo $base; ?>/login" class="space-y-4">
  <div>
    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
    <input id="username" name="username" type="text" required autofocus
      value="<?php echo htmlspecialchars($username ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
    <input id="password" name="password" type="password" required
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <button type="submit" class="w-full px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">
    Login
  </button>
  <a class="block w-full text-center px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold" href="<?php echo $base; ?>/signup">
    Sign Up
  </a>
</form>

<?php ui_public_shell_end(); ?>
