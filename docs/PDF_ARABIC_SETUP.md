# PDF and Arabic Invoice Rendering Setup

## Overview

Invoice presentation is centralized around one display-data pipeline and one reusable Blade document:

- Controllers load invoices and route browser, print-preview, and PDF requests without changing invoice submission or JoFotara logic.
- `App\Services\Invoices\InvoiceDisplayDataFactory` converts invoice, company, customer, totals, items, notes, and the official JoFotara QR value into safe presentation data.
- `resources/views/company/invoices/partials/document.blade.php` is the shared invoice body used by the web view, printable preview, and PDF templates.
- `resources/views/company/invoice-templates/partials/base.blade.php` wraps the shared document for template/PDF output.
- `App\Services\Invoices\InvoicePdfRenderer` renders the shared HTML through Browsershot first, with DomPDF only as a fallback.

Printable browser preview and PDF output use the same `print-preview-shell` / `invoice-print-page` wrapper and the same shared document partial. The normal authenticated workspace preview uses the same document partial without the fixed A4 wrapper, so print-only sizing cannot alter the working dashboard preview.

The refactor intentionally leaves calculations, XML/UBL generation, validation, submission workflow, API payloads, database schema, and official JoFotara QR generation untouched.

## Audit Findings and Root Causes

1. **Arabic PDF rendering was broken by fallback font/runtime behavior.** DomPDF does not reliably shape Arabic contextual glyphs, and the previous fallback used a generic font with no project-level font cache or deterministic font directory.
2. **PDF and browser output were not using a fixed print rendering profile.** Browsershot rendered with default viewport behavior, no print media emulation, and no network-idle wait, so fonts/CSS could be captured before they were fully ready.
3. **Invoice CSS was compressed into one public file.** That made template review difficult and encouraged inline overrides.
4. **Template-specific inline CSS existed in the PDF wrapper.** This created differences between browser and PDF output and made styling inconsistent.
5. **Table layout had inline width rules.** It made the items table harder to standardize across HTML, print, and PDF.
6. **Print page-break rules were incomplete.** Table headers and rows needed explicit print behavior for multi-page invoices.
7. **The printable browser received a `file://` stylesheet URL.** A normal HTTPS browser cannot load a server-local filesystem URL, so the printable HTML appeared unstyled and collapsed into a narrow content column.
8. **The PDF renderer silently fell back to DomPDF.** When Chromium was unavailable or failed, the exception was discarded and DomPDF emitted visually reversed/disconnected Arabic because it does not provide the same complex-script shaping as Chromium.

## Fonts

Selected font: **Hasan Alquds Unicode**.

Locations:

- Regular: `public/assets/fonts/ArbFONTS-Hasan-Alquds-Unicode.ttf`
- Bold: `public/assets/fonts/ArbFONTS-Hasan-Alquds-Unicode-Bold.ttf`

Why it was chosen:

- It already exists in the repository, so no new font dependency is required.
- It includes Arabic glyph coverage suitable for invoice labels, names, addresses, and notes.
- It has regular and bold weights, allowing a consistent professional invoice hierarchy.

## CSS

Canonical invoice styles are maintained in:

- `resources/css/invoice/invoice.css`
- `resources/css/invoice/invoice-print.css`
- `resources/css/invoice/invoice-pdf.css`

The deployed public stylesheet is:

- `public/css/invoice-document.css`

Fonts are loaded once with `@font-face` and referenced as `InvoiceArabic`, then fallback to `DejaVu Sans` and `Arial`.

Important CSS behaviors:

- `direction: rtl` and `unicode-bidi: plaintext` preserve mixed Arabic/English text flow.
- `.num` forces invoice numbers, amounts, UUIDs, and Latin values to LTR.
- `@page { size: A4; margin: 10mm; }` optimizes print/PDF layout.
- `thead { display: table-header-group; }` repeats item table headers across pages.
- Rows and summary sections use `break-inside: avoid` / `page-break-inside: avoid`.

## DomPDF Configuration

DomPDF is retained only as a fallback. The renderer sets important options at runtime:

- `isHtml5ParserEnabled`: enables modern HTML parsing.
- `isRemoteEnabled`: allows image/CSS/font assets when needed.
- `defaultFont`: points to the invoice Arabic font family name.
- `fontDir`: uses `public/assets/fonts` so the repository fonts are discoverable.
- `fontCache`: uses `storage/fonts` for generated font metrics/cache.
- `chroot`: uses the project base path so public and storage assets can resolve safely.
- `dpi`: set to `144` for sharper raster images and QR output.
- `isFontSubsettingEnabled`: embeds only required glyphs to reduce PDF size.

> Note: Browsershot/Chromium is the preferred engine for Arabic because Chromium provides proper Arabic shaping. DomPDF fallback can still be useful for environments without Node/Chromium, but Chromium should be available in production for best Arabic PDF output.

## Browsershot Configuration

Browsershot renders the exact HTML used by browser preview, with print-specific settings:

- `format('A4')`: uses A4 paper.
- `margins(0, 0, 0, 0)`: prevents Chromium margins from stacking with the A4 wrapper's single `10mm` padding.
- `showBackground()`: preserves table headers, badges, and brand accents.
- `waitUntilNetworkIdle()`: waits for CSS, fonts, and images to finish loading.
- `emulateMedia('print')`: applies print CSS rules.
- `windowSize(1240, 1754)`: provides a stable A4-like viewport.
- `deviceScaleFactor(1)`: avoids inconsistent scaling between environments.
- `waitForSelector('.invoice-document.invoice-page')`: prevents capture before the shared invoice root exists.
- `waitForFunction(...)`: waits for `document.fonts` to report that local fonts are loaded.
- zero renderer margins: the fixed A4 wrapper owns the single `10mm` page padding, avoiding compounded margins and narrow output.

The PDF-only HTML embeds the shared stylesheet and local font bytes. Browser printable preview uses the normal absolute application asset URL; it never receives a `file://` URL.

Production configuration:

```dotenv
INVOICE_PDF_NODE_BINARY=/usr/bin/node
INVOICE_PDF_CHROME_PATH=/usr/bin/chromium
INVOICE_PDF_ALLOW_DOMPDF_FALLBACK=false
```

If Chromium fails in production, the failure is logged and PDF download returns a controlled `503` response by default instead of silently returning a visually corrupted Arabic PDF. DomPDF fallback is available only when explicitly enabled and is intended for diagnostics or constrained non-production environments.

## Arabic Rendering

Arabic support depends on four layers working together:

1. **UTF-8 HTML**: every invoice wrapper includes `<meta charset="utf-8">`.
2. **Unicode Arabic font**: Hasan Alquds Unicode is loaded through CSS and embedded by Chromium in generated PDFs.
3. **RTL document flow**: invoice documents render with `dir="rtl"` and CSS direction rules.
4. **LTR islands for technical values**: amounts, UUIDs, invoice numbers, and Latin text use `.num` / `dir=ltr` behavior to prevent RTL reordering.

The official JoFotara QR value is not regenerated or replaced. The display layer only renders the data URI produced from the official value already returned by JoFotara.

## Troubleshooting

### Arabic appears as squares

- Confirm the font files exist in `public/assets/fonts`.
- Run `php artisan optimize:clear` and regenerate any deployment artifact/cache.
- Confirm `public/css/invoice-document.css` contains the `@font-face` rules.
- Confirm Browsershot can access local `file://` CSS and public font paths.

### Disconnected Arabic letters

- Ensure Chromium/Browsershot is being used rather than DomPDF fallback.
- Confirm Node dependencies are installed and Chromium is available.
- Check application logs for Browsershot exceptions.

### Incorrect RTL

- Verify the invoice wrapper includes `dir="rtl"`.
- Ensure Arabic text is not manually wrapped in LTR containers.
- Use `.num` only for amounts, UUIDs, tax numbers, and Latin identifiers.

### Fonts not loading

- Check browser dev tools for 404 errors under `/assets/fonts/...`.
- Confirm file permissions allow the web server/PHP user to read the fonts.
- Clear Laravel, browser, and CDN caches.

### QR not displayed

- Confirm the invoice has an official JoFotara QR value.
- Confirm the invoice passed/was accepted by JoFotara where applicable.
- Do not regenerate QR values manually; inspect the stored JoFotara response first.

### Logo missing

- Confirm the stored logo path is relative to `public` or otherwise web-accessible.
- Confirm file permissions and storage symlink configuration if the logo is stored under `storage/app/public`.

### Different browser/PDF appearance

- Ensure both preview and PDF use the shared document partial.
- Confirm `emulateMedia('print')` is active for Browsershot.
- Avoid adding inline CSS to templates.

### Broken page breaks

- Keep invoice rows as table rows and do not replace the items table with flex/grid.
- Avoid placing large unbreakable images or text in a single table cell.

### Missing CSS

- Confirm `public/css/invoice-document.css` is deployed.
- Rebuild frontend assets if your deployment pipeline copies from `resources/css`.

## Deployment

Run these commands after deploying invoice rendering/font changes:

```bash
composer dump-autoload
npm install
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache
mkdir -p storage/fonts
chmod -R ug+rw storage/fonts
```

If PDFs still use stale fonts, clear `storage/fonts` and regenerate a PDF once to rebuild DomPDF font metrics.
