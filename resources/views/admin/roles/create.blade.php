@extends('layouts.app')
@section('title','دور جديد')
@section('content')
<x-layout.page-header title="دور جديد" subtitle="إنشاء دور وربطه بصلاحيات Spatie." />
<form method="post" action="{{ route('admin.roles.store') }}" class="card card-body">@include('admin.roles._form')</form>
@endsection
