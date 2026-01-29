<?php
require_once __DIR__ . '/auth.php';

$role = current_role();
?>
<div style="background:#ffffff; border-bottom:1px solid #e0e0e0;">
  <div style="max-width:1400px; margin:0 auto; padding:12px 20px; display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
    <a href="home.php" style="text-decoration:none; font-weight:700; color:#1e3c72;">RLI</a>

    <?php if ($role === 'viewer'): ?>
      <a href="home.php">Home</a>
      <a href="create_request.php">Create Request</a>
      <a href="requests.php">My Requests</a>
      <a href="profile.php">Profile</a>
    <?php elseif ($role === 'admin'): ?>
      <a href="home.php">Home</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="create_request.php">Create Request</a>
      <a href="requests.php">All Requests</a>
      <a href="suppliers.php">Suppliers</a>
      <a href="statistics.php">Statistics</a>
      <a href="profile.php">Profile</a>
    <?php elseif ($role === 'super_admin'): ?>
      <a href="home.php">Home</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="create_request.php">Create Request</a>
      <a href="requests.php">All Requests</a>
      <a href="suppliers.php">Suppliers</a>
      <a href="statistics.php">Statistics</a>
      <a href="users.php">User Accounts</a>
      <a href="profile.php">Profile</a>
    <?php else: ?>
      <a href="index.php">Landing</a>
    <?php endif; ?>

    <div style="margin-left:auto; display:flex; gap:10px; align-items:center;">
      <?php if (is_logged_in()): ?>
        <span style="color:#6c757d;">
          <?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?> (<?php echo htmlspecialchars($role ?? ''); ?>)
        </span>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</div>

