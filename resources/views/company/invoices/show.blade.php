@extends('layouts.company-workspace')
@section('title', 'تفاصيل فاتورة')
@section('content')
@php
    $primary = $branding['primary_color'] ?? '#00a9c4';
    $secondary = $branding['secondary_color'] ?? '#12c2b2';
    $statusLabels = ['draft'=>'مسودة','ready'=>'جاهزة للإرسال','submitted'=>'تم إرسالها','cancelled'=>'ملغاة','pending'=>'مراجعة داخلية','approved'=>'معتمدة'];
    $typeLabels = ['tax_invoice'=>'فاتورة ضريبية','simplified_invoice'=>'فاتورة مبسطة','credit_note'=>'إشعار دائن','debit_note'=>'إشعار مدين'];
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
<style>
.invoice-show{--invoice-primary:{{ $primary }};--invoice-secondary:{{ $secondary }}}.invoice-summary-hero{background:linear-gradient(135deg,var(--invoice-primary),var(--invoice-secondary));color:#fff;border-radius:26px;padding:26px;margin-bottom:18px;box-shadow:0 18px 42px rgba(15,23,42,.12)}.invoice-summary-hero .btn{border-radius:999px}.summary-card{border:1px solid #e5eef4;border-radius:22px;box-shadow:0 12px 30px rgba(15,23,42,.06);height:100%}.summary-card h2{font-size:1rem;font-weight:800;margin-bottom:14px}.summary-line{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px dashed #e5eef4}.summary-line:last-child{border-bottom:0}.summary-label{color:#64748b}.summary-value{font-weight:700;color:#172033;text-align:end}.status-chip{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:7px 12px;background:#f1f9fb;color:#0f6170;border:1px solid #d7eef3}.grand-total{font-size:1.35rem;color:var(--invoice-primary);font-weight:900}.invoice-actions .btn{border-radius:999px}.items-card{border:1px solid #e5eef4;border-radius:22px;overflow:hidden}.items-card th{background:#f7fbfc;color:#475569}.qr-panel{border:1px solid #d7eef3;background:#f8fdff;border-radius:18px;padding:16px}.end-user-note{background:#fff8e6;border:1px solid #ffe2a8;color:#7a5200;border-radius:18px;padding:14px}
</style>
<div class="invoice-show">
    <div class="invoice-summary-hero d-flex flex-column flex-xl-row justify-content-between gap-3 align-items-xl-center">
        <div>
            <div class="opacity-75 mb-1">فاتورة</div>
            <h1 class="h3 mb-2">{{ $invoice->invoice_number }}</h1>
            <div class="d-flex flex-wrap gap-2"><span class="status-chip bg-white text-dark">{{ $statusLabels[$invoice->status] ?? $invoice->status }}</span><span class="status-chip bg-white text-dark">{{ $invoice->jofotara_status ?: 'غير مرسلة لجوفوتارا' }}</span></div>
        </div>
        <div class="invoice-actions d-flex flex-wrap gap-2">
            <a class="btn btn-light" href="{{ route('company.invoices.printable', [$company, $invoice]) }}">⬇️ PDF</a>
            <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="link"><button class="btn btn-outline-light">🔗 مشاركة</button></form>
            <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="whatsapp"><button class="btn btn-outline-light">🟢 واتساب</button></form>
            @if(in_array($invoice->status, ['draft','ready'], true))<a class="btn btn-outline-light" href="{{ route('company.invoices.edit', [$company, $invoice]) }}">✏️ تعديل</a>@endif
            @if($invoice->status === 'draft')<form method="post" action="{{ route('company.invoices.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-light">🚀 تجهيز</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-light">🚫 إلغاء</button></form>@endif
            @if($invoice->status === 'ready')@if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-danger">🏛️ إرسال وطني</button></form>@endif<form method="post" action="{{ route('company.invoices.draft', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-light">↩️ مسودة</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-light">🚫 إلغاء</button></form>@endif
        </div>
    </div>

    @if($failedJofotara)
        <div class="alert alert-danger"><strong>تعذر إرسال الفاتورة.</strong><div>{{ $invoice->jofotara_error_message ?: 'يمكن تعديل البيانات ثم إعادة المحاولة.' }}</div>@if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}" class="mt-2">@csrf<button class="btn btn-danger">🔁 إعادة الإرسال</button></form>@endif</div>
    @elseif($invoice->status === 'ready' && ! $canJofotara)
        <div class="end-user-note"><strong>تنبيه:</strong><ul class="mb-0 mt-2">@foreach($warnings as $warning)<li>{{ $warning }}</li>@endforeach</ul></div>
    @endif

    @if(session('share_payload'))
        <div class="alert alert-success mt-3"><strong>رابط المشاركة:</strong> <input class="form-control mt-2" readonly value="{{ session('share_payload.copy_link') }}"><div class="d-flex gap-2 mt-2"><a class="btn btn-sm btn-outline-primary" href="{{ session('share_payload.copy_link') }}" target="_blank">فتح</a><a class="btn btn-sm btn-outline-success" href="{{ session('share_payload.whatsapp_url') }}" target="_blank">واتساب</a><a class="btn btn-sm btn-outline-secondary" href="{{ session('share_payload.mailto_url') }}">إيميل</a></div></div>
    @endif

    <div class="row g-3 mt-1">
        <div class="col-lg-4"><div class="summary-card card card-body"><h2>👤 العميل</h2><div class="summary-line"><span class="summary-label">الاسم</span><span class="summary-value">{{ $invoice->contact?->name_ar ?: 'عميل نقدي' }}</span></div><div class="summary-line"><span class="summary-label">نوع الفاتورة</span><span class="summary-value">{{ $typeLabels[$invoice->invoice_type] ?? 'فاتورة' }}</span></div><div class="summary-line"><span class="summary-label">المصدر</span><span class="summary-value">{{ $invoice->source === 'jofotara_import' ? 'مستوردة' : 'منشأة محلياً' }}</span></div></div></div>
        <div class="col-lg-4"><div class="summary-card card card-body"><h2>📅 التواريخ</h2><div class="summary-line"><span class="summary-label">الإصدار</span><span class="summary-value">{{ $invoice->issue_date?->format('Y-m-d') }}</span></div><div class="summary-line"><span class="summary-label">الاستحقاق</span><span class="summary-value">{{ $invoice->due_date?->format('Y-m-d') ?: '—' }}</span></div><div class="summary-line"><span class="summary-label">الحالة</span><span class="summary-value">{{ $statusLabels[$invoice->status] ?? $invoice->status }}</span></div></div></div>
        <div class="col-lg-4"><div class="summary-card card card-body"><h2>💰 الإجمالي</h2><div class="summary-line"><span class="summary-label">المجموع</span><span class="summary-value">{{ number_format((float) $invoice->subtotal, 3) }}</span></div><div class="summary-line"><span class="summary-label">الخصم</span><span class="summary-value">{{ number_format((float) $invoice->discount_total, 3) }}</span></div><div class="summary-line"><span class="summary-label">الضريبة</span><span class="summary-value">{{ number_format((float) $invoice->tax_total, 3) }}</span></div><div class="summary-line"><span class="summary-label">الإجمالي النهائي</span><span class="summary-value grand-total">{{ number_format((float) $invoice->grand_total, 3) }} {{ $invoice->currency }}</span></div></div></div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-8"><div class="summary-card card card-body"><h2>🏛️ حالة الفوترة الوطنية</h2><div class="summary-line"><span class="summary-label">حالة الإرسال</span><span class="summary-value">{{ $invoice->jofotara_status ?: 'غير مرسلة' }}</span></div><div class="summary-line"><span class="summary-label">نتيجة التحقق</span><span class="summary-value">{{ $invoice->jofotara_validation_result ?: 'بانتظار الإرسال' }}</span></div>@if($invoice->jofotara_uuid)<div class="summary-line"><span class="summary-label">رقم التتبع الوطني</span><span class="summary-value">{{ $invoice->jofotara_uuid }}</span></div>@endif @if($invoice->jofotara_submitted_at)<div class="summary-line"><span class="summary-label">وقت الإرسال</span><span class="summary-value">{{ $invoice->jofotara_submitted_at?->format('Y-m-d H:i') }}</span></div>@endif</div></div>
        <div class="col-lg-4"><div class="summary-card card card-body"><h2>🔳 رمز QR</h2>@if($hasOfficialQr)<div class="qr-panel text-center"><img alt="رمز QR" src="{{ route('company.invoices.qr', [$company, $invoice]) }}" width="160" height="160"><p class="text-muted small mb-0 mt-2">رمز الفاتورة الرسمي بعد الإرسال.</p></div>@else<div class="qr-panel text-center text-muted">سيظهر رمز QR الرسمي بعد قبول الفاتورة في نظام الفوترة الوطني.</div>@endif</div></div>
    </div>

    <div class="items-card card mt-3">
        <div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }}</td><td>{{ $item->discount_amount }}</td><td>{{ $item->tax_amount }}</td><td><strong>{{ $item->line_total }}</strong></td></tr>@endforeach</tbody></table></div>
    </div>
</div>
@endsection
