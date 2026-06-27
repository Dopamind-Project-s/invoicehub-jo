@extends('layouts.app')
@section('title', 'تفاصيل المنشأة')
@section('page_title', 'تفاصيل المنشأة')
@section('content')
@php
    $access = $subscriptionAccess ?? $company->subscriptionAccess();
    $subscription = $access['subscription'] ?? null;
    $plan = $access['plan'] ?? null;
    $statusLabels = [
        'active' => 'نشط',
        'trialing' => 'تجريبي',
        'grace' => 'ضمن فترة السماح',
        'expired' => 'منتهي',
        'cancelled' => 'ملغي',
        'suspended' => 'معلق',
        'no_subscription' => 'لا يوجد اشتراك',
    ];
    $dateLabel = fn ($date) => $date ? $date->format('Y-m-d') : '—';
@endphp
<x-layout.page-header :title="$company->name_ar ?: $company->legal_name_ar" subtitle="تفاصيل المنشأة والمزايا وجوفوتارا.">
    <x-slot:actions><a class="btn btn-outline-primary" href="{{ route('admin.companies.edit', $company) }}">تعديل</a>@if($company->isSuspended())<form method="post" action="{{ route('admin.companies.activate', $company) }}">@csrf<button class="btn btn-success">تفعيل المنشأة</button></form>@else<form method="post" action="{{ route('admin.companies.suspend', $company) }}">@csrf<button class="btn btn-warning">تعطيل المنشأة</button></form>@endif</x-slot:actions>
</x-layout.page-header>
<div class="row g-4">
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">بيانات المنشأة</h2><dl class="row mb-0"><dt class="col-5">الرقم الضريبي</dt><dd class="col-7">{{ $company->tax_number }}</dd><dt class="col-5">الرقم الوطني</dt><dd class="col-7">{{ $company->national_number ?: '—' }}</dd><dt class="col-5">الهاتف</dt><dd class="col-7">{{ $company->phone ?: '—' }}</dd><dt class="col-5">البريد</dt><dd class="col-7">{{ $company->email ?: '—' }}</dd><dt class="col-5">الحالة</dt><dd class="col-7">{{ $company->isSuspended() ? 'معطلة' : 'فعالة' }}</dd><dt class="col-5">اللغة/العملة</dt><dd class="col-7">{{ $company->default_language }} / {{ $company->default_currency }}</dd></dl></div></div>
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">جوفوتارا</h2><dl class="row mb-0"><dt class="col-5">مصدر الدخل</dt><dd class="col-7">{{ $company->jofotara_source_id ?: '—' }}</dd><dt class="col-5">Client ID</dt><dd class="col-7">{{ $company->hasJofotaraClientId() ? 'موجود' : 'غير موجود' }}</dd><dt class="col-5">Secret Key</dt><dd class="col-7">{{ $company->hasJofotaraSecretKey() ? 'موجود' : 'غير موجود' }}</dd><dt class="col-5">آخر حالة إرسال</dt><dd class="col-7">{{ optional($company->invoices()->latest('submitted_at')->first())->status ?: '—' }}</dd></dl></div></div>
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">الباقة الحالية</h2>@if($plan)<p class="mb-2"><strong>{{ $plan->name_ar ?: $plan->name }}</strong></p><p class="text-muted mb-2">{{ $plan->description_ar ?: $plan->description }}</p><div class="d-flex gap-2 flex-wrap">@foreach($plan->featureKeys as $feature)<span class="badge bg-light text-dark border">{{ $feature->name_ar ?: $feature->code }}</span>@endforeach</div>@else<span class="text-muted">لا توجد باقة نشطة.</span>@endif</div></div>
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">حالة الاشتراك</h2><dl class="row mb-0"><dt class="col-5">حالة الاشتراك</dt><dd class="col-7">{{ $statusLabels[$access['effective_status']] ?? $access['effective_status'] }}</dd><dt class="col-5">دورة الفوترة</dt><dd class="col-7">{{ $access['billing_cycle'] ?: '—' }}</dd><dt class="col-5">بداية الفترة</dt><dd class="col-7">{{ $dateLabel($access['period_start']) }}</dd><dt class="col-5">نهاية الفترة</dt><dd class="col-7">{{ $dateLabel($access['period_end']) }}</dd><dt class="col-5">فترة السماح</dt><dd class="col-7">{{ $dateLabel($access['grace_end']) }}</dd><dt class="col-5">الأيام المتبقية</dt><dd class="col-7">{{ $access['days_remaining'] ?? '—' }}</dd><dt class="col-5">مصدر التجديد</dt><dd class="col-7">{{ $subscription?->source ?: '—' }}</dd><dt class="col-5">التجديد التلقائي</dt><dd class="col-7">{{ $subscription?->auto_renew ? 'مفعل' : 'غير مفعل' }}</dd></dl>@if($subscription)<div class="d-flex gap-2 mt-3"><form method="post" action="{{ route('admin.companies.subscriptions.renew', [$company, 'monthly']) }}">@csrf<button class="btn btn-sm btn-outline-primary">تجديد شهري</button></form><form method="post" action="{{ route('admin.companies.subscriptions.renew', [$company, 'yearly']) }}">@csrf<button class="btn btn-sm btn-primary">تجديد سنوي</button></form></div>@endif</div></div>
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">المزايا المفعلة</h2><div class="d-flex gap-2 flex-wrap">@forelse($company->featureKeys as $feature)<span class="badge bg-primary-subtle text-primary border">{{ $feature->name_ar ?: $feature->code }}</span>@empty<span class="text-muted">لا توجد مزايا مفعلة.</span>@endforelse</div><a class="btn btn-sm btn-outline-primary mt-3" href="{{ route('admin.companies.edit', $company) }}">تعديل الباقة والمزايا</a></div></div>
</div>
@endsection
