-- Migration: Add supervisor mobile numbers
-- Run in phpMyAdmin for database rli_systems

USE rli_systems;

-- Add mobile column (ignore duplicate column errors if already exists)
ALTER TABLE supervisors ADD COLUMN mobile VARCHAR(30) NULL AFTER email;

-- Temporary numbers (PJJ real for testing, others placeholders)
UPDATE supervisors SET mobile = '09170000003' WHERE initials = 'APL';
UPDATE supervisors SET mobile = '09170000001' WHERE initials = 'MTS';
UPDATE supervisors SET mobile = '09763717916' WHERE initials = 'PJJ';
UPDATE supervisors SET mobile = '09170000002' WHERE initials = 'ALU';

