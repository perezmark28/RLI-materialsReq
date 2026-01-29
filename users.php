<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_role(['super_admin']);

$error = '';
$success = '';
// Search
$q = trim($_GET['q'] ?? '');

// Create user/admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'viewer';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (!in_array($role, ['viewer','admin','super_admin'], true)) {
        $error = 'Invalid role.';
    } else {
        $r = $conn->prepare("SELECT id FROM roles WHERE role_name=? LIMIT 1");
        $r->bind_param("s", $role);
        $r->execute();
        $res = $r->get_result();
        $role_row = $res ? $res->fetch_assoc() : null;
        $r->close();

        if (!$role_row) {
            $error = 'Role not found. Run migration.';
        } else {
            $role_id = (int)$role_row['id'];
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password_hash, full_name, email, role_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $hash, $full_name, $email, $role_id);
            if ($stmt->execute()) {
                $success = 'User created.';
            } else {
                $error = 'Create failed: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$result = null;
if ($q !== '') {
  $like = '%' . $q . '%';
  $stmt = $conn->prepare("
    SELECT u.id, u.username, u.full_name, u.email, u.status, r.role_name, u.created_at
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR r.role_name LIKE ?
    ORDER BY u.id DESC
  ");
  $stmt->bind_param("ssss", $like, $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("
    SELECT u.id, u.username, u.full_name, u.email, u.status, r.role_name, u.created_at
    FROM users u
    JOIN roles r ON u.role_id = r.id
    ORDER BY u.id DESC
  ");
}
ui_layout_start('User Accounts - RLI', 'users');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">User Accounts</h1>
    <p class="text-slate-500 mt-1">Create and manage viewer/admin/super admin accounts.</p>
  </div>
  <form method="GET" class="flex gap-2 items-end">
    <div>
      <label for="q" class="block text-sm font-semibold text-slate-700 mb-2">Search</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($q); ?>"
        placeholder="Search users..."
        class="min-w-[260px] rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <button class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" type="submit">Search</button>
    <a href="users.php" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Reset</a>
  </form>
</div>

<?php if ($error): ?>
  <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="mt-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3 text-green-700 font-semibold"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-4">
  <div class="lg:col-span-1 rounded-card bg-bgGrey border border-slate-100 p-5">
    <h2 class="text-lg font-bold text-slate-900">Create Account</h2>
    <form method="POST" class="mt-4 space-y-3">
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="username">Username</label>
        <input id="username" name="username" type="text" required
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="password">Password</label>
        <input id="password" name="password" type="password" required
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="full_name">Full Name</label>
        <input id="full_name" name="full_name" type="text"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="email">Email</label>
        <input id="email" name="email" type="email"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="role">Role</label>
        <select id="role" name="role" required
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
          <option value="viewer">Viewer</option>
          <option value="admin">Admin</option>
          <option value="super_admin">Super Admin</option>
        </select>
      </div>
      <button class="w-full px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Create</button>
    </form>
  </div>

  <div class="lg:col-span-2 rounded-card bg-bgGrey border border-slate-100 p-5">
    <h2 class="text-lg font-bold text-slate-900">Accounts</h2>
    <div class="mt-4 overflow-x-auto rounded-2xl bg-white border border-slate-100">
      <table class="min-w-[900px] w-full">
        <thead>
          <tr class="text-left text-slate-500 text-sm">
            <th class="py-4 px-4 font-semibold">ID</th>
            <th class="py-4 px-4 font-semibold">Username</th>
            <th class="py-4 px-4 font-semibold">Full Name</th>
            <th class="py-4 px-4 font-semibold">Email</th>
            <th class="py-4 px-4 font-semibold">Role</th>
            <th class="py-4 px-4 font-semibold">Status</th>
            <th class="py-4 px-4 font-semibold">Created</th>
            <th class="py-4 px-4 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="text-slate-900">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($u = $result->fetch_assoc()): ?>
              <?php
                $statusBadge = $u['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-700';
              ?>
              <tr class="border-t border-slate-100">
                <td class="py-4 px-4 font-semibold text-slate-800">#<?php echo (int)$u['id']; ?></td>
                <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($u['username']); ?></td>
                <td class="py-4 px-4 text-slate-700"><?php echo htmlspecialchars($u['full_name'] ?? ''); ?></td>
                <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                <td class="py-4 px-4 text-slate-700"><?php echo htmlspecialchars($u['role_name']); ?></td>
                <td class="py-4 px-4">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?php echo $statusBadge; ?>">
                    <?php echo htmlspecialchars($u['status']); ?>
                  </span>
                </td>
                <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($u['created_at']); ?></td>
                <td class="py-4 px-4 text-right whitespace-nowrap">
                  <a class="px-3 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" href="user_edit.php?id=<?php echo (int)$u['id']; ?>">Edit</a>
                  <?php if ((int)$u['id'] !== (int)($_SESSION['user']['id'] ?? 0)): ?>
                    <a class="ml-2 px-3 py-2 rounded-xl bg-red-600 text-white font-semibold hover:opacity-95" href="user_delete.php?id=<?php echo (int)$u['id']; ?>" onclick="return confirm('Delete this user?')">Delete</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr class="border-t border-slate-100">
              <td class="py-6 px-4 text-slate-600" colspan="8">No users found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php ui_layout_end(); ?>

