@props(['plan'])
@php($features = $plan->relationLoaded('featureKeys') ? $plan->featureKeys : collect())
<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm h-100 position-relative overflow-hidden']) }}>
    @if($plan->is_recommended)<div class="position-absolute top-0 end-0 m-3"><span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle">موصى بها</span></div>@endif
    <div class="card-body p-4 d-flex flex-column gap-3">
        <div class="pe-5"><div class="d-flex align-items-center gap-2 mb-2"><h3 class="h5 fw-bold mb-0">{{ $plan->name_ar ?: $plan->name }}</h3><span class="badge rounded-pill {{ $plan->is_active ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle' }} border">{{ $plan->is_active ? 'فعالة' : 'معطلة' }}</span></div><p class="text-muted mb-0 small">{{ $plan->description_ar ?: $plan->description ?: 'باقة اشتراك قابلة للتخصيص حسب مزايا المنشأة.' }}</p></div>
        <div class="row g-2 text-center"><div class="col-6"><div class="bg-light rounded-4 p-3"><div class="small text-muted">شهرياً</div><strong>{{ number_format((float) $plan->monthly_price, 3) }} {{ $plan->currency ?: 'JOD' }}</strong></div></div><div class="col-6"><div class="bg-light rounded-4 p-3"><div class="small text-muted">سنوياً</div><strong>{{ number_format((float) $plan->yearly_price, 3) }} {{ $plan->currency ?: 'JOD' }}</strong></div></div></div>
        <div class="d-flex flex-wrap gap-2"><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $plan->billing_cycle ?: 'monthly' }}</span><span class="badge bg-cyan-subtle text-primary border">{{ $plan->feature_keys_count ?? $features->count() }} ميزة</span></div>
        <div><div class="small fw-semibold mb-2">معاينة المزايا</div><div class="d-flex flex-wrap gap-2">@forelse($features->take(4) as $feature)<span class="badge bg-light text-dark border">{{ $feature->name_ar ?: $feature->code }}</span>@empty<span class="text-muted small">لم يتم ربط مزايا بعد.</span>@endforelse @if($features->count() > 4)<span class="badge bg-light text-muted border">+{{ $features->count() - 4 }}</span>@endif</div></div>
        <div class="mt-auto pt-2"><a class="btn btn-outline-primary w-100" href="{{ route('admin.plans.edit', $plan) }}">تعديل الباقة</a></div>
    </div>
</div>
