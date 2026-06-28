@extends('layouts.app')
@section('title', 'الباقات')
@section('content')
<x-layout.page-header title="الباقات" subtitle="إدارة احترافية لباقات الاشتراك والأسعار ومفاتيح المزايا.">
    <x-slot:actions><a class="btn btn-primary px-4" href="#create-plan">+ إنشاء باقة</a></x-slot:actions>
</x-layout.page-header>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-lg-5"><label class="form-label">بحث</label><input name="search" value="{{ request('search') }}" class="form-control" placeholder="اسم الباقة أو الوصف"></div>
            <div class="col-md-3"><label class="form-label">الحالة</label><select name="status" class="form-select"><option value="">كل الحالات</option><option value="active" @selected(request('status')==='active')>فعالة</option><option value="inactive" @selected(request('status')==='inactive')>معطلة</option></select></div>
            <div class="col-md-2"><label class="form-label">التمييز</label><select name="recommended" class="form-select"><option value="">الكل</option><option value="1" @selected(request('recommended')==='1')>موصى بها</option></select></div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-primary">تصفية</button></div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-4" id="create-plan">
        <form method="post" action="{{ route('admin.plans.store') }}" class="card border-0 shadow-sm card-body sticky-lg-top" style="top:1rem">
            <div class="d-flex align-items-center justify-content-between mb-3"><div><h2 class="h5 fw-bold mb-1">إنشاء باقة</h2><p class="text-muted small mb-0">أضف الأسعار والمزايا المرتبطة.</p></div><span class="badge bg-primary-subtle text-primary border border-primary-subtle">جديدة</span></div>
            @include('admin.plans._form', ['plan' => $plan, 'features' => $features, 'enabledFeatureIds' => []])
        </form>
    </div>
    <div class="col-xl-8">
        @if($plans->count())
            <div class="row g-4">@foreach($plans as $plan)<div class="col-md-6"><x-plan-card :plan="$plan" /></div>@endforeach</div>
            <div class="mt-4">{{ $plans->links() }}</div>
        @else
            <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><div class="display-5 mb-3">📦</div><h2 class="h5 fw-bold">لا توجد باقات مطابقة</h2><p class="text-muted">جرّب تعديل البحث أو أنشئ باقة جديدة للبدء.</p><a class="btn btn-primary" href="#create-plan">إنشاء باقة</a></div></div>
        @endif
    </div>
</div>
@endsection
