@extends('layouts.app')
@section('title', 'إضافة جهات الاتصال')
@section('content')
<x-layout.page-header title="إضافة جهات الاتصال" />
<form method="post" action="{{ route('company.contacts.store', $company) }}" class="card card-body">@include('company.master-data.contacts._form')</form>
@endsection
