-- ============================================================
-- Migration: 003_barangay_settings_singleton.sql
-- Run this in Supabase SQL Editor AFTER 001_initial_schema.sql
--
-- MEDIUM-10 fix: barangay_settings was a "singleton by convention" only —
-- nothing in the schema actually prevented a second row from being
-- inserted. A unique index on a constant expression guarantees at most
-- one row can ever exist in the table.
-- ============================================================

CREATE UNIQUE INDEX IF NOT EXISTS idx_barangay_settings_singleton
  ON barangay_settings ((true));
