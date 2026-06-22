@extends('layouts.app')
@section('title', 'إضافة عميل / مورد')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header title="إضافة عميل / مورد" subtitle="أضف بيانات جهة الاتصال لاستخدامها مباشرة في الفواتير." />
<form method="post" action="{{ route('company.contacts.store', ['company' => $routeCompanyId]) }}" data-contact-form>@include('company.master-data.contacts._form', ['mode' => 'create'])</form>
@endsection
