<?php
/**
 * Save Material Request Handler
 * Processes form submission and saves to database using prepared statements
 */

// Turn off error display to prevent HTML output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

// Start output buffering to catch any unexpected output
ob_start();

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'request_id' => null
);

require_once __DIR__ . '/includes/auth.php';
if (!is_logged_in()) {
    ob_clean();
    $response['message'] = 'Unauthorized. Please login.';
    echo json_encode($response);
    exit;
}

require_once __DIR__ . '/includes/sms.php';

// Include database connection with error handling
try {
    require_once 'includes/db_connect.php';
} catch (Exception $e) {
    ob_clean();
    $response['message'] = 'Database connection failed: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Parse nested array data from form
// Handle both cases: when PHP parses it automatically and when it doesn't
if (isset($_POST['items']) && is_array($_POST['items'])) {
    // PHP already parsed it as nested array
    $items = $_POST['items'];
} else {
    // Parse manually from flat POST data
    $items = array();
    foreach ($_POST as $key => $value) {
        if (preg_match('/^items\[(\d+)\]\[(.+)\]$/', $key, $matches)) {
            $item_index = $matches[1];
            $field_name = $matches[2];
            if (!isset($items[$item_index])) {
                $items[$item_index] = array();
            }
            $items[$item_index][$field_name] = $value;
        }
    }
}

// Validate required fields
if (empty($_POST['requester_name'])) {
    $response['message'] = "Missing required field: requester_name";
    echo json_encode($response);
    exit;
}

if (empty($_POST['date_requested'])) {
    $response['message'] = "Missing required field: date_requested";
    echo json_encode($response);
    exit;
}

if (empty($_POST['date_needed'])) {
    $response['message'] = "Missing required field: date_needed";
    echo json_encode($response);
    exit;
}

if (empty($_POST['particulars'])) {
    $response['message'] = "Missing required field: particulars";
    echo json_encode($response);
    exit;
}

if (empty($_POST['supervisor_id'])) {
    $response['message'] = "Please select a supervisor before submitting.";
    echo json_encode($response);
    exit;
}

// Validate items array
if (empty($items)) {
    $response['message'] = 'At least one item is required.';
    echo json_encode($response);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Sanitize and prepare data for material_requests table
    $user_id = (int)($_SESSION['user']['id'] ?? 0);
    if ($user_id <= 0) {
        throw new Exception('Invalid session user.');
    }
    $requester_name = trim($_POST['requester_name']);
    $date_requested = $_POST['date_requested'];
    $date_needed = $_POST['date_needed'];
    $particulars = trim($_POST['particulars']);
    $supervisor_id = isset($_POST['supervisor_id']) && !empty($_POST['supervisor_id']) ? intval($_POST['supervisor_id']) : null;

    // Validate dates
    if (!strtotime($date_requested) || !strtotime($date_needed)) {
        throw new Exception('Invalid date format.');
    }

    // Insert into material_requests table using prepared statement
    $stmt = $conn->prepare("INSERT INTO material_requests (user_id, requester_name, date_requested, date_needed, particulars, supervisor_id, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("issssi", $user_id, $requester_name, $date_requested, $date_needed, $particulars, $supervisor_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Get the last insert ID
    $request_id = $conn->insert_id;
    $stmt->close();

    // Insert items into request_items table
    $item_stmt = $conn->prepare("INSERT INTO request_items (request_id, item_no, item_name, specs, quantity, unit, price, amount, item_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$item_stmt) {
        throw new Exception("Prepare failed for items: " . $conn->error);
    }

    // Validate that we have at least one valid item
    $valid_items_count = 0;
    foreach ($items as $item) {
        if (!empty($item['item_name']) && isset($item['quantity']) && isset($item['price']) && 
            $item['quantity'] > 0 && $item['price'] >= 0) {
            $valid_items_count++;
        }
    }
    
    if ($valid_items_count === 0) {
        throw new Exception('At least one valid item with name, quantity, and price is required.');
    }

    $item_no = 1;
    foreach ($items as $item) {
        // Validate required item fields
        if (empty($item['item_name']) || !isset($item['quantity']) || !isset($item['price'])) {
            continue; // Skip invalid items
        }

        // Sanitize and prepare item data
        $item_name = trim($item['item_name']);
        $specs = isset($item['specs']) ? trim($item['specs']) : '';
        $quantity = floatval($item['quantity']);
        $unit = isset($item['unit']) ? trim($item['unit']) : '';
        $price = floatval($item['price']);
        $amount = $quantity * $price;
        $item_link = isset($item['item_link']) ? trim($item['item_link']) : '';

        // Bind parameters and execute
        // Type string: i=integer, s=string, d=double/decimal
        // Parameters: request_id(i), item_no(i), item_name(s), specs(s), quantity(d), unit(s), price(d), amount(d), item_link(s)
        $item_stmt->bind_param("iissdsdds", 
            $request_id, 
            $item_no, 
            $item_name, 
            $specs, 
            $quantity, 
            $unit, 
            $price, 
            $amount, 
            $item_link
        );

        if (!$item_stmt->execute()) {
            throw new Exception("Execute failed for item: " . $item_stmt->error);
        }

        $item_no++;
    }

    $item_stmt->close();

    // Commit transaction
    $conn->commit();

    // Send SMS to assigned supervisor (best-effort: request still succeeds even if SMS fails)
    $sms_notice = '';
    if (!empty($supervisor_id)) {
        $supStmt = $conn->prepare("SELECT mobile FROM supervisors WHERE id = ? LIMIT 1");
        if ($supStmt) {
            $supStmt->bind_param("i", $supervisor_id);
            $supStmt->execute();
            $supRes = $supStmt->get_result();
            $supRow = $supRes ? $supRes->fetch_assoc() : null;
            $supStmt->close();

            $mobile = trim((string)($supRow['mobile'] ?? ''));
            if ($mobile !== '') {
                // Link to request details (best for approval) + optionally shorten
                $detailsUrl = app_url('request_view.php?id=' . (int)$request_id);
                $shortUrl = try_shorten_url($detailsUrl);

                $msg = 'You have a new request for approval. Kindly check the material request form. ' . $shortUrl;
                $sms = httpsms_send($mobile, $msg);
                if (!$sms['success']) {
                    $sms_notice = ' (SMS not sent: ' . ($sms['error'] ?? 'Unknown error') . ')';
                }
            } else {
                $sms_notice = ' (SMS not sent: supervisor mobile not set)';
            }
        } else {
            $sms_notice = ' (SMS not sent: ' . $conn->error . ')';
        }
    }

    // Success response
    $response['success'] = true;
    $response['message'] = 'Material request saved successfully!' . $sms_notice;
    $response['request_id'] = $request_id;

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    $response['message'] = 'Error: ' . $e->getMessage();
} catch (Error $e) {
    // Catch PHP 7+ errors
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
    }
    $response['message'] = 'Fatal error: ' . $e->getMessage();
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

// Clear any unexpected output
ob_clean();

// Return JSON response
echo json_encode($response);
exit;
?>
