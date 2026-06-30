-- ============================================================
-- Migration: 004_form_download_counter_rpc.sql
-- Run AFTER 001_initial_schema.sql
--
-- M2-HIGH-02 fix: atomic download counter increment.
-- The previous approach read download_count then wrote +1, creating
-- a race condition under concurrent downloads. This function does
-- the increment in a single UPDATE statement inside the DB.
-- ============================================================

CREATE OR REPLACE FUNCTION increment_form_download_count(form_id UUID)
RETURNS void
LANGUAGE sql
SECURITY DEFINER
AS $$
  UPDATE public_forms
  SET    download_count = download_count + 1
  WHERE  id = form_id
    AND  is_active = TRUE;
$$;

-- Only the service role (called from PHP via service key) may execute this.
REVOKE EXECUTE ON FUNCTION increment_form_download_count(UUID) FROM PUBLIC;
REVOKE EXECUTE ON FUNCTION increment_form_download_count(UUID) FROM anon;
REVOKE EXECUTE ON FUNCTION increment_form_download_count(UUID) FROM authenticated;
GRANT  EXECUTE ON FUNCTION increment_form_download_count(UUID) TO service_role;
