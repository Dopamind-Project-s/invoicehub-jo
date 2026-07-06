@extends('layouts.app')
@section('title','تعديل مستخدم')
@section('content')
<x-layout.page-header :title="$user->name" subtitle="تعديل بيانات المستخدم وأدواره." />
<form method="post" action="{{ route('admin.users.update',$user) }}" class="card card-body">@method('PUT') @include('admin.users._form')</form>
@endsection
