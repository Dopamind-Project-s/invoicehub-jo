<!doctype html>
<html lang="{{ $data->language }}" dir="{{ $data->direction }}">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        body { margin: 0; color: #172033; font-family: invoicearabic; font-size: 8.5pt; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        .header { border-bottom: 2px solid #0f766e; margin-bottom: 4mm; padding-bottom: 3mm; }
        .brand-table td { width: 50%; }
        .logo { width: 18mm; height: 18mm; object-fit: contain; }
        .brand-name { margin: 0 0 1mm; color: #0f766e; font-size: 13pt; }
        .muted { color: #64748b; font-size: 7.5pt; }
        .title { margin: 3mm 0 1mm; font-size: 16pt; color: #0f172a; }
        .number { direction: ltr; white-space: nowrap; }
        .badge { display: inline-block; padding: 1mm 2mm; border: .2mm solid #b9d9d5; border-radius: 2mm; color: #0f766e; font-size: 7pt; }
        .info { margin: 0 0 4mm; table-layout: fixed; }
        .info > tbody > tr > td { width: 50%; border: .2mm solid #d8e2ea; padding: 2.5mm; }
        .info h3 { margin: 0 0 1.5mm; color: #0f766e; font-size: 9.5pt; }
        .kv { width: 100%; border-collapse: collapse; }
        .kv td { padding: .8mm 0; border-bottom: .15mm dotted #d8e2ea; }
        .kv td:last-child { text-align: left; }
        .items { table-layout: fixed; margin-bottom: 3mm; }
        .items th { padding: 2mm 1mm; background: #e5edf3; color: #334155; border-bottom: .3mm solid #94a3b8; font-size: 7pt; }
        .items td { padding: 2mm 1mm; border-bottom: .2mm solid #d8e2ea; }
        .items .description { width: 32%; }
        .items .numeric { width: 13.6%; direction: ltr; text-align: center; }
        .closing { page-break-inside: avoid; }
        .closing > tbody > tr > td { width: 50%; }
        .totals th, .totals td { padding: 1.4mm 2mm; border-bottom: .2mm solid #d8e2ea; }
        .totals th { text-align: right; font-weight: normal; }
        .totals td { direction: ltr; text-align: left; font-weight: bold; }
        .totals .grand th, .totals .grand td { background: #0f766e; color: #fff; font-weight: bold; }
        .qr { text-align: center; }
        .qr img { width: 34mm; height: 34mm; }
        .qr-note { margin: 6mm 3mm; padding: 3mm; border: .2mm solid #d8e2ea; color: #64748b; }
        .notes { margin-top: 3mm; padding: 2.5mm; border: .2mm solid #d8e2ea; page-break-inside: avoid; }
        .footer { margin-top: 4mm; padding-top: 2mm; border-top: .2mm solid #d8e2ea; text-align: center; color: #64748b; font-size: 7pt; }
    </style>
</head>
<body>
<div class="header">
    <table class="brand-table">
        <tr>
            <td>
                <table><tr>
                    @if(!empty($doc['company']['logo_data_uri']))
                        <td style="width:21mm"><img class="logo" src="{{ $doc['company']['logo_data_uri'] }}"></td>
                    @endif
                    <td>
                        <h2 class="brand-name">{{ $doc['company']['name'] ?? '—' }}</h2>
                        @if(!empty($doc['company']['legal_name']))<div class="muted">{{ $doc['company']['legal_name'] }}</div>@endif
                        <div class="muted">الرقم الضريبي: <span class="number">{{ $doc['company']['tax_number'] ?? '—' }}</span></div>
                        @if(!empty($doc['company']['address']))<div class="muted">{{ $doc['company']['address'] }}</div>@endif
                    </td>
                </tr></table>
            </td>
            <td style="text-align:left">
                @if(($doc['jofotara']['submitted'] ?? false) && !empty($doc['jofotara']['logo_data_uri']))
                    <img class="logo" src="{{ $doc['jofotara']['logo_data_uri'] }}"><br>
                    <strong>نظام الفوترة الوطني</strong><br><span class="muted">JoFotara</span>
                @endif
            </td>
        </tr>
    </table>
    <table><tr>
        <td><h1 class="title">{{ $doc['invoice']['type'] ?? 'فاتورة' }}</h1><span class="badge">{{ $doc['invoice']['status'] ?? '—' }}</span> <span class="badge">JoFotara: {{ $doc['invoice']['jofotara_status'] ?? 'غير مرسلة' }}</span></td>
        <td style="text-align:left;padding-top:5mm"><span class="muted">رقم الفاتورة</span><br><strong class="number">{{ $doc['invoice']['number'] ?? '—' }}</strong></td>
    </tr></table>
</div>

<table class="info"><tr>
    <td>
        <h3>بيانات الفاتورة</h3>
        <table class="kv">
            <tr><td>تاريخ الإصدار</td><td>{{ $doc['invoice']['issue_date'] ?? '—' }}</td></tr>
            <tr><td>وقت الإصدار</td><td>{{ $doc['invoice']['issue_time'] ?? '—' }}</td></tr>
            <tr><td>تاريخ الاستحقاق</td><td>{{ $doc['invoice']['due_date'] ?? '—' }}</td></tr>
            <tr><td>طريقة الدفع</td><td>{{ $doc['invoice']['payment'] ?? '—' }}</td></tr>
            <tr><td>نتيجة التحقق</td><td>{{ $doc['invoice']['validation'] ?? '—' }}</td></tr>
        </table>
    </td>
    <td>
        <h3>بيانات العميل</h3>
        <table class="kv">
            <tr><td>الاسم</td><td>{{ $doc['customer']['name'] ?? 'عميل نقدي' }}</td></tr>
            <tr><td>الرقم الضريبي</td><td>{{ $doc['customer']['tax_number'] ?? '—' }}</td></tr>
            <tr><td>الرقم الوطني</td><td>{{ $doc['customer']['national_number'] ?? '—' }}</td></tr>
            <tr><td>الهاتف</td><td>{{ $doc['customer']['phone'] ?? '—' }}</td></tr>
            <tr><td>العنوان</td><td>{{ $doc['customer']['address'] ?? '—' }}</td></tr>
        </table>
    </td>
</tr></table>

<table class="items">
    <thead><tr><th class="description">المنتج/الخدمة والوصف</th><th>الكمية</th><th>سعر الوحدة</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr></thead>
    <tbody>
    @forelse($doc['items'] ?? [] as $item)
        <tr>
            <td><strong>{{ $item['product'] ?: $item['description'] }}</strong>@if($item['product'] && $item['description'])<br><span class="muted">{{ $item['description'] }}</span>@endif</td>
            <td class="numeric">{{ $item['quantity'] }}</td><td class="numeric">{{ $item['unit_price'] }}</td><td class="numeric">{{ $item['discount'] }}</td><td class="numeric">{{ $item['tax'] }}<br><span class="muted">{{ $item['tax_percent'] }}</span></td><td class="numeric"><strong>{{ $item['total'] }}</strong></td>
        </tr>
    @empty
        <tr><td colspan="6">لا توجد بنود.</td></tr>
    @endforelse
    </tbody>
</table>

<table class="closing"><tr>
    <td>
        <table class="totals">
            <tr><th>الإجمالي قبل الخصم</th><td>{{ $doc['totals']['subtotal'] ?? '—' }}</td></tr>
            <tr><th>مجموع الخصومات</th><td>{{ $doc['totals']['discount'] ?? '—' }}</td></tr>
            <tr><th>الخاضع للضريبة</th><td>{{ $doc['totals']['taxable'] ?? '—' }}</td></tr>
            <tr><th>مجموع الضرائب</th><td>{{ $doc['totals']['tax'] ?? '—' }}</td></tr>
            <tr class="grand"><th>الإجمالي النهائي / المستحق</th><td>{{ $doc['totals']['payable'] ?? ($doc['totals']['grand'] ?? '—') }}</td></tr>
        </table>
    </td>
    <td class="qr">
        @if(!empty($doc['qr']['data_uri']))
            <img src="{{ $doc['qr']['data_uri'] }}"><br><strong>رمز QR الرسمي</strong>
        @else
            <div class="qr-note">رمز QR الرسمي غير متوفر لأن الفاتورة لم تُعتمد بعد من نظام الفوترة الوطني.</div>
        @endif
    </td>
</tr></table>

@if(!empty($doc['invoice']['notes']))<div class="notes"><strong>ملاحظات</strong><br>{{ $doc['invoice']['notes'] }}</div>@endif
<div class="footer">{{ $data->branding['footer_text'] }}</div>
</body>
</html>
