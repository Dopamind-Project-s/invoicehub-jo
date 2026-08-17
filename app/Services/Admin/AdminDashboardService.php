<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Blog;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use Illuminate\Support\Facades\Cache;

class AdminDashboardService
{
    public const CACHE_KEY = 'admin-dashboard:v4';

    public function get(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($this->isValid($cached)) {
            return $cached;
        }

        Cache::forget(self::CACHE_KEY);

        return Cache::remember(self::CACHE_KEY, 300, function (): array {
            $now = now();

            return [
                'total_companies' => Company::count(),
                'blogs_total' => Blog::count(),
                'blogs_published' => Blog::where('status', Blog::STATUS_PUBLISHED)->count(),
                'blogs_drafts' => Blog::where('status', Blog::STATUS_DRAFT)->count(),
                'active_companies' => Company::where('status', 'active')->where('is_active', true)->count(),
                'inactive_companies' => Company::where('status', '!=', 'active')->orWhere('is_active', false)->count(),
                'pending_requests' => SubscriptionRequest::where('status', 'pending')->count(),
                'contacted_requests' => SubscriptionRequest::where('status', 'contacted')->count(),
                'active_subscriptions' => Subscription::where('status', 'active')->count(),
                'expiring_subscriptions' => Subscription::where('status', 'active')->whereBetween('current_period_end_at', [$now, $now->copy()->addDays(14)])->count(),
                'expired_subscriptions' => Subscription::where('status', 'expired')->orWhere('current_period_end_at', '<', $now)->count(),
                'latest_requests' => SubscriptionRequest::latest()->limit(6)->get()->map(fn ($request): array => [
                    'id' => $request->id,
                    'display_name' => $request->company_name ?: $request->contact_name ?: "طلب #{$request->id}",
                    'status' => $request->status ?: 'غير محدد',
                ])->all(),
                'latest_companies' => Company::latest()->limit(6)->get()->map(fn (Company $company): array => [
                    'id' => $company->id,
                    'display_name' => $company->name_ar ?: $company->legal_name_ar ?: $company->name_en ?: $company->legal_name_en ?: "Company #{$company->id}",
                    'status' => $company->status ?: 'غير محدد',
                ])->all(),
                'latest_audits' => AuditLog::latest()->limit(6)->get()->map(fn (AuditLog $audit): array => [
                    'action' => $audit->action ?: 'عملية غير محددة',
                    'occurred_at' => $audit->created_at?->format('Y-m-d H:i') ?: '—',
                ])->all(),
                'alerts' => [
                    ['label' => 'طلبات اشتراك معلقة', 'count' => SubscriptionRequest::where('status', 'pending')->count()],
                    ['label' => 'اشتراكات قريبة الانتهاء', 'count' => Subscription::where('status', 'active')->whereBetween('current_period_end_at', [$now, $now->copy()->addDays(14)])->count()],
                    ['label' => 'شركات بدون اشتراك فعال', 'count' => Company::whereDoesntHave('activeSubscription')->count()],
                ],
            ];
        });
    }

    public static function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function isValid(mixed $value): bool
    {
        $keys = ['total_companies', 'blogs_total', 'blogs_published', 'blogs_drafts', 'active_companies', 'inactive_companies', 'pending_requests', 'contacted_requests', 'active_subscriptions', 'expiring_subscriptions', 'expired_subscriptions', 'latest_requests', 'latest_companies', 'latest_audits', 'alerts'];
        if (! is_array($value) || array_diff($keys, array_keys($value))) {
            return false;
        }

        return collect(['latest_requests', 'latest_companies', 'latest_audits', 'alerts'])
            ->every(fn (string $key): bool => is_array($value[$key]) && collect($value[$key])->every(fn ($row): bool => is_array($row)));
    }
}
