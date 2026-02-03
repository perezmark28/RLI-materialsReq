<?php
/**
 * Supervisor Model
 * Handles supervisor database operations
 */
namespace App\Models;

use App\Core\Model;

class Supervisor extends Model {
    protected $table = 'supervisors';

    /**
     * Get all supervisors
     */
    public function all() {
        $sql = "SELECT * FROM supervisors ORDER BY initials ASC";
        return $this->fetchAll($sql);
    }

    /**
     * Find supervisor by ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM supervisors WHERE id = ?";
        return $this->fetchOne($sql, "i", [$id]);
    }

    /**
     * Find supervisor by initials
     */
    public function findByInitials($initials) {
        $sql = "SELECT * FROM supervisors WHERE UPPER(initials) = UPPER(?)";
        return $this->fetchOne($sql, "s", [$initials]);
    }

    /**
     * Create supervisor
     */
    public function create($initials, $email, $mobile = null) {
        $sql = "INSERT INTO supervisors (initials, email, mobile) VALUES (?, ?, ?)";
        return $this->execute($sql, "sss", [$initials, $email, $mobile]);
    }

    /**
     * Update supervisor
     */
    public function update($id, $data) {
        $allowed = ['initials', 'email', 'mobile'];
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

        $sql = "UPDATE supervisors SET " . implode(", ", $updates) . " WHERE id = ?";
        return $this->execute($sql, $types, $values);
    }

    /**
     * Delete supervisor
     */
    public function delete($id) {
        $sql = "DELETE FROM supervisors WHERE id = ?";
        return $this->execute($sql, "i", [$id]);
    }

    /**
     * Check if initials exist
     */
    public function initialsExist($initials, $excludeId = null) {
        $sql = "SELECT id FROM supervisors WHERE UPPER(initials) = UPPER(?)";
        $params = [$initials];
        $types = "s";

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }

        $result = $this->fetchOne($sql, $types, $params);
        return $result !== null;
    }

    /**
     * Count supervisors
     */
    public function count() {
        $result = $this->fetchOne("SELECT COUNT(*) as count FROM supervisors");
        return $result['count'] ?? 0;
    }
}
