@extends('layouts.app')
@section('title', 'إضافة منتج / خدمة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header title="إضافة منتج / خدمة" subtitle="أدخل بيانات المنتج أو الخدمة التي ستظهر في الفواتير." />
<form method="post" enctype="multipart/form-data" action="{{ route('company.products.store', ['company' => $routeCompanyId]) }}" data-product-form>@include('company.master-data.products._form', ['mode' => 'create'])</form>
@endsection
