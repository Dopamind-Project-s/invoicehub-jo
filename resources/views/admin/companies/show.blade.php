@extends('layouts.app')
@section('title', 'تفاصيل المنشأة')
@section('page_title', 'تفاصيل المنشأة')
@section('content')
@php
    $access = $subscriptionAccess ?? $company->subscriptionAccess();
    $subscription = $access['subscription'] ?? null;
    $plan = $access['plan'] ?? null;
    $dateLabel = fn ($date) => $date ? $date->format('Y-m-d') : '—';
    $money = fn ($amount, $currency = null) => $amount !== null ? number_format((float) $amount, 3).' '.($currency ?: 'JOD') : '—';
@endphp
<x-layout.page-header :title="$company->name_ar ?: $company->legal_name_ar" subtitle="لوحة إدارية موحدة لبيانات المنشأة والاشتراك والمزايا.">
    <x-slot:actions>
        <a class="btn btn-outline-primary" href="{{ route('admin.companies.edit', $company) }}">تعديل بيانات المنشأة</a>
        @if($company->isSuspended())<form method="post" action="{{ route('admin.companies.activate', $company) }}">@csrf<button class="btn btn-success">تفعيل المنشأة</button></form>@else<form method="post" action="{{ route('admin.companies.suspend', $company) }}">@csrf<button class="btn btn-warning">تعطيل المنشأة</button></form>@endif
    </x-slot:actions>
</x-layout.page-header>

<ul class="nav nav-pills gap-2 mb-4 flex-wrap" id="companyTabs" role="tablist">
    @foreach(['overview'=>'نظرة عامة','company'=>'بيانات المنشأة','subscription'=>'الاشتراك','invoices'=>'الفواتير','users'=>'المستخدمون','features'=>'المزايا','jofotara'=>'إعدادات جوفوتارا','activity'=>'سجل النشاط'] as $key => $label)
        <li class="nav-item"><button class="nav-link @if($loop->first) active @endif" data-company-tab="#tab-{{ $key }}" type="button">{{ $label }}</button></li>
    @endforeach
</ul>

<div class="tab-content">
    <div class="tab-pane fade company-tab-pane show active" id="tab-overview">
        <div class="row g-4">
            <div class="col-md-6 col-xl-3"><x-metric-card label="الباقة الحالية" :value="$plan?->name_ar ?: $plan?->name ?: '—'" icon="💎" /></div>
            <div class="col-md-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small mb-2">حالة الاشتراك</div><x-subscription-status-badge :status="$access['effective_status']" /></div></div></div>
            <div class="col-md-6 col-xl-3"><x-metric-card label="تاريخ الانتهاء" :value="$dateLabel($access['period_end'])" icon="⏳" tone="warning" /></div>
            <div class="col-md-6 col-xl-3"><x-metric-card label="المنتجات" :value="$summary['products_count'] ?? 0" icon="📦" tone="info" /></div>
            <div class="col-md-6 col-xl-3"><x-metric-card label="العملاء" :value="$summary['customers_count'] ?? 0" icon="👥" tone="primary" /></div>
            <div class="col-md-6 col-xl-3"><x-metric-card label="الفواتير" :value="$company->invoices_count" icon="🧾" tone="success" /></div>
            <div class="col-md-6 col-xl-3"><x-metric-card label="فواتير مرسلة" :value="$company->submitted_invoices_count" icon="🚀" tone="info" /></div>
            <div class="col-md-6 col-xl-3"><x-metric-card label="مستخدمون نشطون" :value="$summary['active_users_count'] ?? 0" icon="✅" tone="success" /></div>
        </div>
    </div>

    <div class="tab-pane fade company-tab-pane" id="tab-company"><div class="row g-4"><div class="col-lg-6"><div class="card card-body border-0 shadow-sm h-100"><h2 class="h5 fw-bold mb-3">المعلومات القانونية والضريبية</h2><x-info-row label="الاسم القانوني" :value="$company->legal_name_ar"/><x-info-row label="الرقم الضريبي" :value="$company->tax_number"/><x-info-row label="الرقم الوطني" :value="$company->national_number ?: '—'"/><x-info-row label="الحالة" :value="$company->isSuspended() ? 'معلقة' : 'نشطة'"/></div></div><div class="col-lg-6"><div class="card card-body border-0 shadow-sm h-100"><h2 class="h5 fw-bold mb-3">بيانات الاتصال والإعدادات</h2><x-info-row label="الهاتف" :value="$company->phone ?: '—'"/><x-info-row label="البريد الإلكتروني" :value="$company->email ?: '—'"/><x-info-row label="اللغة الافتراضية" :value="$company->default_language"/><x-info-row label="العملة" :value="$company->default_currency"/></div></div></div></div>

    <div class="tab-pane fade company-tab-pane" id="tab-subscription"><div class="row g-4"><div class="col-xl-8"><div class="card border-0 shadow-sm"><div class="card-body p-4"><div class="d-flex justify-content-between flex-wrap gap-3 mb-4"><div><h2 class="h4 fw-bold mb-1">{{ $plan?->name_ar ?: $plan?->name ?: 'لا توجد باقة حالية' }}</h2><p class="text-muted mb-0">تفاصيل الاشتراك النشط وبيانات فترة الفوترة.</p></div><div class="d-flex gap-2 align-items-start"><x-subscription-status-badge :status="$access['effective_status']" />@if($plan?->is_recommended)<span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3 py-2">موصى بها</span>@endif</div></div><div class="row g-3"><div class="col-md-6"><x-info-row label="الخطة الحالية" :value="$plan?->name_ar ?: $plan?->name ?: '—'"/><x-info-row label="الحالة الحالية"><x-subscription-status-badge :status="$access['effective_status']" /></x-info-row><x-info-row label="شهري / سنوي" :value="$access['billing_cycle'] ?: '—'"/><x-info-row label="بداية الفترة الحالية" :value="$dateLabel($access['period_start'])"/><x-info-row label="نهاية الفترة الحالية" :value="$dateLabel($access['period_end'])"/></div><div class="col-md-6"><x-info-row label="نهاية فترة السماح" :value="$dateLabel($access['grace_end'])"/><x-info-row label="التجديد التلقائي" :value="$subscription?->auto_renew ? 'مفعل' : 'غير مفعل'"/><x-info-row label="الأيام المتبقية" :value="$access['days_remaining'] ?? '—'"/><x-info-row label="مصدر الاشتراك" :value="$subscription?->source ?: '—'"/><x-info-row label="تاريخ آخر تجديد" :value="$dateLabel($subscription?->renewed_at)"/><x-info-row label="السعر" :value="$money($subscription?->price_amount, $subscription?->currency)"/><x-info-row label="العملة" :value="$subscription?->currency ?: $plan?->currency ?: '—'"/></div></div><hr><h3 class="h6 fw-bold mb-3">مزايا الباقة</h3><div class="d-flex flex-wrap gap-2">@forelse($plan?->featureKeys ?? collect() as $feature)<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ $feature->name_ar ?: $feature->code }}</span>@empty<span class="text-muted">لا توجد مزايا مرتبطة بهذه الباقة.</span>@endforelse</div></div></div></div><div class="col-xl-4"><div class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">إجراءات الاشتراك</h2><div class="d-grid gap-2">@if($subscription)<form method="post" action="{{ route('admin.companies.subscriptions.renew', [$company, 'monthly']) }}">@csrf<button class="btn btn-outline-primary w-100">تجديد شهر</button></form><form method="post" action="{{ route('admin.companies.subscriptions.renew', [$company, 'yearly']) }}">@csrf<button class="btn btn-primary w-100">تجديد سنة</button></form>@endif<button class="btn btn-outline-secondary" disabled>تغيير الباقة</button><button class="btn btn-outline-warning" disabled>تعليق الاشتراك</button><button class="btn btn-outline-success" disabled>إعادة التفعيل</button><button class="btn btn-outline-danger" disabled>إلغاء الاشتراك</button></div><p class="text-muted small mt-3 mb-0">الأزرار غير المفعلة واجهات جاهزة لربطها بمنطق الأعمال الحالي لاحقاً دون تغييره هنا.</p></div></div></div></div></div>

    <div class="tab-pane fade company-tab-pane" id="tab-invoices"><div class="card card-body border-0 shadow-sm text-center py-5"><h2 class="h5">ملخص الفواتير</h2><p class="text-muted mb-0">إجمالي الفواتير: {{ $company->invoices_count }} — المرسلة: {{ $company->submitted_invoices_count }}</p></div></div>
    <div class="tab-pane fade company-tab-pane" id="tab-users"><div class="card card-body border-0 shadow-sm text-center py-5"><h2 class="h5">المستخدمون</h2><p class="text-muted mb-0">المستخدمون النشطون: {{ $summary['active_users_count'] ?? 0 }}</p></div></div>
    <div class="tab-pane fade company-tab-pane" id="tab-features"><div class="card card-body border-0 shadow-sm"><h2 class="h5 fw-bold mb-3">المزايا المفعلة</h2><div class="d-flex gap-2 flex-wrap">@forelse($company->featureKeys as $feature)<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">{{ $feature->name_ar ?: $feature->code }}</span>@empty<span class="text-muted">لا توجد مزايا مفعلة.</span>@endforelse</div></div></div>
    <div class="tab-pane fade company-tab-pane" id="tab-jofotara"><div class="card card-body border-0 shadow-sm"><h2 class="h5 fw-bold mb-3">إعدادات جوفوتارا</h2><x-info-row label="تسلسل مصدر الدخل" :value="$company->jofotara_source_id ?: '—'"/><x-info-row label="Client ID" :value="$company->hasJofotaraClientId() ? 'موجود' : 'غير موجود'"/><x-info-row label="Secret Key" :value="$company->hasJofotaraSecretKey() ? 'موجود' : 'غير موجود'"/></div></div>
    <div class="tab-pane fade company-tab-pane" id="tab-activity"><div class="card card-body border-0 shadow-sm text-center py-5"><div class="display-6 mb-2">🕘</div><h2 class="h5">سجل النشاط</h2><p class="text-muted mb-0">سيتم عرض آخر أحداث المنشأة هنا عند توفر مصدر السجل.</p></div></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-company-tab]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('[data-company-tab]').forEach((item) => item.classList.remove('active'));
            document.querySelectorAll('.company-tab-pane').forEach((pane) => pane.classList.remove('show', 'active'));
            button.classList.add('active');
            document.querySelector(button.dataset.companyTab)?.classList.add('show', 'active');
        });
    });
});
</script>
@endpush
