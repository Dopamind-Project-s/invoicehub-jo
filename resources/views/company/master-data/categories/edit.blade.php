@extends('layouts.app')
@section('title', 'تعديل فئة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header :title="'تعديل: '.$category->name_ar" :subtitle="'الحالة: '.($category->is_active ? 'نشطة' : 'غير نشطة').' — آخر تحديث: '.($category->updated_at?->format('Y-m-d H:i') ?: '—')" />
<form method="post" action="{{ route('company.product-categories.update', ['company' => $routeCompanyId, 'product_category' => $category->id]) }}" data-category-form>@method('PUT') @include('company.master-data.categories._form', ['mode' => 'edit'])</form>
@endsection
