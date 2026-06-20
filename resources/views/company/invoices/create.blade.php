@extends('layouts.company-workspace')
@section('title', 'فاتورة جديدة')
@section('content')
<x-layout.page-header title="فاتورة جديدة" subtitle="احفظ كمسودة أو جهّز الفاتورة للإرسال إلى نظام الفوترة الوطني." />
<form method="post" action="{{ route('company.invoices.store', $company) }}" class="card card-body">@include('company.invoices._form')</form>
@endsection
