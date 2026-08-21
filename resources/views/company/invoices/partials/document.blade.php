@php($doc = $doc ?? [])
<article class="invoice-page invoice-document" lang="{{ $language ?? 'ar' }}" dir="{{ $direction ?? 'rtl' }}">
    <header class="invoice-header avoid-break">
        <div class="invoice-header-main">
            <section class="invoice-brand">
                <div class="invoice-logo-box">
                    @if(!empty($doc['company']['logo_data_uri']))
                        <img src="{{ $doc['company']['logo_data_uri'] }}" alt="شعار المنشأة">
                    @else
                        <span class="invoice-logo-fallback">{{ mb_substr($doc['company']['name'] ?? 'IH', 0, 2) }}</span>
                    @endif
                </div>
                <div class="invoice-brand-copy">
                    <h2>{{ $doc['company']['name'] ?? '—' }}</h2>
                    @if(!empty($doc['company']['legal_name']) && $doc['company']['legal_name'] !== ($doc['company']['name'] ?? null))
                        <div class="muted">{{ $doc['company']['legal_name'] }}</div>
                    @endif
                    <div class="muted">الرقم الضريبي: <span class="invoice-number inline-num">{{ $doc['company']['tax_number'] ?? '—' }}</span></div>
                    @if(!empty($doc['company']['national_number']))
                        <div class="muted">الرقم الوطني/التسجيل: <span class="invoice-number inline-num">{{ $doc['company']['national_number'] }}</span></div>
                    @endif
                    @if(!empty($doc['company']['address']))
                        <div class="muted">{{ $doc['company']['address'] }}</div>
                    @endif
                </div>
            </section>

            <section class="invoice-national-brand">
                @if(($doc['jofotara']['submitted'] ?? false) && !empty($doc['jofotara']['logo_data_uri']))
                    <div class="invoice-logo-box invoice-logo-box-national">
                        <img src="{{ $doc['jofotara']['logo_data_uri'] }}" alt="شعار نظام الفوترة الوطني JoFotara">
                    </div>
                    <div class="invoice-national-copy">
                        <strong>نظام الفوترة الوطني</strong>
                        <span class="muted">JoFotara</span>
                    </div>
                @endif
            </section>
        </div>

        <div class="invoice-heading-row">
            <div class="invoice-title">
                <h1>{{ $doc['invoice']['type'] ?? 'فاتورة' }}</h1>
                <div class="invoice-statuses">
                    <span class="invoice-badge">{{ $doc['invoice']['status'] ?? '—' }}</span>
                    <span class="invoice-badge invoice-badge-national">JoFotara: {{ $doc['invoice']['jofotara_status'] ?? 'غير مرسلة' }}</span>
                </div>
            </div>
            <div class="invoice-reference">
                <span>رقم الفاتورة</span>
                <strong class="invoice-number">{{ $doc['invoice']['number'] ?? '—' }}</strong>
            </div>
        </div>
    </header>

    <section class="invoice-grid invoice-info-grid avoid-break">
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

    <table class="invoice-items invoice-items-table">
        <thead><tr><th class="invoice-item-description">المنتج/الخدمة والوصف</th><th>الكمية</th><th>سعر الوحدة</th><th>الخصم</th><th>الضريبة</th><th>الإجمالي</th></tr></thead>
        <tbody>
        @forelse($doc['items'] ?? [] as $item)
            <tr>
                <td>
                    <strong>{{ $item['product'] ?: $item['description'] }}</strong>
                    @if($item['product'] && $item['description'])<div class="muted">{{ $item['description'] }}</div>@endif
                </td>
                <td class="invoice-number">{{ $item['quantity'] }}</td>
                <td class="invoice-number">{{ $item['unit_price'] }}</td>
                <td class="invoice-number">{{ $item['discount'] }}</td>
                <td class="invoice-number">{{ $item['tax'] }}<br><span class="invoice-tax-rate">{{ $item['tax_percent'] }}</span></td>
                <td class="invoice-number invoice-line-total"><strong>{{ $item['total'] }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="6">لا توجد بنود.</td></tr>
        @endforelse
        </tbody>
    </table>

    <section class="invoice-closing avoid-break">
        <table class="invoice-totals">
            <tr><th>الإجمالي قبل الخصم</th><td class="invoice-number">{{ $doc['totals']['subtotal'] ?? '—' }}</td></tr>
            <tr><th>مجموع الخصومات</th><td class="invoice-number">{{ $doc['totals']['discount'] ?? '—' }}</td></tr>
            <tr><th>الخاضع للضريبة</th><td class="invoice-number">{{ $doc['totals']['taxable'] ?? '—' }}</td></tr>
            <tr><th>مجموع الضرائب</th><td class="invoice-number">{{ $doc['totals']['tax'] ?? '—' }}</td></tr>
            <tr class="grand"><th>الإجمالي النهائي / المستحق</th><td class="invoice-number">{{ $doc['totals']['payable'] ?? ($doc['totals']['grand'] ?? '—') }}</td></tr>
        </table>

        <figure class="invoice-qr-block">
            @if(!empty($doc['qr']['data_uri']))
                <img class="official-jofotara-qr" src="{{ $qrImageUrl ?? $doc['qr']['data_uri'] }}" alt="رمز QR الرسمي من JoFotara">
                <figcaption>رمز QR الرسمي</figcaption>
            @else
                <div class="qr-note">رمز QR الرسمي غير متوفر لأن الفاتورة لم تُعتمد بعد من نظام الفوترة الوطني.</div>
            @endif
        </figure>
    </section>

    @if(!empty($doc['invoice']['notes']))
        <section class="invoice-card invoice-notes-card avoid-break"><h3>ملاحظات</h3><div class="invoice-notes">{{ $doc['invoice']['notes'] }}</div></section>
    @endif
</article>
