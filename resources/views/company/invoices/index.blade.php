@extends('layouts.company-workspace')
@section('title', 'الفواتير')
@section('content')
@php
    $primary = $branding['primary_color'] ?? '#00a9c4';
    $secondary = $branding['secondary_color'] ?? '#12c2b2';
    $statusLabels = ['draft'=>'مسودة','ready'=>'جاهزة','submitted'=>'مرسلة','cancelled'=>'ملغاة','pending'=>'مراجعة','approved'=>'معتمدة'];
@endphp
<style>
.invoice-shell{--invoice-primary:{{ $primary }};--invoice-secondary:{{ $secondary }}}.invoice-hero{background:linear-gradient(135deg,var(--invoice-primary),var(--invoice-secondary));color:#fff;border-radius:24px;padding:24px;margin-bottom:20px;box-shadow:0 18px 42px rgba(15,23,42,.12)}.invoice-hero .btn{border-radius:999px}.invoice-filter,.invoice-list-card{border:1px solid #e5eef4;border-radius:22px;box-shadow:0 12px 30px rgba(15,23,42,.06)}.invoice-filter .form-control,.invoice-filter .form-select{border-radius:14px}.invoice-table{vertical-align:middle}.invoice-table thead th{background:#f7fbfc;color:#475569;border-bottom:1px solid #e5eef4}.invoice-number{font-weight:800;color:#172033}.status-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:5px 10px;background:#eef9fb;color:#0f6170;border:1px solid #d7eef3;font-size:.82rem}.status-pill.draft{background:#fff8e6;color:#946200;border-color:#ffe2a8}.status-pill.ready{background:#e8f7ff;color:#075985;border-color:#bde7ff}.status-pill.submitted{background:#eafaf1;color:#166534;border-color:#bbf7d0}.status-pill.cancelled{background:#fff1f2;color:#9f1239;border-color:#fecdd3}.action-icon{width:34px;height:34px;display:inline-grid;place-items:center;border-radius:12px;text-decoration:none;background:#f1f9fb;color:#0f6170;border:1px solid #d7eef3}.action-icon:hover{background:var(--invoice-primary);color:#fff;border-color:var(--invoice-primary)}
</style>
<div class="invoice-shell">
    <div class="invoice-hero d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <h1 class="h3 mb-2">🧾 الفواتير</h1>
            <p class="mb-0 opacity-75">إدارة الفواتير، حالة الإرسال، وتنزيل النسخ المطابقة للقالب الافتراضي.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(! app()->environment('production'))<a class="btn btn-light" href="{{ route('company.invoices.jofotara.uat', $company) }}">🧪 فحص الربط</a>@endif
            @if($company->featureKeys->contains('code', 'JOFOTARA_SYNC'))<a class="btn btn-outline-light" href="{{ route('company.invoices.import.index', $company) }}">⬇️ استيراد</a>@endif
            <a class="btn btn-light" href="{{ route('company.invoices.create', $company) }}">➕ فاتورة جديدة</a>
        </div>
    </div>

    <form class="invoice-filter card card-body mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-lg-3"><label class="form-label small text-muted">بحث</label><input name="search" class="form-control" placeholder="رقم الفاتورة أو العميل" value="{{ request('search') }}"></div>
            <div class="col-lg-2"><label class="form-label small text-muted">الحالة</label><select name="status" class="form-select"><option value="">كل الحالات</option>@foreach(['draft'=>'مسودة','ready'=>'جاهزة للإرسال','submitted'=>'مرسلة للفوترة الوطنية','cancelled'=>'ملغاة'] as $value => $label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-2"><label class="form-label small text-muted">المصدر</label><select name="source" class="form-select"><option value="">كل المصادر</option><option value="local" @selected(request('source')==='local')>محلي</option><option value="jofotara_import" @selected(request('source')==='jofotara_import')>استيراد جوفوتارا</option></select></div>
            <div class="col-lg-3"><label class="form-label small text-muted">النوع</label><select name="invoice_type" class="form-select"><option value="">كل الأنواع</option>@foreach(['tax_invoice'=>'فاتورة ضريبية','simplified_invoice'=>'فاتورة مبسطة','credit_note'=>'إشعار دائن','debit_note'=>'إشعار مدين'] as $value => $label)<option value="{{ $value }}" @selected(request('invoice_type')===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-lg-2"><button class="btn btn-primary w-100">🔎 تصفية</button></div>
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
