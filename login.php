<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ui_public.php';

if (is_logged_in()) {
    header('Location: home.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $stmt = $conn->prepare("
            SELECT u.id, u.username, u.password_hash, u.full_name, u.email, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = ? AND u.status = 'active'
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = 'Invalid credentials.';
        } else {
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role_name'],
            ];
            header('Location: home.php');
            exit;
        }
    }
}
?>
<?php
ui_public_head('Login - RLI');
ui_public_shell_start();
?>

<div class="flex items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Login</h1>
    <p class="text-slate-600 mt-2">Enter your username and password.</p>
  </div>
  <a href="index.php" class="text-sm font-semibold text-slate-700 hover:text-black">Back</a>
</div>

<?php if ($error): ?>
  <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST" action="login.php" class="mt-6 space-y-4">
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

  <button class="w-full px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Login</button>
  <a class="block w-full text-center px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold" href="signup.php">Sign Up</a>
</form>

<?php ui_public_shell_end(); ?>

