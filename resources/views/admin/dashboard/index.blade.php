@extends('layouts.app')
@section('title', 'لوحة تحكم المدير العام')
@push('styles')<link rel="stylesheet" href="{{ asset('css/dashboard-cards.css') }}">@endpush
@section('content')
<div class="dash-shell" dir="rtl">
    <div class="dash-hero"><span class="badge bg-white text-primary mb-2">Super Admin</span><h1 class="h3 mb-2">مؤشرات المنصة والاشتراكات</h1><p class="mb-0 opacity-75">لوحة نظام عامة بدون تسريب إحصائيات منشأة واحدة.</p></div>
    <div class="dash-grid">
        @foreach(['total_companies'=>'إجمالي المنشآت','active_companies'=>'منشآت نشطة','pending_requests'=>'طلبات جديدة','active_subscriptions'=>'اشتراكات نشطة','expiring_subscriptions'=>'قريبة الانتهاء','expired_subscriptions'=>'منتهية','contacted_requests'=>'قيد التواصل','inactive_companies'=>'غير نشطة','blogs_total'=>'إجمالي المقالات','blogs_published'=>'مقالات منشورة','blogs_drafts'=>'مسودات'] as $key=>$label)
            <div class="dash-stat"><div class="dash-stat-value">{{ $dashboard[$key] }}</div><div class="dash-stat-label">{{ $label }}</div></div>
        @endforeach
        <div class="dash-panel span-4"><h2 class="h5">تنبيهات مهمة</h2><div class="dash-list">@forelse($dashboard['alerts'] as $alert)<div class="dash-alert d-flex justify-content-between"><span>{{ $alert['label'] ?: 'تنبيه' }}</span><strong>{{ $alert['count'] ?? 0 }}</strong></div>@empty<p class="text-muted">لا توجد تنبيهات حالياً.</p>@endforelse</div></div>
        <div class="dash-panel span-4"><h2 class="h5">أحدث طلبات الاشتراك</h2><div class="dash-list">@forelse($dashboard['latest_requests'] as $item)<a class="dash-list-item text-decoration-none" href="{{ route('admin.subscription-requests.show', $item['id']) }}"><span>{{ $item['display_name'] ?: 'طلب غير مسمى' }}</span><small>{{ $item['status'] ?: 'غير محدد' }}</small></a>@empty<p class="text-muted">لا توجد طلبات اشتراك.</p>@endforelse</div></div>
        <div class="dash-panel span-4"><h2 class="h5">أحدث المنشآت</h2><div class="dash-list">@forelse($dashboard['latest_companies'] as $item)<a class="dash-list-item text-decoration-none" href="{{ route('admin.companies.show', $item['id']) }}"><span>{{ $item['display_name'] ?: 'منشأة غير مسماة' }}</span><small>{{ $item['status'] ?: 'غير محدد' }}</small></a>@empty<p class="text-muted">لا توجد منشآت مسجلة.</p>@endforelse</div></div>
        <div class="dash-panel span-8"><h2 class="h5">آخر عمليات النظام</h2><div class="dash-list">@forelse($dashboard['latest_audits'] as $item)<div class="dash-list-item"><span>{{ $item['action'] ?: 'عملية غير محددة' }}</span><small>{{ $item['occurred_at'] ?: '—' }}</small></div>@empty<p class="text-muted">لا توجد عمليات مسجلة.</p>@endforelse</div></div>
    </div>
</div>
@endsection
