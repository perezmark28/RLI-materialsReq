<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db_connect.php';

require_role(['admin', 'super_admin']);

$user  = current_user();
$role  = current_role();

// Fetching all requests
$sql = "
SELECT
  mr.id,
  mr.requester_name,
  mr.date_requested,
  mr.date_needed,
  mr.status,
  approver.full_name AS approver_full_name
FROM material_requests mr
LEFT JOIN users approver ON mr.approved_by = approver.id
ORDER BY mr.id DESC
";

$requests = $conn->query($sql);

// Prepare statement to load items per request
$itemStmt = $conn->prepare("
  SELECT item_name, specs, quantity, unit, price, amount
  FROM request_items
  WHERE request_id = ?
  ORDER BY id ASC
");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>All Material Request Form</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white; padding: 0; }
      .print-container { width: 100%; max-width: 100%; border: none; shadow: none; }
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; font-size: 11px; }
    th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
  </style>
</head>
<body class="bg-slate-100 p-4">

  <div class="print-container max-w-7xl mx-auto bg-white p-8 shadow-lg border border-slate-300">
    
    <div class="flex justify-between items-center mb-6 no-print">
        <h1 class="text-xl font-bold">Print Preview</h1>
        <div class="space-x-2">
            <button onclick="window.print()" class="px-5 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Print Form</button>
            <a href="requests.php" class="px-5 py-2 bg-slate-500 text-white rounded-md hover:bg-slate-600">Back</a>
        </div>
    </div>

    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold uppercase tracking-widest">All Material Request Form</h2>
        <p class="text-sm text-slate-500">Official Document Summary</p>
    </div>

    <table>
      <thead>
        <tr>
          <th>Requester</th>
          <th>Date Requested</th>
          <th>Date Needed</th>
          <th style="width: 40px;">NO.</th>
          <th>Item Name</th>
          <th>Specs</th>
          <th>Quantity</th>
          <th>Unit</th>
          <th>Price</th>
          <th>Amount</th>
          <th>Approved By</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($requests && $requests->num_rows > 0): ?>
          <?php while ($row = $requests->fetch_assoc()): 
              $itemStmt->bind_param("i", $row['id']);
              $itemStmt->execute();
              $items = $itemStmt->get_result();
              $totalItems = $items->num_rows;
              $itemCounter = 1; // This handles the auto-increment for "NO."
          ?>
              <?php if ($totalItems > 0): ?>
                  <?php while ($item = $items->fetch_assoc()): ?>
                  <tr>
                      <?php if ($itemCounter === 1): ?>
                          <td rowspan="<?php echo $totalItems; ?>" class="font-bold"><?php echo htmlspecialchars($row['requester_name']); ?></td>
                          <td rowspan="<?php echo $totalItems; ?>"><?php echo htmlspecialchars($row['date_requested']); ?></td>
                          <td rowspan="<?php echo $totalItems; ?>"><?php echo htmlspecialchars($row['date_needed']); ?></td>
                      <?php endif; ?>
                      
                      <td class="text-center"><?php echo $itemCounter; ?></td>
                      <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                      <td><?php echo htmlspecialchars($item['specs']); ?></td>
                      <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                      <td><?php echo htmlspecialchars($item['unit']); ?></td>
                      <td><?php echo number_format($item['price'], 2); ?></td>
                      <td class="font-semibold"><?php echo number_format($item['amount'], 2); ?></td>

                      <?php if ($itemCounter === 1): ?>
                          <td rowspan="<?php echo $totalItems; ?>"><?php echo htmlspecialchars($row['approver_full_name'] ?? 'N/A'); ?></td>
                          <td rowspan="<?php echo $totalItems; ?>" class="font-bold text-center uppercase"><?php echo htmlspecialchars($row['status']); ?></td>
                      <?php endif; ?>
                  </tr>
                  <?php $itemCounter++; endwhile; ?>
              <?php else: ?>
                  <tr>
                      <td class="font-bold"><?php echo htmlspecialchars($row['requester_name']); ?></td>
                      <td><?php echo htmlspecialchars($row['date_requested']); ?></td>
                      <td><?php echo htmlspecialchars($row['date_needed']); ?></td>
                      <td colspan="7" class="text-center italic text-slate-400">No items found</td>
                      <td><?php echo htmlspecialchars($row['approver_full_name'] ?? 'N/A'); ?></td>
                      <td class="text-center font-bold uppercase"><?php echo htmlspecialchars($row['status']); ?></td>
                  </tr>
              <?php endif; ?>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="12" class="py-20 text-center text-lg font-medium">No Request Data Available</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="mt-10 flex justify-between text-[10px] text-slate-500 italic">
        <p>Printed By: <?php echo htmlspecialchars($user['username']); ?></p>
        <p>Date Generated: <?php echo date('F j, Y, g:i a'); ?></p>
    </div>
  </div>

</body>
</html>