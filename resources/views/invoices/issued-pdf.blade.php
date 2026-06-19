<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<style>
@page { margin: 22px; }
body { direction: rtl; text-align: right; font-family: "DejaVu Sans", sans-serif; color:#111; font-size:12px; }
table { width:100%; border-collapse: collapse; margin-top:10px; }
th, td { border:1px solid #b8b8b8; padding:7px; vertical-align: top; }
th { background:#f1f4f8; font-weight:bold; }
.header td { border:none; }
.title { font-size:24px; font-weight:bold; color:#123; }
.subtitle { color:#555; margin-top:4px; }
.ltr { direction:ltr; unicode-bidi:embed; text-align:left; }
.totals { width:42%; margin-right:auto; }
.qrbox { text-align:center; border:1px solid #bbb; padding:10px; margin-top:12px; width:170px; }
.qrbox img { width:145px; height:145px; }
.watermark { position:fixed; top:40%; left:18%; font-size:54px; color:#c00; opacity:.14; transform:rotate(-20deg); }
.footer { margin-top:22px; text-align:center; color:#555; border-top:1px solid #ccc; padding-top:10px; }
</style>
</head>
<body>
@if($invoice->status !== 'ACCEPTED')<div class="watermark">غير مَفْوترة</div>@endif
<table class="header"><tr><td><div class="title">{{ $invoice->status === 'ACCEPTED' ? 'فاتورة مَفْوترة' : 'مسودة فاتورة' }}</div><div class="subtitle">دخل ذمم / محلي / JOD</div></td><td class="ltr" style="text-align:left">{{ $invoice->invoice_number }}</td></tr></table>
<table><tr><th colspan="2">البائع</th><th colspan="2">العميل</th></tr><tr><td>البائع</td><td>{{ $invoice->supplier?->legal_name_ar }}</td><td>العميل</td><td>{{ $invoice->customer?->name ?? 'عميل نقدي' }}</td></tr><tr><td>الرقم الضريبي</td><td class="ltr">{{ $invoice->supplier?->tax_number }}</td><td>هاتف العميل</td><td class="ltr">{{ $invoice->customer?->phone ?: '-' }}</td></tr><tr><td>تسلسل مصدر الدخل</td><td class="ltr">{{ $invoice->supplier?->jofotara_source_id }}</td><td>العنوان</td><td>{{ $invoice->customer?->address ?: '-' }}</td></tr><tr><td>هاتف البائع</td><td class="ltr">{{ $invoice->supplier?->phone }}</td><td>عنوان البائع</td><td>{{ $invoice->supplier?->street ?: $invoice->supplier?->city }}</td></tr></table>
<table><tr><th>رقم الفاتورة</th><th>UUID</th><th>ICV</th><th>تاريخ الإصدار</th><th>الحالة</th></tr><tr><td class="ltr">{{ $invoice->invoice_number }}</td><td class="ltr">{{ $invoice->uuid }}</td><td class="ltr">{{ $invoice->icv }}</td><td class="ltr">{{ $invoice->issue_date?->format('Y-m-d') }} {{ $invoice->issue_time }}</td><td>{{ $invoice->status }}</td></tr></table>
<table><tr><th>#</th><th>الصنف</th><th>الكمية</th><th>سعر الوحدة</th><th>الخصم</th><th>الضريبة</th><th>المجموع</th></tr>@foreach($invoice->items as $i => $item)<tr><td class="ltr">{{ $i + 1 }}</td><td>{{ $item->description }}</td><td class="ltr">{{ $item->quantity }}</td><td class="ltr">{{ $item->unit_price }}</td><td class="ltr">{{ $item->discount }}</td><td class="ltr">{{ $item->tax_amount }}</td><td class="ltr">{{ $item->line_total }}</td></tr>@endforeach</table>
<table class="totals"><tr><td>Subtotal</td><td class="ltr">{{ $invoice->subtotal }} JOD</td></tr><tr><td>Discount</td><td class="ltr">{{ $invoice->discount_amount }} JOD</td></tr><tr><td>Tax</td><td class="ltr">{{ $invoice->tax_amount }} JOD</td></tr><tr><th>الإجمالي</th><th class="ltr">{{ $invoice->payable_amount }} JOD</th></tr></table>
<div class="qrbox">@if($qrDataUri)<img src="{{ $qrDataUri }}" alt="QR"><div>رمز QR</div>@else<div>لا يوجد رمز QR مقبول لهذه الفاتورة.</div>@endif</div>
<div class="footer">تم إصدار هذه الفاتورة عبر نظام الفوترة الوطني الأردني JoFotara.</div>
</body>
</html>
