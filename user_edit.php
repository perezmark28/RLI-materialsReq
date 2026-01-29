<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_role(['super_admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo "Bad Request"; exit; }

$stmt = $conn->prepare("
  SELECT u.id, u.username, u.full_name, u.email, u.status, r.role_name
  FROM users u
  JOIN roles r ON u.role_id = r.id
  WHERE u.id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$u = $res ? $res->fetch_assoc() : null;
$stmt->close();
if (!$u) { http_response_code(404); echo "Not Found"; exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $role = $_POST['role'] ?? 'viewer';
    $new_password = $_POST['password'] ?? '';

    if (!in_array($status, ['active','inactive'], true)) $status = 'active';
    if (!in_array($role, ['viewer','admin','super_admin'], true)) $role = 'viewer';

    $r = $conn->prepare("SELECT id FROM roles WHERE role_name=? LIMIT 1");
    $r->bind_param("s", $role);
    $r->execute();
    $role_row = ($r->get_result()) ? $r->get_result()->fetch_assoc() : null;
    $r->close();
    if (!$role_row) {
        $error = 'Role not found.';
    } else {
        $role_id = (int)$role_row['id'];

        if ($new_password !== '') {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, status=?, role_id=?, password_hash=? WHERE id=?");
            $stmt->bind_param("sssisi", $full_name, $email, $status, $role_id, $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, status=?, role_id=? WHERE id=?");
            $stmt->bind_param("sssii", $full_name, $email, $status, $role_id, $id);
        }

        if ($stmt->execute()) {
            $success = 'User updated.';
            $u['full_name'] = $full_name;
            $u['email'] = $email;
            $u['status'] = $status;
            $u['role_name'] = $role;
        } else {
            $error = 'Update failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}
ui_layout_start('Edit User - RLI', 'users');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Edit User</h1>
    <p class="text-slate-500 mt-1">Update account role, status, and optional password reset.</p>
  </div>
  <a href="users.php" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Back</a>
</div>

<?php if ($error): ?>
  <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="mt-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3 text-green-700 font-semibold"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
    <input type="text" value="<?php echo htmlspecialchars($u['username']); ?>" readonly
      class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-600">
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="full_name">Full Name</label>
    <input id="full_name" name="full_name" type="text" value="<?php echo htmlspecialchars($u['full_name'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="email">Email</label>
    <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($u['email'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="role">Role</label>
    <select id="role" name="role" required
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      <option value="viewer" <?php echo $u['role_name']==='viewer'?'selected':''; ?>>Viewer</option>
      <option value="admin" <?php echo $u['role_name']==='admin'?'selected':''; ?>>Admin</option>
      <option value="super_admin" <?php echo $u['role_name']==='super_admin'?'selected':''; ?>>Super Admin</option>
    </select>
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="status">Status</label>
    <select id="status" name="status" required
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      <option value="active" <?php echo $u['status']==='active'?'selected':''; ?>>Active</option>
      <option value="inactive" <?php echo $u['status']==='inactive'?'selected':''; ?>>Inactive</option>
    </select>
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="password">New Password (optional)</label>
    <input id="password" name="password" type="password" placeholder="Leave blank to keep current password"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>

  <div class="md:col-span-2 flex gap-3 flex-wrap">
    <button class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Save</button>
    <a class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" href="users.php">Cancel</a>
  </div>
</form>

<?php ui_layout_end(); ?>

