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

## 2026-06-28 — Company Details + Subscriptions Center + Seeder Cleanup

- تم تحويل صفحة تفاصيل المنشأة الإدارية إلى صفحة واحدة بدون تبويبات قديمة، مع رأس مختصر، بيانات المنشأة، ملخص تشغيلي، وبطاقة اشتراك صغيرة.
- تمت إضافة مركز اشتراكات إداري لكل منشأة عبر `/admin/companies/{company}/subscriptions` مع الاشتراك الحالي، سجل الاشتراكات، وأزرار التجديد/التجديد التلقائي/الإلغاء/إعادة التفعيل.
- تمت إضافة صفحة اشتراك لمساحة المنشأة عبر `/companies/{company}/subscriptions` لعرض الاشتراك والمزايا والسجل وإرسال طلبات ترقية/تخفيض/تجديد/إلغاء بدون دفع أو تنفيذ تلقائي.
- تمت إضافة جدول `subscription_change_requests` وحقول جاهزية دفع مستقبلية `payment_provider` و`payment_reference` على الاشتراكات بدون تنفيذ بوابات دفع.
- تم تنظيف بذور البيانات لتشمل تصنيفات، وحدات، ملفات ضريبية، منتجات، جهات اتصال، قوالب، واشتراكات تاريخية/حالية ببيانات عربية واقعية وبمفاتيح `updateOrCreate`/`updateOrInsert`.
- تم تحديث التنقل الإداري ومساحة المنشأة لإبراز الاشتراكات وتقليل العناصر المزدحمة.
- ملاحظات التكامل المستقبلي: يمكن ربط مزود الدفع لاحقاً عبر `payment_provider` و`payment_reference` و`source` مع إبقاء طلبات المستخدم معلقة حتى مراجعة الإدارة أو Webhook الدفع.

## 2026-06-28 — Subscription Center Finalization

- تمت إضافة `SubscriptionPresentationService` لتوحيد Health Badge وTimeline وRenewal Summary وPayment Placeholder وPreview للترقية/التخفيض.
- تمت إضافة جدول `subscription_events` وحقول `payment_status` و`renewal_source` و`renewed_by` على الاشتراكات لتجهيز البنية للدفع والتجديد التلقائي بدون تنفيذ Gateway.
- تم تحسين مركز الاشتراكات الإداري بعرض Timeline، Subscription Health، Renewal Summary، Subscription Events، Upgrade/Downgrade Preview، وطرق دفع Coming Soon.
- تم تحسين صفحة اشتراك المنشأة بعرض Timeline، الحالة الصحية، طرق الدفع القادمة، الطلبات، والأحداث بدون تنفيذ أي تغيير إداري مباشر.
- تمت إضافة Widget مستقل في Dashboard المنشأة بعنوان "الاشتراك الحالي" مع زر إدارة الاشتراك.
- تمت إضافة جدول مقارنة للباقات يعرض المزايا والحدود ومؤشرات API/JoFotara/PDF/Reports باستخدام ✓ أو — مع شارة Recommended.
- تمت إعادة ترتيب السيدرز إلى مجموعات System / Company / Master Data / Demo Data / Sample Data، وإضافة Seeders للـ Permissions/Roles/Landing/Subscriptions/Subscription History/Subscription Requests.
- لا يزال الربط الفعلي مع بوابات الدفع مؤجلاً؛ الحقول الحالية تكفي لربط Visa/MasterCard/CliQ/eFAWATEERcom/Bank Transfer لاحقاً عبر Provider/Reference/Status/Webhooks.
