<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 24px; }
        body { font-family: DejaVu Sans, Tahoma, sans-serif; direction: rtl; color: #222; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; }
        .logo, .logo-placeholder { width: 95px; height: 65px; border: 1px solid #d6d6d6; border-radius: 6px; object-fit: contain; text-align: center; line-height: 65px; color: #999; font-size: 12px; }
        .seller { text-align: right; font-weight: bold; font-size: 20px; }
        .title { text-align: center; font-size: 26px; margin: 25px; }
        .boxes { display: flex; gap: 12px; margin-bottom: 15px; }
        .box { border: 1px solid #bbb; border-radius: 8px; padding: 10px; flex: 1; }
        .customer { width: 45%; border: 1px solid #bbb; border-radius: 8px; padding: 12px; margin-bottom: 15px; }
        .qr { text-align: center; margin: 12px; }
        .qr img { width: 120px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #999; padding: 9px; text-align: center; }
        th { background: #eee; }
        .total { text-align: left; font-size: 20px; margin-top: 12px; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; color: #777; }
    </style>
</head>
<body>
@php
    $seller = $invoice->seller;
    $sellerName = $seller?->name ?: config('services.jofotara.seller_name') ?: 'اسم البائع';
    $buyerTaxNumber = $invoice->customer?->tax_number;
    $buyerNationalNumber = $invoice->customer?->national_number;
@endphp
<div class="header">
    <div class="seller">{{ $sellerName }}</div>
    @if($seller?->logo_path)
        <img class="logo" src="{{ public_path('storage/'.$seller->logo_path) }}" alt="شعار البائع">
    @else
        <div class="logo-placeholder">شعار</div>
    @endif
</div>
<div class="title">فاتورة {{ $invoice->invoice_number }}</div>
<div class="boxes">
    <div class="box">التسلسل #<br><b>{{ $invoice->invoice_number }}</b></div>
    <div class="box">تاريخ الفاتورة<br><b>{{ $invoice->invoice_date?->format('Y-m-d') }}</b></div>
    <div class="box">تاريخ استحقاق الفاتورة<br><b>{{ $invoice->due_date?->format('Y-m-d') ?: '-' }}</b></div>
</div>
<div class="customer">
    العميل: <b>{{ $invoice->customer?->name ?? 'عميل نقدي' }}</b><br>
    @if(filled($buyerTaxNumber) && $buyerTaxNumber !== '000000000')
        الرقم الضريبي: {{ $buyerTaxNumber }}<br>
    @endif
    @if(filled($buyerNationalNumber))
        الرقم الوطني: {{ $buyerNationalNumber }}<br>
    @endif
</div>
@if($invoice->jofotara_qr)
    <div class="qr"><img src="{{ $invoice->jofotara_qr }}" alt="QR"></div>
@endif
<table>
    <tr><th>الوصف</th><th>الكمية</th><th>سعر الوحدة</th><th>الضرائب</th><th>المبلغ</th></tr>
    @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td>{{ number_format($item->quantity, 3) }}</td>
            <td>{{ number_format($item->unit_price, 3) }}</td>
            <td>{{ number_format($item->tax_amount, 3) }}</td>
            <td>{{ number_format($item->line_total, 3) }}</td>
        </tr>
    @endforeach
    <tr><th colspan="4">الإجمالي</th><th>{{ number_format($invoice->total, 3) }} JOD</th></tr>
</table>
<div class="total">مرجع الدفعة: {{ $invoice->payment_reference }}</div>
<div class="footer">Page: 1 of 1</div>
</body>
</html>
