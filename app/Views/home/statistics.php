<?php
/**
 * Statistics View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Statistics - RLHI', 'statistics');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Statistics</h1>
    <p class="text-slate-500 mt-1">Overall request insights.</p>
  </div>
  <a href="<?php echo $base; ?>/home" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
    Back to Home
  </a>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Total Requests</div>
    <div class="text-3xl font-bold text-slate-900 mt-2"><?php echo $stats['total'] ?? 0; ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Pending</div>
    <div class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $stats['pending'] ?? 0; ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Approved</div>
    <div class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['approved'] ?? 0; ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Declined</div>
    <div class="text-3xl font-bold text-red-600 mt-2"><?php echo $stats['declined'] ?? 0; ?></div>
  </div>
</div>

<?php ui_layout_end(); ?>
