@extends('layouts.app')
@section('title','تعديل دور')
@section('content')
<x-layout.page-header :title="$role->name" subtitle="تعديل صلاحيات الدور." />
<form method="post" action="{{ route('admin.roles.update',$role) }}" class="card card-body">@method('PUT') @include('admin.roles._form')</form>
@endsection
