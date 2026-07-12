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
    public function get(): array
    {
        return Cache::remember('admin-dashboard:v3', 300, function (): array {
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
                'latest_requests' => SubscriptionRequest::with('plan')->latest()->limit(6)->get(),
                'latest_companies' => Company::latest()->limit(6)->get(),
                'latest_audits' => AuditLog::with('user')->latest()->limit(6)->get(),
                'alerts' => [
                    'طلبات اشتراك معلقة' => SubscriptionRequest::where('status', 'pending')->count(),
                    'اشتراكات قريبة الانتهاء' => Subscription::where('status', 'active')->whereBetween('current_period_end_at', [$now, $now->copy()->addDays(14)])->count(),
                    'شركات بدون اشتراك فعال' => Company::whereDoesntHave('activeSubscription')->count(),
                ],
            ];
        });
    }
}
