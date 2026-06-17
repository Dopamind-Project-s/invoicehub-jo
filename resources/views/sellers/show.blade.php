@extends('layouts.app')
@section('content')
<div class="card card-body">
    <h1>{{ $seller->name }}</h1>
    <p>{{ $seller->tax_number }}</p>
    <p>{{ $seller->address }}</p>
</div>
@endsection
