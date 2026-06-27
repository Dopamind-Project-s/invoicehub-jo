@extends('layouts.app')
@section('title', 'لوحة تحكم المنشأة')
@section('page_title', 'لوحة تحكم المنشأة')
@section('content')
@php
$stats = $stats ?? [];
$dashboardDate = static fn ($value, string $format = 'Y-m-d') => $value instanceof \Carbon\CarbonInterface ? $value->format($format) : ($value ?: '—');
@endphp
<style>
    .company-hero {
        background: linear-gradient(135deg, #00a9c4, #12c2b2);
        color: #fff;
        border-radius: 26px;
        padding: 24px;
        margin-bottom: 18px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .12)
    }

    .hero-badge {
        display: inline-flex;
        border-radius: 999px;
        padding: 6px 12px;
        background: #fff;
        color: #0f6170;
        font-weight: 800
    }

    .dashboard-card {
        border: 1px solid #e5eef4;
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .06);
        height: 100%
    }

    .metric-card {
        border: 1px solid #e5eef4;
        border-radius: 20px;
        background: #fff;
        padding: 18px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        height: 100%
    }

    .metric-card .icon {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        background: #eefcff;
        display: grid;
        place-items: center;
        font-size: 1.35rem
    }

    .metric-card .value {
        font-size: 1.45rem;
        font-weight: 900;
        color: #172033
    }

    .metric-card .label {
        color: #64748b;
        font-size: .9rem
    }

    .quick-actions .btn {
        border-radius: 999px;
        font-weight: 800
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 0;
        border-bottom: 1px dashed #e5eef4
    }

    .info-row:last-child {
        border-bottom: 0
    }

    .status-badge {
        display: inline-flex;
        border-radius: 999px;
        padding: 5px 10px;
        border: 1px solid #d7eef3;
        background: #f1f9fb;
        color: #0f6170;
        font-size: .8rem
    }

    .timeline-item {
        padding: 12px 0;
        border-bottom: 1px dashed #e5eef4
    }

    .timeline-item:last-child {
        border-bottom: 0
    }

    .empty-soft {
        border: 1px dashed #b7dce5;
        border-radius: 18px;
        background: #f8fdff;
        padding: 18px;
        text-align: center;
        color: #64748b
    }
</style>
<div class="company-hero" dir="rtl">
    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 align-items-xl-center">
        <div><span class="hero-badge mb-2">🏢 منشأة</span>
            <h1 class="h3 mb-2">{{ $company->name_ar ?: $company->legal_name_ar }}</h1>
            <p class="mb-0 opacity-75">لوحة تحكم احترافية بإحصائيات محفوظة مؤقتاً ومؤشرات تشغيلية للمنشأة.</p>
        </div>
        <div class="quick-actions d-flex flex-wrap gap-2"><a class="btn btn-light" href="{{ route('company.invoices.create', $company) }}">🧾 إنشاء فاتورة</a><a class="btn btn-outline-light" href="{{ route('company.products.create', $company) }}">📦 إضافة منتج</a><a class="btn btn-outline-light" href="{{ route('company.contacts.create', $company) }}">🤝 إضافة عميل</a><a class="btn btn-outline-light" href="{{ route('company.settings.edit', $company) }}">⚙️ إعدادات المنشأة</a></div>
    </div>
</div>
<div class="row g-3 mb-4" dir="rtl">
    @foreach([
    ['📦','عدد المنتجات',$stats['product_count'] ?? $productCount ?? 0],['🤝','عدد العملاء والموردين',$stats['contact_count'] ?? $contactCount ?? 0],['🧾','عدد الفواتير',$stats['invoice_count'] ?? $invoiceCount ?? 0],['📝','فواتير Draft',$stats['draft_invoices'] ?? 0],['✅','فواتير Ready',$stats['ready_invoices'] ?? $pendingInvoices ?? 0],['📡','فواتير Submitted',$stats['submitted_invoices'] ?? $approvedInvoices ?? 0],['⚠️','فواتير JoFotara ERROR',$stats['jofotara_error_invoices'] ?? 0],['💰','إجمالي المبيعات',number_format((float)($stats['sales_total'] ?? 0),3).' JOD'],['🧮','إجمالي الضريبة',number_format((float)($stats['tax_total'] ?? 0),3).' JOD']
    ] as $metric)
    <div class="col-md-2 col-xl-1">
        <div class="metric-card">
            <div class="d-flex align-items-center gap-3">
                <div class="icon">{{ $metric[0] }}</div>
                <div>
                    <div class="label">{{ $metric[1] }}</div>
                    <div class="value">{{ $metric[2] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="row g-4" dir="rtl">
    <div class="col-xl-5">
        <div class="dashboard-card card card-body">
            <h2 class="h5 mb-3">ملف المنشأة</h2>
            <div class="info-row"><span class="text-muted">اسم المنشأة</span><strong>{{ $company->name_ar ?: $company->legal_name_ar }}</strong></div>
            <div class="info-row"><span class="text-muted">الرقم الضريبي</span><strong>{{ $company->tax_number ?: '—' }}</strong></div>
            <div class="info-row"><span class="text-muted">مصدر الدخل</span><strong>{{ $company->economic_activity ?: '—' }}</strong></div>
            <div class="info-row"><span class="text-muted">الهاتف</span><strong>{{ $company->phone ?: '—' }}</strong></div>
            <div class="info-row"><span class="text-muted">العنوان</span><strong>{{ collect([$company->city, $company->street, $company->building_no])->filter()->join('، ') ?: '—' }}</strong></div>
            <div class="info-row"><span class="text-muted">حالة الربط مع جوفوتارا</span><span class="status-badge">{{ $company->hasJofotaraClientId() && $company->hasJofotaraSecretKey() && filled($company->jofotara_source_id) ? 'مكتمل' : 'غير مكتمل' }}</span></div>
            <div class="info-row"><span class="text-muted">الباقة الحالية</span><strong>{{ $company->activeSubscription?->plan?->name_ar ?: $company->activeSubscription?->plan?->name ?: '—' }}</strong></div>
            <h3 class="h6 mt-3">المزايا الفعالة</h3>
            <div class="d-flex gap-2 flex-wrap">@forelse($company->featureKeys as $feature)<span class="status-badge">{{ $feature->name_ar ?: $feature->code }}</span>@empty<span class="text-muted">لا توجد مزايا مفعلة.</span>@endforelse</div>
        </div>
    </div>
   

    <div class="col-xl-6">
        <div class="dashboard-card card card-body">
            <h2 class="h5 mb-3">آخر فاتورة مرسلة</h2>@if($stats['last_submitted_invoice'] ?? null)<div class="timeline-item"><strong>{{ data_get($stats, 'last_submitted_invoice.invoice_number', '—') }}</strong>
                <div class="text-muted small">{{ $dashboardDate(data_get($stats, 'last_submitted_invoice.jofotara_submitted_at'), 'Y-m-d H:i') }} — {{ data_get($stats, 'last_submitted_invoice.jofotara_status', '—') ?: '—' }}</div>
            </div>@else<div class="empty-soft">لا توجد فاتورة مرسلة بعد.</div>@endif
        </div>
    </div>
    <div class="col-12">
        <div class="dashboard-card card card-body">
            <h2 class="h5 mb-3">آخر 5 نشاطات</h2>@forelse(($stats['recent_activities'] ?? collect()) as $activity)<div class="timeline-item"><strong>{{ data_get($activity, 'action', '—') }}</strong>
                <div class="text-muted small">{{ $dashboardDate(data_get($activity, 'created_at'), 'Y-m-d H:i') }} — {{ data_get($activity, 'user_name') ?: data_get($activity, 'user.name', 'النظام') }}</div>
            </div>@empty<div class="empty-soft">لا توجد نشاطات حديثة.</div>@endforelse
        </div>
    </div>
</div>
@endsection