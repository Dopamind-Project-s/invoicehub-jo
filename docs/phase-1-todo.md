# Phase 1 — Professional Invoice Templates, PDF Engine, and Product UX

## Completed in this pass
- Improved company settings with professional tabs/cards for general data, visual identity, invoice settings, JoFotara status, language/currency, logo/stamp previews, color pickers, helper text, and Arabic frontend validation without exposing JoFotara secrets.
- Improved `/companies/{company}` and company-user `/dashboard` with a professional profile/dashboard layout, quick actions, JoFotara connection status, subscription/features, last submitted invoice, last activity, recent invoices, and recent activities.
- Added `CompanyDashboardStatsService` with company-scoped 10-minute cached stats for products, contacts, invoices by status, JoFotara errors, sales totals, tax totals, recent invoices, and recent activities.
- Added dashboard cache invalidation after invoice create/update/status/Jofotara changes, product create/update, contact create/update, and JoFotara imports.
- Improved `/companies/{company}/contacts` with Arabic theme styling, clear `العملاء والموردون` heading, helper copy, prominent add button, name/tax/type/status filters, improved table, actions, and professional empty state.
- Improved contact create/edit screens with organized cards for basic information, communication data, tax data, address, status, placeholders, helper text, last-updated context, activate/deactivate action, and Arabic frontend validation.
- Preserved contact duplicate-prevention behavior for tax/national numbers while adding safer backend validation for non-empty Arabic names, email format, phone characters, and save-and-add-another flow.
- Fixed `/companies/{company}/units/{unit}/edit` for both company-specific and global units while preserving 404 isolation for units owned by another company.
- Improved units list/create/edit pages with Arabic theme styling, professional cards, clear helper copy, empty state, placeholders, save-and-add-another flow, and Arabic frontend validation.
- Improved tax profile list/create/edit pages with clear tax setup copy, professional table/cards, status/default badges, placeholders, helper text explaining tax percent/type/Jofotara code, save-and-add-another flow, and Arabic frontend validation.
- Improved `/companies/{company}/activity` with user/action/date-from/date-to filters and a sanitized timeline that avoids before/after sensitive payloads.
- Improved `/companies/{company}/product-categories` with Arabic theme styling, a prominent `➕ إضافة فئة جديدة` button, category icon guidance, helper copy, search/status filters, improved table layout, status badges, and a professional empty state.
- Improved product category create/edit screens with the same product-management card style, logical field grouping, placeholders, helper text, an icon picker, current icon display, last-updated context on edit, and clear save/cancel actions.
- Added `product_categories.icon` support with backend validation and plain JavaScript Arabic frontend validation for required non-empty Arabic name and category code without using Vite.
- Improved `/companies/{company}/products` with Arabic theme styling, clear title `المنتجات والخدمات`, helper text, prominent `إضافة منتج / خدمة جديدة` button, search/filter controls, image thumbnails, useful catalog columns, status badges, and clear actions.
- Improved product create/edit screens with a professional RTL card form grouped into: basic information, category/unit/tax, pricing, description/image, and status.
- Added single product image support using `products.image_path`, public disk storage, server-side validation for JPG/JPEG/PNG/WEBP up to 2MB, current image display, and replacement handling on update.
- Added client-side validation without Vite or external assets for required Arabic name/type/price, non-negative price/cost, and image type/size.
- Reviewed backend validation for company-scoped category/unit/tax profile, company-unique SKU, safe active status handling, product/service type, numeric price/cost, and company isolation on edit/update/activate/deactivate.
- Added/updated tests for settings/profile/dashboard rendering, company-scoped cached stats, dashboard cache invalidation, secrets not exposed, contacts create/update/duplicate prevention/company isolation, units/tax/activity page rendering, global unit edit behavior, category icon persistence, product image create/update, validation, and frontend-view no-`@vite` checks.

## Previously completed
- Built invoice template data preparation and rendering services.
- Added five invoice templates, QR placeholder/exact QR handling, template preview/download, PDF rendering with Browsershot attempt and DomPDF fallback, and template selection UI.
- Improved invoice listing, create/edit, and show pages with template-aware styling and cleaner actions.

## Remaining risks
- Dashboard statistics are cached for 10 minutes; very high-write installations may later benefit from model observers or queued cache warming.
- Contact phone validation is intentionally permissive for digits and common dialing symbols; stricter local phone normalization can be added later.
- Global units can now be opened from a company workspace edit route; if stricter global-edit governance is required, add a dedicated clone-to-company workflow later.
- Category icons are stored as simple emoji/text values; replacing them with a full icon library can be handled later if needed.
- Product images are stored on the `public` disk and require the usual Laravel public storage link in deployed environments.
- Product image support is intentionally single-image only; media gallery management is not included.
- Full suite still depends on date-sensitive JoFotara test fixtures in this runtime.

## Recommended next step
- Consider moving cache invalidation into model observers once the domain event structure is formalized.

## Landing CMS Phase 1 Implementation Update

Completed in this phase:

- Added `site_settings` for public contact, footer, CTA, SEO, and social settings.
- Added `landing_faqs` for active FAQ content rendered on the landing page.
- Extended `plans` with bilingual marketing fields, sort order, and recommended badge support.
- Added seeders for Arabic-first site settings and FAQ content.
- Updated plan seeding with Arabic/English marketing copy and recommended plan metadata.
- Added `LandingPageDataService` with cache key `landing:home:ar` for settings, active FAQs, and active plans with feature keys.
- Split `welcome.blade.php` into landing section partials for hero, features, integrations, pricing, FAQ, testimonials, statistics, partners, and CTA.
- Kept hero, integrations, testimonials, statistics, and partners hardcoded for Phase 1 while replacing generic AI copy with Arabic InvoSync content.
- Wired pricing to active database plans and included feature keys.
- Added admin website menu entries for `الموقع الإلكتروني`, `الإعدادات العامة`, `الأسئلة الشائعة`, and a link to existing plans.
- Added protected admin CRUD for landing FAQs and settings editing.

Remaining risks / follow-up:

- Footer still contains legacy theme demo dashboard/offcanvas markup and should be isolated or removed in a later cleanup phase.
- Hero/integration/testimonial/statistics CMS CRUD is intentionally deferred beyond Phase 1 per the implementation instruction to keep those hardcoded for now.
- A richer media manager is not implemented; uploaded landing images should be handled in a later phase.
