@extends('layouts.app')
@section('title', 'تفاصيل المنشأة')
@section('page_title', 'تفاصيل المنشأة')
@section('content')
@php
    $access = $subscriptionAccess ?? $company->subscriptionAccess();
    $subscription = $access['subscription'] ?? null;
    $plan = $access['plan'] ?? null;
    $dateLabel = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('Y-m-d') : '—';
    $bool = fn ($v) => $v ? 'مفعل' : 'غير مفعل';
@endphp
<x-layout.page-header :title="$company->name_ar ?: $company->legal_name_ar" subtitle="ملف منشأة SaaS مختصر بدون تبويبات مزدحمة.">
    <x-slot:actions>
        <a class="btn btn-outline-primary" href="{{ route('admin.companies.edit', $company) }}">تعديل المنشأة</a>
        <a class="btn btn-primary" href="{{ route('admin.companies.subscriptions.index', $company) }}">فتح صفحة الاشتراك</a>
        @if($company->isSuspended())
            <form method="post" action="{{ route('admin.companies.activate', $company) }}">@csrf<button class="btn btn-success">تفعيل</button></form>
        @else
            <form method="post" action="{{ route('admin.companies.suspend', $company) }}">@csrf<button class="btn btn-warning">تعليق</button></form>
        @endif
    </x-slot:actions>
</x-layout.page-header>

<div class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><div class="row g-3 align-items-center">
    <div class="col-lg-5"><h2 class="h4 fw-bold mb-1">{{ $company->trade_name ?: $company->legal_name_ar }}</h2><div class="text-muted">الرقم الضريبي: {{ $company->tax_number ?: '—' }}</div></div>
    <div class="col-lg-7"><div class="d-flex flex-wrap gap-2 justify-content-lg-end"><span class="badge {{ $company->isSuspended() ? 'bg-warning' : 'bg-success' }} px-3 py-2">{{ $company->isSuspended() ? 'معلقة' : 'نشطة' }}</span><span class="badge bg-primary px-3 py-2">{{ $plan?->name_ar ?: $plan?->name ?: 'بدون باقة' }}</span><x-subscription-status-badge :status="$access['effective_status']" /></div></div>
</div></div></div>

<div class="row g-4 mb-4">
    <div class="col-xl-8"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h3 class="h5 fw-bold mb-3">بيانات المنشأة</h3><div class="row g-2">
        @foreach([
            'الاسم العربي'=>$company->legal_name_ar, 'الاسم الإنجليزي'=>$company->legal_name_en, 'الاسم التجاري'=>$company->trade_name,
            'الرقم الضريبي'=>$company->tax_number, 'الرقم الوطني'=>$company->national_number, 'رقم التسجيل'=>$company->registration_number,
            'الهاتف'=>$company->phone, 'البريد الإلكتروني'=>$company->email, 'المدينة'=>$company->city, 'الشارع'=>$company->street,
            'رقم المبنى'=>$company->building_no, 'العملة الافتراضية'=>$company->default_currency, 'JoFotara Source ID'=>$company->jofotara_source_id,
            'حالة بيانات جوفوتارا'=>($company->hasJofotaraClientId() && $company->hasJofotaraSecretKey() ? 'مكتملة بدون عرض الأسرار' : 'غير مكتملة')
        ] as $label => $value)
            <div class="col-md-6"><x-info-row :label="$label" :value="$value ?: '—'" /></div>
        @endforeach
    </div></div></div></div>
    <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h3 class="h5 fw-bold mb-3">ملخص الاشتراك</h3>
        <x-info-row label="الباقة الحالية" :value="$plan?->name_ar ?: $plan?->name ?: '—'" />
        <x-info-row label="دورة الفوترة" :value="$access['billing_cycle'] ?: '—'" />
        <x-info-row label="بداية الفترة" :value="$dateLabel($access['period_start'])" />
        <x-info-row label="نهاية الفترة" :value="$dateLabel($access['period_end'])" />
        <x-info-row label="نهاية السماح" :value="$dateLabel($access['grace_end'])" />
        <x-info-row label="التجديد التلقائي" :value="$bool($subscription?->auto_renew)" />
        <x-info-row label="الأيام المتبقية" :value="$access['days_remaining'] ?? '—'" />
        <a class="btn btn-primary w-100 mt-3" href="{{ route('admin.companies.subscriptions.index', $company) }}">إدارة الاشتراكات</a>
    </div></div></div>
</div>

<h3 class="h5 fw-bold mb-3">ملخص تشغيلي</h3>
<div class="row g-4">
    <div class="col-md-6 col-xl-2"><x-metric-card label="المنتجات" :value="$summary['products_count'] ?? 0" icon="📦" /></div>
    <div class="col-md-6 col-xl-2"><x-metric-card label="العملاء والموردون" :value="$summary['contacts_count'] ?? 0" icon="🤝" tone="primary" /></div>
    <div class="col-md-6 col-xl-2"><x-metric-card label="الفواتير" :value="$company->invoices_count" icon="🧾" tone="success" /></div>
    <div class="col-md-6 col-xl-2"><x-metric-card label="مرسلة لجوفوتارا" :value="$company->submitted_invoices_count" icon="🚀" tone="info" /></div>
    <div class="col-md-6 col-xl-2"><x-metric-card label="مستخدمون نشطون" :value="$summary['active_users_count'] ?? 0" icon="✅" tone="success" /></div>
    <div class="col-md-6 col-xl-2"><x-metric-card label="آخر إرسال" :value="$dateLabel($summary['last_jofotara_submission'] ?? null)" icon="🕘" tone="warning" /></div>
</div>
@endsection
