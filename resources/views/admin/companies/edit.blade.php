@extends('layouts.app')
@section('title', 'تعديل شركة')
@section('page_title', 'تعديل شركة')
@section('content')
<x-layout.page-header title="تعديل شركة" subtitle="تحديث بيانات الشركة ومفاتيح الميزات بدون عرض الأسرار." />
<form method="post" action="{{ route('admin.companies.update', $company) }}" enctype="multipart/form-data" class="card card-body">@method('PUT') @include('admin.companies._form')</form>
@endsection
