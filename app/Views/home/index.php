<?php
/**
 * Landing Page View (public shell)
 */
require_once __DIR__ . '/../../../includes/ui_public.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_public_head('RLHI - Material Request System');
ui_public_shell_start();
?>

<div class="flex items-start justify-between gap-4 mb-6">
  <div>
    <div class="text-sm font-semibold text-slate-500 uppercase">Welcome</div>
    <h1 class="text-2xl font-bold text-slate-900">RLHI Material Request System</h1>
    <p class="text-slate-600 mt-2">
      A clean internal workflow for submitting, reviewing, and approving material requests.
    </p>
  </div>
</div>

<div class="space-y-3 mb-6">
  <a href="<?php echo $base; ?>/login"
     class="block w-full px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold text-center hover:opacity-95">
    Login
  </a>
  <a href="<?php echo $base; ?>/signup"
     class="block w-full px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold text-center">
    Sign Up
  </a>
</div>

<div class="rounded-2xl bg-bgGrey border border-slate-100 p-4">
  <div class="text-sm text-slate-500">Default accounts (if you ran setup)</div>
  <div class="mt-2 text-sm text-slate-700 leading-6">
    <strong>Admin:</strong> mts / admin123<br/>
    <strong>Super Admin:</strong> apl / superadmin123
  </div>
</div>

<?php ui_public_shell_end(); ?>
