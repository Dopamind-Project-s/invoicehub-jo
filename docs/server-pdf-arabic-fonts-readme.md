# إعداد PDF العربي باستخدام mPDF

يعتمد تصدير الفواتير على `mpdf/mpdf` فقط. لا يستخدم التطبيق Chromium أو Node أو Browsershot أو Dompdf.

## متطلبات PHP

تأكد أن PHP الخاص بخادم الويب (وليس CLI فقط) يملك الامتدادات التالية:

```bash
php -m | grep -E '^(gd|mbstring)$'
```

ثم ثبّت اعتماديات Composer من ملف القفل:

```bash
composer install --no-dev --optimize-autoloader
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Mpdf\\Mpdf'));"
```

يجب أن تكون نتيجة الأمر الأخير `bool(true)`. وجود `mpdf/mpdf` في `composer.json` أو وجود مجلد فارغ تحت `vendor` لا يعني أن المكتبة مثبّتة.

## الخطوط ومجلد العمل المؤقت

يجب أن تكون الملفات التالية قابلة للقراءة من مستخدم PHP-FPM:

```text
public/assets/fonts/ArbFONTS-Droid.Arabic.Kufi_DownloadSoftware.iR_.ttf
public/assets/fonts/ArbFONTS-Droid.Arabic.Kufi_.Bold_DownloadSoftware.iR_.ttf
```

ويجب أن يكون مجلد Laravel قابلاً للكتابة:

```bash
mkdir -p storage/framework/cache/mpdf
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

## إكمال النشر

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

قالب PDF منفصل ومبسّط مبني بالجداول وCSS الذي تدعمه mPDF. لا يعاد استخدام Grid/Flex الخاص بمعاينة المتصفح، ولذلك لا يعتمد ترتيب عناصر PDF على إمكانات CSS الخاصة بالمتصفح.

## تشخيص فشل الإنشاء

راجع الاستثناء الأصلي في:

```bash
tail -n 100 storage/logs/laravel.log
```

أكثر الأسباب شيوعاً هي عدم اكتمال `composer install`، أو تشغيل PHP-FPM بإصدار/امتدادات مختلفة عن CLI، أو عدم امتلاك مستخدم الويب صلاحية الكتابة إلى `storage/framework/cache/mpdf`.
