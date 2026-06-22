@extends('layouts.app')
@section('title', 'إضافة وحدة جديدة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header title="إضافة وحدة جديدة" subtitle="عرّف وحدة قياس لاستخدامها في المنتجات والخدمات." />
<form method="post" action="{{ route('company.units.store', ['company' => $routeCompanyId]) }}" data-unit-form>@include('company.master-data.units._form', ['mode' => 'create'])</form>
@endsection
