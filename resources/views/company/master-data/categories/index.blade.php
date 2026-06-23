@extends('layouts.app')
@section('title', 'فئات المنتجات')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<div class="category-hero d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3" dir="rtl">
    <div class="d-flex gap-3 align-items-center"><div class="category-icon bg-white text-info">🗂️</div><div><h1 class="h3 mb-2">فئات المنتجات</h1><p class="mb-0 opacity-75">استخدم الفئات لتنظيم المنتجات والخدمات.</p></div></div>
    <a class="btn btn-light btn-lg" href="{{ route('company.product-categories.create', ['company' => $routeCompanyId]) }}">➕ إضافة فئة جديدة</a>
</div>
<form class="category-filter card card-body mb-3" dir="rtl">
    <div class="row g-2 align-items-end">
        <div class="col-lg-7"><label class="form-label small text-muted">بحث</label><input name="search" class="form-control" placeholder="ابحث بالاسم أو الكود" value="{{ request('search') }}"></div>
        <div class="col-lg-3"><label class="form-label small text-muted">الحالة</label><select name="status" class="form-select"><option value="">كل الحالات</option><option value="active" @selected(request('status')==='active')>نشطة</option><option value="inactive" @selected(request('status')==='inactive')>غير نشطة</option></select></div>
        <div class="col-lg-2"><button class="btn btn-primary w-100">🔎 تصفية</button></div>
    </div>
</form>
@if($categories->isEmpty())
    <div class="empty-state" dir="rtl"><div class="empty-state-icon mb-3">🗂️</div><h2 class="h4">لا توجد فئات بعد</h2><p class="text-muted">ابدأ بإنشاء فئات لتنظيم المنتجات والخدمات وتسهيل إدارتها داخل الفواتير.</p><a class="btn btn-primary" href="{{ route('company.product-categories.create', ['company' => $routeCompanyId]) }}">➕ إضافة أول فئة</a></div>
@else
<div class="category-table-card card overflow-hidden" dir="rtl">
    <div class="table-responsive"><table class="table category-table mb-0 align-middle">
        <thead><tr><th>الأيقونة</th><th>الكود</th><th>الاسم العربي</th><th>الاسم الإنجليزي</th><th>الوصف</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
        <tbody>@foreach($categories as $category)<tr>
            <td><span class="category-icon">{{ $category->icon ?: '🗂️' }}</span></td>
            <td><code>{{ $category->code }}</code></td>
            <td><strong>{{ $category->name_ar }}</strong></td>
            <td>{{ $category->name_en ?: '—' }}</td>
            <td class="text-muted">{{ $category->description ? \Illuminate\Support\Str::limit($category->description, 70) : '—' }}</td>
            <td><span class="status-badge {{ $category->is_active ? '' : 'inactive' }}">{{ $category->is_active ? 'نشطة' : 'غير نشطة' }}</span></td>
            <td><div class="d-flex flex-wrap gap-1"><a class="btn btn-sm btn-outline-primary action-btn" href="{{ route('company.product-categories.edit', ['company' => $routeCompanyId, 'product_category' => $category->id]) }}">تعديل</a><form method="post" action="{{ $category->is_active ? route('company.product-categories.deactivate', ['company' => $routeCompanyId, 'product_category' => $category->id]) : route('company.product-categories.activate', ['company' => $routeCompanyId, 'product_category' => $category->id]) }}">@csrf<button class="btn btn-sm {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} action-btn">{{ $category->is_active ? 'تعطيل' : 'تفعيل' }}</button></form></div></td>
        </tr>@endforeach</tbody>
    </table></div>
</div><div class="mt-3">{{ $categories->links() }}</div>
@endif
@endsection
