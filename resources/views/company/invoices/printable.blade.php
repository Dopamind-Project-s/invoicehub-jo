<!doctype html>
<html lang="{{ ($branding['template']?->language ?? 'ar') === 'en' ? 'en' : 'ar' }}" dir="{{ ($branding['template']?->language ?? 'ar') === 'en' ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/internal-extracted.css') }}">
</head>
<body>
<div class="sheet">
    <div class="header">
        <div>
            @if($branding['logo'])<img class="logo" src="{{ asset($branding['logo']) }}" alt="logo">@endif
            <h1>{{ $branding['template']?->name ?? 'Arabic Classic' }}</h1>
            <p class="muted">{{ $invoice->company?->name_ar ?: $invoice->company?->legal_name_ar }}</p>
        </div>
        <div>
            <h2>{{ $invoice->invoice_number }}</h2>
            <span class="badge">{{ $invoice->status }}</span>
            <p class="muted">{{ $invoice->issue_date?->format('Y-m-d') }}</p>
        </div>
    </div>
    <div class="grid">
        <div class="card"><h3>العميل / Customer</h3><p>{{ $invoice->contact?->name_ar }}</p><p class="muted">{{ $invoice->contact?->tax_number ?: $invoice->contact?->national_number }}</p></div>
        <div class="card"><h3>معلومات الفاتورة</h3><p>العملة: {{ $invoice->currency }}</p><p>الاستحقاق: {{ $invoice->due_date?->format('Y-m-d') ?: '—' }}</p></div>
    </div>
    <table><thead><tr><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr></thead><tbody>@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }}</td><td>{{ $item->discount_amount }}</td><td>{{ $item->tax_amount }}</td><td>{{ $item->line_total }}</td></tr>@endforeach</tbody></table>
    <table class="totals"><tr><th>المجموع</th><td>{{ $invoice->subtotal }}</td></tr><tr><th>الخصم</th><td>{{ $invoice->discount_total }}</td></tr><tr><th>الضريبة</th><td>{{ $invoice->tax_total }}</td></tr><tr><th>الإجمالي</th><td>{{ $invoice->grand_total }} {{ $invoice->currency }}</td></tr></table>
    <div class="grid">
        <div class="card">
            <strong>QR / Barcode</strong>
            @if($invoice->jofotara_qr || $invoice->qr_code || $invoice->jofotara_uuid || $invoice->submission_uuid)
                <p class="muted">UUID: {{ $invoice->jofotara_uuid ?: $invoice->submission_uuid ?: '—' }}</p>
                <div class="break-all">{{ $invoice->jofotara_qr ?: $invoice->qr_code ?: '—' }}</div>
            @else
                <p class="muted">سيظهر QR / UUID بعد الإرسال إلى نظام الفوترة الوطني.</p>
            @endif
        </div>
        <div class="card"><strong>{{ $branding['signature_block'] }}</strong><br>@if($branding['stamp_image'])<img class="logo" src="{{ asset($branding['stamp_image']) }}" alt="stamp">@endif</div>
    </div>
    <div class="footer"><p>{{ $branding['terms_and_conditions'] }}</p><p class="muted">{{ $branding['footer_text'] }}</p></div>
</div>
</body>
</html>
