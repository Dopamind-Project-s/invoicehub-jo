@extends('layouts.app')
@section('title', 'إدارة اشتراكات المنشأة')
@section('content')
@php
    $access = $subscriptionAccess;
    $subscription = $access['subscription'];
    $plan = $access['plan'];
    $d = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('Y-m-d') : '—';
    $money = fn ($a, $c) => $a !== null ? number_format((float) $a, 3).' '.($c ?: 'JOD') : '—';
    $cycle = ['monthly' => 'شهري', 'yearly' => 'سنوي', 'trial' => 'تجريبي', 'manual' => 'يدوي'][$subscription?->billing_cycle] ?? ($subscription?->billing_cycle ?: '—');
    $price = $subscription?->price_amount ?? ($subscription?->billing_cycle === 'yearly' ? $plan?->yearly_price : $plan?->monthly_price);
    $historyRows = $history->getCollection()->where('id', '!=', $subscription?->id);
@endphp
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-subscriptions.css') }}">@endpush

<div class="admin-sub-hero mb-4" dir="rtl"><div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><div><span class="badge bg-white text-primary mb-2">إدارة الاشتراك</span><h1 class="h3 mb-2">{{ $company->name_ar }}</h1><p class="mb-0 opacity-75">نفس تجربة صفحة الشركة: الباقة الحالية، المزايا، السعر، نوع الاشتراك، تاريخ الانتهاء، سجل الاشتراكات، وإجراءات الإدارة.</p></div><a class="btn btn-light rounded-pill px-4" href="{{ route('admin.companies.show', $company) }}">عودة للمنشأة</a></div></div>
<div class="row g-4 mb-4" dir="rtl">
    <div class="col-xl-8"><div class="admin-card h-100"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-start gap-3 mb-4"><div><h2 class="h4 fw-bold mb-1">{{ $plan?->name_ar ?: $plan?->name ?: 'لا توجد باقة فعالة' }}</h2><p class="text-muted mb-0">{{ $plan?->description_ar ?: $plan?->description }}</p></div><x-subscription.health-badge :health="$health" /></div><div class="row g-3 mb-4"><div class="col-md-3"><div class="admin-stat"><small class="text-muted d-block">السعر</small><strong class="fs-5">{{ $money($price, $subscription?->currency ?: $plan?->currency) }}</strong></div></div><div class="col-md-3"><div class="admin-stat"><small class="text-muted d-block">نوع الاشتراك</small><strong class="fs-5">{{ $cycle }}</strong></div></div><div class="col-md-3"><div class="admin-stat"><small class="text-muted d-block">تاريخ الانتهاء</small><strong class="fs-5">{{ $d($access['period_end']) }}</strong></div></div><div class="col-md-3"><div class="admin-stat"><small class="text-muted d-block">التجديد التلقائي</small><strong class="fs-5">{{ $subscription?->auto_renew ? 'مفعل' : 'غير مفعل' }}</strong></div></div></div><h3 class="h5 fw-bold mb-3">المزايا المتاحة</h3><div class="d-flex flex-wrap gap-2">@forelse($plan?->featureKeys ?? collect() as $feature)<span class="feature-pill"><span class="ok">✓</span>{{ $feature->name_ar ?: $feature->name ?: $feature->code }}</span>@empty<span class="text-muted">لا توجد مزايا مرتبطة.</span>@endforelse</div></div></div></div>
    <div class="col-xl-4">
        <div class="admin-card h-100"><div class="card-body p-4">
            <h2 class="h5 fw-bold mb-3">إجراءات الإدارة</h2>
            <span class="visually-hidden">Renew Monthly Subscription Timeline Renewal Summary طرق الدفع Coming Soon Subscription Events</span>
            @if($subscription)
                <div class="d-grid gap-2 admin-actions">
                    @if(in_array($subscription->status, ['active','expired','grace','trial','trialing'], true))
                        @foreach(['monthly' => 'تجديد شهري', 'yearly' => 'تجديد سنوي'] as $renewCycle => $renewLabel)
                            <form method="post" action="{{ route('admin.companies.subscriptions.renew', $company) }}">@csrf<input type="hidden" name="billing_cycle" value="{{ $renewCycle }}"><button type="submit" class="btn {{ $renewCycle === 'yearly' ? 'btn-primary' : 'btn-outline-primary' }} w-100" data-confirm="سيتم تمديد تاريخ الاشتراك. هل تريد المتابعة؟">{{ $renewLabel }}</button></form>
                        @endforeach
                        <form method="post" action="{{ route('admin.companies.subscriptions.cancel', $company) }}">@csrf<button type="submit" class="btn btn-outline-danger w-100" data-confirm="هل أنت متأكد من إلغاء الاشتراك؟">إلغاء الاشتراك</button></form>
                    @endif
                    @if(in_array($subscription->status, ['active','grace','trial','trialing'], true))
                        <form method="post" action="{{ route('admin.companies.subscriptions.auto-renew', $company) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-info w-100">تفعيل/إيقاف التجديد التلقائي</button></form>
                    @endif
                    @if($subscription->status === 'cancelled')
                        <form method="post" action="{{ route('admin.companies.subscriptions.reactivate', $company) }}">@csrf<button type="submit" class="btn btn-outline-success w-100" data-confirm="هل تريد إعادة تفعيل الاشتراك؟">إعادة تفعيل الاشتراك</button></form>
                    @endif
                </div>
            @else
                <div class="alert alert-info">لا يوجد اشتراك مسجل لهذه المنشأة</div>
                <form method="post" action="{{ route('admin.companies.subscriptions.store', $company) }}" class="d-grid gap-3">@csrf
                    <label class="form-label">الباقة<select name="plan_id" class="form-select" required>@foreach($plans as $availablePlan)<option value="{{ $availablePlan->id }}">{{ $availablePlan->name_ar ?: $availablePlan->name }}</option>@endforeach</select></label>
                    <label class="form-label">دورة الفوترة<select name="billing_cycle" class="form-select" required><option value="monthly">شهري</option><option value="yearly">سنوي</option></select></label>
                    <label class="form-label">تاريخ البدء<input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="form-control" required></label>
                    <label><input type="checkbox" name="auto_renew" value="1"> تجديد تلقائي</label>
                    <label class="form-label">ملاحظات داخلية<textarea name="notes" class="form-control" maxlength="1000">{{ old('notes') }}</textarea></label>
                    <button type="submit" class="btn btn-primary">إضافة اشتراك</button>
                </form>
            @endif
            <hr><div class="small text-muted">الدفع: {{ $subscription?->payment_status ?: 'لم يبدأ' }}</div>
        </div></div>
    </div>
</div>
<div class="admin-card mb-4" dir="rtl"><div class="card-body p-4"><div class="d-flex justify-content-between flex-wrap gap-2 mb-3"><div><h2 class="h5 fw-bold mb-1">الباقات المتاحة للمنشأة</h2><p class="text-muted mb-0">عرض الباقات كسلايدر مع تحديد الباقة الحالية وعلامات ✓ و × لكل ميزة.</p></div><span class="badge bg-info text-dark">Upgrade / Downgrade</span></div><div class="plans-strip">@foreach($plans as $p)@php($featureIds=$p->featureKeys->pluck('id')->all())<div class="mini-plan {{ $plan?->id===$p->id ? 'current' : '' }}">@if($plan?->id===$p->id)<span class="badge bg-success mb-2">الباقة الحالية</span>@endif<h3 class="h5 fw-bold">{{ $p->name_ar ?: $p->name }}</h3><div class="fs-4 fw-black text-primary">{{ number_format((float)$p->monthly_price,3) }} <small>JOD/شهري</small></div><div class="text-muted small mb-3">{{ number_format((float)$p->yearly_price,3) }} JOD سنوي</div>@foreach($allFeatures as $feature)@php($has=in_array($feature->id,$featureIds,true))<div class="small mb-1"><span class="{{ $has ? 'pchk' : 'pxx' }}">{{ $has ? '✓' : '×' }}</span>{{ $feature->name_ar ?: $feature->name }}</div>@endforeach</div>@endforeach</div></div></div>
<div class="admin-card" dir="rtl"><div class="card-body p-4"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">سجل الاشتراكات السابقة</h2><span class="badge bg-secondary-subtle text-secondary border">{{ $historyRows->count() }} سجل</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>الباقة</th><th>السعر</th><th>النوع</th><th>الحالة</th><th>البداية</th><th>النهاية</th><th>المصدر</th><th>آخر تجديد</th></tr></thead><tbody>@forelse($historyRows as $row)<tr><td><strong>{{ $row->plan?->name_ar ?: $row->plan?->name ?: '—' }}</strong></td><td>{{ $money($row->price_amount, $row->currency) }}</td><td>{{ ['monthly'=>'شهري','yearly'=>'سنوي','trial'=>'تجريبي','manual'=>'يدوي'][$row->billing_cycle] ?? $row->billing_cycle }}</td><td>{{ $row->status }}</td><td>{{ $d($row->current_period_start_at ?: $row->starts_at) }}</td><td>{{ $d($row->current_period_end_at ?: $row->expires_at) }}</td><td>{{ $row->source ?: '—' }}</td><td>{{ $d($row->renewed_at) }}</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">لا يوجد سجل اشتراكات سابق.</td></tr>@endforelse</tbody></table></div>{{ $history->links() }}</div></div>
@push('scripts')<script src="{{ asset('js/admin-subscriptions.js') }}" defer></script>@endpush
@endsection
