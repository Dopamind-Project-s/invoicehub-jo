@extends('layouts.app')
@section('content')
<div dir="rtl">
    <div class="d-flex justify-content-between mb-3"><h1>المنشآت</h1><a class="btn btn-primary" href="{{ route('companies.create') }}">منشأة جديدة</a></div>
    <div class="card"><table class="table mb-0"><tr><th>الاسم</th><th>الرقم الضريبي</th><th>مصدر الدخل</th><th>الهاتف</th><th>نشطة</th><th></th></tr>
    @foreach($companies as $company)<tr><td>{{ $company->legal_name_ar }}</td><td>{{ $company->tax_number }}</td><td>{{ $company->jofotara_source_id }}</td><td>{{ $company->phone }}</td><td>{{ $company->is_active ? 'نعم' : 'لا' }}</td><td><a href="{{ route('companies.edit', $company) }}">تعديل</a></td></tr>@endforeach
    </table></div>{{ $companies->links() }}
</div>
@endsection
