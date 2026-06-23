@extends('layouts.app')
@section('title', 'تعديل سؤال شائع')
@section('page_title', 'الموقع الإلكتروني')
@section('content')
<x-layout.page-header title="تعديل سؤال شائع" />
<form method="post" action="{{ route('admin.landing-cms.faqs.update', $faq) }}" class="card card-body">@method('PUT') @include('admin.landing-cms.faqs._form')</form>
@endsection
