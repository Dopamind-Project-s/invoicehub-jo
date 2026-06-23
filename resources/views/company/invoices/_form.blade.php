@csrf
@php
    $primary = $branding['primary_color'] ?? '#00a9c4';
    $secondary = $branding['secondary_color'] ?? '#12c2b2';
    $rows = old('items', $invoice->items?->toArray() ?: [['description'=>'','quantity'=>'1','unit_price'=>'0','discount_amount'=>'0','tax_percent'=>'0']]);
    $templateSlug = $branding['template']?->slug ?? 'arabic-classic';
@endphp
<div class="invoice-form-shell theme-{{ $templateSlug }}" dir="rtl">
    <div class="invoice-form-banner d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
        <div>
            <h2 class="h4 mb-1">🧾 بيانات الفاتورة</h2>
            <p class="mb-0 opacity-75">املأ البيانات الأساسية والبنود، وسيتم تطبيق قالب الطباعة الافتراضي عند المعاينة أو التنزيل.</p>
        </div>
        <span class="template-chip bg-white text-dark">🎨 {{ $branding['template']?->name ?? 'Arabic Classic' }}</span>
    </div>

    <div class="invoice-form-body">
        <section class="form-section">
            <div class="form-section-title">👤 بيانات العميل والفاتورة</div>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">جهة الاتصال</label><select name="contact_id" class="form-select" required><option value="">اختر العميل</option>@foreach($contacts as $contact)<option value="{{ $contact->id }}" @selected((string) old('contact_id', $invoice->contact_id)===(string) $contact->id)>{{ $contact->name_ar }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">نوع الفاتورة</label><select name="invoice_type" class="form-select">@foreach(['tax_invoice'=>'فاتورة ضريبية','simplified_invoice'=>'فاتورة مبسطة','credit_note'=>'إشعار دائن','debit_note'=>'إشعار مدين'] as $value => $label)<option value="{{ $value }}" @selected(old('invoice_type', $invoice->invoice_type)===$value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label">تاريخ الإصدار</label><input name="issue_date" type="date" class="form-control" value="{{ old('issue_date', optional($invoice->issue_date)->format('Y-m-d') ?: now()->format('Y-m-d')) }}" required></div>
                <div class="col-md-2"><label class="form-label">تاريخ الاستحقاق</label><input name="due_date" type="date" class="form-control" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}"></div>
                <div class="col-md-2"><label class="form-label">العملة</label><input name="currency" class="form-control" value="{{ old('currency', $invoice->currency ?: 'JOD') }}" maxlength="3" required></div>
                <div class="col-md-10"><label class="form-label">ملاحظات تظهر في الفاتورة</label><input name="notes" class="form-control" placeholder="مثال: شكراً لتعاملكم معنا" value="{{ old('notes', $invoice->notes) }}"></div>
            </div>
        </section>

        <section class="form-section">
            <div class="form-section-title">📦 بنود الفاتورة</div>
            <div class="table-responsive">
                <table class="table items-table align-middle">
                    <thead><tr><th>المنتج</th><th>الوصف</th><th>الكمية</th><th>السعر</th><th>الخصم</th><th>الضريبة %</th></tr></thead>
                    <tbody>
                    @foreach($rows as $i => $row)
                        <tr>
                            <td><select name="items[{{ $i }}][product_id]" class="form-select"><option value="">بدون</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((string)($row['product_id'] ?? '')===(string)$product->id)>{{ $product->name_ar }}</option>@endforeach</select></td>
                            <td><input name="items[{{ $i }}][description]" class="form-control" value="{{ $row['description'] ?? '' }}" required></td>
                            <td><input name="items[{{ $i }}][quantity]" type="number" step="0.000001" class="form-control" value="{{ $row['quantity'] ?? 1 }}" required></td>
                            <td><input name="items[{{ $i }}][unit_price]" type="number" step="0.000001" class="form-control" value="{{ $row['unit_price'] ?? 0 }}" required></td>
                            <td><input name="items[{{ $i }}][discount_amount]" type="number" step="0.000001" class="form-control" value="{{ $row['discount_amount'] ?? $row['discount'] ?? 0 }}"></td>
                            <td><input name="items[{{ $i }}][tax_percent]" type="number" step="0.000001" class="form-control" value="{{ $row['tax_percent'] ?? 0 }}"></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted mb-0">💡 يمكنك الحفظ كمسودة للمتابعة لاحقاً، أو تجهيز الفاتورة عندما تصبح جاهزة للإرسال.</p>
        </section>
    </div>

    <div class="form-actions d-flex flex-wrap gap-2">
        <button class="btn btn-outline-primary" name="save_action" value="draft">💾 حفظ كمسودة</button>
        <button class="btn btn-primary" name="save_action" value="ready">🚀 حفظ وتجهيز</button>
        <a class="btn btn-outline-secondary" href="{{ route('company.invoices.index', $company) }}">↩️ إلغاء</a>
    </div>
</div>
