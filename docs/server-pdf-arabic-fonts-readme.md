# متطلبات تنزيل PDF للفواتير العربية

يستخدم تنزيل قوالب الفواتير في مساحة المنشأة Browsershot مع Chromium headless. هذا المسار لا يستخدم DomPDF بديلاً؛ عند تعطل Chromium أو فقد ملف خط يرجع التطبيق بخطأ HTTP 503 بدلاً من تنزيل PDF بعربية غير سليمة.

## متطلبات الإنتاج

- PHP 8.3 وما يلزم التطبيق من Composer packages، ومنها `spatie/browsershot`.
- Node.js 22 أو أحدث وPuppeteer المثبت من `package.json` و`package-lock.json`.
- Google Chrome أو Chromium متوافق مثبت كحزمة نظام وصالح للتشغيل headless.
- صلاحية تشغيل Node وChromium من حساب PHP worker وصلاحية قراءة ملفات الخطوط في `public/assets/fonts`.
- مساحة مؤقتة كافية و`/dev/shm` مناسب لتشغيل Chromium headless.

## التثبيت والإعداد

ثبّت Node dependencies من ملف القفل أثناء النشر:

```bash
npm ci --ignore-scripts
```

يمنع `--ignore-scripts` تنزيل متصفح ثانٍ بواسطة Puppeteer؛ ثبّت Chromium عبر مدير حزم الخادم، وحدد مساره في إعداد التطبيق:

```dotenv
INVOICE_PDF_NODE_BINARY=/usr/bin/node
INVOICE_PDF_CHROME_PATH=/usr/bin/chromium
```

عدّل المسارات بحسب التوزيعة. يجب أن يصل إليها نفس المستخدم الذي يشغل PHP-FPM أو queue worker. إذا كان Node موجوداً في `PATH` فلا حاجة إلى `INVOICE_PDF_NODE_BINARY`.

تأكد من نشر هذه الملفات مع التطبيق:

```text
public/css/invoice-document.css
public/assets/fonts/Cairo-Regular.ttf
public/assets/fonts/Cairo-Bold.ttf
public/assets/fonts/Cairo-OFL.txt
public/assets/fonts/OpenSans-Regular-webfont.woff
public/assets/fonts/OpenSans-Bold-webfont.woff
```

يقوم مسار PDF بتضمين CSS والخطوط من ملفات المشروع نفسها داخل HTML المرسل إلى Chromium. لذلك لا يعتمد على خطوط مثبتة في الخادم أو جهاز قارئ PDF.

## التحقق من بيئة الخادم

نفذ الفحوصات التالية بحساب النشر، ثم اختبر تنزيل فاتورة من مساحة المنشأة:

```bash
node --version
npm ls puppeteer
/usr/bin/chromium --version
test -r public/assets/fonts/Cairo-Regular.ttf
test -r public/assets/fonts/Cairo-Bold.ttf
php artisan optimize:clear
```

إذا فشل تشغيل Chromium، راجع سجل Laravel ومسارات `INVOICE_PDF_NODE_BINARY` و`INVOICE_PDF_CHROME_PATH` وصلاحيات المستخدم. لا تفعّل DomPDF كحل بديل لهذا المسار.
