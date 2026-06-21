@extends('layouts.app')
@section('title', 'إضافة منتج/خدمة')
@section('content')
<x-layout.page-header title="إضافة منتج/خدمة" />
<form method="post" action="{{ route('company.products.store', $company) }}" class="card card-body">@include('company.master-data.products._form')</form>
@endsection
