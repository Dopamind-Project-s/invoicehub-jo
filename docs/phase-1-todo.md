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
