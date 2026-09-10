@extends('layouts.company-workspace')
@section('title', 'تفاصيل فاتورة')
@section('content')
@php
    $hasSubmitFeature = $company->featureKeys->contains('code', 'JOFOTARA_SUBMIT');
    $hasCredentials = $company->hasJofotaraClientId() && $company->hasJofotaraSecretKey() && filled($company->jofotara_source_id);
    $canJofotara = $hasSubmitFeature && $hasCredentials && ($company->is_active ?? true) && auth()->user()?->can('invoices.submit') && $invoice->status === 'ready' && ! ($invoice->jofotara_status === 'ACCEPTED' || ($invoice->jofotara_status === 'SUBMITTED' && $invoice->jofotara_validation_result === 'PASS' && filled($invoice->jofotara_qr) && filled($invoice->jofotara_uuid)));
    $warnings = [];
    if ($invoice->status !== 'ready' && $invoice->status !== 'submitted') $warnings[] = 'يجب تجهيز الفاتورة قبل إرسالها إلى نظام الفوترة الوطني.';
    if (! $hasSubmitFeature) $warnings[] = 'هذه المنشأة لا تملك ميزة الإرسال للفوترة.';
    if (! $hasCredentials) $warnings[] = 'بيانات الربط مع نظام الفوترة غير مكتملة.';
    $failedJofotara = in_array($invoice->jofotara_status, ['NOT_SUBMITTED', 'ERROR', 'REJECTED'], true) || $invoice->jofotara_validation_result === 'ERROR';
@endphp
<link rel="stylesheet" href="{{ asset('css/invoice-document.css') }}?v={{ filemtime(public_path('css/invoice-document.css')) }}">
<div class="invoice-shell">
    <div class="invoice-toolbar no-print">
        <a class="invoice-btn" target="_blank" href="{{ route('company.invoices.printable', [$company, $invoice, 'preview' => 1]) }}">معاينة الطباعة</a>
        <a class="invoice-btn" href="{{ route('company.invoices.printable', [$company, $invoice]) }}">تنزيل PDF</a>
        <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="link"><button class="invoice-btn">مشاركة</button></form>
        @if(in_array($invoice->status, ['draft','ready'], true))<a class="invoice-btn" href="{{ route('company.invoices.edit', [$company, $invoice]) }}">تعديل</a>@endif
        @if($invoice->status === 'draft')<form method="post" action="{{ route('company.invoices.submit', [$company, $invoice]) }}">@csrf<button class="invoice-btn">اعتماد للإرسال</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="invoice-btn">إلغاء</button></form>@endif
        @if($invoice->status === 'ready')@if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}">@csrf<button class="invoice-btn">إرسال وطني</button></form>@endif<form method="post" action="{{ route('company.invoices.draft', [$company, $invoice]) }}">@csrf<button class="invoice-btn">إرجاع لمسودة</button></form>@endif
    </div>
    @if($failedJofotara)
        <div class="alert alert-danger no-print"><strong>فشل إرسال الفاتورة إلى نظام الفوترة الوطني.</strong><div>{{ $invoice->jofotara_error_message ?: 'يمكن تعديل البيانات ثم إعادة المحاولة.' }}</div></div>
    @elseif($invoice->status === 'ready' && ! $canJofotara)
        <div class="alert alert-warning no-print"><strong>تنبيه إداري:</strong><ul class="mb-0 mt-2">@foreach($warnings as $warning)<li>{{ $warning }}</li>@endforeach</ul></div>
    @endif
    @if(session('share_payload'))
        <div class="alert alert-info share-link-panel no-print" role="status">
            <strong>تم إنشاء رابط المشاركة</strong>
            <div class="share-link-row">
                <a target="_blank" rel="noopener" href="{{ session('share_payload.copy_link') }}">{{ session('share_payload.copy_link') }}</a>
                <button class="invoice-btn" type="button" data-copy-text="{{ session('share_payload.copy_link') }}">نسخ الرابط</button>
            </div>
            <small data-copy-feedback aria-live="polite"></small>
        </div>
    @endif
    @include('company.invoices.partials.document', ['doc' => $doc])
</div>
@endsection
