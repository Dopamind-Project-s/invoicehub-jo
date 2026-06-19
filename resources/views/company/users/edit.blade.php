@extends('layouts.app')
@section('title', 'تعديل مستخدم')
@section('content')
<x-layout.page-header title="تعديل مستخدم" subtitle="تعديل بيانات المستخدم وأدواره." />
<form method="post" action="{{ route('company.users.update', [$company, $user]) }}" class="card card-body">@method('PUT') @include('company.users._form')</form>
@endsection
