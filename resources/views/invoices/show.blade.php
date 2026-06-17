@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between">
    <h1>فاتورة {{ $invoice->invoice_number }}</h1>
    <div>
        <a class="btn btn-secondary" href="{{ route('invoices.edit', $invoice) }}">تعديل</a>
        <a class="btn btn-info" href="{{ route('invoices.preview', $invoice) }}">معاينة</a>
        <a class="btn btn-dark" href="{{ route('invoices.pdf', $invoice) }}">PDF</a>
    </div>
</div>
<div class="card card-body mt-3">
    <p>البائع: {{ $invoice->seller?->name ?? 'غير محدد' }}</p>
    <p>العميل: {{ $invoice->customer?->name ?? 'عميل نقدي' }}</p>
    <p>الحالة: {{ $invoice->status }}</p>
    <table class="table">
        <tr><th>الوصف</th><th>الكمية</th><th>سعر الوحدة</th><th>الضريبة</th><th>المبلغ</th></tr>
        @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ number_format($item->quantity, 3) }}</td>
                <td>{{ number_format($item->unit_price, 3) }}</td>
                <td>{{ number_format($item->tax_amount, 3) }}</td>
                <td>{{ number_format($item->line_total, 3) }}</td>
            </tr>
        @endforeach
    </table>
    <h3 class="text-end">الإجمالي {{ number_format($invoice->total, 3) }} JOD</h3>
    <form method="post" action="{{ route('invoices.submit-to-jofotara', $invoice) }}">
        @csrf
        <button class="btn btn-success">إرسال إلى جوفوتارا</button>
    </form>
    @if($invoice->jofotara_response)
        <pre class="mt-3 bg-light p-3">{{ $invoice->jofotara_response }}</pre>
    @endif
</div>
@endsection
