<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureSubscriptionFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_feature_keys_are_seeded_by_migration(): void
    {
        $expected = ['COMPANY_USERS', 'PRODUCTS', 'CUSTOMERS', 'SUPPLIERS', 'INVOICES', 'PDF_EXPORT', 'WHATSAPP_SHARE', 'EMAIL_SHARE', 'JOFOTARA_SUBMIT', 'JOFOTARA_SYNC', 'ADVANCED_REPORTS', 'API_ACCESS', 'AUDIT_LOGS'];

        $this->assertSame($expected, FeatureKey::orderBy('id')->pluck('code')->all());
    }

    public function test_company_features_and_subscription_relationships_are_available(): void
    {
        $company = Company::create(['legal_name_ar' => 'شركة اشتراك', 'tax_number' => '12121212']);
        $feature = FeatureKey::where('code', 'AUDIT_LOGS')->firstOrFail();
        $company->featureKeys()->attach($feature);
        $plan = Plan::create(['name' => 'Trial', 'slug' => 'trial', 'price' => 0, 'billing_cycle' => 'monthly']);
        Subscription::create(['company_id' => $company->id, 'plan_id' => $plan->id, 'starts_at' => now(), 'expires_at' => now()->addMonth(), 'status' => 'active']);

        $this->assertTrue($company->featureKeys()->where('code', 'AUDIT_LOGS')->exists());
        $this->assertSame('trial', $company->activeSubscription()->first()?->plan?->slug);
    }
}
