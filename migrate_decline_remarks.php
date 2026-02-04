<?php
/**
 * One-time migration: Add decline_remarks column
 * Visit this URL once: http://localhost/RLI-materialsReq/migrate_decline_remarks.php
 * Delete this file after running.
 */
require_once __DIR__ . '/includes/db_connect.php';

$result = $conn->query("SHOW COLUMNS FROM material_requests LIKE 'decline_remarks'");
if ($result && $result->num_rows > 0) {
    echo "Column 'decline_remarks' already exists. No action needed.";
    exit;
}

if ($conn->query("ALTER TABLE material_requests ADD COLUMN decline_remarks TEXT NULL AFTER approved_at")) {
    echo "Migration successful: decline_remarks column added.";
} else {
    echo "Migration failed: " . $conn->error;
}
