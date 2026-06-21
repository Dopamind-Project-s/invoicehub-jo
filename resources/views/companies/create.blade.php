@extends('layouts.app')
@section('content')
<h1 dir="rtl">منشأة جديدة</h1>
<form method="post" action="{{ route('companies.store') }}" class="card card-body">@csrf @include('companies._form')</form>
@endsection
