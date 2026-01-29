<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui_public.php';

if (is_logged_in()) {
    header('Location: home.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        // Role: viewer by default
        $role_id = null;
        $r = $conn->query("SELECT id FROM roles WHERE role_name='viewer' LIMIT 1");
        if ($r && $r->num_rows > 0) {
            $role_id = (int)$r->fetch_assoc()['id'];
        }
        if (!$role_id) {
            $error = 'Role setup missing. Please run the RBAC migration.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password_hash, full_name, email, role_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $hash, $full_name, $email, $role_id);
            if ($stmt->execute()) {
                $success = 'Account created. You can now login.';
            } else {
                $error = 'Signup failed: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<?php
ui_public_head('Sign Up - RLI');
ui_public_shell_start();
?>

<div class="flex items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Sign Up</h1>
    <p class="text-slate-600 mt-2">Create a viewer account.</p>
  </div>
  <a href="index.php" class="text-sm font-semibold text-slate-700 hover:text-black">Back</a>
</div>

<?php if ($error): ?>
  <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="mt-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3 text-green-700 font-semibold"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" action="signup.php" class="mt-6 space-y-4">
  <div>
    <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
    <input id="full_name" name="full_name" type="text"
      value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
    <input id="email" name="email" type="email"
      value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
    <input id="username" name="username" type="text" required
      value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
    <input id="password" name="password" type="password" required
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>

  <button class="w-full px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Create Account</button>
  <a class="block w-full text-center px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold" href="login.php">Back to Login</a>
</form>

<?php ui_public_shell_end(); ?>

