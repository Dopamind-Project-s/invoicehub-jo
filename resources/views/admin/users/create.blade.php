@extends('layouts.app')
@section('title','مستخدم جديد')
@section('content')
<x-layout.page-header title="مستخدم جديد" subtitle="إنشاء مستخدم وربطه بالأدوار والصلاحيات." />
<form method="post" action="{{ route('admin.users.store') }}" class="card card-body">@include('admin.users._form')</form>
@endsection
