@php($doc = $doc ?? [])
<article class="invoice-page" dir="rtl">
    <header class="invoice-header avoid-break">
        <section class="invoice-brand">
            @if(!empty($doc['company']['logo']))
                <img class="invoice-logo" src="{{ asset($doc['company']['logo']) }}" alt="شعار المنشأة">
            @else
                <div class="invoice-logo-fallback">{{ mb_substr($doc['company']['name'] ?? 'IH', 0, 2) }}</div>
            @endif
            <div>
                <h2>{{ $doc['company']['name'] ?? '—' }}</h2>
                @if(!empty($doc['company']['legal_name']) && $doc['company']['legal_name'] !== ($doc['company']['name'] ?? null))
                    <div class="muted">{{ $doc['company']['legal_name'] }}</div>
                @endif
                <div class="muted">الرقم الضريبي: {{ $doc['company']['tax_number'] ?? '—' }}</div>
                @if(!empty($doc['company']['national_number']))
                    <div class="muted">الرقم الوطني/التسجيل: {{ $doc['company']['national_number'] }}</div>
                @endif
                @if(!empty($doc['company']['address']))
                    <div class="muted">{{ $doc['company']['address'] }}</div>
                @endif
            </div>
        </section>
        <section class="invoice-title">
            <h1>{{ $doc['invoice']['type'] ?? 'فاتورة' }}</h1>
            <div class="invoice-badge">{{ $doc['invoice']['status'] ?? '—' }}</div>
            <div class="invoice-badge">JoFotara: {{ $doc['invoice']['jofotara_status'] ?? 'غير مرسلة' }}</div>
            <div class="kv"><span>رقم الفاتورة</span><strong>{{ $doc['invoice']['number'] ?? '—' }}</strong></div>
            @if(!empty($doc['invoice']['uuid']))
                <div class="kv"><span>UUID</span><span class="num">{{ $doc['invoice']['uuid'] }}</span></div>
            @endif
        </section>
    </header>

    <section class="invoice-grid avoid-break">
        <div class="invoice-card">
            <h3>بيانات الفاتورة</h3>
            <div class="kv"><span>تاريخ الإصدار</span><span>{{ $doc['invoice']['issue_date'] ?? '—' }}</span></div>
            <div class="kv"><span>وقت الإصدار</span><span>{{ $doc['invoice']['issue_time'] ?? '—' }}</span></div>
            <div class="kv"><span>تاريخ الاستحقاق</span><span>{{ $doc['invoice']['due_date'] ?? '—' }}</span></div>
            <div class="kv"><span>طريقة الدفع</span><span>{{ $doc['invoice']['payment'] ?? '—' }}</span></div>
            <div class="kv"><span>نتيجة التحقق</span><span>{{ $doc['invoice']['validation'] ?? '—' }}</span></div>
        </div>
        <div class="invoice-card">
            <h3>بيانات العميل</h3>
            <div class="kv"><span>الاسم</span><span>{{ $doc['customer']['name'] ?? 'عميل نقدي' }}</span></div>
            <div class="kv"><span>الرقم الضريبي</span><span>{{ $doc['customer']['tax_number'] ?? '—' }}</span></div>
            <div class="kv"><span>الرقم الوطني</span><span>{{ $doc['customer']['national_number'] ?? '—' }}</span></div>
            <div class="kv"><span>الهاتف</span><span>{{ $doc['customer']['phone'] ?? '—' }}</span></div>
            <div class="kv"><span>العنوان</span><span>{{ $doc['customer']['address'] ?? '—' }}</span></div>
        </div>
    </section>

    <table class="invoice-items">
        <thead><tr><th style="width:34%">المنتج/الخدمة والوصف</th><th>الكمية</th><th>سعر الوحدة</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr></thead>
        <tbody>
        @forelse($doc['items'] ?? [] as $item)
            <tr>
                <td>
                    <strong>{{ $item['product'] ?: $item['description'] }}</strong>
                    @if($item['product'] && $item['description'])<div class="muted">{{ $item['description'] }}</div>@endif
                </td>
                <td class="num">{{ $item['quantity'] }}</td>
                <td class="num">{{ $item['unit_price'] }}</td>
                <td class="num">{{ $item['discount'] }}</td>
                <td class="num">{{ $item['tax'] }}<br><span class="muted">{{ $item['tax_percent'] }}</span></td>
                <td class="num"><strong>{{ $item['total'] }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="6">لا توجد بنود.</td></tr>
        @endforelse
        </tbody>
    </table>

    <table class="invoice-totals avoid-break">
        <tr><th>الإجمالي قبل الخصم</th><td class="num">{{ $doc['totals']['subtotal'] ?? '—' }}</td></tr>
        <tr><th>مجموع الخصومات</th><td class="num">{{ $doc['totals']['discount'] ?? '—' }}</td></tr>
        <tr><th>الخاضع للضريبة</th><td class="num">{{ $doc['totals']['taxable'] ?? '—' }}</td></tr>
        <tr><th>مجموع الضرائب</th><td class="num">{{ $doc['totals']['tax'] ?? '—' }}</td></tr>
        <tr class="grand"><th>الإجمالي النهائي / المستحق</th><td class="num">{{ $doc['totals']['payable'] ?? ($doc['totals']['grand'] ?? '—') }}</td></tr>
    </table>

    <section class="invoice-grid avoid-break">
        <div class="invoice-card">
            <h3>رمز QR الرسمي</h3>
            @if(!empty($doc['qr']['data_uri']))
                <div class="invoice-qr"><img src="{{ $doc['qr']['data_uri'] }}" alt="رمز QR الرسمي من JoFotara"><div class="muted">تم إنشاء الصورة من قيمة QR الرسمية الراجعة من نظام الفوترة الوطني دون استخدام رابط داخلي أو UUID محلي.</div></div>
            @else
                <div class="qr-note">رمز QR الرسمي غير متوفر لأن الفاتورة لم تُعتمد بعد من نظام الفوترة الوطني.</div>
            @endif
        </div>
        @if(!empty($doc['invoice']['notes']))
            <div class="invoice-card"><h3>ملاحظات</h3><div class="invoice-notes">{{ $doc['invoice']['notes'] }}</div></div>
        @endif
    </section>
</article>
