# README — إعداد PDF بالخطوط العربية على السيرفر

هذا الدليل لتجهيز تنزيل فواتير PDF بخطوط عربية صحيحة في InvoSync على Ubuntu/Debian.

## 1) تثبيت حزم النظام

```bash
sudo apt-get update
sudo apt-get install -y \
  fontconfig \
  fonts-dejavu-core \
  fonts-noto-core \
  fonts-noto-extra \
  fonts-noto-ui-core \
  fonts-noto-ui-extra \
  fonts-noto-color-emoji \
  libfontconfig1 \
  libfreetype6 \
  libjpeg-turbo8 \
  libpng16-16
```

إذا كان السيرفر يستخدم Browsershot/Chromium لإخراج PDF، ثبّت Chromium واعتمادياته:

```bash
sudo apt-get install -y chromium-browser chromium || true
sudo apt-get install -y \
  libnss3 libatk-bridge2.0-0 libatk1.0-0 libcups2 libdrm2 \
  libxkbcommon0 libxcomposite1 libxdamage1 libxrandr2 libgbm1 \
  libasound2 libpangocairo-1.0-0 libpango-1.0-0 libcairo2
```

## 2) تأكيد وجود الخطوط المحلية داخل المشروع

القوالب تستخدم خطوطًا محلية موجودة في:

```text
public/assets/fonts/ArbFONTS-Droid-Arabic-Kufi.ttf
public/assets/fonts/ArbFONTS-Droid.Arabic.Naskh_.Regular_DownloadSoftware.iR_.ttf
public/assets/fonts/OpenSans-Regular-webfont.woff
```

تأكد من وجودها بعد النشر:

```bash
test -f public/assets/fonts/ArbFONTS-Droid-Arabic-Kufi.ttf
test -f public/assets/fonts/ArbFONTS-Droid.Arabic.Naskh_.Regular_DownloadSoftware.iR_.ttf
test -f public/assets/fonts/OpenSans-Regular-webfont.woff
```

## 3) صلاحيات مجلدات Laravel وDompdf

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rw storage bootstrap/cache
mkdir -p storage/fonts
sudo chown -R www-data:www-data storage/fonts
sudo chmod -R ug+rw storage/fonts
```

## 4) إعدادات `.env` المقترحة

```dotenv
APP_ENV=production
APP_DEBUG=false
FILESYSTEM_DISK=public
```

إذا كانت بيئة Chromium تحتاج مسارًا صريحًا، أضف حسب السيرفر:

```dotenv
CHROME_PATH=/usr/bin/chromium
```

> ملاحظة: التطبيق يحاول استخدام Browsershot عند توفره، ثم يرجع إلى Dompdf تلقائيًا إذا تعذر تشغيل Chromium.

## 5) تنظيف الكاش بعد النشر

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6) اختبار سريع من السيرفر

1. افتح صفحة قوالب الفواتير من لوحة المنشأة.
2. اختر قالبًا عربيًا مثل `Jordan Tax Pro` أو `Premium Ledger`.
3. اضغط **تنزيل PDF**.
4. تأكد أن النص العربي ظاهر بدون مربعات أو أحرف مفصولة.

## 7) مشاكل شائعة

### النص العربي يظهر مربعات

- تأكد من وجود ملفات الخطوط داخل `public/assets/fonts`.
- نفذ:

```bash
fc-cache -f -v
php artisan optimize:clear
```

### PDF لا ينزل أو يظهر خطأ Chromium

- تأكد من تثبيت Chromium واعتمادياته.
- تأكد من السماح للمستخدم `www-data` بالكتابة داخل `storage`.
- جرّب fallback إلى Dompdf بإزالة/تعطيل Chromium مؤقتًا أو مراجعة logs.

### الصور أو الشعار لا تظهر في PDF

- نفذ:

```bash
php artisan storage:link
```

- تأكد من أن `APP_URL` صحيح ويطابق دومين السيرفر.
