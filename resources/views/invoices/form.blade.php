@extends('layouts.app')
@section('content')
<div dir="rtl" class="container py-4">
    <h1>{{ $invoice->exists ? 'تعديل فاتورة' : 'إنشاء فاتورة يدوية' }}</h1>
    <form method="post" action="{{ $invoice->exists ? route('invoices.update', $invoice) : route('invoices.store') }}" class="card card-body">
        @csrf
        @if($invoice->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">المنشأة</label><select name="supplier_id" class="form-select" required>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(old('supplier_id', $invoice->supplier_id) == $company->id)>{{ $company->legal_name_ar }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">العميل</label><select name="customer_id" class="form-select"><option value="">عميل نقدي</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', $invoice->customer_id) == $customer->id)>{{ $customer->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">تاريخ الإصدار</label><input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', $invoice->issue_date?->format('Y-m-d') ?: now()->toDateString()) }}" required></div>
            <div class="col-md-2"><label class="form-label">وقت الإصدار</label><input type="time" step="1" name="issue_time" class="form-control ltr" value="{{ old('issue_time', $invoice->issue_time ?: now()->format('H:i:s')) }}" required></div>
            <div class="col-md-3"><label class="form-label">نوع المكلف</label><select name="taxpayer_type" class="form-select"><option value="income" @selected(old('taxpayer_type', $invoice->taxpayer_type ?: 'income') === 'income')>دخل</option><option value="general_sales" @selected(old('taxpayer_type', $invoice->taxpayer_type) === 'general_sales')>مبيعات عامة</option><option value="special_sales" @selected(old('taxpayer_type', $invoice->taxpayer_type) === 'special_sales')>مبيعات خاصة</option></select></div>
            <div class="col-md-3"><label class="form-label">طريقة الدفع</label><select name="payment_type" class="form-select"><option value="receivable" @selected(old('payment_type', $invoice->payment_type ?: 'receivable') === 'receivable')>ذمم</option><option value="cash" @selected(old('payment_type', $invoice->payment_type) === 'cash')>نقدي</option></select></div>
            <div class="col-md-3"><label class="form-label">النطاق</label><select name="invoice_scope" class="form-select"><option value="local" @selected(old('invoice_scope', $invoice->invoice_scope ?: 'local') === 'local')>محلي</option><option value="export" @selected(old('invoice_scope', $invoice->invoice_scope) === 'export')>تصدير</option><option value="development_area" @selected(old('invoice_scope', $invoice->invoice_scope) === 'development_area')>منطقة تنموية</option></select></div>
            <div class="col-md-3"><label class="form-label">العملة</label><input name="currency_code" class="form-control" value="{{ old('currency_code', $invoice->currency_code ?: 'JOD') }}" required maxlength="3"></div>
        </div>

        <h4 class="mt-4">الأصناف</h4>
        <table class="table" id="items-table">
            <thead><tr><th>الصنف</th><th>الكمية</th><th>سعر الوحدة</th><th>الخصم</th><th>الضريبة %</th><th>المجموع</th><th></th></tr></thead>
            <tbody>
            @php($rows = old('items', $invoice->items?->map(fn($i) => $i->only(['description','quantity','unit_price','discount','tax_percent']))->all() ?: [['description'=>'','quantity'=>'1','unit_price'=>'0','discount'=>'0','tax_percent'=>'0']]))
            @foreach($rows as $index => $item)
                <tr><td><input name="items[{{ $index }}][description]" class="form-control" value="{{ $item['description'] ?? '' }}" required></td><td><input name="items[{{ $index }}][quantity]" type="number" step="0.001" min="0.001" class="form-control calc" value="{{ $item['quantity'] ?? 1 }}" required></td><td><input name="items[{{ $index }}][unit_price]" type="number" step="0.001" min="0" class="form-control calc" value="{{ $item['unit_price'] ?? 0 }}" required></td><td><input name="items[{{ $index }}][discount]" type="number" step="0.001" min="0" class="form-control calc" value="{{ $item['discount'] ?? 0 }}"></td><td><input name="items[{{ $index }}][tax_percent]" type="number" step="0.001" min="0" class="form-control calc" value="{{ $item['tax_percent'] ?? 0 }}"></td><td class="line-total ltr">0.000</td><td><button type="button" class="btn btn-sm btn-danger remove-row">حذف</button></td></tr>
            @endforeach
            </tbody>
        </table>
        <button type="button" id="add-row" class="btn btn-outline-primary">إضافة صنف</button>
        <h3 class="mt-3">الإجمالي: <span id="grand-total" class="ltr">0.000</span> JOD</h3>
        <button class="btn btn-success mt-3">حفظ مسودة</button>
    </form>
</div>
<script>
function recalc(){let total=0;document.querySelectorAll('#items-table tbody tr').forEach(row=>{const nums=row.querySelectorAll('.calc');const q=parseFloat(nums[0].value)||0,p=parseFloat(nums[1].value)||0,d=parseFloat(nums[2].value)||0,t=parseFloat(nums[3].value)||0;const line=Math.max(q*p-d,0)*(1+t/100);row.querySelector('.line-total').textContent=line.toFixed(3);total+=line;});document.getElementById('grand-total').textContent=total.toFixed(3)}
document.addEventListener('input',e=>{if(e.target.classList.contains('calc'))recalc()});document.getElementById('add-row').onclick=()=>{const tbody=document.querySelector('#items-table tbody'),i=tbody.children.length;tbody.insertAdjacentHTML('beforeend',`<tr><td><input name="items[${i}][description]" class="form-control" required></td><td><input name="items[${i}][quantity]" type="number" step="0.001" min="0.001" class="form-control calc" value="1" required></td><td><input name="items[${i}][unit_price]" type="number" step="0.001" min="0" class="form-control calc" value="0" required></td><td><input name="items[${i}][discount]" type="number" step="0.001" min="0" class="form-control calc" value="0"></td><td><input name="items[${i}][tax_percent]" type="number" step="0.001" min="0" class="form-control calc" value="0"></td><td class="line-total ltr">0.000</td><td><button type="button" class="btn btn-sm btn-danger remove-row">حذف</button></td></tr>`);recalc()};document.addEventListener('click',e=>{if(e.target.classList.contains('remove-row')){e.target.closest('tr').remove();recalc()}});recalc();
</script>
@endsection
