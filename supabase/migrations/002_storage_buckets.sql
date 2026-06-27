-- ============================================================
-- Migration: 002_storage_buckets.sql
-- Run this in Supabase SQL Editor AFTER 001_initial_schema.sql
-- Creates all required storage buckets with correct policies
-- ============================================================

-- Private bucket: resident ID photos (staff + owner access only)
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
  'resident-ids',
  'resident-ids',
  FALSE,
  5242880, -- 5MB
  ARRAY['image/jpeg', 'image/png', 'image/webp']
) ON CONFLICT (id) DO NOTHING;

-- Private bucket: generated PDF documents
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
  'generated-documents',
  'generated-documents',
  FALSE,
  10485760, -- 10MB
  ARRAY['application/pdf']
) ON CONFLICT (id) DO NOTHING;

-- Public bucket: images for announcements, events, projects
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
  'public-images',
  'public-images',
  TRUE,
  5242880, -- 5MB
  ARRAY['image/jpeg', 'image/png', 'image/webp']
) ON CONFLICT (id) DO NOTHING;

-- Public bucket: downloadable public forms (PDFs)
INSERT INTO storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
VALUES (
  'public-forms',
  'public-forms',
  TRUE,
  10485760, -- 10MB
  ARRAY['application/pdf']
) ON CONFLICT (id) DO NOTHING;

-- ── Storage Policies ─────────────────────────────────────────

-- resident-ids: owner and staff can read; owner can upload their own
CREATE POLICY "resident_ids_upload_own" ON storage.objects FOR INSERT
  WITH CHECK (
    bucket_id = 'resident-ids'
    AND auth.uid()::text = (storage.foldername(name))[1]
  );

CREATE POLICY "resident_ids_read_own_or_staff" ON storage.objects FOR SELECT
  USING (
    bucket_id = 'resident-ids'
    AND (
      auth.uid()::text = (storage.foldername(name))[1]
      OR (SELECT role FROM public.profiles WHERE id = auth.uid()) IN ('captain','secretary','clerk')
    )
  );

-- generated-documents: only staff can upload; owner and staff can read
CREATE POLICY "documents_staff_upload" ON storage.objects FOR INSERT
  WITH CHECK (
    bucket_id = 'generated-documents'
    AND (SELECT role FROM public.profiles WHERE id = auth.uid()) IN ('captain','secretary','clerk')
  );

CREATE POLICY "documents_read_owner_or_staff" ON storage.objects FOR SELECT
  USING (
    bucket_id = 'generated-documents'
    AND (
      auth.uid()::text = (storage.foldername(name))[1]
      OR (SELECT role FROM public.profiles WHERE id = auth.uid()) IN ('captain','secretary','clerk')
    )
  );

-- public-images: staff can upload; everyone can read (public bucket)
CREATE POLICY "public_images_staff_upload" ON storage.objects FOR INSERT
  WITH CHECK (
    bucket_id = 'public-images'
    AND (SELECT role FROM public.profiles WHERE id = auth.uid()) IN ('captain','secretary')
  );

-- public-forms: staff can upload/delete; everyone can read (public bucket)
CREATE POLICY "public_forms_staff_upload" ON storage.objects FOR INSERT
  WITH CHECK (
    bucket_id = 'public-forms'
    AND (SELECT role FROM public.profiles WHERE id = auth.uid()) IN ('captain','secretary')
  );

CREATE POLICY "public_forms_staff_delete" ON storage.objects FOR DELETE
  USING (
    bucket_id = 'public-forms'
    AND (SELECT role FROM public.profiles WHERE id = auth.uid()) IN ('captain','secretary')
  );
