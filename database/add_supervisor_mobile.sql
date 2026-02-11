-- Migration: Add supervisor mobile numbers
-- Run in phpMyAdmin for database rli_systems

USE rli_systems;

-- Add mobile column (ignore duplicate column errors if already exists)
ALTER TABLE supervisors ADD COLUMN mobile VARCHAR(30) NULL AFTER email;

-- Default supervisor mobile numbers
UPDATE supervisors SET mobile = '09170000003' WHERE initials = 'APL';
UPDATE supervisors SET mobile = '09170000001' WHERE initials = 'MTS';
UPDATE supervisors SET mobile = '+639178187240' WHERE initials = 'PJJ';
UPDATE supervisors SET mobile = '+639178187240' WHERE initials = 'ALU';

