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
        $starterCodes = ['PRODUCTS_MANAGEMENT', 'CONTACTS_MANAGEMENT', 'INVOICES_CREATE', 'PDF_EXPORT', 'SETTINGS_MANAGEMENT'];
        $professionalCodes = array_merge($starterCodes, ['INVOICES_APPROVE', 'WHATSAPP_SHARE', 'USERS_MANAGEMENT', 'REPORTS_VIEW', 'JOFOTARA_SUBMIT', 'JOFOTARA_SYNC']);

        $starter = $this->plan('starter', 'باقة البداية', 'Starter', 'باقة مناسبة لتجربة إدارة المنشأة والفواتير الأساسية.', 'A starter plan for electronic invoices and establishment setup.', 15, 150, $starterCodes, 1, false);
        $this->plan('professional', 'باقة الأعمال', 'Professional', 'باقة أوسع لإدارة المستخدمين والمشاركة والاعتماد والربط مع الفوترة الوطنية.', 'A professional plan for teams, approvals, sharing, and national e-invoicing readiness.', 35, 350, $professionalCodes, 2, true);

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
