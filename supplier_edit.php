<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/ui.php';
require_role(['admin','super_admin']);

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo "Bad Request"; exit; }

$stmt = $conn->prepare("SELECT * FROM suppliers WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$supplier = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$supplier) { http_response_code(404); echo "Not Found"; exit; }

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($supplier_name === '') {
        $error = 'Supplier name is required.';
    } else {
        $stmt = $conn->prepare("UPDATE suppliers SET supplier_name=?, contact_person=?, contact_email=?, contact_phone=?, address=? WHERE id=?");
        $stmt->bind_param("sssssi", $supplier_name, $contact_person, $contact_email, $contact_phone, $address, $id);
        if ($stmt->execute()) {
            $success = 'Supplier updated.';
            $supplier['supplier_name'] = $supplier_name;
            $supplier['contact_person'] = $contact_person;
            $supplier['contact_email'] = $contact_email;
            $supplier['contact_phone'] = $contact_phone;
            $supplier['address'] = $address;
        } else {
            $error = 'Update failed: ' . $stmt->error;
        }
        $stmt->close();
    }
}
ui_layout_start('Edit Supplier - RLI', 'suppliers');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Edit Supplier</h1>
    <p class="text-slate-500 mt-1">Update supplier details.</p>
  </div>
  <a href="suppliers.php" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Back</a>
</div>

<?php if ($error): ?>
  <div class="mt-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-700 font-semibold"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
  <div class="mt-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3 text-green-700 font-semibold"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="POST" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="supplier_name">Supplier Name</label>
    <input id="supplier_name" name="supplier_name" type="text" required value="<?php echo htmlspecialchars($supplier['supplier_name']); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="contact_person">Contact Person</label>
    <input id="contact_person" name="contact_person" type="text" value="<?php echo htmlspecialchars($supplier['contact_person'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="contact_email">Contact Email</label>
    <input id="contact_email" name="contact_email" type="email" value="<?php echo htmlspecialchars($supplier['contact_email'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="contact_phone">Contact Phone</label>
    <input id="contact_phone" name="contact_phone" type="text" value="<?php echo htmlspecialchars($supplier['contact_phone'] ?? ''); ?>"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div class="md:col-span-2">
    <label class="block text-sm font-semibold text-slate-700 mb-2" for="address">Address</label>
    <textarea id="address" name="address" rows="2"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></textarea>
  </div>
  <div class="md:col-span-2 flex gap-3 flex-wrap">
    <button class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95" type="submit">Save</button>
    <a class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold" href="suppliers.php">Cancel</a>
  </div>
</form>

<?php ui_layout_end(); ?>

