@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>الفواتير</h1>
    <a class="btn btn-primary" href="{{ route('invoices.create') }}">فاتورة جديدة</a>
</div>
<div class="card">
    <table class="table mb-0">
        <tr><th>الرقم</th><th>البائع</th><th>العميل</th><th>التاريخ</th><th>الإجمالي</th><th>الحالة</th><th></th></tr>
        @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->seller?->name }}</td>
                <td>{{ $invoice->customer?->name }}</td>
                <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                <td>{{ number_format($invoice->total, 3) }}</td>
                <td>{{ $invoice->status }}</td>
                <td><a href="{{ route('invoices.show', $invoice) }}">عرض</a></td>
            </tr>
        @endforeach
    </table>
</div>
{{ $invoices->links() }}
@endsection
