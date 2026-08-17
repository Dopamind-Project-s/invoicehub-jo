<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminSubscriptionService
{
    public function __construct(private readonly SubscriptionEventLogger $events) {}

    public function create(Company $company, array $data, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $data, $actor): Subscription {
            $lockedCompany = Company::lockForUpdate()->findOrFail($company->id);
            if ($lockedCompany->subscriptions()->whereIn('status', ['active', 'trial', 'trialing', 'grace'])->exists()) {
                throw ValidationException::withMessages(['plan_id' => 'يوجد اشتراك فعال لهذه المنشأة بالفعل.']);
            }
            $plan = Plan::whereKey($data['plan_id'])->where('is_active', true)->first();
            if (! $plan) {
                throw ValidationException::withMessages(['plan_id' => 'الباقة المحددة غير متاحة.']);
            }
            $cycle = $data['billing_cycle'];
            $start = Carbon::parse($data['start_date'] ?? today())->startOfDay();
            $end = $this->calculateEndDate($start, $cycle);
            $subscription = $lockedCompany->subscriptions()->create([
                'plan_id' => $plan->id, 'starts_at' => $start, 'expires_at' => $end, 'status' => 'active',
                'billing_cycle' => $cycle, 'current_period_start_at' => $start, 'current_period_end_at' => $end,
                'grace_ends_at' => $end->copy()->addDays((int) $plan->grace_period_days), 'source' => 'admin_direct',
                'renewal_source' => 'admin', 'renewed_by' => $actor->id,
                'price_amount' => $cycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price,
                'currency' => $plan->currency ?: 'JOD', 'auto_renew' => (bool) ($data['auto_renew'] ?? false),
                'payment_status' => 'not_required', 'metadata' => filled($data['notes'] ?? null) ? ['internal_notes' => $data['notes']] : null,
            ]);
            $lockedCompany->featureKeys()->syncWithoutDetaching($plan->featureKeys()->pluck('feature_keys.id'));
            $this->events->record($lockedCompany, $subscription, 'created', 'admin_direct', $actor, ['billing_cycle' => $cycle]);

            return $subscription;
        }, 3);
    }

    public function renew(Company $company, string $cycle, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $cycle, $actor): Subscription {
            $subscription = $this->lockedCurrent($company);
            if ($subscription->status === 'cancelled') {
                throw ValidationException::withMessages(['subscription' => 'يجب إعادة تفعيل الاشتراك الملغي قبل تجديده.']);
            }
            $start = ($subscription->current_period_end_at && $subscription->current_period_end_at->isFuture()) ? $subscription->current_period_end_at->copy() : now();
            $end = $this->calculateEndDate($start, $cycle);
            $subscription->update(['billing_cycle' => $cycle, 'current_period_start_at' => $start, 'current_period_end_at' => $end, 'expires_at' => $end, 'grace_ends_at' => $end->copy()->addDays((int) $subscription->plan->grace_period_days), 'status' => 'active', 'renewed_at' => now(), 'renewal_source' => 'admin', 'renewed_by' => $actor->id, 'price_amount' => $cycle === 'yearly' ? $subscription->plan->yearly_price : $subscription->plan->monthly_price, 'payment_status' => 'not_required']);
            $this->events->record($company, $subscription, 'renewed', 'admin', $actor, ['billing_cycle' => $cycle]);

            return $subscription;
        }, 3);
    }

    public function toggleAutoRenew(Company $company, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $actor) {
            $s = $this->lockedCurrent($company);
            $s->update(['auto_renew' => ! $s->auto_renew]);
            $this->events->record($company, $s, $s->auto_renew ? 'auto_renew_enabled' : 'auto_renew_disabled', 'admin', $actor);

            return $s;
        }, 3);
    }

    public function cancel(Company $company, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $actor) {
            $s = $this->lockedCurrent($company);
            if ($s->status === 'cancelled') {
                throw ValidationException::withMessages(['subscription' => 'الاشتراك ملغي بالفعل.']);
            } $s->update(['status' => 'cancelled', 'cancelled_at' => now(), 'auto_renew' => false, 'status_reason' => 'admin_cancelled']);
            $this->events->record($company, $s, 'cancelled', 'admin', $actor);

            return $s;
        }, 3);
    }

    public function reactivate(Company $company, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $actor) {
            $s = $this->lockedCurrent($company);
            if ($s->status !== 'cancelled') {
                throw ValidationException::withMessages(['subscription' => 'إعادة التفعيل متاحة للاشتراك الملغي فقط.']);
            } $start = now();
            $end = $this->calculateEndDate($start, in_array($s->billing_cycle, ['monthly', 'yearly'], true) ? $s->billing_cycle : 'monthly');
            $s->update(['status' => 'active', 'cancelled_at' => null, 'ended_at' => null, 'status_reason' => null, 'current_period_start_at' => $start, 'current_period_end_at' => $end, 'expires_at' => $end]);
            $this->events->record($company, $s, 'reactivated', 'admin', $actor);

            return $s;
        }, 3);
    }

    public function calculateEndDate(Carbon $start, string $cycle): Carbon
    {
        return match ($cycle) {
            'monthly' => $start->copy()->addMonthNoOverflow(), 'yearly' => $start->copy()->addYear(), default => throw ValidationException::withMessages(['billing_cycle' => 'دورة الفوترة غير مدعومة.'])
        };
    }

    private function lockedCurrent(Company $company): Subscription
    {
        $subscription = Subscription::with('plan')->where('company_id', $company->id)->latest('current_period_start_at')->latest('id')->lockForUpdate()->first();
        if (! $subscription) {
            throw ValidationException::withMessages(['subscription' => 'لا يوجد اشتراك مسجل لهذه المنشأة.']);
        }
        if (! $subscription->plan?->is_active) {
            throw ValidationException::withMessages(['subscription' => 'الباقة المرتبطة بالاشتراك غير متاحة.']);
        }

        return $subscription;
    }
}
