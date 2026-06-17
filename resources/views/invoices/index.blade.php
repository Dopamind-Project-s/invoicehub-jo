@extends('layouts.app')
@section('content')
<div dir="rtl" class="container py-4">
    <div class="alert alert-warning">
        الإرسال الحقيقي إلى نظام الفوترة الوطني سيقوم بمحاولة إصدار فاتورة فعلية.
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>الفواتير الإلكترونية - جوفوتارا</h1>
        <div class="d-flex gap-2"><a class="btn btn-success" href="{{ route('invoices.create') }}">فاتورة يدوية</a><form method="post" action="{{ route('jofotara.create-real-sample') }}">
            @csrf
            <button class="btn btn-primary" onclick="return confirm('إنشاء فاتورة عينة حقيقية جاهزة لجوفوتارا؟')">إنشاء فاتورة عينة حقيقية</button>
        </form></div>
    </div>
    @if(session('success'))<pre class="alert alert-success text-start" dir="ltr">{{ session('success') }}</pre>@endif
    @if(session('error'))<pre class="alert alert-danger text-start" dir="ltr">{{ session('error') }}</pre>@endif
    <div class="card">
        <table class="table mb-0 align-middle">
            <tr><th>الرقم</th><th>البائع</th><th>العميل</th><th>التاريخ</th><th>ICV</th><th>الإجمالي</th><th>الحالة</th><th></th></tr>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->supplier?->legal_name_ar }}</td>
                    <td>{{ $invoice->customer?->name ?? 'عميل نقدي' }}</td>
                    <td>{{ $invoice->issue_date?->format('Y-m-d') }}</td>
                    <td>{{ $invoice->icv }}</td>
                    <td>{{ number_format((float) $invoice->payable_amount, 3) }} JOD</td>
                    <td><span class="badge bg-secondary">{{ $invoice->status }}</span></td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('invoices.show', $invoice) }}">عرض</a></td>
                </tr>
            @endforeach
        </table>
    </div>
    {{ $invoices->links() }}
</div>
@endsection
