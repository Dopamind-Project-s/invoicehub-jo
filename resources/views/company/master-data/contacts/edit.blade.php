@extends('layouts.app')
@section('title', 'تعديل عميل / مورد')
@section('content')
@php($routeCompanyId = $company->id ?? request()->route('company')?->id ?? request()->route('company') ?? auth()->user()?->company_id)
<x-layout.page-header :title="'تعديل: '.$contact->name_ar" :subtitle="'الحالة: '.($contact->is_active ? 'نشط' : 'غير نشط').' — آخر تحديث: '.($contact->updated_at?->format('Y-m-d H:i') ?: '—')" />
<form method="post" action="{{ route('company.contacts.update', ['company' => $routeCompanyId, 'contact' => $contact->id]) }}" data-contact-form>@method('PUT') @include('company.master-data.contacts._form', ['mode' => 'edit'])</form>
<form method="post" class="mt-3" action="{{ $contact->is_active ? route('company.contacts.deactivate', ['company' => $routeCompanyId, 'contact' => $contact->id]) : route('company.contacts.activate', ['company' => $routeCompanyId, 'contact' => $contact->id]) }}">@csrf<button class="btn {{ $contact->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill">{{ $contact->is_active ? 'تعطيل جهة الاتصال' : 'تفعيل جهة الاتصال' }}</button></form>
@endsection
