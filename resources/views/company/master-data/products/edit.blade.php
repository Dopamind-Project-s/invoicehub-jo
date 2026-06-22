@extends('layouts.app')
@section('title', 'تعديل منتج / خدمة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header :title="'تعديل: '.$product->name_ar" :subtitle="'الحالة: '.($product->is_active ? 'نشط' : 'غير نشط').' — آخر تحديث: '.($product->updated_at?->format('Y-m-d H:i') ?: '—')" />
<form method="post" enctype="multipart/form-data" action="{{ route('company.products.update', ['company' => $routeCompanyId, 'product' => $product->id]) }}" data-product-form>@method('PUT') @include('company.master-data.products._form', ['mode' => 'edit'])</form>
<form method="post" class="mt-3" action="{{ $product->is_active ? route('company.products.deactivate', ['company' => $routeCompanyId, 'product' => $product->id]) : route('company.products.activate', ['company' => $routeCompanyId, 'product' => $product->id]) }}">@csrf<button class="btn {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $product->is_active ? 'تعطيل المنتج' : 'تفعيل المنتج' }}</button></form>
@endsection
