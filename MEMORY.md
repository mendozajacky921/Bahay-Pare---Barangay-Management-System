# 🏛️ Barangay Management System — Living Memory
> This document is the single source of truth for all decisions, progress, findings, and context made throughout this project.
> **Always update this file at the end of every milestone or major decision.**
> Last Updated: Milestone 1 Complete + QA Review

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

## 👥 TEAM ROLES (AI-Simulated)

| Role | Responsibilities |
|---|---|
| Product Manager | Requirements, user stories, milestone planning |
| Business Analyst | Gap analysis, stakeholder mapping, discovery questions |
| UX/UI Designer | Layout decisions, Tailwind design, accessibility |
| Software Architect | Folder structure, API design, security layers |
| Database Engineer | Schema, RLS policies, indexes, migrations |
| Senior Backend Engineer | PHP controllers, services, routing, middleware |
| Senior Frontend Engineer | Views, layouts, partials, JS, CSS |
| QA Engineer | Code review, bug finding, improvement suggestions |
| DevOps Engineer | Deployment config, .htaccess, environment setup |

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
- US-001: View news and announcements
- US-002: View barangay projects
- US-003: View upcoming events
- US-004: View emergency hotlines
- US-005: Download public forms
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
- `barangay_settings` is a singleton (one row); enforced by convention (needs constraint — see QA)
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
│   ├── Middleware.php           # ← DOES NOT EXIST (Critical Bug #1)
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
│   ├── public/                  # HomeController, Announcement, Project, Event, Hotline, FormDownload
│   ├── auth/                    # LoginController, RegisterController, LogoutController
│   ├── resident/                # DashboardController, RequestController, ProfileController
│   └── staff/                   # Dashboard, Request, Resident, Announcement, Project, Event,
│                                #   Hotline, Form, Staff, Settings, Report, PaymentController
│
├── views/
│   ├── layouts/                 # public.php, auth.php, resident.php, staff.php
│   ├── partials/                # alerts.php, csrf-field.php
│   ├── public/                  # home.php + subdirs for announcements/projects/events/hotlines/forms
│   ├── auth/                    # login.php, register.php
│   ├── resident/                # dashboard.php, requests/
│   ├── staff/                   # dashboard.php + subdirs (stubs — filled per milestone)
│   ├── pdf/                     # Dompdf templates (clearance, residency, indigency, cedula, barangay-id)
│   └── errors/                  # 403.php, 404.php, 500.php (401, 405 missing — Critical Bug #6)
│
├── public/
│   └── assets/
│       ├── css/app.css          # Custom styles: form-input, badge-*, btn-*, table-auto, prose
│       └── js/app.js            # Global JS: auto-dismiss alerts, confirm dialogs, loading state
│
├── supabase/
│   └── migrations/
│       ├── 001_initial_schema.sql   # Full schema, RLS, indexes, triggers, seed
│       └── 002_storage_buckets.sql  # Storage buckets + policies
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
| File Uploads | MIME type validated from file content (not extension) via `finfo` |
| Session Security | ID regenerated every 5 minutes; staff timeout at 30min inactivity |
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
| **M1** | Foundation — project setup, DB, core, layouts | ✅ **COMPLETE** (pending QA fixes) | 84 files, full scaffold |
| **M2** | Public Portal — announcements, events, projects, hotlines, forms | ⏳ Pending | |
| **M3** | Resident Auth — register, login, email verify, consent flow | ⏳ Pending | |
| **M4** | Document Requests — all 5 types + status tracking | ⏳ Pending | |
| **M5** | Staff Dashboard — request management + PDF generation | ⏳ Pending | |
| **M6** | Resident Management — staff side | ⏳ Pending | |
| **M7** | Content Management — announcements, events, projects, hotlines, forms | ⏳ Pending | |
| **M8** | Payments — PayMongo online + walk-in recording | ⏳ Pending | |
| **M9** | Email Notifications — PHPMailer + SMTP integration | ⏳ Pending | |
| **M10** | Staff Management — Captain creates/manages staff | ⏳ Pending | |
| **M11** | Reports — request and resident summaries | ⏳ Pending | |
| **M12** | Bilingual Support — EN/FIL toggle across all pages | ⏳ Pending | |
| **M13** | Settings & Branding — barangay config, fees, privacy policy | ⏳ Pending | |
| **M14** | QA & Hardening — full test pass, security audit, performance | ⏳ Pending | |

---

## 🔍 QA REVIEW — MILESTONE 1 FINDINGS

> QA performed after M1 completion. Fixes pending approval before implementation.

### ✅ Critical Issues (7) — Must Fix Before M2

| ID | Issue | File | Risk |
|---|---|---|---|
| CRITICAL-01 | `core/Middleware.php` required but doesn't exist | `index.php:34` | App crashes on every request |
| CRITICAL-02 | Dead `App\` namespace in `composer.json` maps to missing `src/` dir | `composer.json:15` | Autoload confusion |
| CRITICAL-03 | Open redirect via `HTTP_REFERER` in `Response` | `core/Response.php:17,51,57,64` | Phishing / user hijacking |
| CRITICAL-04 | Unsafe array key access in `AuthService::login()` | `services/AuthService.php:79–81` | Fatal error on bad Supabase response |
| CRITICAL-05 | `/set-locale` route missing — language toggle broken | `views/layouts/public.php:91` | 404 on every language toggle click |
| CRITICAL-06 | `Response::abort()` infinite recursion when error view missing | `core/Response.php:44` | Stack overflow on 401/405 |
| CRITICAL-07 | Broken array destructuring in `SupabaseService::count()` | `services/SupabaseService.php:241` | Silent wrong counts |

### ⚠️ High Issues (8) — Fix Before Feature Work

| ID | Issue | File |
|---|---|---|
| HIGH-01 | Literal `{barangayName}` placeholder in welcome email (not interpolated) | `services/EmailService.php:58` |
| HIGH-02 | Dead `$db` property in `AuthService` — wastes instantiation on every login | `services/AuthService.php:13,18` |
| HIGH-03 | `Controller::$request` declared but never assigned | `core/Controller.php:9` |
| HIGH-04 | Circular namespace stripping in `Router::callHandler()` | `core/Router.php:103–110` |
| HIGH-05 | `Session::destroy()` called without checking if session is active | `core/Session.php:82` |
| HIGH-06 | No `ob_end_clean()` before `Response::abort()` — garbled error pages | `core/Response.php:31` |
| HIGH-07 | File handle leak in `StorageService::upload()` — `fopen` not closed on exception | `services/StorageService.php:55` |
| HIGH-08 | `APP_URL` not in required env vars — silently empty, breaks all asset URLs | `config/app.php:7` |

### 💡 Medium Issues (10)

| ID | Issue |
|---|---|
| MEDIUM-01 | Tailwind config duplicated across all 4 layout files |
| MEDIUM-02 | Google Fonts loaded redundantly across layouts |
| MEDIUM-03 | `font-700` is not a valid Tailwind class (should be `font-bold`) |
| MEDIUM-04 | `START_TIME` constant defined but never used |
| MEDIUM-05 | `View::$layout` static property declared but never used |
| MEDIUM-06 | `AuthService::inviteStaff()` creates user with no password — unloggable |
| MEDIUM-07 | `strlen()` used on binary PDF data — should be `mb_strlen($pdf, '8bit')` |
| MEDIUM-08 | No `require-dev` / testing framework in `composer.json` |
| MEDIUM-09 | RLS `FOR ALL` and `FOR UPDATE` policies overlap on `profiles` table |
| MEDIUM-10 | `barangay_settings` has no singleton constraint — allows multiple rows |

### 🔵 Low Issues (9)

| ID | Issue |
|---|---|
| LOW-01 | `.htaccess` does not block `vendor/` directory |
| LOW-02 | `use PHPMailer\PHPMailer\SMTP` imported but never used |
| LOW-03 | No `README.md` exists |
| LOW-04 | `storage/logs/` and `storage/cache/` have no `.gitkeep` files |
| LOW-05 | `View::partial()` silently fails if partial file is missing |
| LOW-06 | No security headers (`X-Frame-Options`, `X-Content-Type-Options`, etc.) |
| LOW-07 | CSRF only guards POST (document assumption for future API work) |
| LOW-08 | `AuthService::register()` error key access inconsistent with Supabase format |
| LOW-09 | Error views `401.php` and `405.php` missing |

**QA Status: Awaiting approval to implement fixes.**

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
```

### Auth (guest only)
```
GET  /register                 → RegisterController@show
POST /register                 → RegisterController@store [csrf]
GET  /login                    → LoginController@show
POST /login                    → LoginController@store [csrf]
POST /logout                   → LogoutController@store [auth]
GET  /verify-email             → RegisterController@verifyEmail
POST /set-locale               → ⚠️ MISSING ROUTE (Critical Bug #5)
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

# Should be required (currently not — HIGH-08)
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
| Hosting? | Hostinger (PHP) + Vercel (not used) + Supabase |
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
  └── Milestone 1: Foundation ✅ Built | ⚠️ QA Issues Found — Awaiting Fix Approval
  └── Milestone 2+            ⏳ Pending
Phase 5: Quality Assurance    🔄 M1 Review Complete — 7 Critical, 8 High, 10 Medium, 9 Low
Phase 6: Documentation        ⏳ Pending
```

### Next Action
> **Awaiting approval to implement QA fixes for M1.**
> Once approved, fixes will be applied in priority order before M2 begins.

---

## 📚 KEY FILES QUICK REFERENCE

| What you need | Where to find it |
|---|---|
| All routes | `index.php` (lines 85–157) |
| Database schema | `supabase/migrations/001_initial_schema.sql` |
| Storage setup | `supabase/migrations/002_storage_buckets.sql` |
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
| Public layout | `views/layouts/public.php` |
| Staff layout | `views/layouts/staff.php` |
| Resident layout | `views/layouts/resident.php` |
| Custom CSS classes | `public/assets/css/app.css` |
| Global JS utilities | `public/assets/js/app.js` (`BMS.toast()`, etc.) |
