<?php
/**
 * Suppliers List View (Tailwind layout with sidebar)
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = defined('BASE_PATH') ? BASE_PATH : '';
ui_layout_start('Suppliers - RLHI', 'suppliers');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Suppliers</h1>
    <p class="text-slate-500 mt-1">Manage supplier information.</p>
  </div>
  <a href="<?php echo $base; ?>/suppliers/create" class="px-4 py-2 rounded-xl bg-accentYellow text-black font-semibold hover:opacity-95">
    Add Supplier
  </a>
</div>

<div class="mt-6 flex items-end gap-3 flex-wrap">
  <form method="GET" class="flex items-end gap-3 flex-wrap">
    <div>
      <label for="q" class="block text-sm font-semibold text-slate-700 mb-2">Search</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($search ?? ''); ?>"
        placeholder="Search suppliers..."
        class="min-w-[260px] rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:ring-4 focus:ring-accentYellow/20 focus:border-accentYellow">
    </div>
    <button type="submit" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">
      Filter
    </button>
  </form>
  <a href="<?php echo $base; ?>/suppliers" class="px-4 py-3 rounded-2xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold">Reset</a>
</div>

<div class="mt-6 overflow-x-auto rounded-card bg-bgGrey border border-slate-100 p-4">
  <div class="overflow-x-auto rounded-2xl bg-white border border-slate-100">
    <table class="min-w-[1000px] w-full">
      <thead>
        <tr class="text-left text-slate-500 text-sm">
          <th class="py-4 px-4 font-semibold">Supplier</th>
          <th class="py-4 px-4 font-semibold">Contact Person</th>
          <th class="py-4 px-4 font-semibold">Email</th>
          <th class="py-4 px-4 font-semibold">Phone</th>
          <th class="py-4 px-4 font-semibold">Address</th>
          <th class="py-4 px-4 font-semibold text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="text-slate-900">
        <?php if (empty($suppliers)): ?>
          <tr class="border-t border-slate-100">
            <td colspan="6" class="py-6 px-4 text-slate-600">No suppliers found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($suppliers as $supplier): ?>
            <tr class="border-t border-slate-100">
              <td class="py-4 px-4 font-semibold"><?php echo htmlspecialchars($supplier['supplier_name'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($supplier['contact_person'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($supplier['contact_email'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($supplier['contact_phone'] ?? ''); ?></td>
              <td class="py-4 px-4"><?php echo htmlspecialchars($supplier['address'] ?? ''); ?></td>
              <td class="py-4 px-4 text-right">
                <a href="<?php echo $base; ?>/suppliers/<?php echo (int)$supplier['id']; ?>/edit"
                   class="px-3 py-2 rounded-xl bg-bgGrey hover:bg-slate-200 text-slate-800 font-semibold mr-2">Edit</a>
                <button type="button"
                        data-supplier-id="<?php echo (int)$supplier['id']; ?>"
                        class="px-3 py-2 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700">
                  Delete
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($total_pages > 1): ?>
  <div class="mt-6 flex justify-center gap-2">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?php echo $i; ?>"
         class="px-4 py-2 rounded-xl <?php echo ($page === $i) ? 'bg-ink text-white' : 'bg-white border border-slate-200 text-slate-700'; ?>">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>
  </div>
<?php endif; ?>

<script>
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-supplier-id]');
    if (!btn) return;
    const id = btn.getAttribute('data-supplier-id');
    if (!id) return;
    if (!confirm('Delete this supplier?')) return;

    try {
      const res = await fetch('<?php echo $base; ?>/suppliers/' + id + '/delete', { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Failed to delete supplier.');
      }
    } catch (err) {
      alert('Error: ' + err.message);
    }
  });
</script>

<?php ui_layout_end(); ?>
