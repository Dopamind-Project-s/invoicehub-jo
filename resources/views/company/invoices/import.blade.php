@extends('layouts.company-workspace')
@section('title', 'استيراد فواتير جوفوتارا')
@section('content')
<x-layout.page-header title="استيراد فواتير جوفوتارا" subtitle="استيراد فواتير سابقة ومزامنة فواتير الفوترة الوطنية عند توفر endpoint رسمي. حالياً يتم دعم backfill عبر JSON/CSV بدون اختلاق واجهة سحب رسمية.">
    <x-slot:actions><a class="btn btn-outline-secondary" href="{{ route('company.invoices.index', $company) }}">عودة للفواتير</a></x-slot:actions>
</x-layout.page-header>
<form method="post" enctype="multipart/form-data" action="{{ route('company.invoices.import.store', $company) }}" class="card card-body mb-3">
    @csrf
    <label class="form-label">ملف الاستيراد</label>
    <input type="file" name="import_file" accept=".json,.csv,.txt" class="form-control" required>
    <div class="form-text">الحقول المدعومة: invoice_number, issue_date, total, currency, jofotara_uuid, jofotara_status, jofotara_qr.</div>
    <div class="alert alert-info mt-3 mb-0">المزامنة الآلية تتطلب endpoint/صلاحية رسمية من نظام الفوترة الوطني. استخدم الاستيراد كحل Phase 1 آمن.</div><button class="btn btn-primary mt-3">استيراد فواتير سابقة</button>
</form>
<div class="card"><table class="table mb-0"><tr><th>الفاتورة</th><th>التاريخ</th><th>الحالة</th><th>UUID</th><th>الإجمالي</th></tr>@foreach($imports as $invoice)<tr><td>{{ $invoice->invoice_number }}</td><td>{{ $invoice->issue_date?->format('Y-m-d') }}</td><td>{{ $invoice->jofotara_status }}</td><td>{{ $invoice->jofotara_uuid ?: '—' }}</td><td>{{ $invoice->grand_total }}</td></tr>@endforeach</table></div>{{ $imports->links() }}
@endsection
