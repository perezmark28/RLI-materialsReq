-- Migration: Add Supervisors Table and Update material_requests
-- Run this SQL file if you already have an existing database

USE rli_systems;

-- Create supervisors table if it doesn't exist
CREATE TABLE IF NOT EXISTS supervisors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    initials VARCHAR(10) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default supervisors (ignore duplicates)
INSERT INTO supervisors (initials, email) VALUES
('APL', 'apl.admin@lic.ph'),
('MTS', 'mts.admin@lic.ph'),
('PJJ', 'pjj.admin@lic.ph'),
('ALU', 'alu.admin@lic.ph')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- Add supervisor_id column to material_requests
-- Note: If column already exists, you'll get an error - that's okay, just continue
ALTER TABLE material_requests 
ADD COLUMN supervisor_id INT AFTER particulars;

-- Add foreign key constraint
-- Note: If constraint already exists, you'll get an error - that's okay
ALTER TABLE material_requests 
ADD CONSTRAINT fk_supervisor 
FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE SET NULL;

-- Add index
-- Note: If index already exists, you'll get an error - that's okay
CREATE INDEX idx_supervisor_id ON material_requests(supervisor_id);
