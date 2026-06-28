<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionEventLogger;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('tax_number', '9578331')->first();
        $starter = Plan::where('slug', 'starter')->first();
        if (! $company || ! $starter) return;
        $now = now();
        $subscription = Subscription::updateOrCreate(
            ['company_id' => $company->id, 'source' => 'demo-current'],
            ['plan_id' => $starter->id, 'status' => 'active', 'billing_cycle' => 'monthly', 'starts_at' => $now->copy()->subDays(5), 'expires_at' => $now->copy()->addDays(25), 'current_period_start_at' => $now->copy()->subDays(5), 'current_period_end_at' => $now->copy()->addDays(25), 'grace_ends_at' => $now->copy()->addDays(32), 'renewed_at' => $now->copy()->subDays(5), 'price_amount' => $starter->monthly_price, 'currency' => 'JOD', 'auto_renew' => true, 'payment_provider' => null, 'payment_reference' => null, 'payment_status' => 'not_started', 'renewal_source' => 'admin']
        );
        $company->featureKeys()->syncWithoutDetaching($starter->featureKeys()->pluck('feature_keys.id')->all());
        app(SubscriptionEventLogger::class)->record($company, $subscription, 'subscription_created', 'demo', null, ['occurred_at' => $subscription->starts_at]);
        app(SubscriptionEventLogger::class)->record($company, $subscription, 'renewed', 'admin', null, ['occurred_at' => $subscription->renewed_at, 'billing_cycle' => 'monthly']);
        app(SubscriptionEventLogger::class)->record($company, $subscription, 'auto_renew_enabled', 'admin', null, ['occurred_at' => $subscription->renewed_at]);
    }
}
