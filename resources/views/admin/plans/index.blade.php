@extends('layouts.app')
@section('title', 'الباقات')
@section('content')
<x-layout.page-header title="الباقات" subtitle="أساس بسيط للباقات بدون فوترة اشتراكات." />
<div class="row g-4"><div class="col-lg-5"><form method="post" action="{{ route('admin.plans.store') }}" class="card card-body">@csrf <h2 class="h5">إضافة باقة</h2>@include('admin.plans._form', ['plan' => $plan])</form></div><div class="col-lg-7"><div class="card"><table class="table mb-0"><tr><th>الباقة</th><th>السعر</th><th>الدورة</th><th>الحالة</th><th></th></tr>@foreach($plans as $plan)<tr><td>{{ $plan->name }}</td><td>{{ $plan->price }}</td><td>{{ $plan->billing_cycle }}</td><td>{{ $plan->is_active ? 'فعالة' : 'معطلة' }}</td><td><a href="{{ route('admin.plans.edit', $plan) }}">تعديل</a></td></tr>@endforeach</table></div>{{ $plans->links() }}</div></div>
@endsection
