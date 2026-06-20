@extends('layouts.app')
@section('title', 'لوحة تحكم المدير العام')
@section('page_title', 'لوحة تحكم المدير العام')
@section('content')
<x-layout.page-header title="لوحة تحكم المدير العام" subtitle="إدارة المنشآت والمزايا والباقات والفواتير من مكان واحد."><x-slot:actions><a class="btn btn-primary" href="{{ route('admin.companies.create') }}">إضافة منشأة</a><a class="btn btn-outline-primary" href="{{ route('admin.companies.index') }}">عرض المنشآت</a></x-slot:actions></x-layout.page-header>
<div class="row g-3 mb-4">
<div class="col-md-4 col-xl-2"><x-ui.stat-card label="عدد المنشآت" :value="$totalCompanies" icon="🏢" /></div>
<div class="col-md-4 col-xl-2"><x-ui.stat-card label="المنشآت الفعالة" :value="$activeCompanies" icon="✅" /></div>
<div class="col-md-4 col-xl-2"><x-ui.stat-card label="المنشآت المعطلة" :value="$suspendedCompanies" icon="⏸" /></div>
<div class="col-md-4 col-xl-2"><x-ui.stat-card label="عدد المستخدمين" :value="$userCount" icon="👥" /></div>
<div class="col-md-4 col-xl-2"><x-ui.stat-card label="عدد الفواتير" :value="$invoiceCount" icon="🧾" /></div>
<div class="col-md-4 col-xl-2"><x-ui.stat-card label="عدد المنتجات" :value="$productCount" icon="📦" /></div>
</div>
<div class="card card-body mb-4"><h2 class="h5">إجراءات سريعة</h2><div class="d-flex gap-2 flex-wrap"><a class="btn btn-primary" href="{{ route('admin.companies.create') }}">إضافة منشأة</a><a class="btn btn-outline-primary" href="{{ route('admin.companies.index') }}">إدارة المنشآت</a><a class="btn btn-outline-secondary" href="{{ route('admin.feature-keys.index') }}">إدارة مفاتيح المزايا</a><a class="btn btn-outline-secondary" href="{{ route('admin.plans.index') }}">إدارة الباقات</a></div></div>
<div class="row g-4"><div class="col-lg-4"><div class="card card-body h-100"><h2 class="h5">آخر المنشآت المسجلة</h2>@forelse($recentCompanies as $company)<a class="list-group-item" href="{{ route('admin.companies.show', $company) }}">{{ $company->name_ar ?: $company->legal_name_ar }}<br><small class="text-muted">{{ $company->created_at?->format('Y-m-d') }}</small></a>@empty<p class="text-muted">لا توجد منشآت.</p>@endforelse</div></div><div class="col-lg-4"><div class="card card-body h-100"><h2 class="h5">آخر الفواتير</h2>@forelse($recentInvoices as $invoice)<div class="list-group-item">{{ $invoice->invoice_number }}<br><small class="text-muted">{{ $invoice->company?->name_ar ?: $invoice->company?->legal_name_ar }} — {{ $invoice->status }}</small></div>@empty<p class="text-muted">لا توجد فواتير.</p>@endforelse</div></div><div class="col-lg-4"><div class="card card-body h-100"><h2 class="h5">آخر نشاطات النظام</h2>@forelse($recentAudits as $audit)<div class="list-group-item"><strong>{{ $audit->action }}</strong><br><small class="text-muted">{{ $audit->created_at?->format('Y-m-d H:i') }}</small></div>@empty<p class="text-muted">لا توجد نشاطات.</p>@endforelse</div></div></div>
@endsection
