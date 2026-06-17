@extends('layouts.app') @section('content')<h1>فاتورة جديدة</h1><form method="post" action="{{ route('invoices.store') }}" class="card card-body">@include('invoices._form')</form>@endsection
