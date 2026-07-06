@extends('layouts.app')
@section('title','تعديل مقال')
@push('styles')<link rel="stylesheet" href="{{ asset('css/blog.css') }}">@endpush
@section('content')<x-layout.page-header :title="$post->title_ar" subtitle="تحديث المقال وبيانات SEO." /><form method="post" enctype="multipart/form-data" action="{{ route('admin.blogs.update',$post) }}" class="card card-body">@method('PUT') @include('admin.blogs._form')</form>@endsection
