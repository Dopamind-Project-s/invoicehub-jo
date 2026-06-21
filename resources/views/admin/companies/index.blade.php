@extends('layouts.app')
@section('title', 'إدارة المنشآت')
@section('page_title', 'إدارة المنشآت')
@section('content')
<x-layout.page-header title="إدارة المنشآت" subtitle="إنشاء وتعديل وتفعيل وتعليق شركات النظام.">
    <x-slot:actions><a class="btn btn-primary" href="{{ route('admin.companies.create') }}">منشأة جديدة</a></x-slot:actions>
</x-layout.page-header>
<div class="card"><div class="table-responsive"><table class="table mb-0 align-middle">
    <tr><th>المنشأة</th><th>الرقم الضريبي</th><th>الهاتف</th><th>الحالة</th><th>الميزات</th><th></th></tr>
    @foreach($companies as $company)
        <tr><td>{{ $company->name_ar ?: $company->legal_name_ar }}</td><td>{{ $company->tax_number }}</td><td>{{ $company->phone }}</td><td><span class="badge {{ $company->isSuspended() ? 'bg-warning' : 'bg-success' }}">{{ $company->isSuspended() ? 'معلقة' : 'نشطة' }}</span></td><td>{{ $company->feature_keys_count }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.companies.show', $company) }}">عرض</a></td></tr>
    @endforeach
</table></div></div>
{{ $companies->links() }}
@endsection
