<?php
/**
 * Dashboard View (Admin/Super Admin) – stats + quick actions
 */
require_once __DIR__ . '/../../../includes/ui.php';
$base = $base ?? (defined('BASE_PATH') ? BASE_PATH : '');
ui_layout_start('Dashboard - RLHI', 'dashboard');
?>

<div class="flex items-start justify-between gap-6 flex-wrap">
  <div>
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-slate-500 mt-1">Overview of request status.</p>
  </div>
  <div class="text-sm text-slate-500">
    Role: <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($role ?? ''); ?></span>
  </div>
</div>

<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Total Requests</div>
    <div class="text-3xl font-bold text-slate-900 mt-2"><?php echo $stats['total'] ?? 0; ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Pending</div>
    <div class="text-3xl font-bold text-yellow-600 mt-2"><?php echo $stats['pending'] ?? 0; ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Approved</div>
    <div class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['approved'] ?? 0; ?></div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-bgGrey p-5">
    <div class="text-slate-600 text-sm font-semibold">Declined</div>
    <div class="text-3xl font-bold text-red-600 mt-2"><?php echo $stats['declined'] ?? 0; ?></div>
  </div>
</div>

<!-- Charts -->
<div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
  <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Status Breakdown</h2>
    <div class="h-64 flex items-center justify-center">
      <canvas id="chartStatus" aria-label="Request status pie chart"></canvas>
    </div>
  </div>
  <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <h2 class="text-lg font-bold text-slate-900 mb-4">Requests Over Time (Last 6 Months)</h2>
    <div class="h-64">
      <canvas id="chartMonthly" aria-label="Requests per month line chart"></canvas>
    </div>
  </div>
</div>

<div class="mt-6 rounded-2xl border border-slate-100 bg-bgGrey p-5">
  <h2 class="text-lg font-bold text-slate-900 mb-4">Quick Actions</h2>
  <div class="flex gap-3 flex-wrap">
    <a href="<?php echo $base; ?>/requests/create" class="px-5 py-3 rounded-2xl bg-accentYellow text-black font-semibold hover:opacity-95">
      Create New Request
    </a>
    <a href="<?php echo $base; ?>/requests" class="px-5 py-3 rounded-2xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-900 font-semibold">
      View All Requests
    </a>
    <a href="<?php echo $base; ?>/profile" class="px-5 py-3 rounded-2xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-900 font-semibold">
      My Profile
    </a>
    <a href="<?php echo $base; ?>/statistics" class="px-5 py-3 rounded-2xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-900 font-semibold">
      Statistics
    </a>
    <a href="<?php echo $base; ?>/suppliers" class="px-5 py-3 rounded-2xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-900 font-semibold">
      Suppliers
    </a>
    <?php if (($role ?? '') === 'super_admin'): ?>
      <a href="<?php echo $base; ?>/users" class="px-5 py-3 rounded-2xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-900 font-semibold">
        User Accounts
      </a>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
  var stats = {
    pending: <?php echo (int)($stats['pending'] ?? 0); ?>,
    approved: <?php echo (int)($stats['approved'] ?? 0); ?>,
    declined: <?php echo (int)($stats['declined'] ?? 0); ?>
  };
  var monthly = <?php echo json_encode($chartMonthly ?? []); ?>;

  // Pie chart: Status breakdown
  var statusCanvas = document.getElementById('chartStatus');
  if (statusCanvas) {
    var total = stats.pending + stats.approved + stats.declined;
    if (total > 0) {
      new Chart(statusCanvas, {
        type: 'doughnut',
        data: {
          labels: ['Pending', 'Approved', 'Declined'],
          datasets: [{
            data: [stats.pending, stats.approved, stats.declined],
            backgroundColor: ['#EAB308', '#22C55E', '#EF4444'],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    } else {
      statusCanvas.parentElement.innerHTML = '<p class="text-slate-500 text-sm">No data yet</p>';
    }
  }

  // Line chart: Requests over time
  var monthlyCanvas = document.getElementById('chartMonthly');
  if (monthlyCanvas && monthly.length) {
    new Chart(monthlyCanvas, {
      type: 'line',
      data: {
        labels: monthly.map(function(m) { return m.label; }),
        datasets: [{
          label: 'Requests',
          data: monthly.map(function(m) { return m.count; }),
          borderColor: '#0B0B0C',
          backgroundColor: 'rgba(11, 11, 12, 0.05)',
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        }
      }
    });
  }
})();
</script>

<?php ui_layout_end(); ?>
