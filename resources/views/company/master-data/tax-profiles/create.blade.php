@extends('layouts.app')
@section('title', 'إضافة ضريبة جديدة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header title="إضافة ضريبة جديدة" subtitle="عرّف نسبة ونوع الضريبة وكود جوفوتارا عند الحاجة." />
<form method="post" action="{{ route('company.tax-profiles.store', ['company' => $routeCompanyId]) }}" data-tax-form>@include('company.master-data.tax-profiles._form', ['mode' => 'create'])</form>
@endsection
