@extends('layouts.app')
@section('title', 'تعديل منتج/خدمة')
@section('content')
<x-layout.page-header title="تعديل منتج/خدمة" />
<form method="post" action="{{ route('company.products.update', [$company, $product]) }}" class="card card-body">@method('PUT') @include('company.master-data.products._form')</form>
@endsection
