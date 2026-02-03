<?php
/**
 * Database Configuration and Connection Manager
 */
namespace Config;

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Database configuration
        $dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
        $dbUser = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
        $dbPass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '');
        
        // If Railway linked MySQL, it may expose MYSQL_* vars on this service
        if ($dbPass === '' && (getenv('MYSQL_ROOT_PASSWORD') !== false || isset($_ENV['MYSQL_ROOT_PASSWORD']))) {
            $dbPass = (string)(getenv('MYSQL_ROOT_PASSWORD') ?: ($_ENV['MYSQL_ROOT_PASSWORD'] ?? ''));
        }
        if ($dbHost === 'localhost' && (getenv('MYSQLHOST') !== false || isset($_ENV['MYSQLHOST']))) {
            $dbHost = (string)(getenv('MYSQLHOST') ?: ($_ENV['MYSQLHOST'] ?? $dbHost));
        }
        if ($dbUser === 'root' && (getenv('MYSQLUSER') !== false || isset($_ENV['MYSQLUSER']))) {
            $dbUser = (string)(getenv('MYSQLUSER') ?: ($_ENV['MYSQLUSER'] ?? $dbUser));
        }
        
        $dbName = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'rli_systems');
        $dbPort = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? null);
        
        if ($dbPort === null || $dbPort === '') {
            $dbPort = getenv('MYSQLPORT') ?: ($_ENV['MYSQLPORT'] ?? null);
        }
        if ($dbPort === null || $dbPort === '') {
            $dbPort = null;
        } else {
            $dbPort = (string)$dbPort;
        }

        try {
            if ($dbPort !== null && $dbPort !== '') {
                $this->conn = new \mysqli($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
            } else {
                $this->conn = new \mysqli($dbHost, $dbUser, $dbPass, $dbName);
            }

            if ($this->conn->connect_error) {
                throw new \Exception("Connection failed: " . $this->conn->connect_error);
            }

            $this->conn->set_charset("utf8mb4");
        } catch (\Exception $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    private function __clone() {}
    public function __wakeup() {}
}
