-- Migration for existing installations: RBAC + request ownership/approval + suppliers
-- Run in phpMyAdmin SQL tab for database rli_systems

USE rli_systems;

-- 1) Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (role_name) VALUES
('viewer'),
('admin'),
('super_admin')
ON DUPLICATE KEY UPDATE role_name=VALUES(role_name);

-- 2) Users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    email VARCHAR(255),
    role_id INT NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Suppliers
CREATE TABLE IF NOT EXISTS suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(255) NOT NULL,
    contact_person VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Extend material_requests (run each statement; ignore "duplicate column" errors if already applied)
ALTER TABLE material_requests ADD COLUMN user_id INT NOT NULL AFTER id;
ALTER TABLE material_requests ADD INDEX idx_user_id (user_id);
ALTER TABLE material_requests ADD CONSTRAINT fk_mr_user FOREIGN KEY (user_id) REFERENCES users(id);

ALTER TABLE material_requests MODIFY COLUMN status ENUM('pending','approved','declined') DEFAULT 'pending';
ALTER TABLE material_requests ADD COLUMN approved_by INT NULL AFTER status;
ALTER TABLE material_requests ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by;
ALTER TABLE material_requests ADD CONSTRAINT fk_mr_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;

-- 5) Status history
CREATE TABLE IF NOT EXISTS request_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    status ENUM('pending','approved','declined') NOT NULL,
    changed_by INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    FOREIGN KEY (request_id) REFERENCES material_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_rsh_request_id (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

