-- Migration Script: Add offense_count column to users table
-- Run this if your database already exists without this column

ALTER TABLE users ADD COLUMN IF NOT EXISTS offense_count INT DEFAULT 0;

UPDATE users SET offense_count = 0 WHERE offense_count IS NULL;