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
   - [ ] Add super admin dashboard route/controller/view.
   - [ ] Add tenant/company setup screens that reuse `companies` safely and do not change current JoFotara seller flow.
   - [ ] Add basic company activation/status safeguards.

5. **Users, roles, and feature keys**
   - [ ] Add roles/permissions tables and seed baseline roles without introducing a breaking auth dependency.
   - [ ] Add feature key management tied to companies/tenants.
   - [ ] Gate Phase 1 admin pages while keeping existing routes usable for backward compatibility where needed.

6. **Customers, suppliers, products, and services**
   - [ ] Extend current customer UI where needed, preserving existing fields and validations.
   - [ ] Add supplier management if separate supplier entities are required by references; otherwise document companies-as-suppliers mapping.
   - [ ] Add product/service CRUD on existing `products`, `units`, and `tax_categories` schema.

7. **Invoice workflow and PDF**
   - [ ] Improve invoice creation UX with theme, products/services selection, and validation.
   - [ ] Preserve JoFotara-compatible decimal/tax calculations and add regression tests before changing calculations.
   - [ ] Keep issued PDF/QR behavior compatible and theme invoice screens.

8. **JoFotara submission, sync logs, status tracking**
   - [ ] Add submission log screens using existing `invoice_submission_logs` without exposing secrets or raw sensitive headers.
   - [ ] Add status tracking dashboard and filters for DRAFT/GENERATED/SUBMITTED/ACCEPTED/REJECTED/ERROR.
   - [ ] Add safe retry/sync actions only after confirming current PIH/ICV constraints.

9. **Basic dashboard analytics**
   - [ ] Add analytics cards/charts: invoices by status, totals, accepted/rejected counts, recent submissions, active companies/customers/products.
   - [ ] Use existing invoice data and avoid expensive queries.

10. **Final hardening**
    - [x] Full test pass for this execution step.
    - [x] Route smoke check for this execution step via `php artisan route:list --except-vendor`.
    - [x] Confirm migrations are additive/backward-compatible; credential migration encrypts in place and down() intentionally preserves encrypted credentials.
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
