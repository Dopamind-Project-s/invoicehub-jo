# Phase 1 — Professional Invoice Templates & PDF Engine

## Completed
- Inspected local public assets. Available fonts include Droid Arabic Kufi, Droid Arabic Naskh, Noto Kufi Arabic, Hasan Alquds, Castile, OpenSans webfonts, and other Arabic display fonts under `public/assets/fonts`. `public/assets/images` contained a `.gitkeep`; a local invoice placeholder logo SVG was added. `public/css` contains Theme, Style, Bootstrap RTL lite, and phase1 layout CSS. `public/vendor` contains a README. `public/assets/templates` was created for template preview placeholders.
- Added `InvoiceTemplateData` and `InvoiceTemplateDataFactory` so Blade templates receive prepared invoice, company, customer, items, totals, branding, QR, JoFotara, language, and direction data without querying the database from views.
- Added `InvoicePdfRenderer` with HTML preview rendering, Browsershot PDF attempt, and DomPDF fallback for production-like environments where Chrome/Node is unavailable.
- Added five local-asset Blade invoice templates: Arabic Classic, Arabic Modern, Arabic/English Bilingual, Retail Receipt, and Corporate Tax Invoice.
- Added QR rendering from the exact `jofotara_qr` value only. If no JoFotara QR exists, templates show: “QR Code will appear after submission to the National E-Invoicing System”.
- Added template fields `preview_image` and `view_path`, and seeded five templates idempotently.
- Improved `/companies/{company}/invoice-templates` with card selection UI, preview buttons, current-default state, language/type display, and Arabic theme-compatible styling.
- Updated preview to return HTML with a real latest invoice or generated sample data when none exists.
- Updated invoice PDF download to use company default template, falling back to Arabic Classic via branding settings.

## Safety
- JoFotara XML generation and submission services were not edited.
- JoFotara QR values are displayed exactly as stored and are not modified.
- No `@vite`, CDN, or online fonts are used by invoice templates.

## Remaining risks
- Browsershot depends on the runtime Node/Chrome/Puppeteer setup. The renderer catches failures and falls back to DomPDF.
- DomPDF Arabic shaping can be less accurate than Chromium; Browsershot remains preferred where available.
- Preview image PNG files are placeholders; live preview is available through the preview route.

## Recommended next step
- Add real graphical preview thumbnails for the five templates and test Browsershot in the production container image with the deployed Node/Chrome version.
