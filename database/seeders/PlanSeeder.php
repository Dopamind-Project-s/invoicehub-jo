<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $starterCodes = [
            'INVOICES',
            'INVOICES_APPROVE',
            'INVOICES_CREATE',
            'PDF_EXPORT',
            'JOFOTARA_SUBMIT',
            'CONTACTS_MANAGEMENT',
            'PRODUCTS_MANAGEMENT',
        ];

        $businessCodes = [
            'SETTINGS_MANAGEMENT',
            'USERS_MANAGEMENT',
            'INVOICES',
            'SUPPLIERS',
            'INVOICES_APPROVE',
            'INVOICES_CREATE',
            'PDF_EXPORT',
            'JOFOTARA_SUBMIT',
            'JOFOTARA_SYNC',
            'CONTACTS_MANAGEMENT',
            'PRODUCTS_MANAGEMENT',
            'REPORTS_VIEW',
            'WHATSAPP_SHARE',
        ];

        $advancedCodes = [
            'SETTINGS_MANAGEMENT',
            'USERS_MANAGEMENT',
            'ADVANCED_REPORTS',
            'API_ACCESS',
            'AUDIT_LOGS',
            'COMPANY_USERS',
            'CUSTOMERS',
            'EMAIL_SHARE',
            'INVOICES',
            'PRODUCTS',
            'SUPPLIERS',
            'INVOICES_APPROVE',
            'INVOICES_CREATE',
            'PDF_EXPORT',
            'JOFOTARA_SUBMIT',
            'JOFOTARA_SYNC',
            'CONTACTS_MANAGEMENT',
            'PRODUCTS_MANAGEMENT',
            'REPORTS_VIEW',
            'WHATSAPP_SHARE',
        ];

        $starter = $this->plan('starter', 'باقة البداية', 'Starter', 'باقة مناسبة لتجربة إدارة المنشأة والفواتير الأساسية.', 'A starter plan for electronic invoices and essential establishment management.', 5, 35, $starterCodes, 1, false);
        $this->plan('business', 'باقة الأعمال', 'Business', 'باقة أوسع لإدارة المستخدمين والمشاركة والاعتماد والربط مع الفوترة الوطنية.', 'A business plan for users, sharing, approvals, and national e-invoicing integration.', 7, 50, $businessCodes, 2, true);
        $this->plan('advanced', 'المتقدمة', 'Advanced', 'تحليل الفواتير والمبيعات بشكل معمق واستخراج تقارير تفصيلية عن كل منتج , مورد او عميل', 'Advanced invoice and sales analytics with detailed reports for each product, supplier, or customer.', 8, 80, $advancedCodes, 3, false);

        Plan::where('slug', 'professional')->update(['is_active' => false]);

        $company = Company::where('tax_number', '9578331')->first();
        if ($company) {
            Subscription::updateOrCreate(
                ['company_id' => $company->id, 'status' => 'active'],
                ['plan_id' => $starter->id, 'starts_at' => now(), 'expires_at' => null]
            );

            $company->featureKeys()->syncWithoutDetaching($starter->featureKeys()->pluck('feature_keys.id')->all());
        }
    }

    private function plan(string $slug, string $nameAr, string $nameEn, string $descriptionAr, string $descriptionEn, float $monthly, float $yearly, array $featureCodes, int $sortOrder, bool $recommended): Plan
    {
        $plan = Plan::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $nameAr,
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'description' => $descriptionAr,
                'description_ar' => $descriptionAr,
                'description_en' => $descriptionEn,
                'price' => $monthly,
                'monthly_price' => $monthly,
                'yearly_price' => $yearly,
                'billing_cycle' => 'monthly',
                'sort_order' => $sortOrder,
                'is_active' => true,
                'is_recommended' => $recommended,
            ]
        );

        $featureIds = FeatureKey::whereIn('code', $featureCodes)->pluck('id')->all();
        $plan->featureKeys()->sync($featureIds);

        return $plan;
    }
}
