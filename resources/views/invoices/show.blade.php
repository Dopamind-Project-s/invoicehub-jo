@extends('layouts.app')
@section('content')
<div dir="rtl" class="container py-4">
    <div class="alert alert-warning">الإرسال الحقيقي إلى نظام الفوترة الوطني سيقوم بمحاولة إصدار فاتورة فعلية.</div>
    @if(session('success'))<pre class="alert alert-success text-start" dir="ltr">{{ session('success') }}</pre>@endif
    @if(session('error'))<pre class="alert alert-danger text-start" dir="ltr">{{ session('error') }}</pre>@endif

    <div class="d-flex justify-content-between align-items-center">
        <h1>فاتورة {{ $invoice->invoice_number }}</h1>
        <a class="btn btn-secondary" href="{{ route('invoices.edit', $invoice) }}">تعديل</a> <a class="btn btn-dark" href="{{ route('invoices.issued-pdf', $invoice) }}">تحميل PDF النهائي</a>
    </div>

    <div class="card card-body mt-3">
        <div class="row">
            <div class="col-md-6">
                <p><strong>البائع:</strong> {{ $invoice->supplier?->legal_name_ar }}</p>
                <p><strong>الرقم الضريبي:</strong> {{ $invoice->supplier?->tax_number }}</p>
                <p><strong>تسلسل مصدر الدخل:</strong> {{ $invoice->supplier?->jofotara_source_id }}</p>
                <p><strong>الهاتف:</strong> {{ $invoice->supplier?->phone }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>العميل:</strong> {{ $invoice->customer?->name ?? 'عميل نقدي' }}</p>
                <p><strong>UUID:</strong> <span dir="ltr">{{ $invoice->uuid }}</span></p>
                <p><strong>ICV:</strong> {{ $invoice->icv }}</p>
                <p><strong>الحالة:</strong> {{ $invoice->status }}</p>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap my-3">
            <form method="post" action="{{ route('invoices.prepare', $invoice) }}">@csrf<button class="btn btn-info">تجهيز XML</button></form>
            <form method="post" action="{{ route('invoices.submit-to-jofotara', $invoice) }}">@csrf<button class="btn btn-danger" data-confirm="الإرسال الحقيقي إلى نظام الفوترة الوطني سيقوم بمحاولة إصدار فاتورة فعلية. هل أنت متأكد؟">إرسال فعلي إلى جوفوتارا</button></form>
            <a class="btn btn-outline-secondary" href="{{ route('invoices.download-xml', $invoice) }}">Download XML</a>
            <a class="btn btn-outline-secondary" href="{{ route('invoices.download-payload', $invoice) }}">Download Payload</a>
        </div>

        <table class="table">
            <tr><th>الوصف</th><th>الكمية</th><th>سعر الوحدة</th><th>الخصم</th><th>الضريبة</th><th>المجموع</th></tr>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit_price }}</td>
                    <td>{{ $item->discount }}</td>
                    <td>{{ $item->tax_amount }}</td>
                    <td>{{ $item->line_total }}</td>
                </tr>
            @endforeach
        </table>
        <h5>Subtotal: {{ $invoice->subtotal }} JOD</h5>
        <h5>Discount: {{ $invoice->discount_amount }} JOD</h5>
        <h5>Tax: {{ $invoice->tax_amount }} JOD</h5>
        <h3>Total: {{ $invoice->payable_amount }} JOD</h3>


        <h4 class="mt-4">QR</h4>
        @if($invoice->status === 'ACCEPTED' && $invoice->qr_code)
            <img src="{{ route('invoices.qr', $invoice) }}" alt="QR" class="qr-image">
        @else
            <p class="text-muted">لا يوجد رمز QR مقبول لهذه الفاتورة.</p>
        @endif
        <form method="post" action="{{ route('invoices.update-qr', $invoice) }}" class="mt-2">@csrf<input name="qr_code" class="form-control" placeholder="تحديث QR يدوياً عند الحاجة" value=""><button class="btn btn-outline-primary mt-2">حفظ QR</button></form>

        <h4 class="mt-4">رد جوفوتارا</h4>
        <pre dir="ltr" class="bg-light p-3">{{ $invoice->submission_response ?: 'لا يوجد رد بعد.' }}</pre>
    </div>
</div>
@endsection
