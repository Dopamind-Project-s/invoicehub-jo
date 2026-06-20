@extends('layouts.app')
@section('title', 'إضافة ملفات الضريبة')
@section('content')
<x-layout.page-header title="إضافة ملفات الضريبة" />
<form method="post" action="{{ route('company.tax-profiles.store', $company) }}" class="card card-body">@include('company.master-data.tax-profiles._form')</form>
@endsection
