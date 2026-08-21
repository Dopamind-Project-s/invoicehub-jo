<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminSubscriptionService
{
    private const RENEWABLE = ['active', 'expired', 'grace', 'trial', 'trialing'];

    private const ACTIVE = ['active', 'grace', 'trial', 'trialing'];

    public function __construct(private readonly SubscriptionEventLogger $events, private readonly AuditLogger $audit) {}

    public function create(Company $company, array $data, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $data, $actor): Subscription {
            $lockedCompany = Company::lockForUpdate()->findOrFail($company->id);
            $start = Carbon::parse($data['start_date'] ?? today(), config('app.timezone'))->startOfDay();
            if ($this->hasOverlap($lockedCompany, $start)) {
                throw ValidationException::withMessages(['plan_id' => 'يوجد اشتراك فعال متداخل لهذه المنشأة بالفعل.']);
            }
            $plan = Plan::whereKey($data['plan_id'])->where('is_active', true)->first();
            if (! $plan) {
                throw ValidationException::withMessages(['plan_id' => 'الباقة المحددة غير متاحة.']);
            }
            $cycle = (string) $data['billing_cycle'];
            $price = $this->priceFor($plan, $cycle);
            $end = $this->calculateEndDate($start, $cycle);
            $subscription = $lockedCompany->subscriptions()->create([
                'plan_id' => $plan->id, 'starts_at' => $start, 'expires_at' => $end, 'status' => 'active',
                'billing_cycle' => $cycle, 'current_period_start_at' => $start, 'current_period_end_at' => $end,
                'grace_ends_at' => $end->copy()->addDays((int) $plan->grace_period_days), 'source' => 'admin_direct',
                'renewal_source' => 'admin', 'renewed_by' => $actor->id, 'price_amount' => $price,
                'currency' => $plan->currency ?: 'JOD', 'auto_renew' => (bool) ($data['auto_renew'] ?? false),
                'payment_status' => 'not_required', 'metadata' => filled($data['notes'] ?? null) ? ['internal_notes' => $data['notes']] : null,
            ]);
            $lockedCompany->featureKeys()->syncWithoutDetaching($plan->featureKeys()->pluck('feature_keys.id'));
            $this->events->record($lockedCompany, $subscription, 'created', 'admin_direct', $actor, ['billing_cycle' => $cycle]);
            $this->audit->record('admin.subscription.direct_created', $subscription, [], ['source' => 'admin_direct', 'billing_cycle' => $cycle], userId: $actor->id);

            return $subscription;
        }, 3);
    }

    public function renew(Company $company, string $cycle, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $cycle, $actor): Subscription {
            $subscription = $this->lockedCurrent($company);
            if (! in_array($subscription->status, self::RENEWABLE, true)) {
                throw ValidationException::withMessages(['subscription' => 'حالة الاشتراك الحالية لا تسمح بالتجديد.']);
            }
            $before = $subscription->only(['billing_cycle', 'current_period_start_at', 'current_period_end_at', 'expires_at', 'status']);
            // Business rule: extend from the current end while it is today or later; otherwise restart now.
            $periodEnd = $subscription->current_period_end_at ?: $subscription->expires_at;
            $start = $periodEnd && $periodEnd->greaterThanOrEqualTo(now()) ? $periodEnd->copy() : now();
            $end = $this->calculateEndDate($start, $cycle);
            $subscription->update([
                'billing_cycle' => $cycle, 'current_period_start_at' => $start, 'current_period_end_at' => $end,
                'expires_at' => $end, 'grace_ends_at' => $end->copy()->addDays((int) $subscription->plan->grace_period_days),
                'status' => 'active', 'status_reason' => null, 'ended_at' => null, 'renewed_at' => now(),
                'renewal_source' => 'admin', 'renewed_by' => $actor->id, 'price_amount' => $this->priceFor($subscription->plan, $cycle),
                'payment_status' => 'not_required',
            ]);
            $this->events->record($company, $subscription, 'renewed', 'admin', $actor, ['billing_cycle' => $cycle]);
            $this->audit->record('admin.subscription.renewed', $subscription, $before, $subscription->only(['billing_cycle', 'current_period_start_at', 'current_period_end_at', 'expires_at', 'status']), userId: $actor->id);

            return $subscription;
        }, 3);
    }

    public function toggleAutoRenew(Company $company, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $actor): Subscription {
            $subscription = $this->lockedCurrent($company);
            if (! in_array($subscription->status, self::ACTIVE, true)) {
                throw ValidationException::withMessages(['subscription' => 'لا يمكن تعديل التجديد التلقائي في حالة الاشتراك الحالية.']);
            }
            $before = ['auto_renew' => $subscription->auto_renew];
            $subscription->update(['auto_renew' => ! $subscription->auto_renew]);
            $this->events->record($company, $subscription, $subscription->auto_renew ? 'auto_renew_enabled' : 'auto_renew_disabled', 'admin', $actor);
            $this->audit->record('admin.subscription.auto_renew_toggled', $subscription, $before, ['auto_renew' => $subscription->auto_renew], userId: $actor->id);

            return $subscription;
        }, 3);
    }

    public function cancel(Company $company, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $actor): Subscription {
            $subscription = $this->lockedCurrent($company);
            if ($subscription->status === 'cancelled') {
                throw ValidationException::withMessages(['subscription' => 'الاشتراك ملغي بالفعل.']);
            }
            if (! in_array($subscription->status, self::RENEWABLE, true)) {
                throw ValidationException::withMessages(['subscription' => 'حالة الاشتراك الحالية لا تسمح بالإلغاء.']);
            }
            $before = $subscription->only(['status', 'cancelled_at', 'auto_renew']);
            $subscription->update(['status' => 'cancelled', 'cancelled_at' => now(), 'auto_renew' => false, 'status_reason' => 'admin_cancelled']);
            $this->events->record($company, $subscription, 'cancelled', 'admin', $actor);
            $this->audit->record('admin.subscription.cancelled', $subscription, $before, $subscription->only(['status', 'cancelled_at', 'auto_renew']), userId: $actor->id);

            return $subscription;
        }, 3);
    }

    public function reactivate(Company $company, User $actor): Subscription
    {
        return DB::transaction(function () use ($company, $actor): Subscription {
            $subscription = $this->lockedCurrent($company);
            if ($subscription->status !== 'cancelled') {
                throw ValidationException::withMessages(['subscription' => 'إعادة التفعيل متاحة للاشتراك الملغي فقط.']);
            }
            if ($this->hasOverlap($company, now(), $subscription->id)) {
                throw ValidationException::withMessages(['subscription' => 'يوجد اشتراك فعال آخر لهذه المنشأة.']);
            }
            $before = $subscription->only(['status', 'cancelled_at', 'current_period_start_at', 'current_period_end_at']);
            $start = now();
            $cycle = in_array($subscription->billing_cycle, ['monthly', 'yearly'], true) ? $subscription->billing_cycle : 'monthly';
            $end = $this->calculateEndDate($start, $cycle);
            $subscription->update(['status' => 'active', 'cancelled_at' => null, 'ended_at' => null, 'status_reason' => null, 'current_period_start_at' => $start, 'current_period_end_at' => $end, 'expires_at' => $end]);
            $this->events->record($company, $subscription, 'reactivated', 'admin', $actor);
            $this->audit->record('admin.subscription.reactivated', $subscription, $before, $subscription->only(['status', 'cancelled_at', 'current_period_start_at', 'current_period_end_at']), userId: $actor->id);

            return $subscription;
        }, 3);
    }

    public function calculateEndDate(Carbon $start, string $cycle): Carbon
    {
        return match ($cycle) {
            'monthly' => $start->copy()->addMonthNoOverflow(),
            'yearly' => $start->copy()->addYearNoOverflow(),
            default => throw ValidationException::withMessages(['billing_cycle' => 'دورة الفوترة غير مدعومة.']),
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

    private function hasOverlap(Company $company, Carbon $start, ?int $exceptId = null): bool
    {
        return $company->subscriptions()->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->whereIn('status', self::ACTIVE)
            ->where(fn ($query) => $query->whereNull('current_period_end_at')->orWhere('current_period_end_at', '>=', $start))
            ->lockForUpdate()->exists();
    }

    private function priceFor(Plan $plan, string $cycle): mixed
    {
        $price = match ($cycle) {
            'monthly' => $plan->monthly_price,
            'yearly' => $plan->yearly_price,
            default => throw ValidationException::withMessages(['billing_cycle' => 'دورة الفوترة غير مدعومة.']),
        };
        if ($price === null) {
            throw ValidationException::withMessages(['billing_cycle' => 'الباقة لا تدعم دورة الفوترة المحددة.']);
        }

        return $price;
    }
}
