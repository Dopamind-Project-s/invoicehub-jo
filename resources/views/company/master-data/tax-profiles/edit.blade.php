@extends('layouts.app')
@section('title', 'تعديل ملفات الضريبة')
@section('content')
<x-layout.page-header title="تعديل ملفات الضريبة" />
<form method="post" action="{{ route('company.tax-profiles.update', [$company, $taxProfile]) }}" class="card card-body">@method('PUT') @include('company.master-data.tax-profiles._form')</form>
@endsection
