<?php
/**
 * Supplier Model
 * Handles supplier database operations
 */
namespace App\Models;

use App\Core\Model;

class Supplier extends Model {
    protected $table = 'suppliers';

    /**
     * Get all suppliers
     */
    public function all($limit = null, $offset = 0) {
        $sql = "SELECT * FROM suppliers ORDER BY supplier_name ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->fetchAll($sql, "ii", [$limit, $offset]);
        }

        return $this->fetchAll($sql);
    }

    /**
     * Find supplier by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM suppliers WHERE id = ?";
        return $this->fetchOne($sql, "i", [$id]);
    }

    /**
     * Search suppliers
     */
    public function search($keyword) {
        $like = '%' . $keyword . '%';
        $sql = "
            SELECT * FROM suppliers
            WHERE supplier_name LIKE ? OR contact_person LIKE ? OR contact_email LIKE ? OR contact_phone LIKE ?
            ORDER BY supplier_name ASC
        ";
        return $this->fetchAll($sql, "ssss", [$like, $like, $like, $like]);
    }

    /**
     * Create supplier
     */
    public function create($data) {
        $sql = "
            INSERT INTO suppliers (supplier_name, contact_person, contact_email, contact_phone, address)
            VALUES (?, ?, ?, ?, ?)
        ";
        
        return $this->execute($sql, "sssss", [
            $data['supplier_name'],
            $data['contact_person'] ?? null,
            $data['contact_email'] ?? null,
            $data['contact_phone'] ?? null,
            $data['address'] ?? null
        ]);
    }

    /**
     * Update supplier
     */
    public function update($id, $data) {
        $allowed = ['supplier_name', 'contact_person', 'contact_email', 'contact_phone', 'address'];
        $updates = [];
        $values = [];
        $types = '';

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
                $types .= 's';
            }
        }

        if (empty($updates)) {
            return ['affected' => 0, 'insert_id' => 0];
        }

        $values[] = $id;
        $types .= 'i';

        $sql = "UPDATE suppliers SET " . implode(", ", $updates) . " WHERE id = ?";
        return $this->execute($sql, $types, $values);
    }

    /**
     * Delete supplier
     */
    public function delete($id) {
        $sql = "DELETE FROM suppliers WHERE id = ?";
        return $this->execute($sql, "i", [$id]);
    }

    /**
     * Count suppliers
     */
    public function count() {
        $result = $this->fetchOne("SELECT COUNT(*) as count FROM suppliers");
        return $result['count'] ?? 0;
    }
}
