<?php
/**
 * MaterialRequest Model
 * Handles material request database operations
 */
namespace App\Models;

use App\Core\Model;

class MaterialRequest extends Model {
    protected $table = 'material_requests';

    private static $declineRemarksMigrated = false;

    public function __construct() {
        parent::__construct();
        $this->ensureDeclineRemarksColumn();
    }

    /**
     * Add decline_remarks column if missing (e.g. on Railway/production)
     */
    private function ensureDeclineRemarksColumn(): void {
        if (self::$declineRemarksMigrated) {
            return;
        }
        self::$declineRemarksMigrated = true;
        $result = $this->conn->query("SHOW COLUMNS FROM material_requests LIKE 'decline_remarks'");
        if ($result && $result->num_rows > 0) {
            return;
        }
        $this->conn->query("ALTER TABLE material_requests ADD COLUMN decline_remarks TEXT NULL AFTER approved_at");
    }

    /**
     * Get all requests with filtering
     */
    public function getRequests($filters = [], $limit = 20, $offset = 0) {
        $where = [];
        $params = [];
        $types = '';

        // Build WHERE clause
        if (!empty($filters['user_id'])) {
            $where[] = "mr.user_id = ?";
            $types .= "i";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['supervisor_id'])) {
            $where[] = "mr.supervisor_id = ?";
            $types .= "i";
            $params[] = $filters['supervisor_id'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'approved', 'declined'])) {
            $where[] = "mr.status = ?";
            $types .= "s";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = "(mr.requester_name LIKE ? OR mr.particulars LIKE ? OR mr.id LIKE ?)";
            $types .= "sss";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "
            SELECT
              mr.id,
              mr.requester_name,
              mr.date_requested,
              mr.date_needed,
              mr.particulars,
              mr.status,
              mr.decline_remarks,
              mr.created_at,
              u.username,
              s.initials,
              s.email AS supervisor_email,
              (SELECT COUNT(*) FROM request_items ri WHERE ri.request_id = mr.id) AS item_count,
              (SELECT COALESCE(SUM(ri.amount), 0) FROM request_items ri WHERE ri.request_id = mr.id) AS total_amount
            FROM material_requests mr
            LEFT JOIN users u ON mr.user_id = u.id
            LEFT JOIN supervisors s ON mr.supervisor_id = s.id
            $where_sql
            ORDER BY mr.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        return $this->fetchAll($sql, $types, $params);
    }

    /**
     * Get request by ID with items
     */
    public function getById($id) {
        $sql = "
            SELECT
              mr.id,
              mr.user_id,
              mr.requester_name,
              mr.date_requested,
              mr.date_needed,
              mr.particulars,
              mr.supervisor_id,
              mr.status,
              mr.decline_remarks,
              mr.created_at,
              u.username,
              s.initials,
              s.email as supervisor_email
            FROM material_requests mr
            LEFT JOIN users u ON mr.user_id = u.id
            LEFT JOIN supervisors s ON mr.supervisor_id = s.id
            WHERE mr.id = ?
        ";
        return $this->fetchOne($sql, "i", [$id]);
    }

    /**
     * Get all items for a request
     */
    public function getItems($request_id) {
        $sql = "
            SELECT * FROM request_items
            WHERE request_id = ?
            ORDER BY item_no ASC, id ASC
        ";
        return $this->fetchAll($sql, "i", [$request_id]);
    }

    /**
     * Create new request
     */
    public function create($user_id, $data) {
        $sql = "
            INSERT INTO material_requests
            (user_id, requester_name, date_requested, date_needed, particulars, supervisor_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        return $this->execute($sql, "issssi", [
            $user_id,
            $data['requester_name'],
            $data['date_requested'],
            $data['date_needed'],
            $data['particulars'],
            $data['supervisor_id']
        ]);
    }

    /**
     * Create request item
     */
    public function addItem($request_id, $item_data) {
        $sql = "
            INSERT INTO request_items
            (request_id, item_no, item_name, specs, quantity, unit, price, amount, item_link)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        return $this->execute($sql, "iissdddds", [
            $request_id,
            $item_data['item_no'],
            $item_data['item_name'],
            $item_data['specs'] ?? null,
            $item_data['quantity'],
            $item_data['unit'],
            $item_data['price'],
            $item_data['amount'],
            $item_data['item_link'] ?? null
        ]);
    }

    /**
     * Update request status
     */
    public function updateStatus($id, $status, $approved_by = null, $remarks = null) {
        $remarksVal = ($status === 'declined' && $remarks !== null && trim($remarks) !== '') ? trim($remarks) : null;
        $sql = "UPDATE material_requests SET status = ?, approved_by = ?, approved_at = NOW(), decline_remarks = ? WHERE id = ?";
        return $this->execute($sql, "sisi", [$status, $approved_by, $remarksVal, $id]);
    }

    /**
     * Update request
     */
    public function update($id, $data) {
        $allowed = ['requester_name', 'date_requested', 'date_needed', 'particulars', 'supervisor_id'];
        $updates = [];
        $values = [];
        $types = '';

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
                $types .= is_numeric($data[$field]) ? 'i' : 's';
            }
        }

        if (empty($updates)) {
            return ['affected' => 0, 'insert_id' => 0];
        }

        $values[] = $id;
        $types .= 'i';

        $sql = "UPDATE material_requests SET " . implode(", ", $updates) . " WHERE id = ?";
        return $this->execute($sql, $types, $values);
    }

    /**
     * Delete request (cascades to items)
     */
    public function delete($id) {
        $sql = "DELETE FROM material_requests WHERE id = ?";
        return $this->execute($sql, "i", [$id]);
    }

    /**
     * Count requests
     */
    public function count($filters = []) {
        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $types .= "i";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['supervisor_id'])) {
            $where[] = "supervisor_id = ?";
            $types .= "i";
            $params[] = $filters['supervisor_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $types .= "s";
            $params[] = $filters['status'];
        }

        $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $sql = "SELECT COUNT(*) as count FROM material_requests $where_sql";

        $result = $this->fetchOne($sql, $types, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $sql = "
            SELECT
              COUNT(*) as total,
              SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
              SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
              SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined
            FROM material_requests
        ";
        return $this->fetchOne($sql);
    }

    /**
     * Delete all items for a given request
     */
    public function deleteItemsByRequest($request_id) {
        $sql = "DELETE FROM request_items WHERE request_id = ?";
        return $this->execute($sql, "i", [$request_id]);
    }

    /**
     * Get request counts per month for line chart (last N months)
     * @param int $months Number of months to include
     * @param int|null $supervisor_id Filter by supervisor (null = all)
     * @return array [['label' => 'Jan 2025', 'count' => 5], ...]
     */
    public function getRequestsPerMonth($months = 6, $supervisor_id = null) {
        $where = "created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)";
        $params = [$months];
        $types = "i";

        if ($supervisor_id !== null) {
            $where .= " AND supervisor_id = ?";
            $params[] = $supervisor_id;
            $types .= "i";
        }

        $sql = "
            SELECT
              DATE_FORMAT(created_at, '%Y-%m') as month_key,
              DATE_FORMAT(created_at, '%b %Y') as month_label,
              COUNT(*) as count
            FROM material_requests
            WHERE $where
            GROUP BY month_key, month_label
            ORDER BY month_key ASC
        ";

        $rows = $this->fetchAll($sql, $types, $params);

        // Ensure all months in range have an entry (fill gaps with 0)
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $dt = new \DateTime();
            $dt->modify("-$i months");
            $key = $dt->format('Y-m');
            $label = $dt->format('M Y');
            $found = null;
            foreach ($rows as $r) {
                if ($r['month_key'] === $key) {
                    $found = (int) $r['count'];
                    break;
                }
            }
            $result[] = ['label' => $label, 'count' => $found ?? 0];
        }
        return $result;
    }
}
