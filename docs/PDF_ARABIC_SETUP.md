# PDF and Arabic Invoice Rendering Setup

## Overview

Invoice presentation is centralized around one display-data pipeline and one reusable Blade document:

- Controllers load invoices and route browser, print-preview, and PDF requests without changing invoice submission or JoFotara logic.
- `App\Services\Invoices\InvoiceDisplayDataFactory` converts invoice, company, customer, totals, items, notes, and the official JoFotara QR value into safe presentation data.
- `resources/views/company/invoices/partials/document.blade.php` is the shared invoice body used by the web view, printable preview, and PDF templates.
- `resources/views/company/invoice-templates/partials/base.blade.php` wraps the shared document for template/PDF output.
- `App\Services\Invoices\InvoicePdfRenderer` renders the shared HTML through Browsershot and Chromium. It fails closed if that engine or a bundled font is unavailable; it never silently returns a different renderer's PDF.

Printable browser preview and PDF output use the same `print-preview-shell` / `invoice-print-page` wrapper and the same shared document partial. The normal authenticated workspace preview uses the same document partial without the fixed A4 wrapper, so print-only sizing cannot alter the working dashboard preview.

The refactor intentionally leaves calculations, XML/UBL generation, validation, submission workflow, API payloads, database schema, and official JoFotara QR generation untouched.

### Template themes

`InvoiceTemplateResolver` is the single presentation map for the eight stable template slugs. Every theme keeps the same `InvoiceTemplateDataFactory` / `InvoiceDisplayDataFactory` contract and A4 base stylesheet, then loads one scoped stylesheet from `public/css/invoice-templates`. The resolver supplies unique header, information, table, totals, QR, density, and layout identifiers, preventing the shared print infrastructure from flattening all templates into one design.

Template-management preview passes the requested `InvoiceTemplate` directly to `InvoicePdfRenderer`; it does not update the company default. Normal invoice print/PDF rendering continues to use the company-selected template, with Arabic Classic as the system fallback.

## Audit Findings and Root Causes

1. **Local and test rendering silently switched to DomPDF when Browsershot failed.** DomPDF does not reliably shape Arabic, so the download could succeed with reversed or disconnected Arabic. The renderer now uses Chromium only and returns a controlled `503` if it cannot run.
2. **The font stack preferred a machine-local Cairo installation.** This made output depend on fonts installed on the server and could bypass the bundled font. Invoice PDFs now embed the project's Arabic and numeric font files as data URLs and fail if any referenced font is missing.
3. **Bilingual templates had an invalid document language tag.** `ar_en` was interpreted as English even when direction was RTL. Arabic and bilingual templates now resolve to an Arabic language tag; English is used only by English templates.
4. **Free-form mixed-language values inherited the page direction.** Names, addresses, item descriptions, notes, and footer text now use `dir="auto"`; numeric identifiers remain isolated LTR.
5. **The print rendering profile did not wait for fonts or emulate print media.** Chromium now waits for `document.fonts.ready`, applies print media, and uses the shared fixed A4 wrapper.

## Fonts

Invoice rendering uses bundled **Cairo** for Arabic and mixed text, with bundled Open Sans regular/bold files for numeric fields and Latin fallback. Typography is set on `.invoice-document` as well as the standalone document body, so workspace previews cannot inherit a different font from the application theme.

Locations:

- Regular: `public/assets/fonts/Cairo-Regular.ttf`
- Bold: `public/assets/fonts/Cairo-Bold.ttf`
- SIL Open Font License: `public/assets/fonts/Cairo-OFL.txt`
- Source: https://github.com/google/fonts/tree/main/ofl/cairo

Why it was chosen:

- It includes Arabic and Latin glyphs and clear, readable invoice typography.
- Static regular and bold instances avoid Chromium's Type3 conversion of this variable font. They were generated from the official variable font with FontTools, pinning `slnt=0` and `wght=400` / `wght=700` with `--update-name-table`; FontTools is not a runtime dependency.
- The browser serves the local file; PDF generation embeds it as a data URL. Neither path needs a system-installed font or an external font service.

## CSS

Canonical invoice styles are maintained in:

- `resources/css/invoice/invoice.css`
- `resources/css/invoice/invoice-print.css`
- `resources/css/invoice/invoice-pdf.css`

The deployed public stylesheet is:

- `public/css/invoice-document.css`

Fonts are declared once with `@font-face` as `InvoiceArabic` and `InvoiceNumeric`. PDF generation embeds their bytes from `public/assets/fonts`; it does not depend on fonts installed on the server or PDF reader.

Important CSS behaviors:

- `direction: rtl` and `unicode-bidi: plaintext` preserve mixed Arabic/English text flow.
- `.num` forces invoice numbers, amounts, UUIDs, and Latin values to LTR.
- `@page { size: A4; margin: 10mm; }` optimizes print/PDF layout.
- `thead { display: table-header-group; }` repeats item table headers across pages.
- Rows and summary sections use `break-inside: avoid` / `page-break-inside: avoid`.

## Browsershot Configuration

Browsershot renders the exact HTML used by browser preview, with print-specific settings:

- `format('A4')`: uses A4 paper.
- `margins(0, 0, 0, 0)`: prevents Chromium margins from stacking with the A4 wrapper's single `10mm` padding.
- `showBackground()`: preserves table headers, badges, and brand accents.
- `waitForFunction(document.fonts.ready)`: waits for bundled web fonts to finish loading before capture.
- `emulateMedia('print')`: applies print CSS rules.
- `windowSize(1240, 1754)`: provides a stable A4-like viewport.
- `deviceScaleFactor(1)`: avoids inconsistent scaling between environments.
- `waitForSelector('.invoice-document.invoice-page')`: prevents capture before the shared invoice root exists.
- zero renderer margins: the fixed A4 wrapper owns the single `10mm` page padding, avoiding compounded margins and narrow output.

The PDF-only HTML embeds the shared stylesheet, template stylesheet, and local font bytes. Browser printable preview uses normal application asset URLs; it never receives a `file://` URL. Chromium embeds those font bytes in the output PDF, so PDF readers need no local fonts.

Production configuration:

```dotenv
INVOICE_PDF_NODE_BINARY=/usr/bin/node
INVOICE_PDF_CHROME_PATH=/usr/bin/chromium
```

The project declares Puppeteer in `package.json` and locks it in `package-lock.json`. Install Node.js 22 or newer, run `npm ci --ignore-scripts`, and install a supported Chromium/Chrome binary on the server. Set `INVOICE_PDF_CHROME_PATH` to that binary and `INVOICE_PDF_NODE_BINARY` if Node is not on the PHP worker's `PATH`. Setup disables npm install scripts, so Puppeteer does not download a second browser; use the server's maintained Chromium package. The PHP worker needs read/execute access to Node and Chromium and enough shared-memory/temp space to launch headless Chromium.

If Chromium, Puppeteer, or a bundled font is missing, the failure is logged and download returns `503`. This invoice path does not fall back to DomPDF.

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
- Confirm the PHP worker can read the stylesheet/font files and launch the configured Chromium binary.

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

## Local Windows / Laragon setup

If Puppeteer reports `Could not find Chrome`, point the renderer at an installed
Chrome binary instead of relying on Puppeteer's browser cache. For Laragon, use
the actual Node installation directory on your machine:

```dotenv
INVOICE_PDF_NODE_BINARY="C:/laragon/bin/nodejs/node-v22/node.exe"
INVOICE_PDF_CHROME_PATH="C:/Program Files/Google/Chrome/Application/chrome.exe"
```

Run `php artisan config:clear` after changing these values. Both executables must
be accessible to the PHP web server account. These paths are local examples;
production must use the server's own Node and Chromium paths.

## Deployment

Run these commands after deploying invoice rendering/font changes:

```bash
npm ci --ignore-scripts
composer dump-autoload
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

No DomPDF font cache is required for this renderer. Verify `node --version`, `npm ls puppeteer`, and the configured Chromium binary as the PHP worker user before enabling invoice downloads.
