<?php
/**
 * Signup Success View (public shell)
 */
require_once __DIR__ . '/../../../includes/ui_public.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_public_head('Account Created - RLHI');
ui_public_shell_start();
?>

<div class="text-center">
  <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
    </svg>
  </div>
  <h1 class="text-2xl font-bold text-slate-900 mb-2">Account Created!</h1>
  <p class="text-slate-600 mb-6">
    Your account has been successfully created. You can now log in with your credentials.
  </p>

  <div class="space-y-3">
    <a href="<?php echo $base; ?>/login" class="block w-full px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold text-center hover:opacity-95">
      Go to Login
    </a>
    <a href="<?php echo $base; ?>/" class="block w-full px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold text-center">
      Back to Home
    </a>
  </div>
</div>

<?php ui_public_shell_end(); ?>
