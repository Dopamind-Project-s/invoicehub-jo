# InvoiceHub Jo

نظام Laravel بسيط لإصدار فواتير عربية RTL وربطها مبدئياً مع نظام الفوترة الوطني الأردني JoFotara.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan db:seed
php artisan serve
```

> يستخدم المشروع MySQL؛ اضبط `DB_DATABASE` و`DB_USERNAME` و`DB_PASSWORD` في ملف `.env` قبل تشغيل الهجرات.

## JoFotara environment setup

يمكن تخزين بيانات JoFotara لكل بائع من شاشة البائعين. وتبقى قيم `.env` التالية كقيم احتياطية عامة إذا كانت بيانات البائع المحدد ناقصة:

```env
JOFOTARA_API_URL=https://backend.jofotara.gov.jo/core/invoices/
JOFOTARA_CLIENT_ID=
JOFOTARA_SECRET_KEY=
JOFOTARA_SOURCE_ID=
JOFOTARA_TAX_NUMBER=
JOFOTARA_SELLER_NAME=
```

يتم إنشاء رقم المستخدم والمفتاح السري من بوابة JoFotara من خلال خيار **ربط الأجهزة**. يستخدم النظام هذه القيم في ترويسات الطلب `Client-Id` و `Secret-Key` عند الإرسال إلى:

```text
POST https://backend.jofotara.gov.jo/core/invoices/
```

للتحقق من الإعدادات:

```bash
php artisan jofotara:check-config
```

## Usage

1. افتح `/sellers` لإضافة البائع/المصدر وبياناته الضريبية وشعار الفاتورة وبيانات JoFotara الخاصة به.
2. إذا كان هناك بائع واحد فقط فسيتم اختياره تلقائياً عند إنشاء الفاتورة، ويمكن تعيين بائع افتراضي من شاشة البائعين.
3. افتح `/customers` لإضافة الشركات والعملاء وبياناتهم الضريبية.
4. افتح `/invoices/create` لإنشاء فاتورة يدوية، ثم اختر البائع والعميل وأضف البنود.
5. يحسب النظام المجاميع في الواجهة وفي الخادم بدقة 3 خانات عشرية للدينار الأردني.
6. من صفحة عرض الفاتورة اضغط **إرسال إلى جوفوتارا** لإرسال XML بصيغة UBL 2.1 بعد ترميزه Base64.
7. يتم حفظ رد JoFotara كاملاً، وحفظ UUID و QR عند القبول.
8. استخدم زر **PDF** لطباعة الفاتورة أو **معاينة** لعرض تصميم الفاتورة في المتصفح.

## Notes

- خدمة `App\Services\JofotaraService` تحتوي TODO للحقول النهائية التي قد تحتاج مطابقة مع عينات XML الرسمية من ISTD.
- لا يتم تسجيل المفتاح السري في السجلات؛ يتم تسجيل رقم الفاتورة وحالة الاستجابة فقط.
- عند ربط أجهزة المكلف في JoFotara قد يتم تعطيل إنشاء الفواتير يدوياً من البوابة، وبذلك يصبح هذا النظام هو مصدر إصدار الفواتير.
