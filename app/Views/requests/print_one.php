<?php
/**
 * Printable Single Request View - same layout as View All Printable
 * Expects: $requests (array of 1 request), $requestId, $base
 */
$base = $base ?? (defined('BASE_PATH') ? BASE_PATH : '');
$globalNumber = 1;
$row = $requests[0] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Material Request Form #<?php echo (int)$requestId; ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white; padding: 0; }
    }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; font-size: 11px; }
    th { background-color: #f2f2f2 !important; -webkit-print-color-adjust: exact; }
  </style>
</head>
<body class="bg-slate-100 p-4">
  <div class="max-w-7xl mx-auto bg-white p-8 shadow-lg border border-slate-300">
    <div class="flex justify-between items-center mb-6 no-print">
      <h1 class="text-xl font-bold text-slate-700">Material Request Form #<?php echo (int)$requestId; ?></h1>
      <div class="space-x-2">
        <button onclick="window.print()" class="px-5 py-2 bg-blue-600 text-white rounded-md">Print</button>
        <a href="<?php echo htmlspecialchars($base); ?>/requests/<?php echo (int)$requestId; ?>" class="px-5 py-2 bg-slate-500 text-white rounded-md">Back</a>
      </div>
    </div>

    <h2 class="text-2xl font-bold text-center uppercase mb-8 underline">Material Request Form</h2>

    <table>
      <thead>
        <tr>
          <th>Requester</th>
          <th>Particulars</th>
          <th>Date Requested</th>
          <th>Date Needed</th>
          <th style="width: 50px;">NO.</th>
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
        <?php if ($row && !empty($row['items'])): ?>
          <?php
            $items = $row['items'];
            $totalItems = count($items);
            $isFirstRowForThisRequest = true;
          ?>
          <?php foreach ($items as $item): ?>
            <tr>
              <?php if ($isFirstRowForThisRequest): ?>
                <td rowspan="<?php echo $totalItems; ?>" class="font-bold"><?php echo htmlspecialchars($row['requester_name'] ?? ''); ?></td>
                <td rowspan="<?php echo $totalItems; ?>"><?php echo htmlspecialchars($row['particulars'] ?? ''); ?></td>
                <td rowspan="<?php echo $totalItems; ?>"><?php echo htmlspecialchars($row['date_requested'] ?? ''); ?></td>
                <td rowspan="<?php echo $totalItems; ?>"><?php echo htmlspecialchars($row['date_needed'] ?? ''); ?></td>
              <?php endif; ?>

              <td class="text-center font-bold"><?php echo $globalNumber++; ?></td>
              <td><?php echo htmlspecialchars($item['item_name'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($item['specs'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($item['quantity'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($item['unit'] ?? ''); ?></td>
              <td><?php echo number_format((float)($item['price'] ?? 0), 2); ?></td>
              <td><?php echo number_format((float)($item['amount'] ?? 0), 2); ?></td>

              <?php if ($isFirstRowForThisRequest): ?>
                <td rowspan="<?php echo $totalItems; ?>"><?php echo htmlspecialchars($row['approver_full_name'] ?? 'N/A'); ?></td>
                <td rowspan="<?php echo $totalItems; ?>" class="text-center uppercase text-[10px]"><?php echo htmlspecialchars($row['status'] ?? ''); ?></td>
              <?php endif; ?>
            </tr>
            <?php $isFirstRowForThisRequest = false; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="13" class="text-center py-4">No items found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
