@extends('layouts.app') @section('content')<h1>عميل جديد</h1><form method="post" action="{{ route('customers.store') }}" class="card card-body">@include('customers._form')</form>@endsection
