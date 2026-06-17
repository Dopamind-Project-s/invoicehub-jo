<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:DejaVu Sans, sans-serif; direction:rtl; color:#111;}
        table{width:100%; border-collapse:collapse; margin-top:16px;}
        th,td{border:1px solid #999; padding:8px;}
        .muted{color:#555}.total{text-align:left; font-size:18px; font-weight:bold}.watermark{position:fixed; top:40%; left:12%; font-size:46px; color:#d00; opacity:.18; transform:rotate(-20deg)}
    </style>
</head>
<body>
@if($invoice->status !== 'ACCEPTED')<div class="watermark">غير مفوترة / Not Accepted Yet</div>@endif
<h1>{{ $invoice->status === 'ACCEPTED' ? 'فاتورة مصدرة' : 'مسودة فاتورة' }}</h1>
<p class="muted">Local Invoice / Receivable Invoice / JOD</p>
<table>
    <tr><td>البائع</td><td>{{ $invoice->supplier?->legal_name_ar }}</td><td>الرقم الضريبي</td><td>{{ $invoice->supplier?->tax_number }}</td></tr>
    <tr><td>تسلسل مصدر الدخل</td><td>{{ $invoice->supplier?->jofotara_source_id }}</td><td>الهاتف</td><td>{{ $invoice->supplier?->phone }}</td></tr>
    <tr><td>العنوان</td><td>{{ $invoice->supplier?->street ?: $invoice->supplier?->city }}</td><td>العميل</td><td>{{ $invoice->customer?->name ?? 'عميل نقدي' }}</td></tr>
    <tr><td>رقم الفاتورة</td><td>{{ $invoice->invoice_number }}</td><td>UUID</td><td dir="ltr">{{ $invoice->uuid }}</td></tr>
    <tr><td>ICV</td><td>{{ $invoice->icv }}</td><td>تاريخ/وقت الإصدار</td><td>{{ $invoice->issue_date?->format('Y-m-d') }} {{ $invoice->issue_time }}</td></tr>
</table>
<table>
    <tr><th>الصنف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة</th><th>المجموع</th></tr>
    @foreach($invoice->items as $item)
        <tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->unit_price }}</td><td>{{ $item->discount }}</td><td>{{ $item->tax_amount }}</td><td>{{ $item->line_total }}</td></tr>
    @endforeach
</table>
<p>Subtotal: {{ $invoice->subtotal }} JOD</p>
<p>Discount: {{ $invoice->discount_amount }} JOD</p>
<p>Tax: {{ $invoice->tax_amount }} JOD</p>
<p class="total">Total: {{ $invoice->payable_amount }} JOD</p>
@if($invoice->qr_code)<p dir="ltr">QR: {{ $invoice->qr_code }}</p>@endif
<footer>تم إنشاء هذه الفاتورة عبر InvoiceHub JoFotara. لا تعتبر مصدرة إلا إذا كانت الحالة ACCEPTED.</footer>
</body>
</html>
