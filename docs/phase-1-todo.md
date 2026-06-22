# Phase 1 — Professional Invoice Templates, PDF Engine, and Product UX

## Completed in this pass
- Improved `/companies/{company}/products` with Arabic theme styling, clear title `المنتجات والخدمات`, helper text, prominent `إضافة منتج / خدمة جديدة` button, search/filter controls, image thumbnails, useful catalog columns, status badges, and clear actions.
- Improved product create/edit screens with a professional RTL card form grouped into: basic information, category/unit/tax, pricing, description/image, and status.
- Added single product image support using `products.image_path`, public disk storage, server-side validation for JPG/JPEG/PNG/WEBP up to 2MB, current image display, and replacement handling on update.
- Added client-side validation without Vite or external assets for required Arabic name/type/price, non-negative price/cost, and image type/size.
- Reviewed backend validation for company-scoped category/unit/tax profile, company-unique SKU, safe active status handling, product/service type, numeric price/cost, and company isolation on edit/update/activate/deactivate.
- Added/updated product feature tests for list/create/edit page rendering, image create/update, invalid image validation, oversized image validation, company isolation, and no `@vite` in product views.

## Previously completed
- Built invoice template data preparation and rendering services.
- Added five invoice templates, QR placeholder/exact QR handling, template preview/download, PDF rendering with Browsershot attempt and DomPDF fallback, and template selection UI.
- Improved invoice listing, create/edit, and show pages with template-aware styling and cleaner actions.

## Remaining risks
- Product images are stored on the `public` disk and require the usual Laravel public storage link in deployed environments.
- Product image support is intentionally single-image only; media gallery management is not included.
- Full suite still depends on date-sensitive JoFotara test fixtures in this runtime.

## Recommended next step
- Add a dedicated product detail page if the `عرض` action should become read-only instead of routing users to the edit screen.
