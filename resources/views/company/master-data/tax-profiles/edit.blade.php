@extends('layouts.app')
@section('title', 'تعديل ضريبة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header :title="'تعديل ضريبة: '.$taxProfile->name" :subtitle="'آخر تحديث: '.($taxProfile->updated_at?->format('Y-m-d H:i') ?: '—')" />
<form method="post" action="{{ route('company.tax-profiles.update', ['company' => $routeCompanyId, 'tax_profile' => $taxProfile->id]) }}" data-tax-form>@method('PUT') @include('company.master-data.tax-profiles._form', ['mode' => 'edit'])</form>
@endsection
