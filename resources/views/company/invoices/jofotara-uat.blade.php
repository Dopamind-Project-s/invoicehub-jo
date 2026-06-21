@extends('layouts.company-workspace')
@section('title', 'حالة JoFotara UAT')
@section('content')
<x-layout.page-header title="JoFotara UAT Status" subtitle="تشخيص محلي غير متاح في production للتحقق من سلسلة PIH / ICV قبل الإرسال الحي.">
    <x-slot:actions><a class="btn btn-outline-secondary" href="{{ route('company.invoices.index', $company) }}">عودة للفواتير</a></x-slot:actions>
</x-layout.page-header>
<div class="row g-3">
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">المنشأة</h2><dl class="row mb-0"><dt class="col-5">الاسم</dt><dd class="col-7">{{ $company->name_ar ?: $company->legal_name_ar }}</dd><dt class="col-5">مصدر الدخل</dt><dd class="col-7">{{ $company->jofotara_source_id ?: '—' }}</dd><dt class="col-5">Client ID</dt><dd class="col-7">{{ $company->hasJofotaraClientId() ? 'موجود' : 'غير موجود' }}</dd><dt class="col-5">Secret Key</dt><dd class="col-7">{{ $company->hasJofotaraSecretKey() ? 'موجود' : 'غير موجود' }}</dd></dl></div></div>
    <div class="col-lg-6"><div class="card card-body h-100"><h2 class="h5">حالة السلسلة</h2><dl class="row mb-0"><dt class="col-5">آخر فاتورة مقبولة</dt><dd class="col-7">{{ $lastAccepted?->invoice_number ?: '—' }}</dd><dt class="col-5">ICV آخر قبول</dt><dd class="col-7">{{ $lastAccepted?->icv ?: '—' }}</dd><dt class="col-5">آخر فشل</dt><dd class="col-7">{{ $lastFailed?->invoice_number ?: '—' }}</dd><dt class="col-5">الفاتورة التالية المؤهلة</dt><dd class="col-7">{{ $nextEligible?->invoice_number ?: '—' }}</dd></dl></div></div>
</div>
@if($diagnostic)
<div class="card card-body mt-3"><h2 class="h5">تشخيص الفاتورة التالية</h2><dl class="row mb-0"><dt class="col-md-3">ICV المقترح</dt><dd class="col-md-9">{{ $diagnostic['current_icv'] }}</dd><dt class="col-md-3">الفاتورة السابقة المطلوبة</dt><dd class="col-md-9">{{ $diagnostic['previous_invoice_number'] ?? '—' }}</dd><dt class="col-md-3">UUID السابق</dt><dd class="col-md-9">{{ $diagnostic['previous_uuid'] ?? '—' }}</dd><dt class="col-md-3">حالة PIH</dt><dd class="col-md-9">{{ $diagnostic['pih_status'] }}</dd><dt class="col-md-3">الإجراء المقترح</dt><dd class="col-md-9">{{ $diagnostic['next_action'] }}</dd></dl></div>
@else
<div class="alert alert-info mt-3">لا توجد فاتورة جاهزة للإرسال حالياً.</div>
@endif
@endsection
