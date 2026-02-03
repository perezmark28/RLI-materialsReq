<?php
/**
 * Base Model Class
 * Provides common database operations for all models
 */
namespace App\Core;

class Model {
    protected $conn;
    protected $table;

    public function __construct() {
        $database = \Config\Database::getInstance();
        $this->conn = $database->getConnection();
    }

    /**
     * Execute a prepared statement
     */
    protected function query($sql, $types = '', $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new \Exception("Prepare failed: " . $this->conn->error);
        }

        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new \Exception("Execute failed: " . $stmt->error);
        }

        return $stmt;
    }

    /**
     * Fetch a single row
     */
    protected function fetchOne($sql, $types = '', $params = []) {
        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    /**
     * Fetch all rows
     */
    protected function fetchAll($sql, $types = '', $params = []) {
        $stmt = $this->query($sql, $types, $params);
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    /**
     * Execute INSERT/UPDATE/DELETE
     */
    protected function execute($sql, $types = '', $params = []) {
        $stmt = $this->query($sql, $types, $params);
        $affected = $stmt->affected_rows;
        $insert_id = $stmt->insert_id;
        $stmt->close();
        return ['affected' => $affected, 'insert_id' => $insert_id];
    }

    /**
     * Get last insert ID
     */
    protected function lastInsertId() {
        return $this->conn->insert_id;
    }

    /**
     * Begin transaction
     */
    protected function beginTransaction() {
        $this->conn->begin_transaction();
    }

    /**
     * Commit transaction
     */
    protected function commit() {
        $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    protected function rollback() {
        $this->conn->rollback();
    }
}
