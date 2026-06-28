@extends('layouts.app')
@section('title', 'إدارة اشتراكات المنشأة')
@section('content')
@php
    $access = $subscriptionAccess;
    $subscription = $access['subscription'];
    $plan = $access['plan'];
    $d = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '—';
    $money = fn ($a, $c) => $a !== null ? number_format((float) $a, 3).' '.($c ?: 'JOD') : '—';
@endphp
<x-layout.page-header :title="'اشتراكات '.$company->name_ar" subtitle="مركز اشتراكات SaaS جاهز للدفع الإلكتروني لاحقاً.">
    <x-slot:actions><a class="btn btn-outline-primary" href="{{ route('admin.companies.show', $company) }}">عودة للمنشأة</a></x-slot:actions>
</x-layout.page-header>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100"><div class="card-body p-4">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3"><div><h2 class="h5 fw-bold mb-1">الاشتراك الحالي</h2><p class="text-muted mb-0">الحالة، الفترة، التجديد، ومؤشرات الدفع المستقبلية.</p></div><x-subscription.health-badge :health="$health" /></div>
            <div class="row g-3">
                @foreach(['الباقة'=>$plan?->name_ar ?: $plan?->name, 'دورة الفوترة'=>$subscription?->billing_cycle, 'الحالة'=>$access['effective_status'], 'بداية الفترة'=>$d($access['period_start']), 'نهاية الفترة'=>$d($access['period_end']), 'نهاية السماح'=>$d($access['grace_end']), 'الأيام المتبقية'=>$access['days_remaining'] ?? '—', 'Auto Renew'=>$subscription?->auto_renew ? 'مفعل' : 'غير مفعل', 'المصدر'=>$subscription?->source, 'Renewal Source'=>$subscription?->renewal_source ?: '—', 'Payment Provider'=>$subscription?->payment_provider ?: 'Placeholder', 'Payment Reference'=>$subscription?->payment_reference ?: '—', 'Payment Status'=>$subscription?->payment_status ?: 'not_started', 'السعر'=>$money($subscription?->price_amount, $subscription?->currency)] as $label => $value)
                    <div class="col-md-6"><x-info-row :label="$label" :value="$value ?: '—'" /></div>
                @endforeach
            </div>
        </div></div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">Subscription Timeline</h2><x-subscription.timeline :items="$timeline" /></div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">Renewal Summary</h2><x-info-row label="آخر عملية" :value="$renewalSummary['label']"/><x-info-row label="التاريخ" :value="$renewalSummary['date']"/><x-info-row label="النوع" :value="$renewalSummary['type']"/><x-info-row label="المصدر" :value="$renewalSummary['source']"/><x-info-row label="بواسطة" :value="$renewalSummary['actor']"/></div></div></div>
    <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">إجراءات التجديد</h2><div class="d-grid gap-2">
        <form method="post" action="{{ route('admin.companies.subscriptions.renew', [$company, 'monthly']) }}">@csrf<button class="btn btn-outline-primary w-100">Renew Monthly</button></form>
        <form method="post" action="{{ route('admin.companies.subscriptions.renew', [$company, 'yearly']) }}">@csrf<button class="btn btn-primary w-100">Renew Yearly</button></form>
        <form method="post" action="{{ route('admin.companies.subscriptions.toggle-auto-renew', $company) }}">@csrf<button class="btn btn-outline-info w-100">Toggle Auto Renew</button></form>
        <form method="post" action="{{ route('admin.companies.subscriptions.cancel', $company) }}">@csrf<button class="btn btn-outline-danger w-100">Cancel Subscription</button></form>
        <form method="post" action="{{ route('admin.companies.subscriptions.reactivate', $company) }}">@csrf<button class="btn btn-outline-success w-100">Reactivate Subscription</button></form>
    </div></div></div></div>
    <div class="col-xl-4"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">طرق الدفع</h2><span class="badge bg-secondary">Coming Soon</span></div><div class="d-flex flex-wrap gap-2">@foreach($paymentMethods as $method)<span class="payment-pill">{{ $method }}</span>@endforeach</div><p class="text-muted small mt-3 mb-0">لا يوجد ربط بوابات دفع حالياً؛ الحقول جاهزة للربط لاحقاً.</p></div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-5"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">Upgrade / Downgrade Preview</h2><select class="form-select mb-3" disabled><option>اختر باقة للمعاينة القادمة</option>@foreach($plans as $p)<option>{{ $p->name_ar ?: $p->name }}</option>@endforeach</select><div class="row g-2">@foreach(['الباقة الحالية'=>$changePreview['current_plan'], 'الباقة الجديدة'=>$changePreview['new_plan'], 'السعر الحالي'=>$changePreview['current_price'], 'السعر الجديد'=>$changePreview['new_price'], 'نوع الاشتراك'=>$changePreview['billing_cycle'], 'طريقة التنفيذ'=>$changePreview['effective_mode']] as $label=>$value)<div class="col-md-6"><div class="preview-chip"><small class="text-muted d-block">{{ $label }}</small><strong>{{ $value }}</strong></div></div>@endforeach</div><hr><div class="row"><div class="col-6"><h3 class="h6">مزايا جديدة</h3>@forelse($changePreview['new_features'] as $f)<span class="badge bg-success-subtle text-success border mb-1">{{ $f }}</span>@empty<span class="text-muted small">—</span>@endforelse</div><div class="col-6"><h3 class="h6">مزايا ستفقد</h3>@forelse($changePreview['lost_features'] as $f)<span class="badge bg-danger-subtle text-danger border mb-1">{{ $f }}</span>@empty<span class="text-muted small">—</span>@endforelse</div></div><p class="text-muted small mt-3 mb-0">المعاينة فقط؛ لا يتم تنفيذ تغيير باقة من هذه الواجهة الآن.</p></div></div></div>
    <div class="col-xl-7"><div class="card border-0 shadow-sm h-100"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">Subscription Events</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Event</th><th>Source</th><th>Actor</th><th>Date</th></tr></thead><tbody>@forelse($events as $event)<tr><td>{{ str($event->event_type)->headline() }}</td><td>{{ $event->source }}</td><td>{{ $event->actor?->name ?: '—' }}</td><td>{{ $d($event->occurred_at) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">لا توجد أحداث اشتراك بعد.</td></tr>@endforelse</tbody></table></div></div></div></div>
</div>

<div class="card border-0 shadow-sm"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">سجل الاشتراكات</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>plan</th><th>billing cycle</th><th>status</th><th>period start</th><th>period end</th><th>grace end</th><th>source</th><th>renewed at</th><th>created at</th></tr></thead><tbody>@forelse($history as $row)<tr><td>{{ $row->plan?->name_ar ?: $row->plan?->name }}</td><td>{{ $row->billing_cycle }}</td><td>{{ $row->status }}</td><td>{{ $d($row->current_period_start_at) }}</td><td>{{ $d($row->current_period_end_at) }}</td><td>{{ $d($row->grace_ends_at) }}</td><td>{{ $row->source }}</td><td>{{ $d($row->renewed_at) }}</td><td>{{ $d($row->created_at) }}</td></tr>@empty<tr><td colspan="9" class="text-center text-muted py-4">لا يوجد سجل اشتراكات.</td></tr>@endforelse</tbody></table></div>{{ $history->links() }}</div></div>
@endsection
