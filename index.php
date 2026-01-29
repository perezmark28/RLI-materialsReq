<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui_public.php';

if (is_logged_in()) {
    header('Location: home.php');
    exit;
}

ui_public_head('RLI - Landing');
ui_public_shell_start();
?>

<h1 class="text-2xl font-bold text-slate-900">Welcome</h1>
<p class="text-slate-600 mt-2">
  Sign in to create and track material requests.
</p>

<div class="mt-6 flex gap-3 flex-wrap">
  <a href="login.php" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">Login</a>
  <a href="signup.php" class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold">Sign Up</a>
</div>

<div class="mt-8 rounded-2xl bg-bgGrey border border-slate-100 p-4">
  <div class="text-sm text-slate-500">Default accounts (if you ran setup)</div>
  <div class="mt-2 text-sm text-slate-700 leading-6">
    Admin: <span class="font-semibold">mts / admin123</span>, <span class="font-semibold">pjj / admin123</span>, <span class="font-semibold">alu / admin123</span><br/>
    Super Admin: <span class="font-semibold">apl / superadmin123</span>
  </div>
</div>

<?php
ui_public_shell_end();
?>

