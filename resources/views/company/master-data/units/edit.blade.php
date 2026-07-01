@extends('layouts.app')
@section('title', 'تعديل وحدة')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header :title="'تعديل وحدة: '.($unit->name_ar ?: $unit->code)" :subtitle="($unit->company_id ? 'وحدة خاصة بالمنشأة' : 'وحدة عامة').' — آخر تحديث: '.($unit->updated_at?->format('Y-m-d H:i') ?: '—')" />
<form method="post" action="{{ route('company.units.update', ['company' => $routeCompanyId, 'unit' => $unit->id]) }}" data-unit-form>@method('PUT') @include('company.master-data.units._form', ['mode' => 'edit'])</form>
@endsection
