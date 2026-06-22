@extends('layouts.app')
@section('title', 'إضافة فئة جديدة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header title="إضافة فئة جديدة" subtitle="أنشئ فئة لتنظيم المنتجات والخدمات داخل الفواتير." />
<form method="post" action="{{ route('company.product-categories.store', ['company' => $routeCompanyId]) }}" data-category-form>@include('company.master-data.categories._form', ['mode' => 'create'])</form>
@endsection
