-- Migration Script: Drop offense_count from reviews table (if exists)
ALTER TABLE reviews DROP COLUMN IF EXISTS offense_count;