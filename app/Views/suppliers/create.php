<?php
/**
 * Supplier Create View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Add Supplier - RLHI', 'suppliers');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Add Supplier</h1>
    <p class="text-slate-500 mt-1">Create a new supplier record.</p>
  </div>
  <a href="<?php echo $base; ?>/suppliers" class="px-4 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
    Back to Suppliers
  </a>
</div>

<form id="supplierForm" method="POST" action="<?php echo $base; ?>/suppliers/create" class="mt-6 space-y-4">
  <div>
    <label for="supplier_name" class="block text-sm font-semibold text-slate-700 mb-2">Supplier Name</label>
    <input id="supplier_name" name="supplier_name" type="text" required
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div>
    <label for="contact_person" class="block text-sm font-semibold text-slate-700 mb-2">Contact Person</label>
    <input id="contact_person" name="contact_person" type="text"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label for="contact_email" class="block text-sm font-semibold text-slate-700 mb-2">Contact Email</label>
      <input id="contact_email" name="contact_email" type="email"
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <div>
      <label for="contact_phone" class="block text-sm font-semibold text-slate-700 mb-2">Contact Phone</label>
      <input id="contact_phone" name="contact_phone" type="text"
        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
  </div>
  <div>
    <label for="address" class="block text-sm font-semibold text-slate-700 mb-2">Address</label>
    <textarea id="address" name="address" rows="3"
      class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow"></textarea>
  </div>
  <div class="flex gap-3">
    <button type="submit" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">Save Supplier</button>
    <a href="<?php echo $base; ?>/suppliers" class="px-5 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-900 font-semibold">Cancel</a>
  </div>
</form>

<script>
  document.getElementById('supplierForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) {
        window.location.href = '<?php echo $base; ?>/suppliers';
      } else {
        alert(data.message || 'Failed to create supplier.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Save Supplier';
    }
  });
</script>

<?php ui_layout_end(); ?>
