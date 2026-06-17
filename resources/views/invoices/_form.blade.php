@csrf
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">رقم الفاتورة</label>
        <input class="form-control" value="{{ $invoice->invoice_number }}" disabled>
    </div>
    <div class="col-md-3">
        <label class="form-label">البائع</label>
        <select name="seller_id" class="form-select">
            <option value="">بدون بائع</option>
            @foreach($sellers as $seller)
                <option value="{{ $seller->id }}" @selected(old('seller_id', $invoice->seller_id) == $seller->id)>{{ $seller->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">العميل</label>
        <select name="customer_id" class="form-select">
            <option value="">عميل نقدي</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $invoice->customer_id) == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">تاريخ الفاتورة</label>
        <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', optional($invoice->invoice_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div class="col-md-1">
        <label class="form-label">الاستحقاق</label>
        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}">
    </div>
</div>
<hr>
<table class="table" id="items">
    <thead>
    <tr><th>الوصف</th><th>الكمية</th><th>سعر الوحدة</th><th>الضريبة %</th><th>المبلغ</th><th></th></tr>
    </thead>
    <tbody>
    @php($rows = old('items', $invoice->items->toArray() ?: [['description' => '', 'quantity' => 1, 'unit_price' => 0, 'tax_rate' => 0]]))
    @foreach($rows as $i => $item)
        <tr>
            <td><input name="items[{{ $i }}][description]" class="form-control" value="{{ $item['description'] ?? '' }}" required></td>
            <td><input name="items[{{ $i }}][quantity]" class="form-control calc" value="{{ $item['quantity'] ?? 1 }}" type="number" step="0.001" required></td>
            <td><input name="items[{{ $i }}][unit_price]" class="form-control calc" value="{{ $item['unit_price'] ?? 0 }}" type="number" step="0.001" required></td>
            <td><input name="items[{{ $i }}][tax_rate]" class="form-control calc" value="{{ $item['tax_rate'] ?? 0 }}" type="number" step="0.01"></td>
            <td class="line">0.000</td>
            <td><button type="button" class="btn btn-sm btn-danger del">×</button></td>
        </tr>
    @endforeach
    </tbody>
</table>
<button type="button" id="add" class="btn btn-secondary">إضافة بند</button>
<div class="text-end fs-5 mt-3">المجموع: <span id="subtotal">0.000</span> | الضريبة: <span id="tax">0.000</span> | الإجمالي: <b id="total">0.000</b> JOD</div>
<button class="btn btn-primary mt-3">حفظ</button>
<script>
    let idx = {{ count($rows) }};
    function calc() {
        let sub = 0, tax = 0;
        document.querySelectorAll('#items tbody tr').forEach(row => {
            let q = +row.children[1].querySelector('input').value || 0;
            let p = +row.children[2].querySelector('input').value || 0;
            let r = +row.children[3].querySelector('input').value || 0;
            let base = q * p;
            let tx = base * r / 100;
            row.querySelector('.line').textContent = (base + tx).toFixed(3);
            sub += base;
            tax += tx;
        });
        subtotal.textContent = sub.toFixed(3);
        window.tax.textContent = tax.toFixed(3);
        total.textContent = (sub + tax).toFixed(3);
    }
    document.addEventListener('input', event => { if (event.target.classList.contains('calc')) calc(); });
    add.onclick = () => {
        document.querySelector('#items tbody').insertAdjacentHTML('beforeend', `<tr><td><input name="items[${idx}][description]" class="form-control" required></td><td><input name="items[${idx}][quantity]" class="form-control calc" value="1" type="number" step="0.001" required></td><td><input name="items[${idx}][unit_price]" class="form-control calc" value="0" type="number" step="0.001" required></td><td><input name="items[${idx}][tax_rate]" class="form-control calc" value="0" type="number" step="0.01"></td><td class="line">0.000</td><td><button type="button" class="btn btn-sm btn-danger del">×</button></td></tr>`);
        idx++;
        calc();
    };
    document.addEventListener('click', event => { if (event.target.classList.contains('del')) { event.target.closest('tr').remove(); calc(); } });
    calc();
</script>
