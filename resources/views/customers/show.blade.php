@extends('layouts.app') @section('content')<div class="card card-body"><h1>{{ $customer->name }}</h1><p>{{ $customer->tax_number }}</p><p>{{ $customer->address }}</p></div>@endsection
