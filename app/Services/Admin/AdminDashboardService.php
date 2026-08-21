<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Blog;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\SubscriptionRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminDashboardService
{
    public const CACHE_KEY = 'admin-dashboard:v4';

    public function get(): array
    {
        try {
            $cached = Cache::get(self::CACHE_KEY);
            if ($this->isValid($cached)) {
                return $cached;
            }

            Cache::forget(self::CACHE_KEY);

            return Cache::remember(self::CACHE_KEY, 300, fn (): array => $this->build());
        } catch (Throwable $exception) {
            Log::warning('Admin dashboard cache unavailable; using uncached data.', ['exception' => $exception::class]);

            return $this->build();
        }
    }

    public static function clear(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable $exception) {
            Log::warning('Admin dashboard cache invalidation failed.', ['exception' => $exception::class]);
        }
    }

    public static function clearAfterCommit(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(fn () => self::clear());

            return;
        }

        self::clear();
    }

    private function build(): array
    {
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
            'latest_requests' => SubscriptionRequest::latest()->limit(6)->get()->map(fn (SubscriptionRequest $request): array => [
                'id' => (int) $request->id,
                'display_name' => (string) ($request->company_name ?: $request->applicant_name ?: "طلب #{$request->id}"),
                'status' => (string) ($request->status ?: 'غير محدد'),
            ])->all(),
            'latest_companies' => Company::latest()->limit(6)->get()->map(fn (Company $company): array => [
                'id' => (int) $company->id,
                'display_name' => (string) ($company->name_ar ?: $company->legal_name_ar ?: $company->name_en ?: $company->legal_name_en ?: "Company #{$company->id}"),
                'status' => (string) ($company->status ?: 'غير محدد'),
            ])->all(),
            'latest_audits' => AuditLog::latest()->limit(6)->get()->map(fn (AuditLog $audit): array => [
                'action' => (string) ($audit->action ?: 'عملية غير محددة'),
                'occurred_at' => (string) ($audit->created_at?->format('Y-m-d H:i') ?: '—'),
            ])->all(),
            'alerts' => [
                ['label' => 'طلبات اشتراك معلقة', 'count' => SubscriptionRequest::where('status', 'pending')->count()],
                ['label' => 'اشتراكات قريبة الانتهاء', 'count' => Subscription::where('status', 'active')->whereBetween('current_period_end_at', [$now, $now->copy()->addDays(14)])->count()],
                ['label' => 'شركات بدون اشتراك فعال', 'count' => Company::whereDoesntHave('activeSubscription')->count()],
            ],
        ];
    }

    private function isValid(mixed $value): bool
    {
        $keys = ['total_companies', 'blogs_total', 'blogs_published', 'blogs_drafts', 'active_companies', 'inactive_companies', 'pending_requests', 'contacted_requests', 'active_subscriptions', 'expiring_subscriptions', 'expired_subscriptions', 'latest_requests', 'latest_companies', 'latest_audits', 'alerts'];
        if (! is_array($value) || array_keys($value) !== $keys) {
            return false;
        }
        foreach (array_slice($keys, 0, 11) as $countKey) {
            if (! is_int($value[$countKey])) {
                return false;
            }
        }

        return $this->validRows($value['latest_companies'], ['id' => 'int', 'display_name' => 'string', 'status' => 'string'], true)
            && $this->validRows($value['latest_requests'], ['id' => 'int', 'display_name' => 'string', 'status' => 'string'], true)
            && $this->validRows($value['latest_audits'], ['action' => 'string', 'occurred_at' => 'string'])
            && $this->validRows($value['alerts'], ['label' => 'string', 'count' => 'int']);
    }

    private function validRows(mixed $rows, array $fields, bool $positiveId = false): bool
    {
        if (! is_array($rows)) {
            return false;
        }

        return collect($rows)->every(function ($row) use ($fields, $positiveId): bool {
            if (! is_array($row) || array_keys($row) !== array_keys($fields)) {
                return false;
            }
            foreach ($fields as $field => $type) {
                if (get_debug_type($row[$field]) !== $type) {
                    return false;
                }
            }

            return ! $positiveId || $row['id'] > 0;
        });
    }
}
