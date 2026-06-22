# Phase 1 — Professional Invoice Templates, PDF Engine, and Product UX

## Completed in this pass
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
- Added/updated master-data tests for units/tax/activity page rendering, global unit edit behavior, company isolation, category icon persistence, product image create/update, validation, and frontend-view no-`@vite` checks.

## Previously completed
- Built invoice template data preparation and rendering services.
- Added five invoice templates, QR placeholder/exact QR handling, template preview/download, PDF rendering with Browsershot attempt and DomPDF fallback, and template selection UI.
- Improved invoice listing, create/edit, and show pages with template-aware styling and cleaner actions.

## Remaining risks
- Global units can now be opened from a company workspace edit route; if stricter global-edit governance is required, add a dedicated clone-to-company workflow later.
- Category icons are stored as simple emoji/text values; replacing them with a full icon library can be handled later if needed.
- Product images are stored on the `public` disk and require the usual Laravel public storage link in deployed environments.
- Product image support is intentionally single-image only; media gallery management is not included.
- Full suite still depends on date-sensitive JoFotara test fixtures in this runtime.

## Recommended next step
- Add a dedicated product detail page if the `عرض` action should become read-only instead of routing users to the edit screen.
