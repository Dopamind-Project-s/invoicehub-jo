@extends('layouts.app')
@section('content')
<h1 dir="rtl">تعديل المنشأة</h1>
<form method="post" action="{{ route('companies.update', $company) }}" class="card card-body">@csrf @method('PUT') @include('companies._form')</form>
@endsection
