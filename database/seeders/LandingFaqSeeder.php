<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LandingFaq;
use Illuminate\Database\Seeder;

class LandingFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['هل InvoSync مناسب للمنشآت الصغيرة؟', 'Is InvoSync suitable for small establishments?', 'نعم، صُمم InvoSync ليبدأ مع المنشآت الصغيرة ثم يتوسع معها لإدارة الفواتير والعملاء والمنتجات.', 'Yes, InvoSync is designed to start with small establishments and scale with them.', 'general', 1, true],
            ['هل يدعم الربط مع نظام الفوترة الوطني؟', 'Does it support national e-invoicing integration?', 'تتضمن الخطة مساراً واضحاً للربط مع نظام الفوترة الوطني وإدارة بيانات المنشأة المطلوبة للفوترة الإلكترونية.', 'The roadmap supports national e-invoicing integration and establishment data management.', 'jofotara', 2, true],
            ['هل يمكن مشاركة الفاتورة مع العميل؟', 'Can invoices be shared with customers?', 'يمكن تصدير الفواتير بصيغة PDF ومشاركتها بسهولة مع العملاء، مع دعم مشاركة واتساب ضمن مزايا الباقات.', 'Invoices can be exported as PDF and shared easily with customers.', 'features', 3, true],
            ['هل يمكن إدارة أكثر من منشأة؟', 'Can I manage multiple establishments?', 'يدعم النظام هيكلة قابلة للتوسع لإدارة منشآت متعددة حسب صلاحيات المستخدمين والباقات.', 'The system supports a scalable structure for multiple establishments.', 'general', 4, true],
            ['سؤال غير منشور', 'Inactive question', 'هذا السؤال للتأكد من أن الصفحة تعرض الأسئلة الفعالة فقط.', 'Inactive answer.', 'internal', 99, false],
        ];

        foreach ($faqs as [$questionAr, $questionEn, $answerAr, $answerEn, $category, $sortOrder, $active]) {
            LandingFaq::updateOrCreate(['question_ar' => $questionAr], ['question_en' => $questionEn, 'answer_ar' => $answerAr, 'answer_en' => $answerEn, 'category' => $category, 'sort_order' => $sortOrder, 'is_active' => $active]);
        }
    }
}
