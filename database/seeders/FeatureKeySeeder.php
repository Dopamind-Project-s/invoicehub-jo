<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FeatureKey;
use Illuminate\Database\Seeder;

class FeatureKeySeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            ['PRODUCTS_MANAGEMENT', 'إدارة المنتجات', 'Products Management', 'إدارة منتجات وخدمات المنشأة.', 'master-data'],
            ['CONTACTS_MANAGEMENT', 'إدارة العملاء والموردين', 'Contacts Management', 'إدارة العملاء والموردين وجهات الاتصال الموحدة.', 'master-data'],
            ['INVOICES_CREATE', 'إنشاء الفواتير', 'Create Invoices', 'إنشاء فواتير جديدة وحفظها كمسودات.', 'invoices'],
            ['INVOICES_APPROVE', 'اعتماد الفواتير', 'Approve Invoices', 'مراجعة واعتماد الفواتير قبل المشاركة أو التصدير.', 'invoices'],
            ['PDF_EXPORT', 'تصدير PDF', 'PDF Export', 'تحميل الفواتير بصيغة PDF قابلة للطباعة.', 'invoices'],
            ['WHATSAPP_SHARE', 'مشاركة واتساب', 'WhatsApp Sharing', 'تجهيز روابط مشاركة الفواتير عبر واتساب.', 'sharing'],
            ['JOFOTARA_SUBMIT', 'إرسال الفواتير إلى نظام الفوترة الوطني', 'JoFotara Submission', 'إرسال الفواتير المعتمدة إلى نظام الفوترة الوطني.', 'jofotara'],
            ['JOFOTARA_SYNC', 'مزامنة فواتير نظام الفوترة الوطني', 'JoFotara Sync', 'استيراد أو مزامنة الفواتير الصادرة سابقاً من نظام الفوترة الوطني.', 'jofotara'],
            ['USERS_MANAGEMENT', 'إدارة المستخدمين', 'Users Management', 'إدارة مستخدمي المنشأة وأدوارهم.', 'administration'],
            ['SETTINGS_MANAGEMENT', 'إدارة الإعدادات', 'Settings Management', 'تعديل إعدادات المنشأة والهوية البصرية.', 'administration'],
            ['REPORTS_VIEW', 'عرض التقارير', 'Reports View', 'إتاحة قراءة التقارير ولوحات المتابعة.', 'reports'],
        ];

        foreach ($features as [$code, $nameAr, $nameEn, $description, $category]) {
            FeatureKey::updateOrCreate(
                ['code' => $code],
                ['name' => $nameAr, 'name_ar' => $nameAr, 'name_en' => $nameEn, 'description' => $description, 'category' => $category, 'is_active' => true]
            );
        }
    }
}
