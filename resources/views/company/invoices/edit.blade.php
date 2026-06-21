@extends('layouts.company-workspace')
@section('title', 'تعديل فاتورة')
@section('content')
<x-layout.page-header :title="'تعديل '.$invoice->invoice_number" subtitle="يمكن تعديل المسودات والفواتير الجاهزة قبل الإرسال." />
<form method="post" action="{{ route('company.invoices.update', [$company, $invoice]) }}" class="card card-body">@method('PUT') @include('company.invoices._form')</form>
@endsection
