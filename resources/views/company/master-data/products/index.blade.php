@extends('layouts.app')
@section('title', 'المنتجات والخدمات')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<div class="product-hero d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
    <div><h1 class="h3 mb-2">المنتجات والخدمات</h1><p class="mb-0 opacity-75">أضف المنتجات أو الخدمات التي ستظهر في الفواتير.</p></div>
    <a class="btn btn-light btn-lg" href="{{ route('company.products.create', ['company' => $routeCompanyId]) }}">➕ إضافة منتج / خدمة جديدة</a>
</div>
<form class="product-filter card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-lg-5"><label class="form-label small text-muted">بحث</label><input name="search" class="form-control" placeholder="ابحث بالاسم أو SKU أو الباركود" value="{{ request('search') }}"></div>
        <div class="col-lg-2"><label class="form-label small text-muted">النوع</label><select name="type" class="form-select"><option value="">كل الأنواع</option><option value="product" @selected(request('type')==='product')>منتج</option><option value="service" @selected(request('type')==='service')>خدمة</option></select></div>
        <div class="col-lg-2"><label class="form-label small text-muted">الحالة</label><select name="status" class="form-select"><option value="">كل الحالات</option><option value="active" @selected(request('status')==='active')>نشط</option><option value="inactive" @selected(request('status')==='inactive')>غير نشط</option></select></div>
        <div class="col-lg-3"><button class="btn btn-primary w-100">🔎 تصفية المنتجات</button></div>
    </div>
</form>
@if($products->isEmpty())
    <div class="empty-state"><h2 class="h4">لا توجد منتجات بعد</h2><p class="text-muted">ابدأ بإضافة أول منتج أو خدمة لاستخدامها داخل الفواتير.</p><a class="btn btn-primary" href="{{ route('company.products.create', ['company' => $routeCompanyId]) }}">➕ إضافة أول منتج</a></div>
@else
<div class="product-table-card card overflow-hidden">
    <div class="table-responsive"><table class="table product-table mb-0">
        <thead><tr><th>الصورة</th><th>الاسم العربي</th><th>الاسم الإنجليزي</th><th>النوع</th><th>SKU</th><th>التصنيف</th><th>الوحدة</th><th>الضريبة</th><th>السعر</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
        <tbody>@foreach($products as $product)<tr>
            <td>@if($product->image_path)<img class="product-thumb" src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name_ar }}">@else<span class="product-thumb">{{ mb_substr($product->name_ar,0,1) }}</span>@endif</td>
            <td><strong>{{ $product->name_ar }}</strong><div class="text-muted small">{{ $product->barcode ?: 'بدون باركود' }}</div></td>
            <td>{{ $product->name_en ?: '—' }}</td>
            <td>{{ $product->type === 'service' ? 'خدمة' : 'منتج' }}</td>
            <td><code>{{ $product->sku }}</code></td>
            <td>{{ $product->category?->name_ar ?: '—' }}</td>
            <td>{{ $product->unit?->name_ar ?: $product->unit?->code }}</td>
            <td>{{ $product->taxProfile?->name ?: '—' }}</td>
            <td><strong>{{ $product->price }}</strong></td>
            <td><span class="status-badge {{ $product->is_active ? '' : 'inactive' }}">{{ $product->is_active ? 'نشط' : 'غير نشط' }}</span></td>
            <td><div class="d-flex flex-wrap gap-1"><a class="btn btn-sm btn-outline-secondary action-btn" href="{{ route('company.products.edit', ['company' => $routeCompanyId, 'product' => $product->id]) }}">عرض</a><a class="btn btn-sm btn-outline-primary action-btn" href="{{ route('company.products.edit', ['company' => $routeCompanyId, 'product' => $product->id]) }}">تعديل</a><form method="post" action="{{ $product->is_active ? route('company.products.deactivate', ['company' => $routeCompanyId, 'product' => $product->id]) : route('company.products.activate', ['company' => $routeCompanyId, 'product' => $product->id]) }}">@csrf<button class="btn btn-sm {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} action-btn">{{ $product->is_active ? 'تعطيل' : 'تفعيل' }}</button></form></div></td>
        </tr>@endforeach</tbody>
    </table></div>
</div><div class="mt-3">{{ $products->links() }}</div>
@endif
@endsection
