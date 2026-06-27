# Subscription Lifecycle Phase 1 — Dates, Access Service & Admin Renewal Foundation

## تم تنفيذه

- إضافة حقول دورة حياة الاشتراك إلى `subscriptions` مع backfill آمن للاشتراكات الحالية.
- إضافة حقول دورة حياة الخطط إلى `plans` بدون إزالة الأسعار الحالية.
- إنشاء `App\Services\Subscriptions\SubscriptionAccessService` لحساب الحالة الفعالة والمزايا المسموحة وقيود الوصول.
- تحديث مساعدات `Company` للاشتراك الفعال وفترة السماح والانتهاء.
- عرض حالة الاشتراك وتواريخ الفترة وفترة السماح ودورة الفوترة في صفحة تفاصيل المنشأة للإدارة.
- إضافة إجراءات تجديد يدوية شهرية وسنوية من لوحة الإدارة مع audit log.
- إضافة الأمر `subscriptions:expire` لتحديث حالات الاشتراكات إلى `grace` أو `expired` بدون تعطيل المنشأة أو حذف المزايا.
- تسجيل الأمر في الجدولة اليومية عبر `routes/console.php`.
- إضافة اختبارات Phase 1 لسلوك الخدمة والتجديد والأمر المجدول.

## Backfill Behavior

- `billing_cycle` يصبح `manual` إذا لم يكن معروفًا.
- `current_period_start_at` يأخذ `starts_at` ثم `created_at` ثم `now`.
- `current_period_end_at` يأخذ `expires_at` إذا كان موجودًا؛ وإلا يتم تعيين سنة واحدة من بداية الفترة للاشتراكات الحالية/التجريبية.
- `expires_at` يساوي `current_period_end_at` عند غيابه.
- `grace_ends_at` يساوي نهاية الفترة + 7 أيام عند غيابه.
- `currency` يصبح `JOD` و`source` يصبح `admin`.

## حدود Phase 1

- لا يوجد payment gateway.
- لا يوجد auto renew فعلي.
- لا يوجد proration.
- لم يتم تغيير JoFotara XML أو QR أو UUID أو ICV أو PIH أو سير الفواتير.
- لم يتم حذف `company_feature_keys`; ما زالت تعمل كمنح يدوية إضافية بجانب مزايا الخطة.

## Remaining Risks

- بعض الشاشات القديمة قد تستمر بالاعتماد مباشرة على `company_feature_keys` حتى يتم توحيد البوابات في Phase 2.
- حالة `subscriptions.status` الأصلية كانت enum في بعض قواعد البيانات؛ يجب التأكد في بيئات MySQL القديمة من قبول قيمة `grace` قبل التفعيل الإنتاجي.
- التجديد اليدوي لا يربط بسند قبض أو فاتورة اشتراك بعد.

## Recommended Phase 2

- إضافة جدول `subscription_events` لتاريخ كل انتقال وتجديد.
- إضافة جدول `subscription_changes` للترقية/التخفيض المجدول.
- توحيد بوابات الوصول في middleware أو policy لكل وظائف الفواتير وJoFotara وAPI.
- إضافة شاشة إدارية مخصصة للاشتراكات مع تقارير expiring soon وexpired وgrace.
- تجهيز واجهة payment-ready بدون تنفيذ gateway كامل.
