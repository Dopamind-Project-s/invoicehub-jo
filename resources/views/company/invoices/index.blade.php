@extends('layouts.company-workspace')
@section('title', 'الفواتير')
@section('content')
@php
    $primary = $branding['primary_color'] ?? '#00a9c4';
    $secondary = $branding['secondary_color'] ?? '#12c2b2';
    $statusLabels = ['draft'=>'مسودة','ready'=>'جاهزة','submitted'=>'مرسلة','cancelled'=>'ملغاة','pending'=>'مراجعة','approved'=>'معتمدة'];
@endphp
<div class="invoice-shell">
    <div class="invoice-hero d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-2">🧾 الفواتير</h1>
            <p class="mb-0 opacity-75">إدارة الفواتير، حالة الإرسال، وتنزيل النسخ المطابقة للقالب الافتراضي.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(! app()->environment('production'))<a class="btn btn-light invoice-action-btn" href="{{ route('company.invoices.jofotara.uat', $company) }}">🧪 فحص الربط</a>@endif
            @if($company->featureKeys->contains('code', 'JOFOTARA_SYNC'))<a class="btn btn-outline-light invoice-action-btn" href="{{ route('company.invoices.import.index', $company) }}">⬇️ استيراد</a>@endif
            <a class="btn btn-light invoice-action-btn" href="{{ route('company.invoices.create', $company) }}">➕ فاتورة جديدة</a>
        </div>
    </div>

    <form class="invoice-filter card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-3"><label class="form-label small text-muted">بحث</label><input name="search" class="form-control" placeholder="رقم الفاتورة أو العميل" value="{{ request('search') }}"></div>
            <div class="col-lg-2"><label class="form-label small text-muted">الحالة</label><select name="status" class="form-select"><option value="">كل الحالات</option>@foreach(['draft'=>'مسودة','ready'=>'جاهزة للإرسال','submitted'=>'مرسلة للفوترة الوطنية','cancelled'=>'ملغاة'] as $value => $label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label small text-muted">المصدر</label><select name="source" class="form-select"><option value="">كل المصادر</option><option value="local" @selected(request('source')==='local')>محلي</option><option value="jofotara_import" @selected(request('source')==='jofotara_import')>استيراد جوفوتارا</option></select></div>
            <div class="col-lg-3"><label class="form-label small text-muted">النوع</label><select name="invoice_type" class="form-select"><option value="">كل الأنواع</option>@foreach(['tax_invoice'=>'فاتورة ضريبية','simplified_invoice'=>'فاتورة مبسطة','credit_note'=>'إشعار دائن','debit_note'=>'إشعار مدين'] as $value => $label)<option value="{{ $value }}" @selected(request('invoice_type')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-2"><button class="btn btn-primary w-100 invoice-action-btn">🔎 تصفية</button></div>
        </div>
    </form>

    <div class="invoice-list-card card overflow-hidden">
        <div class="table-responsive">
            <table class="table invoice-table mb-0">
                <thead><tr><th>الفاتورة</th><th>العميل</th><th>الحالة</th><th>جوفوتارا</th><th>الإجمالي</th><th class="text-center">إجراءات</th></tr></thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><div class="invoice-number">{{ $invoice->invoice_number }}</div><small class="text-muted">{{ $invoice->issue_date?->format('Y-m-d') }}</small></td>
                        <td>{{ $invoice->contact?->name_ar ?: 'عميل نقدي' }}</td>
                        <td><span class="status-pill {{ $invoice->status }}">● {{ $statusLabels[$invoice->status] ?? $invoice->status }}</span></td>
                        <td><span class="status-pill">{{ $invoice->jofotara_status ?: 'غير مرسلة' }}</span></td>
                        <td><strong>{{ $invoice->grand_total }}</strong> <small>{{ $invoice->currency }}</small></td>
                        <td class="text-center"><a class="action-icon" title="عرض" href="{{ route('company.invoices.show', [$company, $invoice]) }}">👁️</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">لا توجد فواتير مطابقة.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $invoices->links() }}</div>
</div>
@endsection
