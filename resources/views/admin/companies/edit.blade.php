@extends('layouts.app')
@section('title', 'تعديل منشأة')
@section('page_title', 'تعديل منشأة')
@section('content')
<x-layout.page-header title="تعديل منشأة" subtitle="تحديث بيانات المنشأة ومفاتيح الميزات بدون عرض الأسرار." />
<form method="post" action="{{ route('admin.companies.update', $company) }}" enctype="multipart/form-data" class="card card-body">@method('PUT') @include('admin.companies._form')</form>
@endsection
