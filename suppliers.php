<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_role(['admin','super_admin']);

$error = '';
$success = '';
// Search
$q = trim($_GET['q'] ?? '');

// Create supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($supplier_name === '') {
        $error = 'Supplier name is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, contact_person, contact_email, contact_phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $supplier_name, $contact_person, $contact_email, $contact_phone, $address);
        if ($stmt->execute()) {
            $success = 'Supplier added.';
        } else {
            $error = 'Add failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$result = null;
if ($q !== '') {
  $like = '%' . $q . '%';
  $stmt = $conn->prepare("SELECT * FROM suppliers WHERE supplier_name LIKE ? OR contact_person LIKE ? OR contact_email LIKE ? OR contact_phone LIKE ? ORDER BY id DESC");
  $stmt->bind_param("ssss", $like, $like, $like, $like);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $result = $conn->query("SELECT * FROM suppliers ORDER BY id DESC");
}
ui_layout_start('Suppliers - RLI', 'suppliers');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Suppliers</h1>
    <p class="text-slate-500 mt-1">Create and maintain supplier records.</p>
  </div>
  <form method="GET" class="flex gap-2 items-end">
    <div>
      <label for="q" class="block text-sm font-semibold text-slate-700 mb-2">Search</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($q); ?>"
        placeholder="Search suppliers..."
        class="min-w-[260px] rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <button class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" type="submit">Search</button>
    <a href="suppliers.php" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Reset</a>
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
    <h2 class="text-lg font-bold text-slate-900">Add Supplier</h2>
    <form method="POST" class="mt-4 space-y-3">
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="supplier_name">Supplier Name</label>
        <input id="supplier_name" name="supplier_name" type="text" required
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="contact_person">Contact Person</label>
        <input id="contact_person" name="contact_person" type="text"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="contact_email">Contact Email</label>
        <input id="contact_email" name="contact_email" type="email"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="contact_phone">Contact Phone</label>
        <input id="contact_phone" name="contact_phone" type="text"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
      </div>
      <div>
        <label class="block text-sm font-semibold text-slate-700 mb-2" for="address">Address</label>
        <textarea id="address" name="address" rows="2"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"></textarea>
      </div>
      <button class="w-full px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Add</button>
    </form>
  </div>

  <div class="lg:col-span-2 rounded-card bg-bgGrey border border-slate-100 p-5">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-bold text-slate-900">Supplier List</h2>
    </div>
    <div class="mt-4 overflow-x-auto rounded-2xl bg-white border border-slate-100">
      <table class="min-w-[900px] w-full">
        <thead>
          <tr class="text-left text-slate-500 text-sm">
            <th class="py-4 px-4 font-semibold">ID</th>
            <th class="py-4 px-4 font-semibold">Name</th>
            <th class="py-4 px-4 font-semibold">Contact</th>
            <th class="py-4 px-4 font-semibold">Email</th>
            <th class="py-4 px-4 font-semibold">Phone</th>
            <th class="py-4 px-4 font-semibold text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="text-slate-900">
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="border-t border-slate-100">
                <td class="py-4 px-4 font-semibold text-slate-800">#<?php echo (int)$row['id']; ?></td>
                <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                <td class="py-4 px-4 text-slate-700"><?php echo htmlspecialchars($row['contact_person'] ?? ''); ?></td>
                <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($row['contact_email'] ?? ''); ?></td>
                <td class="py-4 px-4 text-slate-600"><?php echo htmlspecialchars($row['contact_phone'] ?? ''); ?></td>
                <td class="py-4 px-4 text-right whitespace-nowrap">
                  <a class="px-3 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" href="supplier_edit.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                  <?php if (current_role() === 'super_admin'): ?>
                    <a class="ml-2 px-3 py-2 rounded-xl bg-red-600 text-white font-semibold hover:opacity-95" href="supplier_delete.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete supplier?')">Delete</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr class="border-t border-slate-100">
              <td class="py-6 px-4 text-slate-600" colspan="6">No suppliers found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php ui_layout_end(); ?>

