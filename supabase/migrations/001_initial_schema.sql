-- ============================================================
-- Migration: 001_initial_schema.sql
-- Barangay Management System — Complete Initial Schema
-- Run this in Supabase SQL Editor
-- ============================================================

-- ── ENUMS ────────────────────────────────────────────────────

CREATE TYPE user_role AS ENUM ('captain', 'secretary', 'clerk', 'resident');

CREATE TYPE request_type AS ENUM (
  'barangay_clearance',
  'certificate_of_residency',
  'certificate_of_indigency',
  'cedula',
  'barangay_id'
);

CREATE TYPE request_status AS ENUM (
  'pending',
  'under_review',
  'approved',
  'rejected',
  'released'
);

CREATE TYPE payment_method AS ENUM ('online', 'walk_in');
CREATE TYPE payment_status AS ENUM ('unpaid', 'paid', 'waived');
CREATE TYPE project_status AS ENUM ('planned', 'ongoing', 'completed');

CREATE TYPE id_type AS ENUM (
  'passport', 'drivers_license', 'sss', 'philhealth', 'pagibig',
  'postal_id', 'voters_id', 'national_id', 'senior_citizen_id',
  'pwd_id', 'other'
);

-- ── PROFILES ─────────────────────────────────────────────────

CREATE TABLE profiles (
  id                    UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  role                  user_role NOT NULL DEFAULT 'resident',
  first_name            TEXT NOT NULL,
  middle_name           TEXT,
  last_name             TEXT NOT NULL,
  suffix                TEXT,
  date_of_birth         DATE NOT NULL,
  place_of_birth        TEXT NOT NULL,
  sex                   TEXT NOT NULL CHECK (sex IN ('male', 'female')),
  civil_status          TEXT NOT NULL CHECK (civil_status IN ('single','married','widowed','separated')),
  citizenship           TEXT NOT NULL DEFAULT 'Filipino',
  address_house_no      TEXT,
  address_street        TEXT NOT NULL,
  address_purok         TEXT,
  address_barangay      TEXT NOT NULL,
  address_city          TEXT NOT NULL,
  address_province      TEXT NOT NULL,
  address_zip           TEXT,
  email                 TEXT UNIQUE NOT NULL,
  phone_number          TEXT,
  profile_photo_url     TEXT,
  -- Identity verification
  id_type               id_type,
  id_number             TEXT,
  id_photo_url          TEXT,
  is_verified           BOOLEAN NOT NULL DEFAULT FALSE,
  verified_by           UUID REFERENCES profiles(id),
  verified_at           TIMESTAMPTZ,
  -- Guardian fallback (for residents without own valid ID)
  guardian_name         TEXT,
  guardian_id_type      id_type,
  guardian_id_number    TEXT,
  guardian_id_photo_url TEXT,
  -- NPC Compliance
  consent_given         BOOLEAN NOT NULL DEFAULT FALSE,
  consent_given_at      TIMESTAMPTZ,
  privacy_policy_version TEXT NOT NULL DEFAULT '1.0',
  -- Status
  is_active             BOOLEAN NOT NULL DEFAULT TRUE,
  created_at            TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at            TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── REQUESTS ─────────────────────────────────────────────────

CREATE TABLE requests (
  id                          UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  resident_id                 UUID NOT NULL REFERENCES profiles(id),
  request_type                request_type NOT NULL,
  status                      request_status NOT NULL DEFAULT 'pending',
  purpose                     TEXT NOT NULL,
  -- Cedula-specific (nullable for all other types)
  cedula_gross_income         NUMERIC(12,2),
  cedula_real_property_value  NUMERIC(12,2),
  -- Processing
  assigned_to                 UUID REFERENCES profiles(id),
  reviewed_by                 UUID REFERENCES profiles(id),
  rejection_reason            TEXT,
  notes                       TEXT,
  -- Payment
  payment_status              payment_status NOT NULL DEFAULT 'unpaid',
  payment_method              payment_method,
  payment_amount              NUMERIC(10,2),
  payment_reference           TEXT,
  payment_recorded_at         TIMESTAMPTZ,
  payment_recorded_by         UUID REFERENCES profiles(id),
  -- Generated document
  document_url                TEXT,
  document_generated_at       TIMESTAMPTZ,
  document_generated_by       UUID REFERENCES profiles(id),
  -- Timestamps
  submitted_at                TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  reviewed_at                 TIMESTAMPTZ,
  released_at                 TIMESTAMPTZ,
  created_at                  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at                  TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── REQUEST STATUS HISTORY ───────────────────────────────────

CREATE TABLE request_status_history (
  id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  request_id    UUID NOT NULL REFERENCES requests(id) ON DELETE CASCADE,
  changed_by    UUID NOT NULL REFERENCES profiles(id),
  old_status    request_status,
  new_status    request_status NOT NULL,
  note          TEXT,
  changed_at    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── ANNOUNCEMENTS ─────────────────────────────────────────────

CREATE TABLE announcements (
  id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title         TEXT NOT NULL,
  title_fil     TEXT,
  content       TEXT NOT NULL,
  content_fil   TEXT,
  image_url     TEXT,
  is_published  BOOLEAN NOT NULL DEFAULT FALSE,
  published_at  TIMESTAMPTZ,
  created_by    UUID NOT NULL REFERENCES profiles(id),
  updated_by    UUID REFERENCES profiles(id),
  created_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── PROJECTS ─────────────────────────────────────────────────

CREATE TABLE projects (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title           TEXT NOT NULL,
  title_fil       TEXT,
  description     TEXT NOT NULL,
  description_fil TEXT,
  status          project_status NOT NULL DEFAULT 'planned',
  budget          NUMERIC(15,2),
  start_date      DATE,
  end_date        DATE,
  image_url       TEXT,
  is_published    BOOLEAN NOT NULL DEFAULT FALSE,
  created_by      UUID NOT NULL REFERENCES profiles(id),
  updated_by      UUID REFERENCES profiles(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── EVENTS ───────────────────────────────────────────────────

CREATE TABLE events (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title           TEXT NOT NULL,
  title_fil       TEXT,
  description     TEXT NOT NULL,
  description_fil TEXT,
  location        TEXT NOT NULL,
  event_date      TIMESTAMPTZ NOT NULL,
  end_date        TIMESTAMPTZ,
  image_url       TEXT,
  is_published    BOOLEAN NOT NULL DEFAULT FALSE,
  created_by      UUID NOT NULL REFERENCES profiles(id),
  updated_by      UUID REFERENCES profiles(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── HOTLINES ─────────────────────────────────────────────────

CREATE TABLE hotlines (
  id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name          TEXT NOT NULL,
  name_fil      TEXT,
  category      TEXT NOT NULL,
  phone_number  TEXT NOT NULL,
  alt_number    TEXT,
  is_active     BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order    INTEGER NOT NULL DEFAULT 0,
  created_by    UUID NOT NULL REFERENCES profiles(id),
  updated_by    UUID REFERENCES profiles(id),
  created_at    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at    TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── PUBLIC FORMS ─────────────────────────────────────────────

CREATE TABLE public_forms (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  title           TEXT NOT NULL,
  title_fil       TEXT,
  description     TEXT,
  file_url        TEXT NOT NULL,
  file_size       INTEGER,
  download_count  INTEGER NOT NULL DEFAULT 0,
  is_active       BOOLEAN NOT NULL DEFAULT TRUE,
  sort_order      INTEGER NOT NULL DEFAULT 0,
  created_by      UUID NOT NULL REFERENCES profiles(id),
  updated_by      UUID REFERENCES profiles(id),
  created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── BARANGAY SETTINGS ────────────────────────────────────────

CREATE TABLE barangay_settings (
  id                    UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  barangay_name         TEXT NOT NULL,
  municipality          TEXT NOT NULL,
  province              TEXT NOT NULL,
  region                TEXT NOT NULL DEFAULT 'Region III',
  captain_name          TEXT NOT NULL,
  contact_email         TEXT,
  contact_phone         TEXT,
  logo_url              TEXT,
  address               TEXT,
  -- Document fees (PHP)
  fee_clearance         NUMERIC(8,2) NOT NULL DEFAULT 50.00,
  fee_residency         NUMERIC(8,2) NOT NULL DEFAULT 50.00,
  fee_indigency         NUMERIC(8,2) NOT NULL DEFAULT 0.00,
  fee_cedula_base       NUMERIC(8,2) NOT NULL DEFAULT 5.00,
  fee_barangay_id       NUMERIC(8,2) NOT NULL DEFAULT 100.00,
  -- Privacy
  privacy_policy_text   TEXT,
  privacy_policy_version TEXT NOT NULL DEFAULT '1.0',
  updated_by            UUID REFERENCES profiles(id),
  updated_at            TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── NOTIFICATION LOGS ────────────────────────────────────────

CREATE TABLE notification_logs (
  id            UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  recipient_id  UUID NOT NULL REFERENCES profiles(id),
  email         TEXT NOT NULL,
  subject       TEXT NOT NULL,
  template_key  TEXT NOT NULL,
  status        TEXT NOT NULL DEFAULT 'sent',
  error_message TEXT,
  sent_at       TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- ── INDEXES ──────────────────────────────────────────────────

CREATE INDEX idx_profiles_role        ON profiles(role);
CREATE INDEX idx_profiles_email       ON profiles(email);
CREATE INDEX idx_profiles_is_verified ON profiles(is_verified);
CREATE INDEX idx_profiles_is_active   ON profiles(is_active);

CREATE INDEX idx_requests_resident_id  ON requests(resident_id);
CREATE INDEX idx_requests_status       ON requests(status);
CREATE INDEX idx_requests_type         ON requests(request_type);
CREATE INDEX idx_requests_submitted_at ON requests(submitted_at DESC);
CREATE INDEX idx_requests_assigned_to  ON requests(assigned_to);

CREATE INDEX idx_req_history_request_id ON request_status_history(request_id);
CREATE INDEX idx_req_history_changed_at ON request_status_history(changed_at DESC);

CREATE INDEX idx_announcements_published ON announcements(is_published, published_at DESC);
CREATE INDEX idx_events_date             ON events(event_date);
CREATE INDEX idx_projects_status         ON projects(status);
CREATE INDEX idx_hotlines_active_order   ON hotlines(is_active, sort_order);
CREATE INDEX idx_forms_active_order      ON public_forms(is_active, sort_order);

-- ── UPDATED_AT TRIGGER ───────────────────────────────────────

CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_profiles_updated_at
  BEFORE UPDATE ON profiles FOR EACH ROW EXECUTE FUNCTION update_updated_at();
CREATE TRIGGER trg_requests_updated_at
  BEFORE UPDATE ON requests FOR EACH ROW EXECUTE FUNCTION update_updated_at();
CREATE TRIGGER trg_announcements_updated_at
  BEFORE UPDATE ON announcements FOR EACH ROW EXECUTE FUNCTION update_updated_at();
CREATE TRIGGER trg_projects_updated_at
  BEFORE UPDATE ON projects FOR EACH ROW EXECUTE FUNCTION update_updated_at();
CREATE TRIGGER trg_events_updated_at
  BEFORE UPDATE ON events FOR EACH ROW EXECUTE FUNCTION update_updated_at();
CREATE TRIGGER trg_hotlines_updated_at
  BEFORE UPDATE ON hotlines FOR EACH ROW EXECUTE FUNCTION update_updated_at();
CREATE TRIGGER trg_public_forms_updated_at
  BEFORE UPDATE ON public_forms FOR EACH ROW EXECUTE FUNCTION update_updated_at();

-- ── ROW LEVEL SECURITY ───────────────────────────────────────

ALTER TABLE profiles            ENABLE ROW LEVEL SECURITY;
ALTER TABLE requests            ENABLE ROW LEVEL SECURITY;
ALTER TABLE request_status_history ENABLE ROW LEVEL SECURITY;
ALTER TABLE announcements       ENABLE ROW LEVEL SECURITY;
ALTER TABLE projects            ENABLE ROW LEVEL SECURITY;
ALTER TABLE events              ENABLE ROW LEVEL SECURITY;
ALTER TABLE hotlines            ENABLE ROW LEVEL SECURITY;
ALTER TABLE public_forms        ENABLE ROW LEVEL SECURITY;
ALTER TABLE notification_logs   ENABLE ROW LEVEL SECURITY;
ALTER TABLE barangay_settings   ENABLE ROW LEVEL SECURITY;

-- Helper: get current user role
CREATE OR REPLACE FUNCTION get_user_role()
RETURNS user_role AS $$
  SELECT role FROM profiles WHERE id = auth.uid();
$$ LANGUAGE sql SECURITY DEFINER STABLE;

-- PROFILES
CREATE POLICY "profiles_select_own_or_staff" ON profiles FOR SELECT
  USING (id = auth.uid() OR get_user_role() IN ('captain','secretary','clerk'));
CREATE POLICY "profiles_insert_own" ON profiles FOR INSERT
  WITH CHECK (id = auth.uid());
CREATE POLICY "profiles_update_own" ON profiles FOR UPDATE
  USING (id = auth.uid()) WITH CHECK (id = auth.uid());
CREATE POLICY "staff_manage_profiles" ON profiles FOR ALL
  USING (get_user_role() IN ('captain','secretary','clerk'));

-- REQUESTS
CREATE POLICY "requests_select" ON requests FOR SELECT
  USING (resident_id = auth.uid() OR get_user_role() IN ('captain','secretary','clerk'));
CREATE POLICY "requests_insert_resident" ON requests FOR INSERT
  WITH CHECK (resident_id = auth.uid());
CREATE POLICY "staff_manage_requests" ON requests FOR ALL
  USING (get_user_role() IN ('captain','secretary','clerk'));

-- REQUEST STATUS HISTORY
CREATE POLICY "history_select" ON request_status_history FOR SELECT
  USING (
    EXISTS (SELECT 1 FROM requests r WHERE r.id = request_id AND r.resident_id = auth.uid())
    OR get_user_role() IN ('captain','secretary','clerk')
  );
CREATE POLICY "staff_insert_history" ON request_status_history FOR INSERT
  WITH CHECK (get_user_role() IN ('captain','secretary','clerk'));

-- PUBLIC CONTENT (anyone can read published)
CREATE POLICY "announcements_public_read" ON announcements FOR SELECT
  USING (is_published = TRUE OR get_user_role() IN ('captain','secretary','clerk'));
CREATE POLICY "announcements_staff_write" ON announcements FOR ALL
  USING (get_user_role() IN ('captain','secretary'));

CREATE POLICY "projects_public_read" ON projects FOR SELECT
  USING (is_published = TRUE OR get_user_role() IN ('captain','secretary','clerk'));
CREATE POLICY "projects_staff_write" ON projects FOR ALL
  USING (get_user_role() IN ('captain','secretary'));

CREATE POLICY "events_public_read" ON events FOR SELECT
  USING (is_published = TRUE OR get_user_role() IN ('captain','secretary','clerk'));
CREATE POLICY "events_staff_write" ON events FOR ALL
  USING (get_user_role() IN ('captain','secretary'));

CREATE POLICY "hotlines_public_read" ON hotlines FOR SELECT
  USING (is_active = TRUE OR get_user_role() IN ('captain','secretary','clerk'));
CREATE POLICY "hotlines_staff_write" ON hotlines FOR ALL
  USING (get_user_role() IN ('captain','secretary'));

CREATE POLICY "forms_public_read" ON public_forms FOR SELECT
  USING (is_active = TRUE OR get_user_role() IN ('captain','secretary','clerk'));
CREATE POLICY "forms_staff_write" ON public_forms FOR ALL
  USING (get_user_role() IN ('captain','secretary'));

-- SETTINGS (public read, captain write)
CREATE POLICY "settings_public_read"  ON barangay_settings FOR SELECT USING (TRUE);
CREATE POLICY "settings_captain_write" ON barangay_settings FOR ALL
  USING (get_user_role() = 'captain');

-- NOTIFICATION LOGS (staff only)
CREATE POLICY "notif_logs_staff" ON notification_logs FOR ALL
  USING (get_user_role() IN ('captain','secretary','clerk'));

-- ── SEED: Default Barangay Settings ──────────────────────────

INSERT INTO barangay_settings (
  barangay_name, municipality, province, region,
  captain_name, contact_email, contact_phone,
  privacy_policy_text, privacy_policy_version
) VALUES (
  'Barangay Sample',
  'Municipality Name',
  'Province Name',
  'Region III',
  'Hon. Juan dela Cruz',
  'barangay@example.com',
  '(045) 123-4567',
  'This barangay management system collects personal data to process your document requests in accordance with Republic Act No. 10173 (Data Privacy Act of 2012). Your information will not be shared with third parties without your consent.',
  '1.0'
);
