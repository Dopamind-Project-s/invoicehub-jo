@extends('layouts.app')
@section('title', 'مستخدم جديد')
@section('content')
<x-layout.page-header title="مستخدم جديد" subtitle="إضافة مستخدم داخل نطاق الشركة." />
<form method="post" action="{{ route('company.users.store', $company) }}" class="card card-body">@include('company.users._form')</form>
@endsection
