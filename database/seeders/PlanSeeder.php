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

        $starter = $this->plan('starter', 'باقة البداية', 'باقة مناسبة لتجربة إدارة المنشأة والفواتير الأساسية.', 15, 150, $starterCodes);
        $this->plan('professional', 'باقة الأعمال', 'باقة أوسع لإدارة المستخدمين والمشاركة والاعتماد.', 35, 350, $professionalCodes);

        $company = Company::where('tax_number', '9578331')->first();
        if ($company) {
            Subscription::updateOrCreate(
                ['company_id' => $company->id, 'status' => 'active'],
                ['plan_id' => $starter->id, 'starts_at' => now(), 'expires_at' => null]
            );

            $company->featureKeys()->syncWithoutDetaching($starter->featureKeys()->pluck('feature_keys.id')->all());
        }
    }

    private function plan(string $slug, string $name, string $description, float $monthly, float $yearly, array $featureCodes): Plan
    {
        $plan = Plan::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'price' => $monthly,
                'monthly_price' => $monthly,
                'yearly_price' => $yearly,
                'billing_cycle' => 'monthly',
                'is_active' => true,
            ]
        );

        $featureIds = FeatureKey::whereIn('code', $featureCodes)->pluck('id')->all();
        $plan->featureKeys()->sync($featureIds);

        return $plan;
    }
}
