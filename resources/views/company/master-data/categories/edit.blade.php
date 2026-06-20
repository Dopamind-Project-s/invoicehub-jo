@extends('layouts.app')
@section('title', 'تعديل تصنيفات المنتجات')
@section('content')
<x-layout.page-header title="تعديل تصنيفات المنتجات" />
<form method="post" action="{{ route('company.product-categories.update', [$company, $category]) }}" class="card card-body">@method('PUT') @include('company.master-data.categories._form')</form>
@endsection
