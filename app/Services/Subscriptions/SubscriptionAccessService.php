<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\Company;
use App\Models\FeatureKey;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SubscriptionAccessService
{
    /** @return array<string,mixed> */
    public function resolve(Company $company): array
    {
        $subscription = $this->currentSubscription($company);
        $plan = $subscription?->plan;
        $status = $this->effectiveStatus($company, $subscription);
        $features = $this->effectiveFeatures($company, $subscription, $status);

        return [
            'subscription' => $subscription,
            'plan' => $plan,
            'billing_cycle' => $subscription?->billing_cycle,
            'period_start' => $subscription?->current_period_start_at ?: $subscription?->starts_at,
            'period_end' => $subscription?->current_period_end_at ?: $subscription?->expires_at,
            'grace_end' => $subscription?->grace_ends_at,
            'effective_status' => $status,
            'allowed_feature_keys' => $features,
            'restriction_mode' => $this->restrictionMode($status),
            'canAccessDashboard' => $this->canAccessDashboard($status),
            'canViewInvoices' => $this->canViewInvoices($status),
            'canCreateInvoices' => $this->canCreateInvoices($status, $features),
            'canSubmitToJofotara' => $this->canSubmitToJofotara($status, $features),
            'canUseApi' => $this->canUseApi($status, $features),
            'canExportPdf' => $this->canExportPdf($status, $features),
            'canManageSettings' => $this->canManageSettings($status, $features),
            'days_remaining' => $this->daysRemaining($subscription),
        ];
    }

    public function currentSubscription(Company $company): ?Subscription
    {
        return $company->subscriptions()
            ->with(['plan.featureKeys' => fn ($query) => $query->where('is_active', true)->orderBy('category')->orderBy('code')])
            ->whereIn('status', ['trial', 'trialing', 'active', 'grace', 'expired', 'cancelled'])
            ->latest('current_period_start_at')
            ->latest('starts_at')
            ->latest('id')
            ->first();
    }

    public function effectiveStatus(Company $company, ?Subscription $subscription = null): string
    {
        if ($company->isSuspended()) {
            return 'suspended';
        }

        $subscription ??= $this->currentSubscription($company);

        if (! $subscription) {
            return 'no_subscription';
        }

        if ($subscription->status === 'cancelled') {
            return 'cancelled';
        }

        $now = now();
        $periodEnd = $subscription->current_period_end_at ?: $subscription->expires_at;
        $graceEnd = $subscription->grace_ends_at;

        if (! $periodEnd || $now->lessThanOrEqualTo($periodEnd)) {
            return in_array($subscription->status, ['trial', 'trialing'], true) || $subscription->billing_cycle === 'trial' ? 'trialing' : 'active';
        }

        if ($graceEnd && $now->lessThanOrEqualTo($graceEnd)) {
            return 'grace';
        }

        return 'expired';
    }

    /** @return Collection<int,FeatureKey> */
    public function effectiveFeatures(Company $company, ?Subscription $subscription = null, ?string $status = null): Collection
    {
        $subscription ??= $this->currentSubscription($company);
        $status ??= $this->effectiveStatus($company, $subscription);

        if (in_array($status, ['expired', 'cancelled', 'suspended', 'no_subscription'], true)) {
            return collect();
        }

        $planFeatures = $subscription?->plan?->featureKeys ?: collect();
        $manualFeatures = $company->loadMissing('featureKeys')->featureKeys->where('is_active', true);

        return $planFeatures
            ->merge($manualFeatures)
            ->unique('code')
            ->values();
    }

    public function renew(Subscription $subscription, string $billingCycle): Subscription
    {
        $plan = $subscription->plan ?: Plan::findOrFail($subscription->plan_id);
        $periodEnd = $subscription->current_period_end_at ?: $subscription->expires_at;
        $start = ($periodEnd && now()->lessThanOrEqualTo($periodEnd)) ? $periodEnd->copy() : now();
        $end = $billingCycle === 'yearly' ? $start->copy()->addYear() : $start->copy()->addMonth();

        $subscription->forceFill([
            'billing_cycle' => $billingCycle,
            'starts_at' => $subscription->starts_at ?: $start,
            'expires_at' => $end,
            'current_period_start_at' => $start,
            'current_period_end_at' => $end,
            'grace_ends_at' => $end->copy()->addDays((int) ($plan->grace_period_days ?? 7)),
            'status' => 'active',
            'status_reason' => null,
            'ended_at' => null,
            'cancelled_at' => null,
            'renewed_at' => now(),
            'source' => 'admin',
            'price_amount' => $billingCycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price,
            'currency' => $plan->currency ?: 'JOD',
        ])->save();

        return $subscription->refresh();
    }

    public function restrictionMode(string $status): string
    {
        return match ($status) {
            'active', 'trialing' => 'full',
            'grace' => 'grace',
            'expired', 'cancelled', 'no_subscription' => 'read_only',
            'suspended' => 'restricted',
            default => 'restricted',
        };
    }

    /** @param Collection<int,FeatureKey> $features */
    public function canCreateInvoices(string $status, Collection $features): bool
    {
        return in_array($status, ['active', 'trialing', 'grace'], true) && $this->hasFeature($features, ['INVOICES_CREATE', 'INVOICES']);
    }

    /** @param Collection<int,FeatureKey> $features */
    public function canSubmitToJofotara(string $status, Collection $features): bool
    {
        return in_array($status, ['active', 'trialing', 'grace'], true) && $this->hasFeature($features, ['JOFOTARA_SUBMIT']);
    }

    /** @param Collection<int,FeatureKey> $features */
    public function canUseApi(string $status, Collection $features): bool
    {
        return in_array($status, ['active', 'trialing'], true) && $this->hasFeature($features, ['API_ACCESS']);
    }

    /** @param Collection<int,FeatureKey> $features */
    public function canExportPdf(string $status, Collection $features): bool
    {
        return in_array($status, ['active', 'trialing', 'grace'], true) && $this->hasFeature($features, ['PDF_EXPORT']);
    }

    /** @param Collection<int,FeatureKey> $features */
    public function canManageSettings(string $status, Collection $features): bool
    {
        return in_array($status, ['active', 'trialing', 'grace'], true) && $this->hasFeature($features, ['SETTINGS_MANAGEMENT']);
    }

    public function canAccessDashboard(string $status): bool
    {
        return $status !== 'suspended';
    }

    public function canViewInvoices(string $status): bool
    {
        return ! in_array($status, ['suspended', 'no_subscription'], true);
    }

    private function hasFeature(Collection $features, array $codes): bool
    {
        return $features->contains(fn (FeatureKey $feature): bool => in_array($feature->code, $codes, true));
    }

    private function daysRemaining(?Subscription $subscription): ?int
    {
        $end = $subscription?->current_period_end_at ?: $subscription?->expires_at;

        if (! $end) {
            return null;
        }

        return (int) max(0, Carbon::now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false));
    }
}
