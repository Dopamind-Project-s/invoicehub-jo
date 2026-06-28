<?php

declare(strict_types=1);

namespace App\Services\Subscriptions;

use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SubscriptionPresentationService
{
    public function health(?Subscription $subscription, string $effectiveStatus): array
    {
        if (! $subscription) {
            return ['key' => 'none', 'label' => 'No Subscription', 'class' => 'bg-secondary'];
        }

        $periodEnd = $subscription->current_period_end_at ?: $subscription->expires_at;
        if ($effectiveStatus === 'active' && $periodEnd && now()->diffInDays($periodEnd, false) <= 7) {
            return ['key' => 'renew_soon', 'label' => 'Renew Soon', 'class' => 'bg-warning text-dark'];
        }

        return match ($effectiveStatus) {
            'trialing', 'trial' => ['key' => 'trial', 'label' => 'Trial', 'class' => 'bg-info text-dark'],
            'active' => ['key' => 'active', 'label' => 'Active', 'class' => 'bg-success'],
            'grace' => ['key' => 'grace', 'label' => 'Grace', 'class' => 'bg-warning text-dark'],
            'expired' => ['key' => 'expired', 'label' => 'Expired', 'class' => 'bg-danger'],
            'cancelled' => ['key' => 'cancelled', 'label' => 'Cancelled', 'class' => 'bg-dark'],
            'suspended' => ['key' => 'suspended', 'label' => 'Suspended', 'class' => 'bg-secondary'],
            default => ['key' => $effectiveStatus, 'label' => str($effectiveStatus)->headline()->toString(), 'class' => 'bg-secondary'],
        };
    }

    public function timeline(?Subscription $subscription, string $effectiveStatus): array
    {
        $periodStart = $subscription?->current_period_start_at ?: $subscription?->starts_at;
        $periodEnd = $subscription?->current_period_end_at ?: $subscription?->expires_at;
        $nextRenewal = $subscription?->auto_renew ? $periodEnd : null;

        return [
            ['label' => 'بداية الاشتراك', 'value' => $this->date($periodStart), 'state' => 'done'],
            ['label' => 'آخر تجديد', 'value' => $this->date($subscription?->renewed_at), 'state' => $subscription?->renewed_at ? 'done' : 'muted'],
            ['label' => 'انتهاء الاشتراك', 'value' => $this->date($periodEnd), 'state' => $effectiveStatus === 'expired' ? 'danger' : 'active'],
            ['label' => 'التجديد القادم', 'value' => $this->date($nextRenewal), 'state' => $subscription?->auto_renew ? 'active' : 'muted'],
            ['label' => 'فترة السماح', 'value' => $this->date($subscription?->grace_ends_at), 'state' => $effectiveStatus === 'grace' ? 'warning' : 'muted'],
            ['label' => 'Auto Renew', 'value' => $subscription?->auto_renew ? 'مفعل' : 'غير مفعل', 'state' => $subscription?->auto_renew ? 'done' : 'muted'],
            ['label' => 'الحالة الحالية', 'value' => $this->health($subscription, $effectiveStatus)['label'], 'state' => 'active'],
        ];
    }

    public function renewalSummary(?Subscription $subscription): array
    {
        $metadata = $subscription?->metadata ?: [];

        return [
            'label' => $subscription?->renewed_at ? 'آخر عملية تجديد' : 'لا توجد عملية تجديد مسجلة',
            'date' => $this->date($subscription?->renewed_at),
            'type' => $subscription?->billing_cycle ?: '—',
            'source' => $subscription?->renewal_source ?: $subscription?->source ?: '—',
            'actor' => $metadata['renewed_by_name'] ?? $metadata['actor_name'] ?? '—',
        ];
    }

    public function paymentMethods(): array
    {
        return ['Visa', 'MasterCard', 'CliQ', 'eFAWATEERcom', 'Bank Transfer'];
    }

    public function changePreview(?Subscription $subscription, $targetPlan = null, string $mode = 'immediate upgrade'): array
    {
        $currentPlan = $subscription?->plan;
        $currentFeatures = collect($currentPlan?->featureKeys ?? [])->pluck('code');
        $targetFeatures = collect($targetPlan?->featureKeys ?? [])->pluck('code');

        return [
            'current_plan' => $currentPlan?->name_ar ?: $currentPlan?->name ?: '—',
            'new_plan' => $targetPlan?->name_ar ?: $targetPlan?->name ?: 'اختر باقة للمعاينة',
            'current_price' => $this->money($subscription?->price_amount, $subscription?->currency),
            'new_price' => $this->money($targetPlan?->monthly_price, $targetPlan?->currency ?: 'JOD'),
            'billing_cycle' => $subscription?->billing_cycle ?: 'monthly',
            'effective_mode' => $mode,
            'new_features' => $targetFeatures->diff($currentFeatures)->values(),
            'lost_features' => $currentFeatures->diff($targetFeatures)->values(),
        ];
    }

    public function planComparisonRows(): array
    {
        return [
            ['label' => 'المزايا', 'type' => 'features'],
            ['label' => 'حدود الاستخدام', 'limit' => 'usage'],
            ['label' => 'الفواتير', 'limit' => 'invoices'],
            ['label' => 'المستخدمين', 'limit' => 'users'],
            ['label' => 'المنتجات', 'limit' => 'products'],
            ['label' => 'العملاء', 'limit' => 'contacts'],
            ['label' => 'API', 'feature' => 'API_ACCESS'],
            ['label' => 'JoFotara', 'feature' => 'JOFOTARA_SUBMIT'],
            ['label' => 'PDF', 'feature' => 'PDF_EXPORT'],
            ['label' => 'Reports', 'feature' => 'REPORTS_VIEW'],
        ];
    }

    private function date($value): string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : '—';
    }

    private function money($amount, ?string $currency): string
    {
        return $amount !== null ? number_format((float) $amount, 3).' '.($currency ?: 'JOD') : '—';
    }
}
