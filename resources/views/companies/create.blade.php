@extends('layouts.app')
@section('content')
<h1 dir="rtl">شركة جديدة</h1>
<form method="post" action="{{ route('companies.store') }}" class="card card-body">@csrf @include('companies._form')</form>
@endsection
