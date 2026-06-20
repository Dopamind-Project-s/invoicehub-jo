@extends('layouts.app')
@section('title', 'تعديل الوحدات')
@section('content')
<x-layout.page-header title="تعديل الوحدات" />
<form method="post" action="{{ route('company.units.update', [$company, $unit]) }}" class="card card-body">@method('PUT') @include('company.master-data.units._form')</form>
@endsection
