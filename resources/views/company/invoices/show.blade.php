@extends('layouts.company-workspace')
@section('title', 'تفاصيل فاتورة')
@section('content')
@php
    $statusLabels = ['draft'=>'مسودة','pending'=>'قيد الاعتماد','approved'=>'معتمدة','cancelled'=>'ملغاة'];
    $canJofotara = $company->featureKeys->contains('code', 'JOFOTARA_SUBMIT') && $company->hasJofotaraClientId() && $company->hasJofotaraSecretKey() && filled($company->jofotara_source_id) && $invoice->status === 'approved' && ! in_array($invoice->jofotara_status, ['ACCEPTED', 'SUBMITTED'], true);
@endphp
<x-layout.page-header :title="$invoice->invoice_number" :subtitle="'الحالة: '.($statusLabels[$invoice->status] ?? $invoice->status)">
    <x-slot:actions>
        <a class="btn btn-outline-secondary" href="{{ route('company.invoices.printable', [$company, $invoice]) }}">تحميل PDF</a>
        <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="link"><button class="btn btn-outline-primary">إنشاء رابط مشاركة</button></form>
        <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="whatsapp"><button class="btn btn-outline-success">رابط واتساب</button></form>
        @if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-danger">إرسال إلى جوفوتارا</button></form>@endif
        @if($invoice->status === 'draft')<a class="btn btn-outline-primary" href="{{ route('company.invoices.edit', [$company, $invoice]) }}">تعديل</a><form method="post" action="{{ route('company.invoices.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-primary">إرسال للاعتماد</button></form>@endif
        @if($invoice->status === 'pending')<form method="post" action="{{ route('company.invoices.approve', [$company, $invoice]) }}">@csrf<button class="btn btn-success">اعتماد</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="btn btn-warning">إلغاء</button></form>@endif
    </x-slot:actions>
</x-layout.page-header>
@if(session('share_payload'))
    <div class="alert alert-success"><strong>رابط المشاركة:</strong> <input class="form-control mt-2" readonly value="{{ session('share_payload.copy_link') }}"><div class="d-flex gap-2 mt-2"><a class="btn btn-sm btn-outline-primary" href="{{ session('share_payload.copy_link') }}" target="_blank">فتح الرابط</a><a class="btn btn-sm btn-outline-success" href="{{ session('share_payload.whatsapp_url') }}" target="_blank">واتساب</a><a class="btn btn-sm btn-outline-secondary" href="{{ session('share_payload.mailto_url') }}">إيميل</a></div></div>
@endif
<div class="row g-3">
    <div class="col-md-6"><div class="card card-body"><h2 class="h5">البيانات</h2><dl class="row"><dt class="col-4">جهة الاتصال</dt><dd class="col-8">{{ $invoice->contact?->name_ar }}</dd><dt class="col-4">النوع</dt><dd class="col-8">{{ $invoice->invoice_type }}</dd><dt class="col-4">المصدر</dt><dd class="col-8">{{ $invoice->source === 'jofotara_import' ? 'استيراد جوفوتارا' : 'محلي' }}</dd><dt class="col-4">الإصدار</dt><dd class="col-8">{{ $invoice->issue_date?->format('Y-m-d') }}</dd><dt class="col-4">الاستحقاق</dt><dd class="col-8">{{ $invoice->due_date?->format('Y-m-d') ?: '—' }}</dd></dl></div></div>
    <div class="col-md-6"><div class="card card-body"><h2 class="h5">الإجماليات</h2><dl class="row"><dt class="col-4">المجموع</dt><dd class="col-8">{{ $invoice->subtotal }}</dd><dt class="col-4">الخصم</dt><dd class="col-8">{{ $invoice->discount_total }}</dd><dt class="col-4">الضريبة</dt><dd class="col-8">{{ $invoice->tax_total }}</dd><dt class="col-4">الإجمالي</dt><dd class="col-8">{{ $invoice->grand_total }} {{ $invoice->currency }}</dd></dl></div></div>
</div>
<div class="card card-body mt-3"><h2 class="h5">حالة جوفوتارا</h2><dl class="row"><dt class="col-md-3">الحالة</dt><dd class="col-md-9">{{ $invoice->jofotara_status ?: 'غير مرسلة' }}</dd><dt class="col-md-3">UUID</dt><dd class="col-md-9">{{ $invoice->jofotara_uuid ?: $invoice->submission_uuid ?: '—' }}</dd><dt class="col-md-3">تاريخ الإرسال</dt><dd class="col-md-9">{{ $invoice->jofotara_submitted_at?->format('Y-m-d H:i') ?: '—' }}</dd><dt class="col-md-3">رسالة آمنة</dt><dd class="col-md-9">{{ $invoice->jofotara_error_message ?: '—' }}</dd></dl>@if($invoice->jofotara_qr || $invoice->qr_code)<div class="alert alert-info"><strong>QR / Barcode:</strong><div class="text-break">{{ $invoice->jofotara_qr ?: $invoice->qr_code }}</div></div>@endif</div>
<div class="card mt-3"><table class="table mb-0"><tr><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }}</td><td>{{ $item->discount_amount }}</td><td>{{ $item->tax_amount }}</td><td>{{ $item->line_total }}</td></tr>@endforeach</table></div>
@endsection
