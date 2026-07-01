@extends('layouts.app')
@section('title', 'منشأة جديدة')
@section('page_title', 'منشأة جديدة')
@section('content')
<x-layout.page-header title="منشأة جديدة" subtitle="إضافة منشأة SaaS جديدة مع ميزاتها الأساسية." />
<form method="post" action="{{ route('admin.companies.store') }}" enctype="multipart/form-data" class="card card-body">@include('admin.companies._form')</form>
@endsection