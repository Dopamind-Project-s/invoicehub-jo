@extends('layouts.app')
@section('title', 'تعديل جهات الاتصال')
@section('content')
<x-layout.page-header title="تعديل جهات الاتصال" />
<form method="post" action="{{ route('company.contacts.update', [$company, $contact]) }}" class="card card-body">@method('PUT') @include('company.master-data.contacts._form')</form>
@endsection
