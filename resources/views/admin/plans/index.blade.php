@extends('layouts.app')
@section('title', 'الباقات')
@section('content')
<x-layout.page-header title="الباقات" subtitle="أساس واضح للباقات والأسعار ومفاتيح المزايا بدون فوترة اشتراكات." />
<div class="row g-4">
    <div class="col-lg-5"><form method="post" action="{{ route('admin.plans.store') }}" class="card card-body"><h2 class="h5">إضافة باقة</h2>@include('admin.plans._form', ['plan' => $plan, 'features' => $features, 'enabledFeatureIds' => []])</form></div>
    <div class="col-lg-7">
        <div class="card"><div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th>الباقة</th><th>شهري</th><th>سنوي</th><th>المزايا</th><th>الحالة</th><th></th></tr></thead><tbody>@foreach($plans as $plan)<tr><td><strong>{{ $plan->name }}</strong><br><small class="text-muted">{{ $plan->description }}</small></td><td>{{ number_format((float) $plan->monthly_price, 3) }}</td><td>{{ number_format((float) $plan->yearly_price, 3) }}</td><td>{{ $plan->feature_keys_count }}</td><td>{{ $plan->is_active ? 'فعالة' : 'معطلة' }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.plans.edit', $plan) }}">تعديل</a></td></tr>@endforeach</tbody></table></div></div>{{ $plans->links() }}
    </div>
</div>
@endsection
