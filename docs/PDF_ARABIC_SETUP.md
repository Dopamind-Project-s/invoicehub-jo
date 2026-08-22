# Arabic invoice PDF architecture

Invoice PDFs are generated exclusively by `mpdf/mpdf`. Browser preview HTML remains optimized for modern browsers, while PDF output uses `resources/views/company/invoice-templates/mpdf.blade.php`, a dedicated table-based A4 document limited to HTML/CSS features supported by mPDF.

## Why the PDF has a dedicated view

The interactive templates use CSS Grid, Flexbox, custom properties, browser font loading, and JavaScript page fitting. Passing that document to a server PDF engine caused shifted or empty output. The mPDF view instead uses tables, explicit dimensions, inline CSS, embedded data-URI images, and a registered local Arabic TrueType font. Arabic text remains logical Unicode; it is not reversed or converted to presentation-form characters before mPDF performs Arabic shaping and bidirectional layout.

## Runtime contract

Production must run:

```bash
composer install --no-dev --optimize-autoloader
mkdir -p storage/framework/cache/mpdf
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
php artisan optimize:clear
```

Verify the same PHP runtime used by PHP-FPM has mPDF and its required extensions:

```bash
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Mpdf\\Mpdf'));"
php -m | grep -E '^(gd|mbstring)$'
```

The class check must print `bool(true)`. The renderer deliberately has no Dompdf, Chromium, or Browsershot fallback: returning a differently rendered or malformed Arabic invoice is not acceptable.

## Required local fonts

```text
public/assets/fonts/ArbFONTS-Droid.Arabic.Kufi_DownloadSoftware.iR_.ttf
public/assets/fonts/ArbFONTS-Droid.Arabic.Kufi_.Bold_DownloadSoftware.iR_.ttf
```

The renderer checks both files before generation. Generation errors are reported through Laravel and can be inspected in `storage/logs/laravel.log`.
