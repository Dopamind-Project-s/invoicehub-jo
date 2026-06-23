@extends('layouts.company-workspace')
@section('title', 'تفاصيل فاتورة')
@section('content')
@php
    $primary = $branding['primary_color'] ?? '#00a9c4';
    $secondary = $branding['secondary_color'] ?? '#12c2b2';
    $statusLabels = ['draft'=>'مسودة','ready'=>'جاهزة للإرسال','submitted'=>'تم إرسالها','cancelled'=>'ملغاة','pending'=>'مراجعة داخلية','approved'=>'معتمدة'];
    $typeLabels = ['tax_invoice'=>'فاتورة ضريبية','simplified_invoice'=>'فاتورة مبسطة','credit_note'=>'إشعار دائن','debit_note'=>'إشعار مدين'];
    $jofotaraLabels = ['NOT_SUBMITTED'=>'غير مرسلة','ERROR'=>'فشل الإرسال','REJECTED'=>'مرفوضة','SUBMITTED'=>'مرسلة','ACCEPTED'=>'مقبولة','PASS'=>'تم التحقق'];
    $hasSubmitFeature = $company->featureKeys->contains('code', 'JOFOTARA_SUBMIT');
    $hasCredentials = $company->hasJofotaraClientId() && $company->hasJofotaraSecretKey() && filled($company->jofotara_source_id);
    $canJofotara = $hasSubmitFeature && $hasCredentials && ($company->is_active ?? true) && auth()->user()?->can('invoices.submit') && $invoice->status === 'ready' && ! ($invoice->jofotara_status === 'ACCEPTED' || ($invoice->jofotara_status === 'SUBMITTED' && $invoice->jofotara_validation_result === 'PASS' && filled($invoice->jofotara_qr) && filled($invoice->jofotara_uuid)));
    $warnings = [];
    if ($invoice->status !== 'ready' && $invoice->status !== 'submitted') $warnings[] = 'يجب تجهيز الفاتورة قبل إرسالها إلى نظام الفوترة الوطني.';
    if (! $hasSubmitFeature) $warnings[] = 'هذه المنشأة لا تملك ميزة الإرسال للفوترة.';
    if (! $hasCredentials) $warnings[] = 'بيانات الربط مع نظام الفوترة غير مكتملة.';
    $failedJofotara = in_array($invoice->jofotara_status, ['NOT_SUBMITTED', 'ERROR', 'REJECTED'], true) || $invoice->jofotara_validation_result === 'ERROR';
    $hasOfficialQr = ! $failedJofotara && filled($invoice->jofotara_qr ?: $invoice->qr_code) && filled($invoice->jofotara_uuid);
    $templateSlug = $branding['template']?->slug ?? 'arabic-classic';
@endphp
<div class="invoice-show theme-{{ $templateSlug }}">
    <div class="invoice-summary-hero">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-start">
            <div>
                <div class="opacity-75 mb-1">فاتورة</div>
                <h1 class="h3 mb-2">{{ $invoice->invoice_number }}</h1>
                <div class="d-flex flex-wrap gap-2"><span class="status-chip bg-white text-dark">{{ $statusLabels[$invoice->status] ?? $invoice->status }}</span><span class="status-chip bg-white text-dark">{{ $jofotaraLabels[$invoice->jofotara_status] ?? ($invoice->jofotara_status ?: 'غير مرسلة لجوفوتارا') }}</span></div>
            </div>
            <div class="invoice-actions justify-content-xl-end">
                <a class="btn btn-light invoice-action-btn" target="_blank" href="{{ route('company.invoices.printable', [$company, $invoice, 'preview' => 1]) }}">👁️ معاينة</a>
                <a class="btn btn-light invoice-action-btn" href="{{ route('company.invoices.printable', [$company, $invoice]) }}">⬇️ PDF</a>
                <form method="post" action="{{ route('company.invoices.shares.store', [$company, $invoice]) }}">@csrf<input type="hidden" name="channel" value="link"><button class="btn btn-light invoice-action-btn">🔗 مشاركة</button></form>
                @if(in_array($invoice->status, ['draft','ready'], true))<a class="btn btn-outline-light invoice-action-btn" href="{{ route('company.invoices.edit', [$company, $invoice]) }}">✏️ تعديل</a>@endif
                @if($invoice->status === 'draft')<form method="post" action="{{ route('company.invoices.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-primary-soft invoice-action-btn">✅ اعتماد للإرسال</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-light invoice-action-btn">🚫 إلغاء</button></form>@endif
                @if($invoice->status === 'ready')@if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}">@csrf<button class="btn btn-danger invoice-action-btn">🏛️ إرسال وطني</button></form>@endif<form method="post" action="{{ route('company.invoices.draft', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-light invoice-action-btn">↩️ إرجاع لمسودة</button></form><form method="post" action="{{ route('company.invoices.cancel', [$company, $invoice]) }}">@csrf<button class="btn btn-outline-light invoice-action-btn">🚫 إلغاء</button></form>@endif
            </div>
        </div>
    </div>

    @if($failedJofotara)
        <div class="alert alert-danger"><strong>فشل إرسال الفاتورة إلى نظام الفوترة الوطني.</strong><div>{{ $invoice->jofotara_error_message ?: 'يمكن تعديل البيانات ثم إعادة المحاولة.' }}</div>@if($canJofotara)<form method="post" action="{{ route('company.invoices.jofotara.submit', [$company, $invoice]) }}" class="mt-2">@csrf<button class="btn btn-danger invoice-action-btn">🔁 إعادة الإرسال إلى نظام الفوترة الوطني</button></form>@endif</div>
    @elseif($invoice->status === 'ready' && ! $canJofotara)
        <div class="end-user-note"><strong>تنبيه:</strong><ul class="mb-0 mt-2">@foreach($warnings as $warning)<li>{{ $warning }}</li>@endforeach</ul></div>
    @endif

    @if(session('share_payload'))
        <div class="share-toast" role="status" aria-live="polite">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-2"><strong>🔔 تم إنشاء رابط المشاركة</strong><a class="text-decoration-none" href="{{ url()->current() }}">×</a></div>
            <div class="copy-link-box"><input class="form-control" readonly value="{{ session('share_payload.copy_link') }}"><a class="btn btn-primary" target="_blank" href="{{ session('share_payload.copy_link') }}">فتح</a></div>
            <small class="text-muted d-block mt-2">انسخ الرابط أو افتحه لمشاركته عبر القناة المناسبة.</small>
        </div>
    @endif

    <div class="row g-3 mt-1 align-items-stretch">
        <div class="col-lg-6"><div class="summary-card card card-body"><h2>👤 بيانات العميل</h2><div class="summary-line"><span class="summary-label">الاسم</span><span class="summary-value">{{ $invoice->contact?->name_ar ?: 'عميل نقدي' }}</span></div><div class="summary-line"><span class="summary-label">نوع الفاتورة</span><span class="summary-value">{{ $typeLabels[$invoice->invoice_type] ?? 'فاتورة' }}</span></div><div class="summary-line"><span class="summary-label">المصدر</span><span class="summary-value">{{ $invoice->source === 'jofotara_import' ? 'مستوردة' : 'منشأة محلياً' }}</span></div></div></div>
        <div class="col-lg-6"><div class="summary-card card card-body"><h2>💰 ملخص المبالغ</h2><div class="summary-line"><span class="summary-label">المجموع</span><span class="summary-value">{{ number_format((float) $invoice->subtotal, 3) }}</span></div><div class="summary-line"><span class="summary-label">الخصم</span><span class="summary-value">{{ number_format((float) $invoice->discount_total, 3) }}</span></div><div class="summary-line"><span class="summary-label">الضريبة</span><span class="summary-value">{{ number_format((float) $invoice->tax_total, 3) }}</span></div><div class="summary-line"><span class="summary-label">الإجمالي النهائي</span><span class="summary-value grand-total">{{ number_format((float) $invoice->grand_total, 3) }} {{ $invoice->currency }}</span></div></div></div>
        <div class="col-lg-6"><div class="summary-card card card-body"><h2>📅 التواريخ والحالة</h2><div class="summary-line"><span class="summary-label">الإصدار</span><span class="summary-value">{{ $invoice->issue_date?->format('Y-m-d') }}</span></div><div class="summary-line"><span class="summary-label">الاستحقاق</span><span class="summary-value">{{ $invoice->due_date?->format('Y-m-d') ?: '—' }}</span></div><div class="summary-line"><span class="summary-label">الحالة</span><span class="summary-value">{{ $statusLabels[$invoice->status] ?? $invoice->status }}</span></div></div></div>
        <div class="col-lg-6"><div class="summary-card card card-body"><h2>🏛️ حالة الفوترة الوطنية</h2><div class="summary-line"><span class="summary-label">حالة الإرسال</span><span class="summary-value">{{ $jofotaraLabels[$invoice->jofotara_status] ?? ($invoice->jofotara_status ?: 'غير مرسلة') }}</span></div><div class="summary-line"><span class="summary-label">نتيجة التحقق</span><span class="summary-value">{{ $jofotaraLabels[$invoice->jofotara_validation_result] ?? ($invoice->jofotara_validation_result ?: 'بانتظار الإرسال') }}</span></div>@if($invoice->jofotara_uuid)<div class="summary-line"><span class="summary-label">رقم التتبع الوطني</span><span class="summary-value">{{ $invoice->jofotara_uuid }}</span></div>@endif @if($invoice->jofotara_submitted_at)<div class="summary-line"><span class="summary-label">وقت الإرسال</span><span class="summary-value">{{ $invoice->jofotara_submitted_at?->format('Y-m-d H:i') }}</span></div>@endif</div></div>
        <div class="col-lg-6"><div class="summary-card card card-body"><h2>🔳 رمز QR</h2>@if($hasOfficialQr)<div class="qr-panel text-center"><img alt="رمز QR" src="{{ route('company.invoices.qr', [$company, $invoice]) }}" width="160" height="160"><p class="text-muted small mb-0 mt-2">رمز الفاتورة الرسمي بعد الإرسال.</p></div>@else<div class="qr-panel text-center text-muted">سيظهر رمز QR الرسمي بعد قبول الفاتورة في نظام الفوترة الوطني.</div>@endif</div></div>
    </div>

    <div class="items-card card mt-3">
        <div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }}</td><td>{{ $item->discount_amount }}</td><td>{{ $item->tax_amount }}</td><td><strong>{{ $item->line_total }}</strong></td></tr>@endforeach</tbody></table></div>
    </div>
</div>
@endsection
