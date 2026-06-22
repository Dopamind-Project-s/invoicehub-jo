@extends('layouts.company-workspace')
@section('title', 'فاتورة جديدة')
@section('content')
<x-layout.page-header title="➕ فاتورة جديدة" subtitle="تصميم الصفحة يتبع ألوان قالب الفاتورة الافتراضي للمنشأة." />
<form method="post" action="{{ route('company.invoices.store', $company) }}">@include('company.invoices._form')</form>
@endsection
