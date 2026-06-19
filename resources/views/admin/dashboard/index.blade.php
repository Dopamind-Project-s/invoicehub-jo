@extends('layouts.app')
@section('title', 'لوحة تحكم المدير العام')
@section('page_title', 'لوحة تحكم المدير العام')
@section('content')
<x-layout.page-header title="لوحة تحكم المدير العام" subtitle="مؤشرات تأسيسية لإدارة شركات SaaS بدون تغيير تدفق جوفوتارا الحالي." />

<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2"><x-ui.stat-card label="إجمالي الشركات" :value="$totalCompanies" icon="🏢" /></div>
    <div class="col-md-4 col-xl-2"><x-ui.stat-card label="الشركات النشطة" :value="$activeCompanies" icon="✅" /></div>
    <div class="col-md-4 col-xl-2"><x-ui.stat-card label="الشركات المعلقة" :value="$suspendedCompanies" icon="⏸" /></div>
    <div class="col-md-4 col-xl-2"><x-ui.stat-card label="تستخدم جوفوتارا" :value="$jofotaraCompanies" icon="🔐" /></div>
    <div class="col-md-4 col-xl-2"><x-ui.stat-card label="فواتير مرسلة" :value="$invoicesSubmitted" icon="🧾" /></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h5 mb-3">آخر نشاطات التدقيق</h2>
            <div class="list-group list-group-flush">
                @forelse($recentAudits as $audit)
                    <div class="list-group-item bg-transparent px-0">
                        <strong>{{ $audit->action }}</strong>
                        <div class="text-muted small">{{ $audit->created_at?->format('Y-m-d H:i') }} — {{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</div>
                    </div>
                @empty
                    <div class="text-muted">لا توجد نشاطات تدقيق بعد.</div>
                @endforelse
            </div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h5 mb-3">آخر الشركات المسجلة</h2>
            <div class="list-group list-group-flush">
                @forelse($recentCompanies as $company)
                    <a class="list-group-item list-group-item-action bg-transparent px-0" href="{{ route('admin.companies.show', $company) }}">
                        <strong>{{ $company->name_ar ?: $company->legal_name_ar }}</strong>
                        <div class="text-muted small">{{ $company->created_at?->format('Y-m-d') }} — {{ $company->status }}</div>
                    </a>
                @empty
                    <div class="text-muted">لا توجد شركات بعد.</div>
                @endforelse
            </div>
        </div></div>
    </div>
</div>
@endsection
