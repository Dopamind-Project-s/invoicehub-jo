@extends('layouts.app')
@section('title', 'تفاصيل الشركة')
@section('page_title', 'تفاصيل الشركة')
@section('content')
<x-layout.page-header :title="$company->name_ar ?: $company->legal_name_ar" subtitle="تفاصيل الشركة والميزات المفعلة.">
    <x-slot:actions>
        <a class="btn btn-outline-primary" href="{{ route('admin.companies.edit', $company) }}">تعديل</a>
        @if($company->isSuspended())
            <form method="post" action="{{ route('admin.companies.activate', $company) }}">@csrf<button class="btn btn-success">تفعيل</button></form>
        @else
            <form method="post" action="{{ route('admin.companies.suspend', $company) }}">@csrf<button class="btn btn-warning">تعليق</button></form>
        @endif
    </x-slot:actions>
</x-layout.page-header>
<div class="row g-4">
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">بيانات الشركة</h2><dl class="row mb-0"><dt class="col-5">الرقم الضريبي</dt><dd class="col-7">{{ $company->tax_number }}</dd><dt class="col-5">الرقم الوطني</dt><dd class="col-7">{{ $company->national_number ?: '—' }}</dd><dt class="col-5">الهاتف</dt><dd class="col-7">{{ $company->phone ?: '—' }}</dd><dt class="col-5">البريد</dt><dd class="col-7">{{ $company->email ?: '—' }}</dd><dt class="col-5">الحالة</dt><dd class="col-7">{{ $company->isSuspended() ? 'معلقة' : 'نشطة' }}</dd><dt class="col-5">اللغة/العملة</dt><dd class="col-7">{{ $company->default_language }} / {{ $company->default_currency }}</dd></dl></div></div>
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">جوفوتارا</h2><dl class="row mb-0"><dt class="col-5">مصدر الدخل</dt><dd class="col-7">{{ $company->jofotara_source_id ?: '—' }}</dd><dt class="col-5">Client ID</dt><dd class="col-7">{{ $company->hasJofotaraClientId() ? 'محفوظ ومشفّر' : 'غير مدخل' }}</dd><dt class="col-5">Secret Key</dt><dd class="col-7">{{ $company->hasJofotaraSecretKey() ? 'محفوظ ومشفّر' : 'غير مدخل' }}</dd></dl></div></div>
    <div class="col-12"><div class="card card-body"><h2 class="h5">الميزات المفعلة</h2><div class="d-flex gap-2 flex-wrap">@forelse($company->featureKeys as $feature)<span class="badge bg-primary-subtle text-primary border">{{ $feature->code }}</span>@empty<span class="text-muted">لا توجد ميزات مفعلة.</span>@endforelse</div></div></div>
</div>
@endsection
