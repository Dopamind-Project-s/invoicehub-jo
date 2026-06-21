@extends('layouts.app')
@section('title', 'تعديل باقة')
@section('content')
<x-layout.page-header title="تعديل باقة" />
<form method="post" action="{{ route('admin.plans.update', $plan) }}" class="card card-body">@csrf @method('PUT') @include('admin.plans._form')</form>
@endsection
