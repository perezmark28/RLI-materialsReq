<?php
/**
 * User Model
 * Handles user-related database operations
 */
namespace App\Models;

use App\Core\Model;

class User extends Model {
    protected $table = 'users';

    /**
     * Find user by username
     */
    public function findByUsername($username) {
        $sql = "
            SELECT u.id, u.username, u.password_hash, u.full_name, u.email, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = ? AND u.status = 'active'
            LIMIT 1
        ";
        return $this->fetchOne($sql, "s", [$username]);
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $sql = "
            SELECT u.id, u.username, u.full_name, u.email, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ";
        return $this->fetchOne($sql, "i", [$id]);
    }

    /**
     * Get all users
     */
    public function all($limit = null, $offset = 0) {
        $sql = "
            SELECT u.id, u.username, u.full_name, u.email, r.role_name, u.status, u.created_at
            FROM users u
            JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC
        ";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return $this->fetchAll($sql, "ii", [$limit, $offset]);
        }

        return $this->fetchAll($sql);
    }

    /**
     * Count users
     */
    public function count() {
        $result = $this->fetchOne("SELECT COUNT(*) as count FROM users");
        return $result['count'] ?? 0;
    }

    /**
     * Create new user
     */
    public function create($username, $password, $full_name, $email, $role_id) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "
            INSERT INTO users (username, password_hash, full_name, email, role_id, status)
            VALUES (?, ?, ?, ?, ?, 'active')
        ";
        return $this->execute($sql, "ssssi", [$username, $password_hash, $full_name, $email, $role_id]);
    }

    /**
     * Update user
     */
    public function update($id, $data) {
        $allowed = ['full_name', 'email', 'status'];
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

        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        return $this->execute($sql, $types, $values);
    }

    /**
     * Delete user
     */
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->execute($sql, "i", [$id]);
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT id FROM users WHERE username = ?";
        $params = [$username];
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
     * Update password
     */
    public function updatePassword($id, $password) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        return $this->execute($sql, "si", [$password_hash, $id]);
    }

    /**
     * Get password hash by user ID
     */
    public function getPasswordHashById($id) {
        $result = $this->fetchOne("SELECT password_hash FROM users WHERE id = ?", "i", [$id]);
        return $result['password_hash'] ?? null;
    }

    /**
     * Get role ID by role name
     */
    public function getRoleIdByName($role_name) {
        $result = $this->fetchOne("SELECT id FROM roles WHERE role_name = ?", "s", [$role_name]);
        return $result['id'] ?? null;
    }
}
