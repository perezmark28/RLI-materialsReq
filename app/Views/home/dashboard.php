<?php
/**
 * Home View (welcome + quick action cards)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Home - RLHI', 'home');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Home</h1>
    <p class="text-slate-500 mt-1">Welcome back, <?php echo htmlspecialchars($user['username'] ?? ''); ?>.</p>
  </div>
  <div class="text-sm text-slate-500">
    Role: <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($role ?? ''); ?></span>
  </div>
</div>

<?php
  $cards = [];
  if (($role ?? '') === 'viewer') {
      $cards = [
          ['title' => 'Create Request', 'desc' => 'Submit a new material request.', 'href' => $base . '/requests/create'],
          ['title' => 'My Requests', 'desc' => 'Track your submitted requests.', 'href' => $base . '/requests'],
          ['title' => 'Profile', 'desc' => 'Update your details and password.', 'href' => $base . '/profile'],
      ];
  } else {
      $cards = [
          ['title' => 'Create Request', 'desc' => 'Submit a new material request.', 'href' => $base . '/requests/create'],
          ['title' => 'All Requests', 'desc' => 'Review and manage request records.', 'href' => $base . '/requests'],
          ['title' => 'Suppliers', 'desc' => 'Manage suppliers list.', 'href' => $base . '/suppliers'],
          ['title' => 'Statistics', 'desc' => 'View totals by status and trends.', 'href' => $base . '/statistics'],
          ['title' => 'Profile', 'desc' => 'Update your details and password.', 'href' => $base . '/profile'],
      ];
      if (($role ?? '') === 'super_admin') {
          $cards[] = ['title' => 'User Accounts', 'desc' => 'Manage user access and roles.', 'href' => $base . '/users'];
      }
  }
?>

<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <?php foreach ($cards as $card): ?>
    <a href="<?php echo htmlspecialchars($card['href']); ?>"
       class="block rounded-2xl border border-slate-100 bg-bgGrey p-5 hover:bg-slate-100 transition">
      <div class="font-semibold text-slate-900"><?php echo htmlspecialchars($card['title']); ?></div>
      <div class="text-slate-500 text-sm mt-1"><?php echo htmlspecialchars($card['desc']); ?></div>
    </a>
  <?php endforeach; ?>
</div>

<?php ui_layout_end(); ?>
