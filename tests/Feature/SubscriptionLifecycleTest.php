<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_subscription_within_period_returns_active(): void
    {
        [$company] = $this->companyWithSubscription(now()->subDay(), now()->addMonth());

        $access = app(SubscriptionAccessService::class)->resolve($company);

        $this->assertSame('active', $access['effective_status']);
    }

    public function test_expired_period_with_grace_returns_grace(): void
    {
        [$company] = $this->companyWithSubscription(now()->subMonth(), now()->subDay(), now()->addDays(3));

        $access = app(SubscriptionAccessService::class)->resolve($company);

        $this->assertSame('grace', $access['effective_status']);
    }

    public function test_after_grace_returns_expired(): void
    {
        [$company] = $this->companyWithSubscription(now()->subMonths(2), now()->subMonth(), now()->subDay());

        $access = app(SubscriptionAccessService::class)->resolve($company);

        $this->assertSame('expired', $access['effective_status']);
    }

    public function test_cancelled_returns_cancelled(): void
    {
        [$company, , $subscription] = $this->companyWithSubscription(now()->subDay(), now()->addMonth());
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $access = app(SubscriptionAccessService::class)->resolve($company->refresh());

        $this->assertSame('cancelled', $access['effective_status']);
    }

    public function test_suspended_company_returns_suspended(): void
    {
        [$company] = $this->companyWithSubscription(now()->subDay(), now()->addMonth());
        $company->forceFill(['status' => 'suspended', 'is_active' => false])->save();

        $access = app(SubscriptionAccessService::class)->resolve($company->refresh());

        $this->assertSame('suspended', $access['effective_status']);
    }

    public function test_no_subscription_returns_no_subscription(): void
    {
        $company = $this->company();

        $access = app(SubscriptionAccessService::class)->resolve($company);

        $this->assertSame('no_subscription', $access['effective_status']);
    }

    public function test_monthly_renewal_extends_from_current_period_end_when_active(): void
    {
        [, , $subscription] = $this->companyWithSubscription(now()->subDay(), now()->addDays(10));
        $oldEnd = $subscription->current_period_end_at->copy();

        $renewed = app(SubscriptionAccessService::class)->renew($subscription, 'monthly');

        $this->assertSame($oldEnd->toDateTimeString(), $renewed->current_period_start_at->toDateTimeString());
        $this->assertSame($oldEnd->copy()->addMonth()->toDateString(), $renewed->current_period_end_at->toDateString());
        $this->assertSame('monthly', $renewed->billing_cycle);
    }

    public function test_yearly_renewal_starts_from_now_when_expired(): void
    {
        $this->travelTo(now()->startOfSecond());
        [, , $subscription] = $this->companyWithSubscription(now()->subMonths(2), now()->subMonth(), now()->subDay());

        $renewed = app(SubscriptionAccessService::class)->renew($subscription, 'yearly');

        $this->assertSame(now()->toDateTimeString(), $renewed->current_period_start_at->toDateTimeString());
        $this->assertSame(now()->copy()->addYear()->toDateString(), $renewed->current_period_end_at->toDateString());
        $this->assertSame('active', $renewed->status);
    }

    public function test_company_feature_keys_still_work_as_manual_extra_grants(): void
    {
        [$company] = $this->companyWithSubscription(now()->subDay(), now()->addMonth(), null, ['INVOICES_CREATE']);
        $apiFeature = FeatureKey::firstOrCreate(['code' => 'API_ACCESS'], ['name' => 'API access', 'is_active' => true]);
        $company->featureKeys()->attach($apiFeature);

        $access = app(SubscriptionAccessService::class)->resolve($company->refresh());

        $this->assertTrue($access['allowed_feature_keys']->contains('code', 'API_ACCESS'));
    }

    public function test_expired_blocks_can_submit_to_jofotara(): void
    {
        [$company] = $this->companyWithSubscription(now()->subMonths(2), now()->subMonth(), now()->subDay(), ['JOFOTARA_SUBMIT']);

        $access = app(SubscriptionAccessService::class)->resolve($company);

        $this->assertFalse($access['canSubmitToJofotara']);
    }

    public function test_active_allows_can_submit_to_jofotara_if_feature_exists(): void
    {
        [$company] = $this->companyWithSubscription(now()->subDay(), now()->addMonth(), null, ['JOFOTARA_SUBMIT']);

        $access = app(SubscriptionAccessService::class)->resolve($company);

        $this->assertTrue($access['canSubmitToJofotara']);
    }

    public function test_subscriptions_expire_command_transitions_statuses_safely(): void
    {
        [, , $graceSubscription] = $this->companyWithSubscription(now()->subMonth(), now()->subDay(), now()->addDays(2));
        [, , $expiredSubscription] = $this->companyWithSubscription(now()->subMonths(2), now()->subMonth(), now()->subDay());

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame('grace', $graceSubscription->refresh()->status);
        $this->assertSame('expired', $expiredSubscription->refresh()->status);
    }

    /** @return array{0: Company, 1: Plan, 2: Subscription} */
    private function companyWithSubscription($start, $end, $graceEnd = null, array $featureCodes = ['INVOICES_CREATE']): array
    {
        $company = $this->company();
        $plan = Plan::create([
            'name' => 'Business',
            'slug' => 'business-'.uniqid(),
            'price' => 7,
            'monthly_price' => 7,
            'yearly_price' => 50,
            'billing_cycle' => 'monthly',
            'grace_period_days' => 7,
            'currency' => 'JOD',
        ]);

        foreach ($featureCodes as $code) {
            $feature = FeatureKey::firstOrCreate(['code' => $code], ['name' => $code, 'is_active' => true]);
            $plan->featureKeys()->syncWithoutDetaching($feature->id);
        }

        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'starts_at' => $start,
            'expires_at' => $end,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'current_period_start_at' => $start,
            'current_period_end_at' => $end,
            'grace_ends_at' => $graceEnd ?: $end->copy()->addDays(7),
            'source' => 'admin',
            'currency' => 'JOD',
        ]);

        return [$company, $plan->refresh()->load('featureKeys'), $subscription->refresh()];
    }

    private function company(): Company
    {
        return Company::create([
            'legal_name_ar' => 'شركة دورة اشتراك '.uniqid(),
            'tax_number' => (string) random_int(100000000, 999999999),
            'status' => 'active',
            'is_active' => true,
            'default_language' => 'ar',
            'default_currency' => 'JOD',
        ]);
    }
}
