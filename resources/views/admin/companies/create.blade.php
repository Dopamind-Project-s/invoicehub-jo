@extends('layouts.app')
@section('title', 'شركة جديدة')
@section('page_title', 'شركة جديدة')
@section('content')
<x-layout.page-header title="شركة جديدة" subtitle="إضافة شركة SaaS جديدة مع ميزاتها الأساسية." />
<form method="post" action="{{ route('admin.companies.store') }}" enctype="multipart/form-data" class="card card-body">@include('admin.companies._form')</form>
@endsection
