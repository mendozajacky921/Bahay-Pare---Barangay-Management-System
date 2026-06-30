# 🏛️ Barangay Management System — Living Memory
> This document is the single source of truth for all decisions, progress, findings, and context made throughout this project.
> **Always update this file at the end of every milestone or major decision. - ask and wait for my approval**

> Last Updated: Milestone 2 — Public Portal Complete + QA Pass 2 Applied

---

## 📌 PROJECT IDENTITY

| Field | Value |
|---|---|
| **Project Name** | Barangay Management System |
| **Type** | Single-barangay civic web application |
| **Country / Context** | Philippines — governed by RA 10173 (Data Privacy Act) |
| **Scale** | ~1,000–1,500 residents |
| **Deployment Target** | Hostinger (PHP shared/VPS hosting) |
| **Version Control** | GitHub |

---

## 🏢 SOFTWARE COMPANY MODE

Claude operates as a **complete software company** building a production-ready Barangay Management System. Every response draws on all roles simultaneously — the right hat is worn for the right task without being asked.

> **Mode:** Software Company
> **Client:** Project Owner
> **Mandate:** Production-ready system, not just working code

### 👥 Team Members & Responsibilities

| Role | Responsibilities |
|---|---|
| **Product Manager** | Requirements, user stories, milestone planning, scope decisions |
| **Business Analyst** | Gap analysis, stakeholder mapping, discovery questions, business rules |
| **UX/UI Designer** | Layout decisions, Tailwind design, accessibility, mobile responsiveness |
| **Software Architect** | Folder structure, API design, security layers, technical decisions |
| **Database Engineer** | Schema, RLS policies, indexes, migrations, query optimization |
| **Senior Backend Engineer** | PHP controllers, services, routing, middleware, business logic |
| **Senior Frontend Engineer** | Views, layouts, partials, JS, CSS, bilingual support |
| **QA Engineer** | Code review, bug finding, improvement suggestions, test planning |
| **DevOps Engineer** | Deployment config, .htaccess, environment setup, CI/CD |

### Operating Principles
- Every milestone output is reviewed by all roles before delivery
- Security, performance, and RA 10173 compliance are non-negotiable on every task
- No placeholder code in production paths — stubs are clearly marked and tracked
- MEMORY.md is updated only after explicit owner approval

---

## 🛠️ CONFIRMED TECH STACK

| Layer | Technology | Notes |
|---|---|---|
| **Language** | PHP 8.2+ | Server-rendered, no framework |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript | No React, no Vue, no Node.js |
| **UI Framework** | Tailwind CSS (CDN) | Custom config: primary blue `#1d4ed8` |
| **Database** | Supabase PostgreSQL | Via REST API (Guzzle HTTP) |
| **Authentication** | Supabase Auth | JWT stored in PHP session |
| **File Storage** | Supabase Storage | 4 buckets (see below) |
| **PDF Generation** | Dompdf `^2.0` | Server-side, HTML templates in `/views/pdf/` |
| **Email** | PHPMailer `^6.9` | Via SMTP (Resend recommended) |
| **Payments** | PayMongo | Online (GCash/card) + walk-in cash recording |
| **Dependency Mgmt** | Composer | PSR-4 autoloading |
| **Version Control** | GitHub | |
| **Deployment** | Hostinger shared/VPS | Apache + `.htaccess` mod_rewrite |

### ❌ Explicitly Excluded
- Next.js, React, Node.js — **never use unless explicitly requested**
- Laravel, Symfony, or any PHP framework
- SMS notifications (email only)

---

## 🏗️ ARCHITECTURE DECISIONS

### Routing
- **Single front controller**: `index.php` handles all requests via `.htaccess` rewrite
- **Custom Router** (`core/Router.php`): regex-based URL matching, supports `{param}` placeholders
- **Middleware chain**: `auth` → `role:x,y` → `csrf` — applied per route in `index.php`
- All routes are **explicitly declared** in `index.php` (no magic discovery)

### Authentication Flow
1. User submits login form → `LoginController::store()`
2. PHP calls Supabase Auth REST API (`/token?grant_type=password`)
3. On success: JWT access token + refresh token stored in `$_SESSION`
4. User profile fetched from `profiles` table and cached in `$_SESSION['user']`
5. `Auth::check()` reads from session on every request (no DB hit per request)
6. Staff sessions expire after **30 minutes of inactivity** (residents do not expire)

### Database Access Pattern
- All DB access goes through `SupabaseService` (never raw SQL from PHP)
- `SupabaseService` uses Guzzle to call Supabase PostgREST REST API
- Service role key used for admin operations; user token used for RLS-scoped operations
- Row Level Security (RLS) enforced at DB level as a **second security layer**

### View Rendering
- `View::render($view, $data, $layout)` captures view output via `ob_start()`, injects into layout
- Layout auto-resolved from view path prefix: `staff/` → staff layout, `resident/` → resident layout, `auth/` → auth layout, everything else → public layout
- `View::e()` must be used on **all user-supplied output** to prevent XSS
- Partials rendered via `View::partial()` (no layout, direct include)

### PDF Generation
- Dompdf renders HTML templates from `/views/pdf/`
- `PdfService::generate()` extracts data vars into template scope
- `PdfService::templateFor($type)` maps request type → template filename
- Generated PDFs uploaded to Supabase Storage `generated-documents` bucket (private)
- Signed URLs used for resident download access

### Email Notifications
- All emails go through `EmailService::send()` which uses PHPMailer
- Template method `EmailService::template()` produces consistent HTML wrapper
- Triggered on: registration, request submitted, status changed, account approved

### Public Content Conventions (established M2)
- All `show()` endpoints validate `{id}` as a UUID before hitting Supabase — prevents noisy 400 errors from PostgREST on malformed IDs; use the shared `isValidUuid()` private method pattern (already in all 4 public controllers)
- `public_forms.file_url` stores a **path-only value** within the `public-forms` bucket (e.g. `forms/my-form.pdf`), never a full URL. `StorageService::publicUrl()` builds the full URL at runtime. `FormDownloadController` has a fallback guard for legacy full URLs
- Download counters use an atomic DB-side RPC (`increment_form_download_count`) — never read-then-write from PHP
- Event timestamps are compared in UTC using `gmdate('Y-m-d\TH:i:s\Z')` — never `date('c')` which uses server timezone
- Content fields (`content`, `description`) are **plain text** — rendered with `nl2br(View::e())`. When M7 introduces a rich-text editor, these fields will switch to sanitised HTML. Do not output raw HTML from these fields until that change is made
- Pagination always: (1) get total count, (2) clamp `$page` to `$totalPages`, (3) derive `$offset` — in that order. Never derive offset before the fallback total is known

---

## 👤 USER ROLES & PERMISSIONS

| Role | Who | Key Permissions |
|---|---|---|
| `resident` | Registered, verified community member | Submit/track own requests, update own profile |
| `clerk` | Barangay staff (data entry) | View/process requests, manage residents, record walk-in payments |
| `secretary` | Barangay staff (approver) | All clerk permissions + approve/reject + manage content |
| `captain` | Barangay Captain / Super Admin | All secretary permissions + manage staff accounts + settings |

### Role Hierarchy
```
captain > secretary > clerk > resident
```
- Captain can do everything Secretary can do
- `Auth::isSecretary()` returns `true` for both `captain` AND `secretary`
- `Auth::isStaff()` returns `true` for `captain`, `secretary`, and `clerk`

---

## 📋 USER STORIES (All Roles)

### Public (Unauthenticated)
- US-001: View news and announcements ✅ M2
- US-002: View barangay projects ✅ M2
- US-003: View upcoming events ✅ M2
- US-004: View emergency hotlines ✅ M2
- US-005: Download public forms ✅ M2
- US-006: Register as a resident

### Resident
- US-007: Log in securely
- US-008: Request Barangay Clearance
- US-009: Request Certificate of Residency
- US-010: Request Certificate of Indigency
- US-011: Request Cedula (CTC)
- US-012: Apply for Barangay ID
- US-013: Track request status
- US-014: Receive email notifications on status changes
- US-015: View request history
- US-016: Update profile
- US-017: Pay for requests online

### Clerk
- US-018: View all incoming requests
- US-019: View resident profiles
- US-020: Update request to "Under Review"
- US-021: Record walk-in payments
- US-022: Generate PDF document for approved request
- US-023: Mark request as "Released"

### Secretary
- US-024: Approve or reject requests with reason
- US-025: Manage resident records
- US-026: Publish news and announcements
- US-027: Manage projects and events
- US-028: Manage emergency hotlines
- US-029: View request reports

### Captain
- US-030: Create and manage staff accounts
- US-031: Assign roles to staff
- US-032: View all system activity
- US-033: Configure barangay information and fees

---

## 🗄️ DATABASE SCHEMA SUMMARY

### Tables
| Table | Purpose |
|---|---|
| `profiles` | Extends `auth.users` — all user data for all roles |
| `requests` | All document requests with full lifecycle tracking |
| `request_status_history` | Immutable audit trail for every status change |
| `announcements` | News/announcements (bilingual: EN + FIL) |
| `projects` | Barangay projects with status tracking |
| `events` | Community events with dates and location |
| `hotlines` | Emergency contact numbers |
| `public_forms` | Downloadable PDF forms |
| `barangay_settings` | Singleton config table (fees, branding, privacy policy) |
| `notification_logs` | Log of every email sent |

### Enums
- `user_role`: `captain | secretary | clerk | resident`
- `request_type`: `barangay_clearance | certificate_of_residency | certificate_of_indigency | cedula | barangay_id`
- `request_status`: `pending | under_review | approved | rejected | released`
- `payment_method`: `online | walk_in`
- `payment_status`: `unpaid | paid | waived`
- `project_status`: `planned | ongoing | completed`
- `id_type`: 11 valid Philippine government ID types + `other`

### Request Status Flow
```
pending → under_review → approved → released
                      → rejected
```

### Storage Buckets
| Bucket | Access | Contents |
|---|---|---|
| `resident-ids` | Private (owner + staff) | Resident ID photos for verification |
| `generated-documents` | Private (owner + staff) | Generated PDF documents |
| `public-images` | Public | Images for announcements/events/projects |
| `public-forms` | Public | Downloadable public form PDFs |

### Key Design Decisions
- `profiles.id` = `auth.users.id` — no separate join needed for auth
- `profiles.email` duplicated from auth (for easier querying without auth join)
- Guardian ID fields on `profiles` — for residents without their own valid ID
- `barangay_settings` is a singleton (one row); enforced by unique index on `(true)` — migration `003`
- All content tables support bilingual fields (`title` + `title_fil`, `content` + `content_fil`)
- Fees stored in `barangay_settings` and configurable by Captain

### RLS Summary
- **Profiles**: residents read/update own; staff read/update all; captain manages roles
- **Requests**: residents CRUD own; staff CRUD all
- **Public content**: anyone reads published; secretary+ writes
- **Settings**: anyone reads; only captain writes
- **Notification logs**: staff only

---

## 📁 FOLDER STRUCTURE

```
barangay-ms/
├── .htaccess                    # URL rewriting, security blocking
├── .env                         # Secrets — never committed
├── .env.example                 # Template for env setup
├── .gitignore
├── composer.json                # PHP dependencies + PSR-4 autoload
├── index.php                    # Entry point: bootstrap + all routes
│
├── config/
│   ├── app.php                  # App name, env, URL, session, locale, limits
│   ├── supabase.php             # Supabase URLs and keys (from env)
│   ├── mail.php                 # SMTP configuration (from env)
│   └── payment.php              # PayMongo keys (from env)
│
├── core/
│   ├── Auth.php                 # Static auth state (session-backed)
│   ├── Controller.php           # Abstract base controller + validate()
│   ├── Middleware.php           # Interface — implemented by all middleware classes
│   ├── Request.php              # HTTP request wrapper
│   ├── Response.php             # Redirect, JSON, abort, flash helpers
│   ├── Router.php               # URL router with middleware support
│   ├── Session.php              # Session manager + flash + CSRF
│   └── View.php                 # Template renderer + escape helper
│
├── middleware/
│   ├── AuthMiddleware.php       # Requires authenticated session
│   ├── CsrfMiddleware.php       # Validates CSRF token on POST
│   ├── GuestMiddleware.php      # Redirects logged-in users away
│   └── RoleMiddleware.php       # Requires specific role(s)
│
├── services/
│   ├── AuthService.php          # Supabase Auth REST calls (login/register/invite)
│   ├── EmailService.php         # PHPMailer + email templates
│   ├── PdfService.php           # Dompdf wrapper + template resolver
│   ├── StorageService.php       # Supabase Storage upload/signed URL
│   └── SupabaseService.php      # Core PostgREST CRUD wrapper
│
├── controllers/
│   ├── public/                  # HomeController, AnnouncementController, ProjectController,
│   │                            #   EventController, HotlineController, FormDownloadController,
│   │                            #   LocaleController
│   ├── auth/                    # LoginController, RegisterController, LogoutController
│   ├── resident/                # DashboardController, RequestController, ProfileController
│   └── staff/                   # DashboardController, RequestController, ResidentController,
│                                #   AnnouncementController, ProjectController, EventController,
│                                #   HotlineController, FormController, StaffController,
│                                #   SettingsController, ReportController, PaymentController
│
├── views/
│   ├── layouts/                 # public.php, auth.php, resident.php, staff.php
│   ├── partials/                # alerts.php, csrf-field.php, pagination.php
│   ├── public/
│   │   ├── home.php             # Landing page with hero, services grid, announcements
│   │   ├── privacy-policy.php   # Privacy policy stub (M3)
│   │   ├── announcements/       # index.php, show.php ✅ M2
│   │   ├── projects/            # index.php, show.php ✅ M2
│   │   ├── events/              # index.php, show.php ✅ M2
│   │   ├── hotlines/            # index.php ✅ M2
│   │   └── forms/               # index.php ✅ M2
│   ├── auth/                    # login.php, register.php (stubs — M3)
│   ├── resident/                # dashboard.php, requests/ (stubs — M4)
│   ├── staff/                   # dashboard.php + subdirs (stubs — M5+)
│   ├── pdf/                     # Dompdf templates (stubs — M5)
│   └── errors/                  # 401.php, 403.php, 404.php, 405.php, 500.php
│
├── public/
│   └── assets/
│       ├── css/app.css          # Custom styles: form-input, badge-*, btn-*, table-auto, prose
│       └── js/app.js            # Global JS: auto-dismiss alerts, confirm dialogs, loading state
│
├── supabase/
│   └── migrations/
│       ├── 001_initial_schema.sql              # Full schema, RLS, indexes, triggers, seed
│       ├── 002_storage_buckets.sql             # Storage buckets + policies
│       ├── 003_barangay_settings_singleton.sql # Unique index enforcing singleton settings row
│       └── 004_form_download_counter_rpc.sql   # Atomic download counter RPC (M2)
│
└── storage/
    ├── logs/app.log             # PHP error log destination
    └── cache/                   # Reserved for future caching
```

---

## 🔐 SECURITY LAYERS

| Layer | Implementation |
|---|---|
| Authentication | Supabase JWT in `$_SESSION` (HTTPOnly, Secure, SameSite=Lax) |
| Authorization | Role middleware on every protected route |
| Database | RLS policies on all tables (second layer beyond PHP) |
| CSRF | Token per session, validated on every POST, rotated after use |
| XSS | `View::e()` (htmlspecialchars) must wrap all output |
| Input Validation | UUID format checked before all Supabase `show()` queries (`isValidUuid()`) |
| File Uploads | MIME type validated from file content (not extension) via `finfo` |
| Session Security | ID regenerated every 5 minutes; staff timeout at 30min inactivity |
| Open Redirect | `Response::safeRedirectTarget()` only trusts referer matching `APP_URL` host |
| Secrets | All keys in `.env`, never committed; loaded via phpdotenv |
| Sensitive Files | `.htaccess` blocks access to `/config`, `/views`, `/services`, etc. |

---

## 💰 BUSINESS RULES

### Document Fees (Defaults — configurable by Captain)
| Document | Fee |
|---|---|
| Barangay Clearance | ₱50.00 |
| Certificate of Residency | ₱50.00 |
| Certificate of Indigency | ₱0.00 (free) |
| Cedula (CTC) | Dynamic (computed from income/property) |
| Barangay ID | ₱100.00 |

### Payment Rules
- Online payment: PayMongo (GCash, Maya, credit/debit card)
- Walk-in payment: Recorded manually by clerk in system
- `payment_status`: `unpaid | paid | waived`
- `payment_method`: `online | walk_in`
- Cedula fee is computed locally — no BIR integration

### Registration Rules
- Self-registration with email verification
- Residents without own valid ID may use **guardian's ID**
- Staff accounts created **only** by Captain via admin interface
- Account must be **verified by staff** before resident can submit requests

### Notification Rules
- Email notifications only (no SMS)
- Triggered on: registration, request received, status change, account approval
- PHPMailer via SMTP (Resend recommended)

### Language
- Bilingual: English + Filipino (Tagalog)
- Toggle stored in `$_SESSION['locale']`
- Bilingual fields in DB: `title`/`title_fil`, `content`/`content_fil`
- Default locale: English
- **Note:** hotlines 911 reminder block is hardcoded EN/FIL — revisit in M12 Bilingual pass

---

## 📦 COMPOSER DEPENDENCIES

| Package | Version | Purpose |
|---|---|---|
| `vlucas/phpdotenv` | `^5.6` | Load `.env` variables |
| `guzzlehttp/guzzle` | `^7.8` | HTTP client for Supabase REST API |
| `dompdf/dompdf` | `^2.0` | PDF generation |
| `phpmailer/phpmailer` | `^6.9` | Email sending via SMTP |
| `ramsey/uuid` | `^4.7` | UUID generation |

---

## 🗺️ MILESTONE PLAN

| # | Milestone | Status | Notes |
|---|---|---|---|
| **M1** | Foundation — project setup, DB, core, layouts | ✅ **Complete + QA Patched** | 5 fixes applied; CRITICAL-01/02/04/07 verified already fixed in repo |
| **M2** | Public Portal — announcements, events, projects, hotlines, forms | ✅ **Complete + QA Patched** | 15 files; 2 QA passes; all 8 issues resolved; migration 004 added |
| **M3** | Resident Auth — register, login, email verify, consent flow | ⏳ Pending | |
| **M4** | Document Requests — all 5 types + status tracking | ⏳ Pending | |
| **M5** | Staff Dashboard — request management + PDF generation | ⏳ Pending | |
| **M6** | Resident Management — staff side | ⏳ Pending | |
| **M7** | Content Management — announcements, events, projects, hotlines, forms | ⏳ Pending | Content fields switch to sanitised HTML at this milestone |
| **M8** | Payments — PayMongo online + walk-in recording | ⏳ Pending | |
| **M9** | Email Notifications — PHPMailer + SMTP integration | ⏳ Pending | |
| **M10** | Staff Management — Captain creates/manages staff | ⏳ Pending | |
| **M11** | Reports — request and resident summaries | ⏳ Pending | |
| **M12** | Bilingual Support — EN/FIL toggle across all pages | ⏳ Pending | Fix hardcoded 911 reminder in hotlines view |
| **M13** | Settings & Branding — barangay config, fees, privacy policy | ⏳ Pending | |
| **M14** | QA & Hardening — full test pass, security audit, performance | ⏳ Pending | Distinguish network errors vs 404 in SupabaseService; review MEDIUM-09 RLS overlap |

---

## 📐 NAMING CONVENTIONS

| Artifact | Convention | Example |
|---|---|---|
| PHP Classes | PascalCase | `SupabaseService`, `HomeController` |
| PHP Methods | camelCase | `selectOne()`, `generateDocument()` |
| PHP Files | PascalCase matching class | `HomeController.php` |
| Database Tables | snake_case, plural | `request_status_history` |
| Database Columns | snake_case | `first_name`, `is_verified` |
| Database Enums | snake_case values | `under_review`, `barangay_clearance` |
| Routes | kebab-case, REST-style | `/staff/staff-accounts/{id}/deactivate` |
| View Files | kebab-case | `privacy-policy.php`, `barangay-id.php` |
| JS Variables | camelCase | `openSidebar()`, `BMS.toast()` |
| CSS Classes | Tailwind utilities + custom kebab | `btn-primary`, `badge-pending` |
| Constants | UPPER_SNAKE_CASE | `SUPABASE_URL`, `APP_SECRET` |

---

## 🌐 ROUTES REFERENCE

### Public (no auth)
```
GET  /                         → HomeController@index
GET  /announcements            → AnnouncementController@index
GET  /announcements/{id}       → AnnouncementController@show
GET  /projects                 → ProjectController@index
GET  /projects/{id}            → ProjectController@show
GET  /events                   → EventController@index
GET  /events/{id}              → EventController@show
GET  /hotlines                 → HotlineController@index
GET  /forms                    → FormDownloadController@index
GET  /forms/{id}/download      → FormDownloadController@download
GET  /privacy-policy           → (inline closure → public/privacy-policy view)
POST /set-locale               → LocaleController@store [csrf] ✅ Fixed (was CRITICAL-05)
```

### Auth (guest only)
```
GET  /register                 → RegisterController@show
POST /register                 → RegisterController@store [csrf]
GET  /login                    → LoginController@show
POST /login                    → LoginController@store [csrf]
POST /logout                   → LogoutController@store [auth]
GET  /verify-email             → RegisterController@verifyEmail
```

### Resident (auth + role:resident)
```
GET  /resident/dashboard
GET  /resident/requests
GET  /resident/requests/new
POST /resident/requests         [csrf]
GET  /resident/requests/{id}
GET  /resident/profile
POST /resident/profile          [csrf]
```

### Staff (auth + role:captain,secretary,clerk)
```
GET  /staff/dashboard
GET  /staff/requests
GET  /staff/requests/{id}
POST /staff/requests/{id}/status     [csrf]
POST /staff/requests/{id}/document   [csrf]
POST /staff/requests/{id}/payment    [csrf]
GET  /staff/residents
GET  /staff/residents/{id}
POST /staff/residents/{id}/verify    [csrf]
POST /staff/residents/{id}/update    [csrf]
GET  /staff/announcements
POST /staff/announcements            [csrf] [role:captain,secretary]
GET  /staff/announcements/{id}/edit
POST /staff/announcements/{id}/update [csrf]
POST /staff/announcements/{id}/delete [csrf]
... (same pattern for projects, events, hotlines, forms)
```

### Captain Only
```
GET  /staff/staff-accounts
POST /staff/staff-accounts           [csrf]
POST /staff/staff-accounts/{id}/update [csrf]
POST /staff/staff-accounts/{id}/deactivate [csrf]
GET  /staff/settings
POST /staff/settings                 [csrf]
GET  /staff/reports
```

---

## 🔑 ENVIRONMENT VARIABLES REFERENCE

```env
# Required (validated at boot)
APP_SECRET=                      # 64-char random string for app-level secrets
SUPABASE_URL=                    # https://xxx.supabase.co
SUPABASE_ANON_KEY=               # Public anon key
SUPABASE_SERVICE_ROLE_KEY=       # Service role key (server-side only, never exposed)

# Should be required (currently not — HIGH-08, carry to M14)
APP_URL=                         # https://yourdomain.com

# Optional with defaults
APP_NAME=Barangay Management System
APP_ENV=production               # production | development
SESSION_LIFETIME=1800            # 30 minutes (for staff)
SESSION_NAME=barangay_session
BARANGAY_NAME=Barangay
BARANGAY_MUNICIPALITY=
BARANGAY_PROVINCE=

# Mail (SMTP)
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=

# PayMongo
PAYMONGO_PUBLIC_KEY=
PAYMONGO_SECRET_KEY=
PAYMONGO_WEBHOOK_SECRET=
```

---

## 📝 DISCOVERY Q&A RECORD

| Question | Answer |
|---|---|
| Single or multi-barangay? | Single barangay |
| Resident count | ~1,000–1,500 |
| Existing system? | No — greenfield |
| How do residents register? | Self-registration; guardian ID fallback if no own ID |
| Authentication method | Email + password (Supabase Auth) |
| Who creates staff accounts? | Captain / Super Admin only |
| Staff roles | Captain, Secretary, Clerk |
| Processing time SLA? | Flexible — depends on staff workload |
| Digital signatures? | No — manual physical signing |
| Payments? | Online (PayMongo) + walk-in (cash, recorded manually) |
| Document format? | PDF only; no official template — we design our own |
| Cedula integration? | Local only — no BIR integration |
| Who manages announcements? | Secretary |
| Notifications? | Email only (no SMS) |
| Languages? | Filipino + English |
| NPC compliance? | Basic — privacy notice + consent tracking |
| Hosting? | Hostinger (PHP) + Supabase |
| Internet reliability? | Generally available; graceful offline handling desired |
| Resident devices? | Mobile + desktop (fully responsive) |
| Staff devices? | Desktop-first at barangay hall |

---

## 🚦 CURRENT STATUS

```
Phase 1: Product Planning     ✅ Complete
Phase 2: System Design        ✅ Complete
Phase 3: MVP Planning         ✅ Complete
Phase 4: Implementation       🔄 In Progress
  └── Milestone 1: Foundation ✅ Complete + QA Patched
  └── Milestone 2: Public Portal ✅ Complete + QA Patched
  └── Milestone 3+            ⏳ Pending
Phase 5: Quality Assurance    ✅ M1 pass complete | ✅ M2 pass complete (2 passes)
Phase 6: Documentation        ⏳ Pending
```

### Next Action
> **Begin Milestone 3 — Resident Auth:** registration form, login form, email verification flow, privacy consent, and the guardian ID fallback UI.

---

## ✅ M1 QA REVIEW — ALL RESOLVED

| ID | Issue | Fix Applied |
|---|---|---|
| CRITICAL-01 | `core/Middleware.php` missing | ✅ Exists as interface in repo |
| CRITICAL-02 | Dead `App\` namespace in composer.json | ✅ Already removed in repo |
| CRITICAL-03 | Open redirect via `HTTP_REFERER` | ✅ `safeRedirectTarget()` in `Response.php` |
| CRITICAL-04 | Unsafe array access in `AuthService::login()` | ✅ Null-checked in repo |
| CRITICAL-05 | `/set-locale` route missing | ✅ `LocaleController` + route added |
| CRITICAL-06 | `Response::abort()` infinite recursion / garbled pages | ✅ `ob_end_clean()` loop added |
| CRITICAL-07 | Broken array destructuring in `SupabaseService::count()` | ✅ Fixed in repo |
| HIGH-01 | `{barangayName}` literal in welcome email | ✅ Fixed — uses `BARANGAY_NAME` constant |
| HIGH-02 | Dead `$db` in `AuthService` | ✅ Removed |
| HIGH-03 | Dead `$request` property in `Controller` | ✅ Removed |
| HIGH-06 | No `ob_end_clean()` before error view | ✅ Fixed |
| HIGH-07 | File handle leak in `StorageService::upload()` | ✅ `finally` block closes handle |
| LOW-08 | Wrong error key in `AuthService::register()` | ✅ Fixed |
| LOW-09 | `401.php` and `405.php` missing | ✅ Both created |
| MEDIUM-10 | No singleton constraint on `barangay_settings` | ✅ Migration `003` added |

### Still open — carried to M14 backlog
- HIGH-08: `APP_URL` not validated at boot — silently empty
- MEDIUM-01/02: Tailwind config + Google Fonts duplicated across 4 layout files
- MEDIUM-06: `AuthService::inviteStaff()` creates passwordless account
- MEDIUM-07: `strlen()` on binary PDF data — should be `mb_strlen($pdf, '8bit')`
- MEDIUM-08: No `require-dev` / testing framework in `composer.json`
- MEDIUM-09: RLS `FOR ALL` / `FOR UPDATE` overlap on `profiles` table
- LOW-01 through LOW-07: minor .htaccess, import, header, CSRF scope issues

---

## ✅ M2 QA REVIEW — ALL RESOLVED (2 passes)

### Pass 1 findings → Pass 2 fixes applied

| ID | Issue | Fix Applied |
|---|---|---|
| M2-CRITICAL-01 | `file_url` full-URL double-build | ✅ `str_starts_with('http')` guard in `FormDownloadController` |
| M2-CRITICAL-02 | Pagination broken when falling back to past events | ✅ Fallback runs before offset/totalPages are derived; page clamped |
| M2-HIGH-01 | Dynamic `bg-<?= $var ?>` Tailwind class in events view | ✅ Replaced with static `if/else` blocks |
| M2-HIGH-02 | Race condition on download counter (read-then-write) | ✅ Atomic `increment_form_download_count()` RPC + migration `004` |
| M2-HIGH-03 | No UUID validation before Supabase `show()` queries | ✅ `isValidUuid()` in all 4 public controllers |
| M2-MEDIUM-01 | `date('c')` uses server timezone for event filter | ✅ Changed to `gmdate('Y-m-d\TH:i:s\Z')` |
| M2-MEDIUM-04 | `tel:` href dropped leading `0` on PH mobile numbers | ✅ Normalises `09xxxxxxxxx` → `+639xxxxxxxxx` |
| M2-LOW-02 | File size always shown in KB | ✅ Shows MB when ≥ 1 MB |

### Remaining low-priority — carried to M14 backlog
- M2-LOW-01: `SupabaseService::selectOne()` null return is ambiguous (network error vs not found) — both correctly 404 for now
- M2-MEDIUM-02: Content fields plain-text only — document as convention until M7 rich-text editor
- M2-MEDIUM-03: `pagination.php` `function_exists` guard + comment in place — no action needed
- M2-LOW-03: MEMORY.md updated (this update)
- M2-LOW-04: Hotlines 911 reminder hardcoded — flagged for M12 bilingual pass

---

## 📚 KEY FILES QUICK REFERENCE

| What you need | Where to find it |
|---|---|
| All routes | `index.php` |
| Database schema | `supabase/migrations/001_initial_schema.sql` |
| Storage setup | `supabase/migrations/002_storage_buckets.sql` |
| Settings singleton constraint | `supabase/migrations/003_barangay_settings_singleton.sql` |
| Atomic download counter RPC | `supabase/migrations/004_form_download_counter_rpc.sql` |
| App constants | `config/app.php` |
| Supabase constants | `config/supabase.php` |
| Base DB operations | `services/SupabaseService.php` |
| Auth operations | `services/AuthService.php` |
| Email templates | `services/EmailService.php` |
| PDF generation | `services/PdfService.php` |
| Session + CSRF | `core/Session.php` |
| Auth state | `core/Auth.php` |
| XSS escaping | `Core\View::e($value)` |
| Flash messages | `Core\Session::flash('success'/'error', $message)` |
| Pagination partial | `views/partials/pagination.php` |
| Public layout | `views/layouts/public.php` |
| Staff layout | `views/layouts/staff.php` |
| Resident layout | `views/layouts/resident.php` |
| Custom CSS classes | `public/assets/css/app.css` |
| Global JS utilities | `public/assets/js/app.js` (`BMS.toast()`, etc.) |
| UUID validation pattern | `isValidUuid()` — private static method in all public show() controllers |
| M2 deliverable | `m2_milestone2_patched.tar.gz` |