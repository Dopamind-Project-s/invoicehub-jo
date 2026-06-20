@extends('layouts.app')
@section('title', 'إضافة الوحدات')
@section('content')
<x-layout.page-header title="إضافة الوحدات" />
<form method="post" action="{{ route('company.units.store', $company) }}" class="card card-body">@include('company.master-data.units._form')</form>
@endsection
