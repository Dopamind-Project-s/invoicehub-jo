@extends('layouts.app')
@section('title','مقال جديد')
@push('styles')<link rel="stylesheet" href="{{ asset('css/blog.css') }}">@endpush
@section('content')<x-layout.page-header title="مقال جديد" subtitle="إنشاء محتوى عربي احترافي للمدونة." /><form method="post" enctype="multipart/form-data" action="{{ route('admin.blogs.store') }}" class="card card-body">@include('admin.blogs._form')</form>@endsection
