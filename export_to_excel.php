<?php
/**
 * Export Material Requests to Excel
 * Exports all material requests with their items as a simple list view CSV file
 */

// Turn off error display
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include database connection
require_once 'includes/db_connect.php';

// Set headers for simple CSV file download (list view only)
$filename = 'RLI_Material_Requests_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Fetch all material requests with their items
$query = "SELECT 
    mr.id AS request_id,
    mr.requester_name,
    mr.date_requested,
    mr.date_needed,
    mr.particulars,
    mr.status,
    mr.created_at,
    s.initials AS supervisor_initials,
    s.email AS supervisor_email,
    ri.item_no,
    ri.item_name,
    ri.specs,
    ri.quantity,
    ri.unit,
    ri.price,
    ri.amount,
    ri.item_link
FROM material_requests mr
LEFT JOIN request_items ri ON mr.id = ri.request_id
LEFT JOIN supervisors s ON mr.supervisor_id = s.id
ORDER BY mr.id ASC, ri.item_no ASC";

$result = $conn->query($query);

if (!$result) {
    fclose($output);
    die("Error fetching data: " . $conn->error);
}

// Write simple CSV headers (list view format)
$headers = array(
    'Request ID',
    'Requester Name',
    'Date Requested',
    'Date Needed',
    'Particulars',
    'Supervisor Initials',
    'Supervisor Email',
    'Status',
    'Created At',
    'Item No',
    'Item Name',
    'Specs',
    'Quantity',
    'Unit',
    'Price',
    'Amount',
    'Item Link'
);
fputcsv($output, $headers, ',', '"');

// Write data rows (simple list format, no formatting)
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format numbers to avoid Excel auto-formatting
        $quantity = $row['quantity'] ?? '';
        $price = $row['price'] ?? '';
        $amount = $row['amount'] ?? '';
        
        // Write as plain list data
        fputcsv($output, array(
            $row['request_id'],
            $row['requester_name'],
            $row['date_requested'],
            $row['date_needed'],
            $row['particulars'],
            $row['supervisor_initials'] ?? '',
            $row['supervisor_email'] ?? '',
            $row['status'],
            $row['created_at'],
            $row['item_no'] ?? '',
            $row['item_name'] ?? '',
            $row['specs'] ?? '',
            $quantity,
            $row['unit'] ?? '',
            $price,
            $amount,
            $row['item_link'] ?? ''
        ), ',', '"');
    }
} else {
    // Write a message if no data
    fputcsv($output, array('No requests found in the database.'), ',', '"');
}

// Close output stream
fclose($output);

// Close database connection
$conn->close();
exit;
?>
