<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_login();

$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['password'] ?? '';

    if ($new_password !== '') {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, password_hash=? WHERE id=?");
        $stmt->bind_param("sssi", $full_name, $email, $hash, $user['id']);
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=? WHERE id=?");
        $stmt->bind_param("ssi", $full_name, $email, $user['id']);
    }

    if ($stmt->execute()) {
        $success = 'Profile updated.';
        $_SESSION['user']['full_name'] = $full_name;
        $_SESSION['user']['email'] = $email;
    } else {
        $error = 'Update failed: ' . $stmt->error;
    }
    $stmt->close();
}

// Reload current values
$stmt = $conn->prepare("
  SELECT u.username, u.full_name, u.email, r.role_name
  FROM users u
  JOIN roles r ON u.role_id = r.id
  WHERE u.id=?
");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$fresh = $res ? $res->fetch_assoc() : null;
$stmt->close();
ui_layout_start('Profile - RLI', 'profile');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Profile</h1>
    <p class="text-slate-500 mt-1">Manage your account details.</p>
  </div>
</div>

<?php if ($error): ?>
  <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="mt-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3 text-green-700 font-semibold"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
  <div class="lg:col-span-1 rounded-card bg-bgGrey border border-slate-100 p-5">
    <div class="text-sm text-slate-500">Account</div>
    <div class="mt-2 text-lg font-bold text-slate-900"><?php echo htmlspecialchars($fresh['username'] ?? ''); ?></div>
    <div class="mt-1 text-slate-600"><?php echo htmlspecialchars($fresh['role_name'] ?? ''); ?></div>
  </div>

  <div class="lg:col-span-2 rounded-card bg-bgGrey border border-slate-100 p-5">
    <h2 class="text-lg font-bold text-slate-900">Update Details</h2>
    <form method="POST" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="full_name">Full Name</label>
        <input id="full_name" name="full_name" type="text" value="<?php echo htmlspecialchars($fresh['full_name'] ?? ''); ?>"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="email">Email</label>
        <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($fresh['email'] ?? ''); ?>"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="password">New Password (optional)</label>
        <input id="password" name="password" type="password" placeholder="Leave blank to keep current password"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div class="md:col-span-2 flex gap-3 flex-wrap">
        <button class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<?php ui_layout_end(); ?>

