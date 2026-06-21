@extends('layouts.company-workspace')
@section('title', 'تفاصيل فاتورة')
@section('content')
@php
    $statusLabels = ['draft'=>'مسودة','ready'=>'جاهزة للإرسال','submitted'=>'تم إرسالها للفوترة الوطنية','cancelled'=>'ملغاة','pending'=>'مراجعة داخلية','approved'=>'جاهزة للإرسال'];
    $hasSubmitFeature = $company->featureKeys->contains('code', 'JOFOTARA_SUBMIT');
    $hasCredentials = $company->hasJofotaraClientId() && $company->hasJofotaraSecretKey() && filled($company->jofotara_source_id);
    $canJofotara = $hasSubmitFeature && $hasCredentials && ($company->is_active ?? true) && auth()->user()?->can('invoices.submit') && $invoice->status === 'ready' && ! ($invoice->jofotara_status === 'ACCEPTED' || ($invoice->jofotara_status === 'SUBMITTED' && $invoice->jofotara_validation_result === 'PASS' && filled($invoice->jofotara_qr) && filled($invoice->jofotara_uuid))); 
    $warnings = [];
    if ($invoice->status !== 'ready' && $invoice->status !== 'submitted') $warnings[] = 'يجب تجهيز الفاتورة قبل إرسالها إلى نظام الفوترة الوطني.';
    if (! $hasSubmitFeature) $warnings[] = 'هذه المنشأة لا تملك ميزة الإرسال للفوترة.';
    if (! $hasCredentials) $warnings[] = 'بيانات الربط مع نظام الفوترة غير مكتملة.';
    $failedJofotara = in_array($invoice->jofotara_status, ['NOT_SUBMITTED', 'ERROR', 'REJECTED'], true) || $invoice->jofotara_validation_result === 'ERROR';
    $hasOfficialQr = ! $failedJofotara && filled($invoice->jofotara_qr ?: $invoice->qr_code) && filled($invoice->jofotara_uuid);
@endphp
<x-layout.page-header :title="$invoice->invoice_number" :subtitle="'الحالة: '.($statusLabels[$invoice->status] ?? $invoice->status)">
    <x-slot:actions>
        <a class="btn btn-outline-secondary" href="{{ route('company.invoices.printable', [$company, $invoice]) }}">تحميل PDF</a>
        <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="link"><button class="btn btn-outline-primary">إنشاء رابط مشاركة</button></form>
        <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="whatsapp"><button class="btn btn-outline-success">رابط واتساب</button></form>
        @if($invoice->status === 'draft')<a class="btn btn-outline-primary" href="{{ route('company.invoices.edit', [$company, $invoice]) }}">تعديل</a><form method="post" action="{{ route('company.invoices.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-primary">تجهيز للإرسال</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-warning">إلغاء</button></form>@endif
        @if($invoice->status === 'ready')<a class="btn btn-outline-primary" href="{{ route('company.invoices.edit', [$company, $invoice]) }}">تعديل</a>@if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-danger">إرسال إلى نظام الفوترة الوطني</button></form>@endif<form method="post" action="{{ route('company.invoices.draft', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-secondary">إرجاع لمسودة</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-warning">إلغاء</button></form>@endif
    </x-slot:actions>
</x-layout.page-header>
@if($failedJofotara)
    <div class="alert alert-danger"><strong>فشل إرسال الفاتورة إلى نظام الفوترة الوطني.</strong><div>{{ $invoice->jofotara_error_message ?: 'لم يتم إرسال الفاتورة، يمكنك تعديل البيانات ثم إعادة المحاولة.' }}</div>@if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}" class="mt-2">@csrf<button class="btn btn-danger">إعادة الإرسال إلى نظام الفوترة الوطني</button></form>@endif</div>
@elseif($invoice->status === 'ready' && ! $canJofotara)
    <div class="alert alert-warning"><strong>لا يمكن الإرسال حالياً:</strong><ul class="mb-0">@foreach($warnings as $warning)<li>{{ $warning }}</li>@endforeach</ul></div>
@endif
@if(session('share_payload'))
    <div class="alert alert-success"><strong>رابط المشاركة:</strong> <input class="form-control mt-2" readonly value="{{ session('share_payload.copy_link') }}"><div class="d-flex gap-2 mt-2"><a class="btn btn-sm btn-outline-primary" href="{{ session('share_payload.copy_link') }}" target="_blank">فتح الرابط</a><a class="btn btn-sm btn-outline-success" href="{{ session('share_payload.whatsapp_url') }}" target="_blank">واتساب</a><a class="btn btn-sm btn-outline-secondary" href="{{ session('share_payload.mailto_url') }}">إيميل</a></div></div>
@endif
<div class="row g-3">
    <div class="col-md-6"><div class="card card-body"><h2 class="h5">البيانات</h2><dl class="row"><dt class="col-4">جهة الاتصال</dt><dd class="col-8">{{ $invoice->contact?->name_ar }}</dd><dt class="col-4">النوع</dt><dd class="col-8">{{ $invoice->invoice_type }}</dd><dt class="col-4">المصدر</dt><dd class="col-8">{{ $invoice->source === 'jofotara_import' ? 'استيراد جوفوتارا' : 'محلي' }}</dd><dt class="col-4">الإصدار</dt><dd class="col-8">{{ $invoice->issue_date?->format('Y-m-d') }}</dd><dt class="col-4">الاستحقاق</dt><dd class="col-8">{{ $invoice->due_date?->format('Y-m-d') ?: '—' }}</dd></dl></div></div>
    <div class="col-md-6"><div class="card card-body"><h2 class="h5">الإجماليات</h2><dl class="row"><dt class="col-4">المجموع</dt><dd class="col-8">{{ $invoice->subtotal }}</dd><dt class="col-4">الخصم</dt><dd class="col-8">{{ $invoice->discount_total }}</dd><dt class="col-4">الضريبة</dt><dd class="col-8">{{ $invoice->tax_total }}</dd><dt class="col-4">الإجمالي</dt><dd class="col-8">{{ $invoice->grand_total }} {{ $invoice->currency }}</dd></dl></div></div>
</div>
<div class="card card-body mt-3">
    <h2 class="h5">حالة الفوترة الوطنية / جوفوتارا</h2>
    <dl class="row">
        <dt class="col-md-3">حالة الفاتورة المحلية</dt><dd class="col-md-9">{{ $invoice->status }}</dd>
        <dt class="col-md-3">حالة جوفوتارا</dt><dd class="col-md-9">{{ $invoice->jofotara_status ?: 'غير مرسلة' }}</dd>
        <dt class="col-md-3">نتيجة التحقق</dt><dd class="col-md-9">{{ $invoice->jofotara_validation_result ?: '—' }}</dd>
        <dt class="col-md-3">رقم UUID</dt><dd class="col-md-9">{{ $invoice->jofotara_uuid ?: '—' }}</dd>
        <dt class="col-md-3">تاريخ الإرسال</dt><dd class="col-md-9">{{ $invoice->jofotara_submitted_at?->format('Y-m-d H:i') ?: '—' }}</dd>
        <dt class="col-md-3">رسالة النظام</dt><dd class="col-md-9">{{ $invoice->jofotara_error_message ?: '—' }}</dd>
    </dl>
    @if($hasOfficialQr)
        <div class="alert alert-info">
            <strong>رمز QR</strong>
            <div class="mt-2"><img alt="رمز QR" src="{{ route('company.invoices.qr', [$company, $invoice]) }}" width="180" height="180"></div>
            <details class="mt-2"><summary>التفاصيل التقنية الخام</summary><code class="text-break d-block mt-2">{{ $invoice->jofotara_qr ?: $invoice->qr_code }}</code></details>
        </div>
    @endif
    <details>
        <summary>استجابة جوفوتارا الخام</summary>
        <code class="text-break d-block mt-2">{{ $invoice->jofotara_response ? Str::limit($invoice->jofotara_response, 2000) : '—' }}</code>
    </details>
</div>

<div class="card card-body mt-3">
    <h2 class="h5">تشخيص سلسلة جوفوتارا PIH / ICV</h2>
    <dl class="row mb-0">
        <dt class="col-md-3">رقم الفاتورة الحالي</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['current_invoice_number'] ?? $invoice->invoice_number }}</dd>
        <dt class="col-md-3">حالة الفاتورة</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['current_status'] ?? $invoice->status }}</dd>
        <dt class="col-md-3">حالة جوفوتارا</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['jofotara_status'] ?? ($invoice->jofotara_status ?: 'غير مرسلة') }}</dd>
        <dt class="col-md-3">ICV الحالي للإرسال</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['current_icv'] ?? $invoice->icv }}</dd>
        <dt class="col-md-3">آخر فاتورة مقبولة</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['last_accepted_invoice_number'] ?? '—' }} @if(!empty($jofotaraDiagnostic['last_accepted_icv']))(ICV {{ $jofotaraDiagnostic['last_accepted_icv'] }})@endif</dd>
        <dt class="col-md-3">الفاتورة السابقة المطلوبة</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['previous_invoice_number'] ?? '—' }}</dd>
        <dt class="col-md-3">UUID السابق</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['previous_uuid'] ?? '—' }}</dd>
        <dt class="col-md-3">حالة PIH</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['pih_status'] ?? '—' }}</dd>
        <dt class="col-md-3">الإجراء المقترح</dt><dd class="col-md-9">{{ $jofotaraDiagnostic['next_action'] ?? '—' }}</dd>
    </dl>
</div>
<div class="card mt-3"><table class="table mb-0"><tr><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }}</td><td>{{ $item->discount_amount }}</td><td>{{ $item->tax_amount }}</td><td>{{ $item->line_total }}</td></tr>@endforeach</table></div>
@endsection
