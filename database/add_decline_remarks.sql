-- Migration: Add decline_remarks column to material_requests
-- Run this once to enable admin/super_admin to add remarks when declining

ALTER TABLE material_requests 
ADD COLUMN decline_remarks TEXT NULL 
AFTER approved_at;
