@extends('layouts.app')
@section('title', 'إضافة تصنيفات المنتجات')
@section('content')
<x-layout.page-header title="إضافة تصنيفات المنتجات" />
<form method="post" action="{{ route('company.product-categories.store', $company) }}" class="card card-body">@include('company.master-data.categories._form')</form>
@endsection
