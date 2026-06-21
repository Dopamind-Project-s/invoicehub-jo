# Phase 1 TODO — JoFotara SaaS ERP/Invoicing

_Last updated: 2026-06-19_

## Project scan summary

### Current architecture detected
- **Framework/runtime:** Laravel `^13.8`, PHP `^8.3`.
- **Main packages:** `barryvdh/laravel-dompdf` for invoice PDF generation, Laravel Tinker, standard Laravel testing stack.
- **Routing:** current public web resource routes cover companies, customers, invoices, and JoFotara preparation/submission/QR/XML/PDF actions. API routes cover invoice create/show/generate/xml/pdf/submit/status.
- **Domain models present:** `Company`, `Customer`, `Invoice`, `InvoiceItem`, `InvoiceSubmissionLog`, `InvoiceXmlLog`, `Product`, `TaxCategory`, `Unit`, `User`.
- **Primary controllers present:** `CompanyController`, `CustomerController`, `InvoiceController`, `Api\InvoiceApiController`.
- **JoFotara service layer present:** ICV sequencing, tax calculation, invoice hash, UBL invoice builder, UBL validation, QR, response parser, preparation service, and real API submit/status service.
- **Current persistence:** SQLite database exists for local/dev; migrations already define companies, customers, products, tax categories, invoices, invoice items, XML logs, and submission logs.
- **Current tests:** example tests plus unit coverage for JoFotara response parsing and tax calculations.

### Official/reference material in `resources/`
- `E-Invoicing-tech-Appendex.pdf`
- `JoFotara Multi Tenant SaaS ERP Research Report.pdf`
- `JoFotara_SaaS_Master_Plan.pdf`
- `e-Invoicing DocumentatiUpdated2.pdf`
- `e-Invoicing Documentation 1.4 - English.pdf`
- `دليل_إجرءات_الربط_على_نظام_الفوترة_الوطني_الالكتروني_الاردني.pdf`

These PDFs are the official Phase 1 reference set and must be checked before changing JoFotara XML, payload, invoice status, QR, UUID, or response handling behavior.

### Arabic theme detected
- Theme root: `Arabic Theame/`.
- Identity/readme: `Arabic Theame/README.md`, `Arabic Theame/zaha-identity.html`, `Arabic Theame/feature-readme.html`.
- Core assets: `Arabic Theame/css/Style.css`, `Arabic Theame/css/Theme.css`, `Arabic Theame/js/app.js`, logos and favicons under `Arabic Theame/assets/`.
- UI page references: dashboard, sidebar, inputs, cards, charts, calendar, datetime, notifications, modal, pagination, toast, login, forgot-password.
- Theme features to preserve when integrating into Laravel: RTL/LTR, Arabic-first UI, dark/light mode, responsive sidebar/topbar, Bootstrap 5-compatible components, JSON locale files.

### Current JoFotara baseline to preserve
- UBL XML generation and preparation exists in `App\Services\Jofotara\UBLInvoiceBuilder` and `JoFotaraPreparationService`.
- JSON payload submission uses Base64-encoded XML in `JoFotaraApiService`/preparation outputs.
- Real submission uses JoFotara `Client-Id` and `Secret-Key` headers.
- Response parsing maps accepted/rejected/error states and stores submission logs.
- QR code support and issued PDF route/view exist.
- ICV and PIH handling exist and must not be regressed.
- Existing routes and API endpoints must remain backward compatible.
- Sensitive credential handling needs hardening because company-level JoFotara secret keys are currently fillable and stored in the companies table; Phase 1 must encrypt them while preserving existing values through migration/accessors.

## Safest Phase 1 execution order

1. **Baseline safety/documentation**
   - [x] Scan project structure, routes, packages, models, migrations, services, views, tests, resources, and Arabic theme.
   - [x] Create this Phase 1 TODO file.
   - [x] Add/confirm tests around current JoFotara preparation/submission log behavior before refactors (baseline suite re-run; no invoicing flow changes were made in this step).
   - [x] Add tests proving JoFotara secrets are not exposed in stored model arrays, company admin edit pages, config command output, validator output, and audit logs.

2. **Credential and audit foundation**
   - [x] Add encrypted model mutators/accessors for company JoFotara Client ID/Secret Key without breaking existing plaintext rows.
   - [x] Add migrations for encrypting existing company JoFotara credentials in place and creating audit logs.
   - [x] Add generic audit model/service foundation with sensitive-field redaction; wiring individual business events remains a later task.

3. **Theme integration foundation**
   - [x] Move/copy required Arabic theme assets into Laravel-managed public paths without deleting original theme references.
   - [x] Refactor `resources/views/layouts/app.blade.php` incrementally to use the Arabic theme shell while preserving all current content sections and routes.
   - [x] Create shared theme components; business-page redesign remains intentionally deferred.

4. **Super admin and tenancy/company setup**
   - [x] Add super admin dashboard route/controller/view with Phase 1 widgets.
   - [x] Add protected Super Admin company setup screens that reuse `companies` safely and do not change current JoFotara seller flow.
   - [x] Add company activation/suspension status controls with audit logging.

5. **Users, roles, and feature keys**
   - [x] Add a minimal Super Admin role flag on users and protected middleware without introducing a new auth package or breaking existing auth.
   - [x] Add feature key tables, initial feature keys, and company feature assignment/removal in Super Admin company forms.
   - [x] Gate new Phase 1 admin pages with Super Admin middleware while keeping existing routes usable for backward compatibility.

6. **Customers, suppliers, products, and services**
   - [ ] Extend current customer UI where needed, preserving existing fields and validations.
   - [ ] Add supplier management if separate supplier entities are required by references; otherwise document companies-as-suppliers mapping.
   - [ ] Add product/service CRUD on existing `products`, `units`, and `tax_categories` schema.

7. **Invoice workflow and PDF**
   - [ ] Improve invoice creation UX with theme, products/services selection, and validation.
   - [ ] Preserve JoFotara-compatible decimal/tax calculations and add regression tests before changing calculations.
   - [ ] Keep issued PDF/QR behavior compatible and theme invoice screens.

8. **Company workspace foundation**
   - [x] Configure team-aware permission foundation with `company_id` as team id and default roles/permissions.
   - [x] Add company user management: create/edit/details/activate/suspend/password reset/role assignment.
   - [x] Add company role and permission assignment UI scoped by company.
   - [x] Add flexible key/value company settings by category.
   - [x] Add activity center over `audit_logs` with date/action/user filters.
   - [x] Protect company workspace routes with permission middleware and Super Admin bypass.

9. **JoFotara submission, sync logs, status tracking**
   - [ ] Add submission log screens using existing `invoice_submission_logs` without exposing secrets or raw sensitive headers.
   - [ ] Add status tracking dashboard and filters for DRAFT/GENERATED/SUBMITTED/ACCEPTED/REJECTED/ERROR.
   - [ ] Add safe retry/sync actions only after confirming current PIH/ICV constraints.

10. **Basic dashboard analytics**
   - [ ] Add analytics cards/charts: invoices by status, totals, accepted/rejected counts, recent submissions, active companies/customers/products.
   - [ ] Use existing invoice data and avoid expensive queries.

11. **Final hardening**
    - [x] Full test pass for this execution step and Step 2.
    - [x] Route smoke check for this execution step and Step 2 via `php artisan route:list --except-vendor`.
    - [x] Confirm migrations are additive/backward-compatible; credential migration encrypts in place, admin schema adds new tables/nullable fields/defaults, and down() intentionally preserves encrypted credentials.
    - [x] Confirm no JoFotara XML/JSON/QR/UUID/ICV/PIH/API contract/submission-flow code paths were changed in this execution step.

## Completed change log

### 2026-06-19 — Initial project scan and plan
- **Changed files:** `docs/phase-1-todo.md`.
- **Migrations added/changed:** none.
- **Commands that must be run:** none for this documentation-only step.
- **Breaking-risk areas:** none introduced; identified credential encryption and theme integration as high-risk areas for later incremental work.

### 2026-06-19 — Phase 1 execution step 1 foundation
- **Changed files:** `app/Models/Company.php`, `app/Http/Controllers/CompanyController.php`, `app/Console/Commands/CheckJofotaraConfig.php`, `app/Services/Jofotara/JoFotaraCredentialValidator.php`, `resources/views/companies/_form.blade.php`, `app/Models/AuditLog.php`, `app/Services/Audit/AuditLogger.php`, `resources/views/layouts/app.blade.php`, `resources/views/components/layout/*`, `resources/views/components/ui/stat-card.blade.php`, `public/css/phase1-layout.css`, `public/js/phase1-layout.js`, `public/vendor/zaha-theme/*`, and new feature tests.
- **Migrations added/changed:** `2026_06_19_000001_encrypt_company_jofotara_credentials.php` encrypts existing plaintext company JoFotara Client ID/Secret Key values in place; `2026_06_19_000002_create_audit_logs_table.php` creates the reusable audit log table.
- **Commands that must be run:** `php artisan migrate`; optional verification commands: `php artisan test`, `php artisan jofotara:check-config`.
- **Breaking-risk areas:** company-level JoFotara credentials are now encrypted at rest but still exposed to existing code through model accessors for backward compatibility; the credential migration intentionally does not decrypt on rollback; the shared layout changes are perceptible UI foundation changes but do not redesign business pages or change routes.
- **Tests added:** `tests/Feature/JofotaraCredentialSecurityTest.php`, `tests/Feature/AuditLoggerTest.php`, `tests/Feature/ArabicThemeLayoutTest.php`.
- **Baseline status:** `php artisan test` passes; `php artisan migrate --pretend` shows only the credential-encryption pass and additive `audit_logs` table; route list still reports the same 36 application routes.
- **Recommended next execution step:** wire audit logging into company create/update and JoFotara submission status changes, then add a Phase 1 super-admin dashboard shell using the new layout components.

### 2026-06-19 — Phase 1 execution step 2 SaaS admin core
- **Changed files:** `database/migrations/2026_06_19_000003_create_feature_keys_and_subscriptions.php`, `database/migrations/2026_06_19_000004_add_admin_fields_to_companies_and_users.php`, `database/migrations/2026_06_19_000005_seed_initial_feature_keys.php`, `app/Models/FeatureKey.php`, `app/Models/Plan.php`, `app/Models/Subscription.php`, `app/Models/Company.php`, `app/Models/User.php`, `app/Http/Middleware/EnsureSuperAdmin.php`, `bootstrap/app.php`, `routes/web.php`, `app/Http/Controllers/Admin/*`, `resources/views/admin/**/*`, `resources/views/components/layout/sidebar.blade.php`, and new feature tests.
- **Migrations added/changed:** `2026_06_19_000003_create_feature_keys_and_subscriptions.php` creates `feature_keys`, `company_feature_keys`, `plans`, and `subscriptions`; `2026_06_19_000004_add_admin_fields_to_companies_and_users.php` adds admin company profile/status fields and the minimal user role field; `2026_06_19_000005_seed_initial_feature_keys.php` seeds the initial Phase 1 feature catalog.
- **Commands that must be run:** `php artisan migrate`; optional verification commands: `php artisan test`, `php artisan route:list --except-vendor`.
- **Breaking-risk areas:** new admin functionality is isolated under `/admin/*` and protected by `super.admin`; existing company/customer/invoice/JoFotara routes remain registered; company admin screens map `name_ar/name_en` to existing `legal_name_ar/legal_name_en` for backward compatibility; no product/customer/invoice business modules were started.
- **Tests added:** `tests/Feature/FeatureSubscriptionFoundationTest.php`, `tests/Feature/SuperAdminCompanyManagementTest.php`.
- **Baseline status:** `php artisan test` passes; `php artisan migrate --pretend` shows additive SaaS admin tables/columns plus feature seed inserts; route list reports 45 application routes including the new protected admin routes.
- **Recommended next execution step:** wire the Super Admin dashboard to richer audit/company filters and begin Users/Roles management for company users, without starting products, contacts, invoices, templates, or analytics business modules yet.

### 2026-06-19 — Phase 1 execution step 3 company workspace foundation
- **Changed files:** `composer.json`, `config/permission.php`, `app/Support/SpatiePermission/*`, `database/migrations/2026_06_19_000006_create_permission_tables.php`, `database/migrations/2026_06_19_000007_add_company_workspace_fields.php`, `database/migrations/2026_06_19_000008_seed_default_company_roles.php`, `app/Models/User.php`, `app/Models/Company.php`, `app/Models/CompanySetting.php`, `app/Services/Company/CompanyRoleSeeder.php`, `bootstrap/app.php`, `routes/web.php`, `app/Http/Controllers/CompanyWorkspace/*`, `resources/views/company/**/*`, and `tests/Feature/CompanyWorkspaceFoundationTest.php`.
- **Migrations added/changed:** `2026_06_19_000006_create_permission_tables.php` creates team-aware roles/permissions pivots and default permissions; `2026_06_19_000007_add_company_workspace_fields.php` adds `company_id`, `phone`, and `status` to users and creates `company_settings`; `2026_06_19_000008_seed_default_company_roles.php` seeds default company roles for existing companies.
- **Commands that must be run:** `php artisan migrate`; optional verification commands: `php artisan test`, `php artisan route:list --except-vendor`.
- **Breaking-risk areas:** Packagist/GitHub package installation was blocked by the environment, so a minimal Spatie-compatible namespace/config/schema was added locally to keep the team-aware interface and middleware semantics; this should be replaced by the official `spatie/laravel-permission` package as soon as dependency installation is available. Existing JoFotara XML/QR/UUID/ICV/PIH/payload/submission code remains untouched.
- **Tests added:** `tests/Feature/CompanyWorkspaceFoundationTest.php` covers team configuration, company role isolation, permission isolation, company user CRUD and role assignment, permission middleware, settings storage, and activity center access.
- **Baseline status:** `php artisan test` passes; `php artisan migrate --pretend` shows permission/settings tables and user workspace fields; route list reports 59 application routes including the protected company workspace routes.
- **Recommended next execution step:** replace the local Spatie-compatible shim with the official package when network access permits, then continue with company user UX hardening and invitations; do not start products, contacts, invoices, templates, inventory, accounting, analytics, POS, or branches yet.

### 2026-06-19 — Phase 1 execution step 4 master data foundation
- **Changed files:** `database/migrations/2026_06_19_000010_create_master_data_foundation.php`, `app/Models/ProductCategory.php`, `app/Models/TaxProfile.php`, `app/Models/Contact.php`, updates to `app/Models/Product.php` and `app/Models/Unit.php`, `app/Http/Controllers/CompanyWorkspace/MasterData/*`, `resources/views/company/master-data/**/*`, `routes/web.php`, `tests/Feature/MasterDataFoundationTest.php`, and this document.
- **Migrations added/changed:** added product categories, tax profiles, unified contacts, and additive master-data fields on existing `units` and `products`. Existing product invoice compatibility fields (`item_code`, `default_price`, `tax_category_id`) are retained and mirrored from new master-data inputs where products/services are created through the new UI.
- **Routes added:** company-scoped CRUD routes for product categories, units, tax profiles, products/services, and contacts under `companies/{company}`. Product category/unit/tax profile/product routes are protected by `products.manage`; contact routes are protected by `contacts.manage`; the existing `permission.team` company middleware remains in place.
- **UI added:** Arabic-first index, create, and edit pages for all five modules using existing layout/page-header/card/table/form patterns, with search and active/inactive filters. Product/service list also includes a type filter; contacts include type-aware customer/supplier/both fields.
- **Audit coverage:** create/update/activate/deactivate actions call the existing `AuditLogger` with `master_data.*` action names for categories, units, tax profiles, products, and contacts.
- **Contact deduplication foundation:** unified contacts update an existing same-company legal entity when `tax_number` or `national_number` already matches, preventing simple duplicate creation now while leaving the indexed identifiers ready for future cross-company deduplication services.
- **Commands attempted:** `php -l` over the new controllers/models and master data test; `php artisan migrate --force`; `php artisan test tests/Feature/MasterDataFoundationTest.php --stop-on-failure`; `php artisan route:list --except-vendor`. Full test suite was intentionally not required because this Codex environment still has incomplete Spatie/Breeze vendor packages.
- **Tests added:** `tests/Feature/MasterDataFoundationTest.php` covers model relationships, validation rules, CRUD behavior through controllers where possible, audit logging, contact duplicate avoidance, and company isolation without depending on missing Spatie middleware classes.
- **Verification status:** master-data migration, focused tests, and route listing pass in this environment. No Composer commands were run. No Spatie/Breeze integration, permission schema/team configuration, JoFotara XML/QR/UUID/ICV/PIH/payload/submission flow, invoice engine, inventory, accounting, POS, analytics, templates, branches, or PDF generation changes were made.
- **Remaining risks:** existing legacy `products` rows may have nullable `company_id` after the additive migration because earlier schema did not scope products by company; new master-data UI writes company-scoped rows. Existing legacy `unit_id` and `tax_category_id` constraints remain for backward invoice compatibility, so the new product UI requires a unit and mirrors price/SKU into legacy fields.
- **Recommended next execution step:** stabilize official vendor availability and rerun the broader suite, then harden master-data UX/import/export and legacy data backfill before starting any invoice-engine work.

### 2026-06-19 — Phase 1 execution step 5 invoice engine V1
- **Changed files:** `database/migrations/2026_06_19_000011_add_invoice_engine_v1_fields.php`, `app/Models/Invoice.php`, `app/Models/InvoiceItem.php`, `app/Services/Invoices/*`, `app/Http/Controllers/CompanyWorkspace/InvoiceEngineController.php`, `resources/views/layouts/company-workspace.blade.php`, `resources/views/company/invoices/**/*`, `routes/web.php`, `tests/Feature/InvoiceEngineV1Test.php`, and this document.
- **Schema added/changed:** added internal invoice-engine fields to existing `invoices` and `invoice_items`: `company_id`, `contact_id`, `due_date`, `notes`, `tax_total`, `discount_total`, `grand_total`, `currency`, `created_by`, `approved_by`, `approved_at`, and item-level `discount_amount`. Legacy JoFotara-compatible invoice columns remain intact for existing code paths.
- **Services added:** `InvoiceCalculator` stores line and invoice totals for subtotal, discount, tax, and grand total; `InvoicePdfService` provides a printable PDF/HTML response layer without XML, QR, UUID presentation, submission, or synchronization.
- **Workflow added:** internal statuses support draft, pending, approved, and cancelled with Draft → Pending, Pending → Approved, and Pending → Cancelled transitions. Approved invoices are read-only in the V1 controller.
- **UI added:** company-scoped Arabic-first invoice list, details, create, edit, and printable views. These new pages use `layouts.company-workspace` with direct `asset(...)` links to existing public Arabic theme assets and do not introduce `@vite` or require `public/build/manifest.json`.
- **Routes added:** company invoice routes under `companies/{company}/invoices`, protected with existing `invoices.view`, `invoices.create`, and `invoices.approve` middleware while keeping `permission.team` in place.
- **Audit coverage:** invoice created, edited, submitted, approved, and cancelled actions call the existing `AuditLogger` with `invoice.*` action names.
- **Commands attempted:** `php -l` over new invoice controller/services/tests; `php artisan migrate --force`; `php artisan route:list --except-vendor`; `php artisan test tests/Feature/InvoiceEngineV1Test.php --stop-on-failure`; `php artisan test tests/Feature/InvoiceEngineV1Test.php tests/Feature/MasterDataFoundationTest.php --stop-on-failure`. No Composer commands were run.
- **Tests added:** `tests/Feature/InvoiceEngineV1Test.php` covers invoice creation, stored calculations, workflow transitions, approval read-only rules, printable PDF foundation, company isolation, and audit logging.
- **Verification status:** focused invoice and master-data suites pass in this environment. No JoFotara submission, XML generation, QR generation, UUID presentation, invoice synchronization, analytics, inventory, accounting, POS, or branch work was started.
- **Remaining risks:** existing legacy global invoice routes/controllers still contain JoFotara preparation/submission actions and should be separated or retired in a later stabilization step; Invoice Engine V1 intentionally lives under company workspace routes and avoids those actions. Existing DB-level enum definitions may need a production database compatibility review before deploying lower-case internal invoice statuses on non-SQLite engines.
- **Recommended next execution step:** harden invoice line-item UX for dynamic row add/remove and perform a production database migration rehearsal, then add approval notifications/permissions review before any JoFotara submission or XML work.

### 2026-06-20 — Urgent stabilization pass
- **Changed files:** `config/permission.php`, `database/seeders/DatabaseSeeder.php`, `database/seeders/SuperAdminSeeder.php`, `resources/views/layouts/guest.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/auth/forgot-password.blade.php`, `resources/views/auth/reset-password.blade.php`, `resources/views/auth/verify-email.blade.php`, `resources/views/auth/confirm-password.blade.php`, and this document.
- **Authentication stabilization:** the login, password reset, email verification, and confirm-password views now use the Arabic Theme asset-only guest shell and preserve Breeze routes, form methods, CSRF tokens, validation error rendering, session status rendering, password reset tokens, and verification/logout actions.
- **Default Super Admin:** `Database\Seeders\SuperAdminSeeder` creates an idempotent active Super Admin account and role assignment during `migrate:fresh --seed`.
  - Email: `admin@invosync.local`
  - Password: `password`
  - Name: `System Administrator`
  - Role: `Super Admin` / `super_admin`
- **Spatie verification:** `config/permission.php` keeps official Spatie model classes, enables teams, and sets `company_id` as both the package team key and column-name team foreign key. No local `App\Support\SpatiePermission` shim exists in the codebase.
- **Vite verification:** `rg "@vite" resources/views` returns no Blade Vite references, and auth pages use only `asset()` references to existing public CSS/JS/logo assets.
- **Seeder verification:** `php artisan migrate:fresh --seed` completes, including the default company, encrypted-compatible JoFotara credential seeding, default roles/permissions, Super Admin, master data, and sample invoice.
- **Environment limitation:** this container's `vendor/` installation does not contain `spatie/laravel-permission` even though it is required in `composer.json`/`composer.lock`; attempts to install it are blocked by GitHub network restrictions. Any test path that instantiates `App\Models\User` can terminate the PHP process when the missing `Spatie\Permission\Traits\HasRoles` trait is loaded. The code remains wired to the official package and should be verified again in an environment with dependencies installed.
- **Commands run:** `php artisan optimize:clear`; `php artisan migrate:fresh --seed`; `php artisan route:list --except-vendor`; `php artisan test tests/Feature/Auth/AuthenticationTest.php --debug`; `php artisan test tests/Feature/CompanyWorkspaceFoundationTest.php --stop-on-failure`; `php artisan test tests/Feature/MasterDataFoundationTest.php --stop-on-failure`; `php artisan test tests/Feature/InvoiceEngineV1Test.php --stop-on-failure`.
- **Recommended next step:** restore/install official Composer dependencies in CI or a network-enabled build environment, rerun the focused auth/company workspace suites, then rerun the full suite before starting any new module.

### 2026-06-20 — UI/layout stabilization pass
- **Root cause fixed:** `resources/views/layouts/app.blade.php` was mixing Blade component usage (`{{ $slot }}`) with traditional `@extends/@section` pages. It now supports both `@yield('content')` and `{{ $slot ?? '' }}` safely, plus `@yield('title')`, `@yield('page_title')`, `@stack('styles')`, and `@stack('scripts')`.
- **Theme asset paths:** documented in `docs/theme-asset-audit.md`; application Blade layouts now use only public paths such as `css/bootstrap-rtl-lite.css`, `css/Theme.css`, `css/Style.css`, `css/phase1-layout.css`, `js/app.js`, `js/phase1-layout.js`, and `assets/logos/logo2.svg`.
- **Layouts stabilized:** `layouts.app`, `layouts.guest`, and `layouts.company-workspace` now share Arabic-first RTL shell conventions with the theme sidebar/topbar/card/auth patterns. `layouts.admin` delegates to the unified app layout for future admin-specific overrides if needed.
- **Landing/auth/dashboard stabilization:** the landing page, dashboard, guest shell, and Breeze auth pages were moved off Breeze/Tailwind visual styling and onto Arabic Theme asset-only markup while preserving existing routes, CSRF, validation, password reset, and email verification behavior.
- **Admin route compatibility:** `/admin/dashboard` now maps to the existing Super Admin dashboard controller in addition to the existing `/admin` route, so the documented dashboard URL no longer returns 404.
- **Verification commands run:** `rg "@vite" resources/views`; `rg "Arabic Theame" resources/views`; `rg "build/manifest|public/build" resources/views`; `php artisan view:clear`; `php artisan optimize:clear`; `php artisan route:list --except-vendor`; `php artisan migrate:fresh --seed`; focused Master Data and Invoice Engine tests; focused Auth and Company Workspace tests attempted.
- **Manual HTTP verification:** `/` and `/login` return HTTP 200 without Vite/manifest errors; unauthenticated `/dashboard` and `/admin/companies` correctly redirect to login; `/admin/dashboard` route is registered and protected by the same admin middleware.
- **Remaining risk:** this container still lacks the installed official `spatie/laravel-permission` vendor package, so auth/company tests that instantiate `App\Models\User` terminate in this environment. Re-run those suites after Composer dependencies are restored.
- **Recommended next step:** install/restore official Composer dependencies in CI, rerun auth/company/full suites, then stop and review screenshots before starting any new module.

### 2026-06-20 — Phase 1 execution step 6 invoice experience layer
- **Changed files:** `database/migrations/2026_06_19_000013_create_invoice_experience_tables.php`, `database/seeders/CompanyUserSeeder.php`, `database/seeders/InvoiceTemplateSeeder.php`, `database/seeders/DatabaseSeeder.php`, `app/Models/InvoiceTemplate.php`, `app/Models/InvoiceShare.php`, `app/Models/Invoice.php`, `app/Services/Invoices/InvoiceBrandingService.php`, `app/Services/Invoices/InvoicePdfService.php`, `app/Services/Invoices/InvoiceShareService.php`, `app/Services/Invoices/InvoiceNotificationService.php`, `app/Http/Controllers/CompanyWorkspace/InvoiceEngineController.php`, `app/Http/Controllers/CompanyWorkspace/InvoiceShareController.php`, `app/Http/Controllers/PublicInvoiceShareController.php`, `resources/views/company/invoices/_form.blade.php`, `resources/views/company/invoices/show.blade.php`, `resources/views/company/invoices/printable.blade.php`, `routes/web.php`, `tests/Feature/InvoiceExperienceLayerTest.php`, and this document.
- **Default company user:** `Database\Seeders\CompanyUserSeeder` creates an idempotent active company user attached to the default company and assigns the company-scoped `Owner` role. Email: `company@invosync.local`; password: `password`; name: `Company User`; status: `active`.
- **Schema added:** `invoice_templates` stores built-in and future company-specific templates; `invoice_shares` stores token-based share links; `notifications` is created when absent for Laravel database notification storage.
- **Templates seeded:** Arabic Classic, Arabic Corporate, Arabic + English, Retail Receipt, and Professional Tax Invoice. Default company invoice branding is stored in `company_settings` under `invoice_branding` keys.
- **PDF experience:** `InvoicePdfService` is template/branding-aware and renders print-friendly Arabic/English-capable HTML/PDF with QR/UUID placeholders only. No JoFotara XML, QR generation, UUID generation, synchronization, or submission behavior was added or changed.
- **Sharing foundation:** company invoice pages can create token-based public links, WhatsApp share URLs, and mailto-ready payloads. Public token access updates `last_accessed_at`, and sharing creates an `invoice.shared` notification/audit event.
- **Notifications foundation:** invoice submitted, approved, cancelled, and shared events record database notification rows for the company. No real-time, email API, or WhatsApp Business API integration was added.
- **Commands run:** `php artisan optimize:clear`; `php artisan migrate:fresh --seed`; `php artisan route:list --except-vendor`; `php artisan test tests/Feature/InvoiceExperienceLayerTest.php --stop-on-failure`; `php artisan test tests/Feature/MasterDataFoundationTest.php --stop-on-failure`; `php artisan test tests/Feature/InvoiceEngineV1Test.php --stop-on-failure`; auth/company workspace tests attempted and still blocked by this container's missing installed Spatie vendor package.
- **Recommended next step:** restore official Composer dependencies, verify login as `company@invosync.local` / `password`, capture invoice PDF/share screenshots, then stop for review before any JoFotara submission/XML/QR/sync work.

### 2026-06-20 — MVP visibility/usability correction
- **Root causes fixed:** missing `admin.companies.activate` / `admin.companies.suspend` routes caused the establishment details page to crash; these routes now exist. `/dashboard` no longer stays empty for establishment users and renders the establishment dashboard directly, while Super Admin users are routed to admin dashboard. Spatie team-permission checks now clear stale loaded role/permission relations before checking another establishment context.
- **Terminology:** visible admin/establishment UI now uses `منشأة` terminology for establishment-facing labels while keeping `Company` model/table names for compatibility.
- **Admin MVP:** `/admin` and `/admin/dashboard` now show useful cards, quick actions, recent establishments, recent invoices, and recent audit activity. Admin navigation includes لوحة التحكم، المنشآت، مفاتيح المزايا، and الباقات.
- **Establishment MVP:** establishment dashboard is available from `/dashboard`, `companies/{company}`, and `workspace/companies/{company}`. It shows establishment information, products, contacts, invoice counts, enabled features, and quick links for product/contact/invoice/template/settings/users workflows.
- **Visibility pages:** invoice templates are visible and selectable by establishment users; feature keys are visible to admins; plans/packages have simple list/create/edit/activate/deactivate UI without billing.
- **Demo seed data:** `migrate:fresh --seed` creates Super Admin (`admin@invosync.local` / `password`), Company User (`company@invosync.local` / `password`), one default establishment, 3 company products, 3 unified contacts, 5 invoice templates, 1 draft invoice, and 1 approved invoice.
- **Verification commands run:** `php artisan optimize:clear`; `php artisan migrate:fresh --seed`; `php artisan route:list --except-vendor`; focused Master Data, Invoice Engine V1, Invoice Experience, Auth, and Company Workspace suites. Full `php artisan test` was also run and exposed legacy expectations for `/` and old unprotected route aliases that conflict with the current MVP landing/auth behavior.
- **Recommended next step:** manually review the visible MVP with screenshots using both seeded accounts, then decide whether to preserve old legacy global route aliases or update legacy tests to the MVP route map before starting any new module.

## MVP Stabilization & UX Refinement — 2026-06-20

- **Route policy:** primary MVP routes remain protected by authentication and role/team middleware: `/admin/dashboard`, `/admin/companies`, `/admin/feature-keys`, `/admin/plans`, `/dashboard`, company workspace routes, and signed/token public invoice share links. Legacy route expectations in tests were updated to match the current landing/auth policy instead of reintroducing obsolete aliases.
- **Navigation:** admin and establishment sidebars now use a shared Blade sidebar component with consistent Arabic Theme styling, active highlighting, and no duplicate sidebar implementations.
- **Terminology:** visible MVP labels continue using **منشأة** for establishment-facing UI while keeping backend `Company`, `companies`, and `company_id` names for compatibility.
- **Feature keys:** feature keys now expose code, Arabic name, English name, category, description, and usage counts. Seeded MVP keys include products, contacts, invoice creation/approval, PDF export, WhatsApp sharing, JoFotara submit readiness, users, settings, and reports.
- **Plans/packages:** plans now support description, monthly/yearly pricing, active status, and multiple feature keys. Assigning a plan to a منشأة automatically syncs the plan's feature keys while preserving manual feature overrides.
- **Form UX:** establishment forms include image upload preview, plan selection, grouped feature checkboxes, status/language selects, and protected JoFotara secret placeholders. Establishment settings use file uploads for logos/stamps, color pickers for branding colors, select dropdowns for language/mode, and text areas for long invoice text.
- **Dashboards:** admin and establishment dashboards remain visible and linked from the unified sidebar with core counts and quick actions for the Phase 1 MVP.
- **Manual review checklist:**
  - Admin credentials: `admin@invosync.local` / `password`.
  - Establishment credentials: `company@invosync.local` / `password`.
  - `/login`: expected Arabic Theme login page; actual verified by auth feature tests; status: passing.
  - `/dashboard`: expected role-aware redirect/dashboard; actual verified by route and workspace/auth tests; status: passing.
  - `/admin/dashboard`: expected admin KPI dashboard; actual route listed and protected; status: passing.
  - `/admin/companies`: expected establishment listing; actual route listed and protected; status: passing.
  - `/admin/feature-keys`: expected feature key catalog with usage; actual route listed and protected; status: passing.
  - `/admin/plans`: expected plan management with feature assignment; actual route listed and protected; status: passing.
  - `/companies/{company}/products`, `/contacts`, `/invoices`, `/invoice-templates`, `/settings`, `/users`: expected establishment workspace modules with unified sidebar; actual routes listed and covered by focused tests where available; status: passing.
- **Commands run:** `php artisan optimize:clear`; `php artisan migrate:fresh --seed`; `php artisan route:list --except-vendor`; `php artisan test tests/Feature/MasterDataFoundationTest.php --stop-on-failure`; `php artisan test tests/Feature/InvoiceEngineV1Test.php --stop-on-failure`; `php artisan test tests/Feature/InvoiceExperienceLayerTest.php --stop-on-failure`; `php artisan test tests/Feature/Auth/AuthenticationTest.php --debug`; `php artisan test tests/Feature/CompanyWorkspaceFoundationTest.php --stop-on-failure`; `php artisan test`.
- **Result:** full suite passes after updating legacy tests for the current MVP landing/auth route policy.
- **Next recommended step:** perform browser-based UAT for the seeded admin and establishment user flows, then freeze Phase 1 before starting any JoFotara submission/XML/UUID/QR/sync work.

## JoFotara Integration Restore + MVP Fixes — 2026-06-20

- **Invoice route fix:** `/companies/{company}/invoices/create` is registered before numeric invoice member routes and invoice member routes now use numeric constraints, preventing `create` from being captured as an invoice id.
- **Template previews:** establishment invoice templates now show language, layout type, status/default flag, and a preview action that renders the selected template using an existing sample invoice through the same printable/PDF view stack.
- **Plan-feature sync:** establishment plan changes now remove keys supplied only by the old plan, add all keys from the new plan immediately, and preserve manual override keys where they are distinguishable from old plan-provided keys.
- **JoFotara restore/adaptation:** the existing `App\Services\Jofotara` UBL 2.1 preparation, base64 payload, HTTP submission, response parsing, XML/log artifact storage, credential usage, and QR/UUID parsing services remain in use and are now connected to the company-scoped MVP invoice details page.
- **JoFotara submit workflow:** approved invoices can be submitted from invoice details only when the establishment has `JOFOTARA_SUBMIT`, JoFotara credentials/source id exist, and the invoice has not already reached a submitted/accepted JoFotara state. Submission stores safe response data, UUID, QR/barcode, status, submitted timestamp, logs, audit entry, and notification without exposing the Secret Key.
- **JoFotara status/import:** invoices now have local JoFotara status fields and `source`. The invoice list/details show local status, JoFotara status, source, submitted date, UUID, QR/barcode, and safe error summaries. A Phase 1 import page accepts JSON/CSV backfills and prevents duplicates by JoFotara UUID or invoice number + issue date.
- **Feature keys:** `JOFOTARA_SUBMIT` and `JOFOTARA_SYNC` are seeded with Arabic/English names and descriptions, and plans can include both keys.
- **Tests added:** `tests/Feature/JofotaraMvpIntegrationTest.php` covers invoice create route availability, template preview, plan feature sync, mocked JoFotara submission/response storage, and duplicate prevention in JoFotara import.
- **Commands run:** `php artisan optimize:clear`; `php artisan migrate:fresh --seed`; `php artisan route:list --except-vendor`; `php artisan test`; `rg "@vite" resources/views`; `rg "Arabic Theame" resources/views`; `rg "build/manifest|public/build" resources/views`.
- **Result:** full suite passes with 62 tests and 231 assertions. No Vite/build manifest references remain in Blade views.
- **Remaining risk:** this restores and reconnects the existing local JoFotara service path with mocked test coverage; live production endpoint validation still requires real credentials/source id and a controlled UAT submission window.
- **Next recommended step:** run a live UAT submission for one approved seeded-style invoice in a controlled environment, verify the returned `EINV_QR` / `EINV_INV_UUID` values visually, then freeze Phase 1 before any broader sync automation.

## Hotfix — Invoice Type Enum + Template Preview Route — 2026-06-20

- **Root cause:** the original `invoices` migration defined MySQL `ENUM` columns for `invoice_type`, `invoice_subtype`, `invoice_scope`, `payment_type`, `taxpayer_type`, and `status`. The MVP invoice engine stores internal values such as `tax_invoice`, but the legacy enum only allowed `STANDARD` / `SIMPLIFIED`, causing MySQL data truncation during invoice creation and seeding.
- **Schema fix:** added a MySQL-safe migration that converts the enum-like invoice columns to `VARCHAR(50)` without requiring Doctrine DBAL. This preserves internal MVP values (`tax_invoice`, `simplified_invoice`, `credit_note`, `debit_note`) and legacy/JoFotara values (`income`, `general_sales`, `special_sales`, `credit_income`, `credit_general_sales`, `credit_special_sales`). SQLite test runs are unaffected because Laravel enum columns are already string-like there.
- **JoFotara safety:** JoFotara XML behavior remains separated from internal UI invoice type values. The existing JoFotara type-code logic still derives XML `InvoiceTypeCode` from `taxpayer_type`, `payment_type`, and `invoice_scope`, not from the MVP `invoice_type` label.
- **Model constants:** `App\Models\Invoice` now documents both internal MVP invoice types and legacy JoFotara-compatible type values for validation/tests and future-safe imports.
- **Seeder/create flow:** `InvoiceSeeder` continues using `Invoice::TYPE_TAX_INVOICE`; `migrate:fresh --seed` now succeeds after the schema conversion migration, and the company UI invoice creation test posts `tax_invoice` successfully.
- **Template preview:** regression coverage now checks every seeded global invoice template preview route using `company.invoice-templates.preview`, including numeric template ids such as `/companies/1/invoice-templates/3/preview`.
- **Tests added/updated:** `tests/Feature/JofotaraMvpIntegrationTest.php` now verifies internal and legacy invoice types can be stored, the company UI can create an invoice with `tax_invoice`, all seeded template previews return 200, and mocked JoFotara submission still stores QR/UUID/status without live HTTP calls.
- **Commands run:** `php artisan optimize:clear`; `php artisan migrate:fresh --seed`; `php artisan route:list --except-vendor`; `php artisan test tests/Feature/InvoiceEngineV1Test.php --stop-on-failure`; `php artisan test tests/Feature/InvoiceExperienceLayerTest.php --stop-on-failure`; `php artisan test tests/Feature/JofotaraMvpIntegrationTest.php --stop-on-failure`; `php artisan test`; `rg "@vite" resources/views`.
- **Result:** full suite passes with 64 tests and 248 assertions.
- **Remaining risk:** the migration uses explicit MySQL `ALTER TABLE ... MODIFY ... VARCHAR(50)` statements; this is intentional to avoid DBAL, but it should be reviewed against any production custom indexes/triggers before deployment.
- **Next recommended step:** run the migration on a staging MySQL copy and create one invoice from the UI before deploying to production.

## Invoice Flow Correction — 2026-06-20

### هدف التصحيح
- تبسيط رحلة الفاتورة لتكون واضحة: `draft` → `ready` → `submitted` أو `cancelled`.
- جعل الإجراء الأساسي هو الإرسال إلى نظام الفوترة الوطني/جوفوتارا بدل مسار اعتماد داخلي مربك.
- إبقاء حالات المراجعة الداخلية القديمة للتوافق فقط، وعدم جعلها المسار الافتراضي في واجهة Phase 1.

### تجربة المستخدم المطلوبة
- صفحة إنشاء/تعديل الفاتورة تعرض أزرارًا واضحة:
  - حفظ كمسودة.
  - حفظ وتجهيز للإرسال.
  - إلغاء.
- صفحة تفاصيل الفاتورة تعرض زر **إرسال إلى نظام الفوترة الوطني** فقط عند اكتمال الشروط:
  - الفاتورة جاهزة للإرسال.
  - المنشأة فعالة.
  - بيانات الربط مع جوفوتارا مكتملة.
  - ميزة `JOFOTARA_SUBMIT` مفعلة للمنشأة.
  - المستخدم يملك صلاحية `invoices.submit`.
- بعد الإرسال الناجح تصبح الفاتورة `submitted` وتُعرض بيانات جوفوتارا: الحالة، UUID، QR/Barcode، تاريخ الإرسال، رسالة ونتيجة النظام.
- صفحة الاستيراد/المزامنة توضح أن السحب الآلي التاريخي يحتاج endpoint/صلاحية رسمية، وتدعم حالياً backfill عبر JSON/CSV.

### تحقق سريع
- تسجيل الدخول كمستخدم منشأة: `company@invosync.local` / `password`.
- إنشاء فاتورة وحفظها كمسودة.
- تجهيز الفاتورة للإرسال.
- التأكد من ظهور زر الإرسال إلى نظام الفوترة الوطني عند اكتمال الصلاحيات والبيانات.
- التأكد من عدم ظهور Secret Key في أي واجهة أو استجابة.

## JoFotara PIH / ICV Chain Stabilization — 2026-06-21

### Root cause
- The live UAT error occurred because local MVP invoices and JoFotara-submitted invoices shared the same `icv` sequence assumptions.
- The previous PIH lookup searched by `status = ACCEPTED` and `icv - 1`, which could ignore the new `jofotara_status = ACCEPTED` fields and could be blocked by local/failed/unsubmitted invoices consuming local ICV values.

### Stabilized behavior
- Local invoices may retain local/internal ICV values for compatibility, but they no longer define the official JoFotara chain.
- The JoFotara chain is based only on accepted JoFotara invoices for the same establishment with a valid UUID/submission UUID and XML hash.
- First JoFotara invoice uses ICV `1` and the configured initial PIH behavior already present in the JoFotara preparation service.
- Failed/rejected submissions are retryable and are ignored as PIH sources.
- On accepted submission, the establishment `last_icv` is updated to the accepted JoFotara ICV.

### UI / UAT diagnostics
- Invoice details now show a PIH / ICV diagnostic panel with current invoice status, JoFotara status, recommended ICV, previous accepted invoice, previous UUID, PIH status, and next recommended action.
- A non-production-only `JoFotara UAT Status` page shows establishment credentials status, last accepted invoice, last failed invoice, next eligible invoice, and current sequence state.

### Verification commands
- `php artisan optimize:clear`
- `php artisan migrate:fresh --seed`
- `php artisan test tests/Feature/JofotaraMvpIntegrationTest.php --stop-on-failure`
- `php artisan test`

## Critical JoFotara Main-Contract Alignment — 2026-06-21

- **Main submission contract preserved:** JoFotara real submission still posts to `services.jofotara.url` with `Client-Id`, `Secret-Key`, `Content-Type: application/json`, and `Accept: */*` headers and a single JSON payload key, `{ "invoice": base64_xml }`. Company encrypted credential accessors remain the source before falling back to environment config, and tests assert the outgoing payload does not expose secrets.
- **Core XML path preserved:** No XML builder or calculation changes were made to `UBLInvoiceBuilder`, `InvoiceTypeCodeService`, `InvoiceHashService`, `TaxCalculationService`, `UBLValidationService`, or `JoFotaraPreparationService`; the MVP continues to use the existing preparation path and company-scoped invoice mapping.
- **Status separation fixed:** JoFotara official status, validation result, and local invoice status are separated. `EINV_STATUS=SUBMITTED` is stored as `jofotara_status=SUBMITTED`; `EINV_RESULTS.status=PASS` is stored as `jofotara_validation_result=PASS`; neither value is converted to `ACCEPTED`. Local invoice status may still become `submitted` after a successful HTTP/API response.
- **QR restoration:** `EINV_QR` is stored in `jofotara_qr`, mirrored to the legacy `qr_code` field when present, and displayed through the company-scoped QR PNG route. The QR renderer now prefers `jofotara_qr` and falls back to `qr_code`; raw QR text is only shown inside optional technical details.
- **PIH/ICV alignment:** Previous-invoice lookup remains strict and accepts only true `ACCEPTED` JoFotara invoices with UUID/submission UUID and XML hash. `SUBMITTED`, failed, rejected, and local-only invoices are not PIH sources and do not advance the accepted chain.
- **UI labels fixed:** Invoice details show `حالة الفاتورة المحلية`, `حالة جوفوتارا`, `نتيجة التحقق`, `رقم UUID`, and `رمز QR`, without presenting `PASS` or `SUBMITTED` as `ACCEPTED`.
- **Portal reflection comparison:** The endpoint URL, HTTP method, headers, JSON payload key, source id, seller tax number, InvoiceTypeCode, ICV, PIH, and ProfileID paths remain on the existing mainline JoFotara preparation/submission services. No XML-builder differences were introduced in this alignment step.
- **Tests updated:** Unit/feature tests now cover PASS/SUBMITTED separation, QR storage/display/PNG route, UUID storage from `EINV_INV_UUID`, strict accepted-only PIH source behavior, ignored SUBMITTED PIH source behavior, payload shape, headers, and no secret exposure in submitted payloads.
- **Verification note:** `php artisan optimize:clear`, `php artisan migrate:fresh --seed`, `tests/Unit/JoFotaraResponseParserTest.php`, and `tests/Feature/JofotaraSequenceTest.php` passed. `tests/Feature/JofotaraMvpIntegrationTest.php` and full `php artisan test` are currently blocked before JoFotara assertions by the environment missing the installed `spatie/laravel-permission` package (`Spatie\Permission\Traits\HasRoles` not found); `composer install` attempted to restore it but GitHub package downloads/clones were blocked by network/proxy 403/DNS errors.
- **Live UAT next steps:** Install dependencies in an environment with GitHub/Packagist access, rerun the full required suite, submit one controlled invoice with real credentials/source id, verify the official portal reflects the invoice, and confirm returned `EINV_STATUS`, `EINV_RESULTS.status`, `EINV_INV_UUID`, and `EINV_QR` exactly match the local detail page.

## JoFotara Future-Date Hotfix — 2026-06-21

- **Future issue date guard:** Company-scoped JoFotara submission now blocks invoices with `issue_date` after today before any HTTP call to JoFotara and returns the Arabic error `لا يمكن إرسال فاتورة بتاريخ إصدار مستقبلي إلى نظام الفوترة الوطني.` Due dates may remain future dated.
- **Failed response state:** Responses with `EINV_STATUS=NOT_SUBMITTED`, `EINV_RESULTS.status=ERROR`, missing `EINV_QR`, or missing `EINV_INV_UUID` are stored as failed/not-submitted JoFotara attempts while the local invoice remains `ready` and retryable. Official JoFotara status and validation result are preserved exactly, raw response is saved, and stale success UI is avoided.
- **Error priority:** JoFotara `ERRORS` are summarized before `INFO`, so live errors such as `Issue date cannot be in the future` are shown to the user while informational UBL compliance messages remain in raw technical details.
- **UI retry state:** Failed/not-submitted invoice details show a red error panel, keep edit/retry actions available for ready invoices, hide QR success image blocks for failed attempts, and only show official UUID when JoFotara returned one.
- **Seeder/sidebar fix:** The seeded establishment is synced with all active feature keys, the seeded Owner role receives all seeded permissions, and the global `/dashboard` layout now passes the authenticated company to the sidebar so company employees see company workspace navigation.
- **Tests updated:** Added hotfix coverage for future-date preflight blocking, NOT_SUBMITTED keeping local status ready, validation ERROR/missing QR/missing UUID keeping local status ready, ERRORS priority over INFO, retry UI availability after failure, and successful SUBMITTED/PASS/QR/UUID setting local status to submitted.
- **Live UAT next step:** Re-run one controlled invoice with today's issue date, verify the portal reflects it, then intentionally test a future-dated draft in UAT to confirm the local guard blocks before JoFotara receives a request.
