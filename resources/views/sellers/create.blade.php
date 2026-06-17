@extends('layouts.app')
@section('content')
<h1>بائع جديد</h1>
<form method="post" action="{{ route('sellers.store') }}" enctype="multipart/form-data" class="card card-body">
    @include('sellers._form')
</form>
@endsection
