-- Migration Script: Add offense_count column to reviews table
-- Run this script if you have an existing database without the offense_count column

-- Add offense_count column with default value 0
ALTER TABLE reviews ADD COLUMN offense_count INT DEFAULT 0;

-- Set all existing reviews to have offense_count = 0 (if not already set by default)
UPDATE reviews SET offense_count = 0 WHERE offense_count IS NULL;
